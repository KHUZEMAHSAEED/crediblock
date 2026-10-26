<?php
/**
 * Admin view template for Credipost Curation.
 *
 * @package    Credipost
 * @subpackage Credipost/admin/partials
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WPINC' ) ) {
	exit;
}
?>

<div class="wrap credipost-admin-wrap">
	<h1 class="wp-heading-inline">
		<span class="dashicons dashicons-media-document" style="font-size: 28px; width: 28px; height: 28px; vertical-align: middle; margin-right: 6px;"></span>
		<?php esc_html_e( 'Credipost Content Curation', 'credipost' ); ?>
	</h1>
	<hr class="wp-header-end">

	<!-- Notice / Feedback Area -->
	<div id="credipost-notice" class="notice notice-alt" style="display: none;">
		<p></p>
	</div>

	<!-- Step 1: Input URL Card -->
	<div class="credipost-card credipost-input-card">
		<h2><?php esc_html_e( 'Fetch & Curate Content', 'credipost' ); ?></h2>
		<p class="description">
			<?php esc_html_e( 'Enter the URL of any web article to extract its content, or paste a video link (YouTube, Vimeo, etc.) to generate a responsive embed.', 'credipost' ); ?>
		</p>

		<div class="credipost-input-row">
			<div class="credipost-input-field-wrap">
				<input
					type="url"
					id="credipost-url-input"
					class="large-text"
					placeholder="<?php esc_attr_e( 'https://example.com/article-to-curate', 'credipost' ); ?>"
					required
					autocomplete="off"
				/>
			</div>
			<div class="credipost-btn-group">
				<button type="button" id="credipost-btn-fetch" class="button button-primary">
					<span class="dashicons dashicons-download" style="vertical-align: middle; margin-top: -2px;"></span>
					<?php esc_html_e( 'Fetch Article', 'credipost' ); ?>
				</button>
				<button type="button" id="credipost-btn-embed" class="button button-secondary">
					<span class="dashicons dashicons-video-alt3" style="vertical-align: middle; margin-top: -2px;"></span>
					<?php esc_html_e( 'Get Video Embed', 'credipost' ); ?>
				</button>
				<span id="credipost-spinner" class="spinner" style="float: none; margin: 6px 0 0 10px;"></span>
			</div>
		</div>
	</div>

	<!-- Step 2: Curation & Editor Workspace (Hidden until fetched) -->
	<div id="credipost-workspace" class="credipost-workspace" style="display: none;">

		<!-- Meta & Publish Bar -->
		<div class="credipost-card credipost-publish-bar">
			<div class="credipost-meta-grid">

				<!-- Editable Title -->
				<div class="credipost-meta-field credipost-col-wide">
					<label for="credipost-post-title"><strong><?php esc_html_e( 'Post Title', 'credipost' ); ?></strong></label>
					<input type="text" id="credipost-post-title" class="large-text" placeholder="<?php esc_attr_e( 'Enter custom post title...', 'credipost' ); ?>" />
				</div>

				<!-- Post Status -->
				<div class="credipost-meta-field">
					<label for="credipost-post-status"><strong><?php esc_html_e( 'Status', 'credipost' ); ?></strong></label>
					<select id="credipost-post-status">
						<option value="draft" selected><?php esc_html_e( 'Draft (Recommended)', 'credipost' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending Review', 'credipost' ); ?></option>
						<option value="publish"><?php esc_html_e( 'Publish Immediately', 'credipost' ); ?></option>
					</select>
				</div>

				<!-- Category -->
				<div class="credipost-meta-field">
					<label for="credipost-category-select"><strong><?php esc_html_e( 'Category', 'credipost' ); ?></strong></label>
					<select id="credipost-category-select">
						<option value="0"><?php esc_html_e( '— Select Category —', 'credipost' ); ?></option>
						<?php foreach ( $categories as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->term_id ); ?>">
								<?php echo esc_html( $cat->name ); ?>
							</option>
						<?php endforeach; ?>
					</select>
				</div>

				<!-- Multisite Destination (if applicable) -->
				<?php if ( is_multisite() && ! empty( $sites ) ) : ?>
					<div class="credipost-meta-field">
						<label for="credipost-site-select"><strong><?php esc_html_e( 'Target Site', 'credipost' ); ?></strong></label>
						<select id="credipost-site-select">
							<option value="0"><?php esc_html_e( 'Current Site', 'credipost' ); ?></option>
							<?php foreach ( $sites as $subsite ) : ?>
								<?php
								$details = get_blog_details( $subsite->blog_id );
								$name    = $details ? $details->blogname : 'Site #' . $subsite->blog_id;
								?>
								<option value="<?php echo esc_attr( $subsite->blog_id ); ?>">
									<?php echo esc_html( $name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>

			</div>

			<!-- Action Buttons -->
			<div class="credipost-publish-actions">
				<button type="button" id="credipost-btn-save" class="button button-primary button-large">
					<span class="dashicons dashicons-saved" style="vertical-align: middle; margin-top: -2px;"></span>
					<?php esc_html_e( 'Save as Post', 'credipost' ); ?>
				</button>
				<button type="button" id="credipost-btn-print" class="button button-secondary button-large">
					<span class="dashicons dashicons-printer" style="vertical-align: middle; margin-top: -2px;"></span>
					<?php esc_html_e( 'Print Article', 'credipost' ); ?>
				</button>
				<button type="button" id="credipost-btn-clear" class="button button-link-delete button-large" style="margin-left: auto;">
					<?php esc_html_e( 'Clear & Reset', 'credipost' ); ?>
				</button>
				<span id="credipost-save-spinner" class="spinner" style="float: none; margin: 8px 0 0 10px;"></span>
			</div>
		</div>

		<!-- Pruning Instructions -->
		<div class="credipost-instruction-badge">
			<span class="dashicons dashicons-info"></span>
			<span><?php esc_html_e( 'Editorial Pruning: Hover over any paragraph, heading, or image and click the red cross icon (×) to remove unwanted blocks prior to publishing.', 'credipost' ); ?></span>
		</div>

		<!-- Undo Toast Bar -->
		<div id="credipost-undo-bar" class="credipost-undo-bar" style="display: none;">
			<span id="credipost-undo-text"><?php esc_html_e( 'Block removed.', 'credipost' ); ?></span>
			<button type="button" id="credipost-btn-undo" class="button button-small button-link">
				<?php esc_html_e( 'Undo', 'credipost' ); ?>
			</button>
		</div>

		<!-- Content Sandbox Area -->
		<div class="credipost-card credipost-content-card">
			<div class="credipost-content-header">
				<span class="credipost-source-badge">
					<strong><?php esc_html_e( 'Source:', 'credipost' ); ?></strong>
					<a id="credipost-source-link" href="#" target="_blank" rel="noopener noreferrer"></a>
				</span>
			</div>

			<!-- Dynamic Content Blocks rendered here -->
			<div id="credipost-rendered-content" class="credipost-rendered-content"></div>
		</div>

	</div>
</div>
