<?php
/**
 * Frontend shortcode view template for Credipost Curation.
 *
 * @package    Credipost
 * @subpackage Credipost/public/partials
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'WPINC' ) ) {
	exit;
}
?>

<div class="credipost-public-wrap">
	<div class="credipost-notice-box" style="display: none;">
		<p></p>
	</div>

	<!-- Step 1: Input Card -->
	<div class="credipost-card credipost-input-card">
		<h3><?php esc_html_e( 'Curate External Content', 'credipost' ); ?></h3>
		<div class="credipost-input-row">
			<input
				type="url"
				id="credipost-pub-url"
				class="credipost-input"
				placeholder="<?php esc_attr_e( 'Enter URL to curate...', 'credipost' ); ?>"
				required
			/>
			<button type="button" id="credipost-pub-btn-fetch" class="credipost-btn credipost-btn-primary">
				<?php esc_html_e( 'Fetch Article', 'credipost' ); ?>
			</button>
			<button type="button" id="credipost-pub-btn-embed" class="credipost-btn credipost-btn-secondary">
				<?php esc_html_e( 'Video Embed', 'credipost' ); ?>
			</button>
			<span id="credipost-pub-spinner" class="credipost-spinner" style="display: none;"></span>
		</div>
	</div>

	<!-- Step 2: Curation Panel -->
	<div id="credipost-pub-workspace" class="credipost-workspace" style="display: none;">
		<div class="credipost-card credipost-publish-bar">
			<div class="credipost-meta-grid">
				<div class="credipost-meta-field credipost-col-wide">
					<label for="credipost-pub-title"><strong><?php esc_html_e( 'Post Title', 'credipost' ); ?></strong></label>
					<input type="text" id="credipost-pub-title" class="credipost-input" />
				</div>
				<div class="credipost-meta-field">
					<label for="credipost-pub-status"><strong><?php esc_html_e( 'Status', 'credipost' ); ?></strong></label>
					<select id="credipost-pub-status" class="credipost-select">
						<option value="draft" selected><?php esc_html_e( 'Draft', 'credipost' ); ?></option>
						<option value="pending"><?php esc_html_e( 'Pending Review', 'credipost' ); ?></option>
						<option value="publish"><?php esc_html_e( 'Publish', 'credipost' ); ?></option>
					</select>
				</div>
				<div class="credipost-meta-field">
					<label for="credipost-pub-category"><strong><?php esc_html_e( 'Category', 'credipost' ); ?></strong></label>
					<select id="credipost-pub-category" class="credipost-select">
						<option value="0"><?php esc_html_e( '— Select Category —', 'credipost' ); ?></option>
						<?php foreach ( $categories as $cat ) : ?>
							<option value="<?php echo esc_attr( $cat->term_id ); ?>"><?php echo esc_html( $cat->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<?php if ( is_multisite() && ! empty( $sites ) ) : ?>
					<div class="credipost-meta-field">
						<label for="credipost-pub-site"><strong><?php esc_html_e( 'Target Site', 'credipost' ); ?></strong></label>
						<select id="credipost-pub-site" class="credipost-select">
							<option value="0"><?php esc_html_e( 'Current Site', 'credipost' ); ?></option>
							<?php foreach ( $sites as $subsite ) : ?>
								<?php
								$details = get_blog_details( $subsite->blog_id );
								$name    = $details ? $details->blogname : 'Site #' . $subsite->blog_id;
								?>
								<option value="<?php echo esc_attr( $subsite->blog_id ); ?>"><?php echo esc_html( $name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				<?php endif; ?>
			</div>

			<div class="credipost-publish-actions">
				<button type="button" id="credipost-pub-btn-save" class="credipost-btn credipost-btn-primary">
					<?php esc_html_e( 'Save as Post', 'credipost' ); ?>
				</button>
				<button type="button" onclick="window.print(); return false;" class="credipost-btn credipost-btn-secondary">
					<?php esc_html_e( 'Print', 'credipost' ); ?>
				</button>
				<button type="button" id="credipost-pub-btn-clear" class="credipost-btn credipost-btn-link" style="margin-left: auto;">
					<?php esc_html_e( 'Clear', 'credipost' ); ?>
				</button>
				<span id="credipost-pub-save-spinner" class="credipost-spinner" style="display: none;"></span>
			</div>
		</div>

		<!-- Undo Toast Bar -->
		<div id="credipost-pub-undo-bar" class="credipost-undo-bar" style="display: none;">
			<span><?php esc_html_e( 'Block removed.', 'credipost' ); ?></span>
			<button type="button" id="credipost-pub-btn-undo" class="credipost-btn-link-white">
				<?php esc_html_e( 'Undo', 'credipost' ); ?>
			</button>
		</div>

		<!-- Content Sandbox Area -->
		<div class="credipost-card credipost-content-card">
			<div id="credipost-pub-rendered-content" class="credipost-rendered-content"></div>
		</div>
	</div>
</div>
