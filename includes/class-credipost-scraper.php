<?php
/**
 * Safe, robust web scraper service for Credipost.
 *
 * @package    Credipost
 * @subpackage Credipost/includes
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WPINC' ) ) {
	exit;
}

class Credipost_Scraper {

	/**
	 * Target URL being scraped.
	 *
	 * @var string
	 */
	private $url = '';

	/**
	 * Parsed URL components.
	 *
	 * @var array
	 */
	private $url_parts = array();

	/**
	 * Constructor.
	 *
	 * @param string $url The target URL.
	 */
	public function __construct( $url = '' ) {
		if ( ! empty( $url ) ) {
			$this->set_url( $url );
		}
	}

	/**
	 * Set and validate the target URL.
	 *
	 * @param string $url
	 * @return bool
	 */
	public function set_url( $url ) {
		$url = trim( $url );

		// Prepend scheme if missing.
		if ( ! preg_match( '#^https?://#i', $url ) ) {
			$url = 'https://' . $url;
		}

		if ( function_exists( 'esc_url_raw' ) ) {
			$url = esc_url_raw( $url );
		}

		if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			return false;
		}

		$this->url       = $url;
		$this->url_parts = wp_parse_url( $url );

		return true;
	}

	/**
	 * Scrape the URL and return structured data.
	 *
	 * @return array|WP_Error Array with title, content, image, source_url or WP_Error on failure.
	 */
	public function scrape() {
		if ( empty( $this->url ) ) {
			return new WP_Error( 'empty_url', __( 'No valid URL was provided.', 'credipost' ) );
		}

		$html = $this->fetch_html( $this->url );
		if ( is_wp_error( $html ) ) {
			return $html;
		}

		if ( empty( $html ) ) {
			return new WP_Error( 'empty_body', __( 'The remote server returned an empty page.', 'credipost' ) );
		}

		return $this->parse_html( $html );
	}

	/**
	 * Safe remote fetch using WordPress HTTP API.
	 *
	 * @param string $url
	 * @return string|WP_Error
	 */
	private function fetch_html( $url ) {
		if ( ! function_exists( 'wp_safe_remote_get' ) ) {
			return new WP_Error( 'missing_wp_http', __( 'WordPress HTTP API is not available.', 'credipost' ) );
		}

		$args = array(
			'timeout'     => 15,
			'redirection' => 5,
			'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36 (Credipost/2.0)',
			'headers'     => array(
				'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
				'Accept-Language' => 'en-US,en;q=0.5',
			),
			'sslverify'   => true,
		);

		$response = wp_safe_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error(
				'fetch_failed',
				sprintf(
					/* translators: %s: error message */
					__( 'Could not connect to URL: %s', 'credipost' ),
					$response->get_error_message()
				)
			);
		}

		$code = wp_remote_retrieve_response_code( $response );
		if ( $code < 200 || $code >= 400 ) {
			return new WP_Error(
				'http_error',
				sprintf(
					/* translators: %d: HTTP status code */
					__( 'Remote server returned HTTP status code %d.', 'credipost' ),
					$code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		return $body;
	}

	/**
	 * Parse HTML using DOMDocument and extract title, featured image, and content.
	 *
	 * @param string $html
	 * @return array
	 */
	private function parse_html( $html ) {
		// Ensure UTF-8 meta tag if missing to prevent encoding corruption in libxml.
		if ( false === stripos( $html, 'charset=' ) && false === stripos( $html, '<meta charset' ) ) {
			$html = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8">' . $html;
		}

		$prev_libxml_errors = libxml_use_internal_errors( true );
		$dom                = new DOMDocument( '1.0', 'UTF-8' );

		// Load HTML safely.
		if ( defined( 'LIBXML_HTML_NOIMPLIED' ) && defined( 'LIBXML_HTML_NODEFDTD' ) ) {
			$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD );
		} else {
			$dom->loadHTML( mb_convert_encoding( $html, 'HTML-ENTITIES', 'UTF-8' ) );
		}
		libxml_clear_errors();
		libxml_use_internal_errors( $prev_libxml_errors );

		$xpath = new DOMXPath( $dom );

		// 1. Extract Title
		$title = '';
		$og_title = $xpath->query( '//meta[@property="og:title"]/@content' );
		if ( $og_title && $og_title->length > 0 ) {
			$title = trim( $og_title->item( 0 )->nodeValue );
		}
		if ( empty( $title ) ) {
			$title_tag = $xpath->query( '//title' );
			if ( $title_tag && $title_tag->length > 0 ) {
				$title = trim( $title_tag->item( 0 )->nodeValue );
			}
		}
		if ( empty( $title ) ) {
			$h1 = $xpath->query( '//h1' );
			if ( $h1 && $h1->length > 0 ) {
				$title = trim( $h1->item( 0 )->nodeValue );
			}
		}

		// 2. Extract Featured Image (og:image)
		$image = '';
		$og_image = $xpath->query( '//meta[@property="og:image"]/@content' );
		if ( $og_image && $og_image->length > 0 ) {
			$image = $this->resolve_url( trim( $og_image->item( 0 )->nodeValue ) );
		}

		// 3. Remove unwanted noise tags from DOM
		$noise_query = '//script | //style | //noscript | //header | //footer | //nav | //aside | //form | //svg | //iframe | //button | //input | //select | //textarea | //template | //*[contains(@class, "sidebar")] | //*[contains(@class, "comment")] | //*[contains(@class, "social-share")] | //*[contains(@class, "advertisement")]';
		$noise_nodes = $xpath->query( $noise_query );
		if ( $noise_nodes ) {
			// Iterate in reverse to avoid DOM mutation indexing issues.
			for ( $i = $noise_nodes->length - 1; $i >= 0; $i-- ) {
				$node = $noise_nodes->item( $i );
				if ( $node && $node->parentNode ) {
					$node->parentNode->removeChild( $node );
				}
			}
		}

		// 4. Find the main content container
		$content_candidates = array(
			'//article',
			'//main',
			'//*[@role="main"]',
			'//*[contains(@class, "post-content")]',
			'//*[contains(@class, "entry-content")]',
			'//*[contains(@class, "article-body")]',
			'//*[contains(@class, "content-area")]',
			'//*[@id="content"]',
			'//body',
		);

		$content_node = null;
		foreach ( $content_candidates as $query ) {
			$candidates = $xpath->query( $query );
			if ( $candidates && $candidates->length > 0 ) {
				// Pick candidate with reasonable text length.
				foreach ( $candidates as $candidate ) {
					if ( strlen( trim( $candidate->textContent ) ) > 100 ) {
						$content_node = $candidate;
						break 2;
					}
				}
			}
		}

		if ( ! $content_node ) {
			$content_node = $dom->getElementsByTagName( 'body' )->item( 0 );
		}

		// 5. Clean up nodes within the container and generate sanitized blocks
		$blocks_html = '';
		if ( $content_node ) {
			// Resolve all relative images and links.
			$imgs = $xpath->query( './/img', $content_node );
			if ( $imgs ) {
				foreach ( $imgs as $img ) {
					$src = $img->getAttribute( 'src' );
					if ( empty( $src ) && $img->hasAttribute( 'data-src' ) ) {
						$src = $img->getAttribute( 'data-src' );
					}
					if ( ! empty( $src ) ) {
						$img->setAttribute( 'src', $this->resolve_url( $src ) );
					}
					// Remove inline styles/scripts.
					$img->removeAttribute( 'style' );
					$img->removeAttribute( 'onclick' );
					$img->removeAttribute( 'onload' );
					$img->removeAttribute( 'onerror' );
				}
			}

			$links = $xpath->query( './/a', $content_node );
			if ( $links ) {
				foreach ( $links as $link ) {
					$href = $link->getAttribute( 'href' );
					if ( ! empty( $href ) ) {
						$link->setAttribute( 'href', $this->resolve_url( $href ) );
						$link->setAttribute( 'target', '_blank' );
						$link->setAttribute( 'rel', 'noopener noreferrer' );
					}
					$link->removeAttribute( 'style' );
					$link->removeAttribute( 'onclick' );
				}
			}

			// Clean remaining allowed block elements.
			$allowed_tags = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'blockquote', 'ul', 'ol', 'li', 'figure', 'img' );
			$child_nodes  = $content_node->childNodes;

			$blocks_html = $this->extract_clean_blocks( $content_node, $dom, $allowed_tags );
		}

		// If no featured image found, check first image in extracted content.
		if ( empty( $image ) && ! empty( $blocks_html ) ) {
			if ( preg_match( '/<img[^>]+src=["\']([^"\']+)["\']/i', $blocks_html, $match ) ) {
				$image = $match[1];
			}
		}

		return array(
			'title'      => wp_strip_all_tags( $title ),
			'content'    => $blocks_html,
			'image'      => $image,
			'source_url' => $this->url,
		);
	}

	/**
	 * Extract clean block elements from a container node.
	 *
	 * @param DOMNode     $container
	 * @param DOMDocument $dom
	 * @param array       $allowed_tags
	 * @return string
	 */
	private function extract_clean_blocks( $container, $dom, $allowed_tags ) {
		$output = '';

		// Query key semantic blocks.
		$xpath  = new DOMXPath( $dom );
		$blocks = $xpath->query( './/h1 | .//h2 | .//h3 | .//h4 | .//h5 | .//h6 | .//p | .//blockquote | .//ul | .//ol | .//figure | .//img[not(ancestor::figure) and not(ancestor::p)]', $container );

		if ( ! $blocks || 0 === $blocks->length ) {
			// Fallback: render innerHTML of container.
			$raw = '';
			foreach ( $container->childNodes as $child ) {
				$raw .= $dom->saveHTML( $child );
			}
			return function_exists( 'wp_kses_post' ) ? wp_kses_post( $raw ) : strip_tags( $raw, '<p><h1><h2><h3><h4><h5><h6><ul><ol><li><blockquote><img><a>' );
		}

		$seen_texts = array();

		foreach ( $blocks as $block ) {
			$tag = strtolower( $block->nodeName );

			// Skip if inside another selected block to avoid duplicate content (e.g. li inside ul).
			if ( in_array( $tag, array( 'li', 'p' ), true ) ) {
				$parent_tag = strtolower( $block->parentNode ? $block->parentNode->nodeName : '' );
				if ( in_array( $parent_tag, array( 'ul', 'ol', 'blockquote' ), true ) ) {
					continue;
				}
			}

			// Clean attributes from block node.
			$this->clean_attributes( $block );

			$text_content = trim( $block->textContent );

			// Check for empty text (unless it's an image or figure).
			if ( 'img' !== $tag && 'figure' !== $tag && empty( $text_content ) ) {
				continue;
			}

			// Skip duplicate paragraphs.
			if ( ! empty( $text_content ) && strlen( $text_content ) > 30 ) {
				$hash = md5( $text_content );
				if ( isset( $seen_texts[ $hash ] ) ) {
					continue;
				}
				$seen_texts[ $hash ] = true;
			}

			$rendered = $dom->saveHTML( $block );
			if ( ! empty( $rendered ) ) {
				$output .= trim( $rendered ) . "\n";
			}
		}

		if ( function_exists( 'wp_kses_post' ) ) {
			$output = wp_kses_post( $output );
		}

		return $output;
	}

	/**
	 * Remove hazardous attributes from DOM element and its descendants.
	 *
	 * @param DOMNode $element
	 */
	private function clean_attributes( $element ) {
		if ( ! $element instanceof DOMElement ) {
			return;
		}

		$allowed_attributes = array( 'href', 'src', 'alt', 'title', 'target', 'rel', 'class' );

		// Remove non-whitelisted attributes.
		if ( $element->hasAttributes() ) {
			$attrs_to_remove = array();
			foreach ( $element->attributes as $attr ) {
				$name = strtolower( $attr->name );
				if ( ! in_array( $name, $allowed_attributes, true ) || strpos( $name, 'on' ) === 0 ) {
					$attrs_to_remove[] = $attr->name;
				}
			}
			foreach ( $attrs_to_remove as $attr_name ) {
				$element->removeAttribute( $attr_name );
			}
		}

		if ( $element->hasChildNodes() ) {
			foreach ( $element->childNodes as $child ) {
				$this->clean_attributes( $child );
			}
		}
	}

	/**
	 * Resolve relative URL to absolute URL using the base URL.
	 *
	 * @param string $relative_url
	 * @return string
	 */
	public function resolve_url( $relative_url ) {
		$relative_url = trim( $relative_url );

		if ( empty( $relative_url ) ) {
			return '';
		}

		// Already absolute?
		if ( preg_match( '#^https?://#i', $relative_url ) ) {
			return $relative_url;
		}

		// Protocol-relative?
		if ( strpos( $relative_url, '//' ) === 0 ) {
			$scheme = ! empty( $this->url_parts['scheme'] ) ? $this->url_parts['scheme'] : 'https';
			return $scheme . ':' . $relative_url;
		}

		$host = ! empty( $this->url_parts['host'] ) ? $this->url_parts['host'] : '';
		if ( empty( $host ) ) {
			return $relative_url;
		}

		$scheme = ! empty( $this->url_parts['scheme'] ) ? $this->url_parts['scheme'] : 'https';
		$port   = ! empty( $this->url_parts['port'] ) ? ':' . $this->url_parts['port'] : '';
		$base   = $scheme . '://' . $host . $port;

		// Root-relative?
		if ( strpos( $relative_url, '/' ) === 0 ) {
			return $base . $relative_url;
		}

		// Path-relative.
		$path = ! empty( $this->url_parts['path'] ) ? $this->url_parts['path'] : '/';
		$dir  = preg_replace( '#/[^/]*$#', '', $path );

		return $base . $dir . '/' . $relative_url;
	}
}
