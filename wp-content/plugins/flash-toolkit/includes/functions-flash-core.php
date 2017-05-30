<?php
/**
 * Flash Core Functions.
 *
 * General core functions available on both the front-end and admin.
 *
 * @author   ThemeGrill
 * @category Core
 * @package  FlashToolkit/Functions
 * @version  1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include core functions (available in both admin and frontend).
include( 'functions-flash-deprecated.php' );
include( 'functions-flash-formatting.php' );
include( 'functions-flash-portfolio.php' );

/**
 * is_flash_pro_active - Check if Flash Pro is active.
 * @return bool
 */
function is_flash_pro_active() {
	return false !== strpos( get_option( 'template' ), 'flash-pro' );
}

/**
 * Queue some JavaScript code to be output in the footer.
 * @param string $code
 */
function flash_toolkit_enqueue_js( $code ) {
	global $flash_toolkit_queued_js;

	if ( empty( $flash_toolkit_queued_js ) ) {
		$flash_toolkit_queued_js = '';
	}

	$flash_toolkit_queued_js .= "\n" . $code . "\n";
}

/**
 * Output any queued javascript code in the footer.
 */
function flash_toolkit_print_js() {
	global $flash_toolkit_queued_js;

	if ( ! empty( $flash_toolkit_queued_js ) ) {
		// Sanitize.
		$flash_toolkit_queued_js = wp_check_invalid_utf8( $flash_toolkit_queued_js );
		$flash_toolkit_queued_js = preg_replace( '/&#(x)?0*(?(1)27|39);?/i', "'", $flash_toolkit_queued_js );
		$flash_toolkit_queued_js = str_replace( "\r", '', $flash_toolkit_queued_js );

		$js = "<!-- Flash Toolkit JavaScript -->\n<script type=\"text/javascript\">\njQuery(function($) { $flash_toolkit_queued_js });\n</script>\n";

		/**
		 * social_icons_queued_js filter.
		 * @param string $js JavaScript code.
		 */
		echo apply_filters( 'flash_toolkit_queued_js', $js );

		unset( $flash_toolkit_queued_js );
	}
}

/**
 * Display a FlashToolkit help tip.
 *
 * @param  string $tip Help tip text
 * @param  bool   $allow_html Allow sanitized HTML if true or escape
 * @return string
 */
function flash_toolkit_help_tip( $tip, $allow_html = false ) {
	if ( $allow_html ) {
		$tip = flash_toolkit_sanitize_tooltip( $tip );
	} else {
		$tip = esc_attr( $tip );
	}

	return '<span class="flash-toolkit-help-tip" data-tip="' . $tip . '"></span>';
}

/**
 * Get all available sidebars.
 * @param  array $sidebars
 * @return array
 */
function flash_toolkit_get_sidebars( $sidebars = array() ) {
	global $wp_registered_sidebars;

	foreach ( $wp_registered_sidebars as $sidebar ) {
		if ( ! in_array( $sidebar['name'], apply_filters( 'flash_toolkit_sidebars_exclude', array( 'Display Everywhere' ) ) ) ) {
			$sidebars[ $sidebar['id'] ] = $sidebar['name'];
		}
	}

	return $sidebars;
}

/**
 * FlashToolkit Layout Supported Screens or Post types.
 * @return array
 */
function flash_toolkit_get_layout_supported_screens() {
	return (array) apply_filters( 'flash_toolkit_layout_supported_screens', array( 'post', 'page', 'portfolio', 'jetpack-portfolio' ) );
}

/**
 * Get and include template files.
 *
 * @param string $template_name
 * @param array  $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 */
function flash_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args );
	}

	$located = flash_locate_template( $template_name, $template_path, $default_path );

	if ( ! file_exists( $located ) ) {
		_doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', $located ), '1.0' );
		return;
	}

	// Allow 3rd party plugin filter template file from their plugin.
	$located = apply_filters( 'flash_get_template', $located, $template_name, $args, $template_path, $default_path );

	do_action( 'flash_toolkit_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'flash_toolkit_after_template_part', $template_name, $template_path, $located, $args );
}

/**
 * Locate a template and return the path for inclusion.
 *
 * Note: FT_TEMPLATE_DEBUG_MODE will prevent overrides in themes from taking priority.
 *
 * This is the load order:
 *
 *      yourtheme       /   $template_path   /   $template_name
 *      yourtheme       /   $template_name
 *      $default_path   /   $template_name
 *
 * @param  string $template_name
 * @param  string $template_path (default: '')
 * @param  string $default_path (default: '')
 * @return string
 */
function flash_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	if ( ! $template_path ) {
		$template_path = FT()->template_path();
	}

	if ( ! $default_path ) {
		$default_path = FT()->plugin_path() . '/templates/';
	}

	// Look within passed path within the theme - this is priority.
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name,
		)
	);

	// Get default template/
	if ( ! $template || FT_TEMPLATE_DEBUG_MODE ) {
		$template = $default_path . $template_name;
	}

	// Return what we found.
	return apply_filters( 'flash_toolkit_locate_template', $template, $template_name, $template_path );
}

/**
 * Get fontawesome icon lists.
 * @return array
 */
function flash_get_fontawesome_icons() {
	return apply_filters( 'flash_get_fontawesome_icons', array(
		'fa-500px'                               => __( '500px', 'flash-toolkit' ),
		'fa-adjust'                              => __( 'Adjust', 'flash-toolkit' ),
		'fa-adn'                                 => __( 'Adn', 'flash-toolkit' ),
		'fa-align-center'                        => __( 'Align Center', 'flash-toolkit' ),
		'fa-align-justify'                       => __( 'Align Justify', 'flash-toolkit' ),
		'fa-align-left'                          => __( 'Align Left', 'flash-toolkit' ),
		'fa-align-right'                         => __( 'Align Right', 'flash-toolkit' ),
		'fa-amazon'                              => __( 'Amazon', 'flash-toolkit' ),
		'fa-ambulance'                           => __( 'Ambulance', 'flash-toolkit' ),
		'fa-american-sign-language-interpreting' => __( 'American Sign Lnguage Interpreting', 'flash-toolkit' ),
		'fa-anchor'                              => __( 'Anchor', 'flash-toolkit' ),
		'fa-angellist'                           => __( 'Angellist', 'flash-toolkit' ),
		'fa-android'                             => __( 'Android', 'flash-toolkit' ),
		'fa-angle-double-left'                   => __( 'Angle Double Left', 'flash-toolkit' ),
		'fa-angle-double-right'                  => __( 'Angle Double Right', 'flash-toolkit' ),
		'fa-angle-double-up'                     => __( 'Angle Double Up', 'flash-toolkit' ),
		'fa-angle-double-down'                   => __( 'Angle Double Down', 'flash-toolkit' ),
		'fa-angle-left'                          => __( 'Angle Left', 'flash-toolkit' ),
		'fa-angle-right'                         => __( 'Angle Right', 'flash-toolkit' ),
		'fa-angle-up'                            => __( 'Angle Up', 'flash-toolkit' ),
		'fa-angle-down'                          => __( 'Angle Down', 'flash-toolkit' ),
		'fa-apple'                               => __( 'Apple', 'flash-toolkit' ),
		'fa-archive'                             => __( 'Archive', 'flash-toolkit' ),
		'fa-area-chart'                          => __( 'Area Chart', 'flash-toolkit' ),
		'fa-arrow-circle-down'                   => __( 'Arrow Circle Down', 'flash-toolkit' ),
		'fa-arrow-circle-left'                   => __( 'Arrow Circle Left', 'flash-toolkit' ),
		'fa-arrow-circle-o-down'                 => __( 'Arrow Circle O Down', 'flash-toolkit' ),
		'fa-arrow-circle-o-left'                 => __( 'Arrow Circle O Left', 'flash-toolkit' ),
		'fa-arrow-circle-o-right'                => __( 'Arrow Circle O Right', 'flash-toolkit' ),
		'fa-arrow-circle-o-up'                   => __( 'Arrow Circle O Up', 'flash-toolkit' ),
		'fa-arrow-circle-right'                  => __( 'Arrow Circle Right', 'flash-toolkit' ),
		'fa-arrow-circle-up'                     => __( 'Arrow Circle Up', 'flash-toolkit' ),
		'fa-arrow-down'                          => __( 'Arrow Down', 'flash-toolkit' ),
		'fa-arrow-left'                          => __( 'Arrow Left', 'flash-toolkit' ),
		'fa-arrow-right'                         => __( 'Arrow Right', 'flash-toolkit' ),
		'fa-arrow-up'                            => __( 'Arrow Up', 'flash-toolkit' ),
		'fa-arrows'                              => __( 'Arrows', 'flash-toolkit' ),
		'fa-arrows-alt'                          => __( 'Arrows Alt', 'flash-toolkit' ),
		'fa-arrows-h'                            => __( 'Arrow H', 'flash-toolkit' ),
		'fa-arrows-v'                            => __( 'Arrow V', 'flash-toolkit' ),
		'fa-asl-interpreting'                    => __( 'Asl Interpreting', 'flash-toolkit' ),
		'fa-assistive-listening-systems'         => __( 'Assistive Listening Systems', 'flash-toolkit' ),
		'fa-asterisk'                            => __( 'Asterisk', 'flash-toolkit' ),
		'fa-at'                                  => __( 'At', 'flash-toolkit' ),
		'fa-audio-description'                   => __( 'Audio Description', 'flash-toolkit' ),
		'fa-automobile'                          => __( 'Automobile', 'flash-toolkit' ),
		'fa-backward'                            => __( 'Backward', 'flash-toolkit' ),
		'fa-balance-scale'                       => __( 'Balance Scale', 'flash-toolkit' ),
		'fa-ban'                                 => __( 'Ban', 'flash-toolkit' ),
		'fa-bank'                                => __( 'Bank', 'flash-toolkit' ),
		'fa-bar-chart'                           => __( 'Bar Chart', 'flash-toolkit' ),
		'fa-bar-chart-o'                         => __( 'Bar Chart O', 'flash-toolkit' ),
		'fa-barcode'                             => __( 'Barcode', 'flash-toolkit' ),
		'fa-bars'                                => __( 'Bars', 'flash-toolkit' ),
		'fa-battery-0'                           => __( 'Battery 0', 'flash-toolkit' ),
		'fa-battery-1'                           => __( 'Battery 1', 'flash-toolkit' ),
		'fa-battery-2'                           => __( 'Battery 2', 'flash-toolkit' ),
		'fa-battery-3'                           => __( 'Battery 3', 'flash-toolkit' ),
		'fa-battery-4'                           => __( 'Battery 4', 'flash-toolkit' ),
		'fa-battery-empty'                       => __( 'Battery Empty', 'flash-toolkit' ),
		'fa-battery-full'                        => __( 'Battery Full', 'flash-toolkit' ),
		'fa-battery-half'                        => __( 'Battery Half', 'flash-toolkit' ),
		'fa-battery-quarter'                     => __( 'Battery Quarter', 'flash-toolkit' ),
		'fa-battery-three-quarters'              => __( 'Battery Three Quater', 'flash-toolkit' ),
		'fa-bed'                                 => __( 'Bed', 'flash-toolkit' ),
		'fa-beer'                                => __( 'Beer', 'flash-toolkit' ),
		'fa-behance'                             => __( 'Behance', 'flash-toolkit' ),
		'fa-behance-square'                      => __( 'Behance Square', 'flash-toolkit' ),
		'fa-bell'                                => __( 'Bell', 'flash-toolkit' ),
		'fa-bell-o'                              => __( 'Bell O', 'flash-toolkit' ),
		'fa-bell-slash'                          => __( 'Bell Slash', 'flash-toolkit' ),
		'fa-bell-slash-o'                        => __( 'Bell Slash O', 'flash-toolkit' ),
		'fa-bicycle'                             => __( 'Bicycle', 'flash-toolkit' ),
		'fa-binoculars'                          => __( 'Binoculars', 'flash-toolkit' ),
		'fa-birthday-cake'                       => __( 'Birthday Cake', 'flash-toolkit' ),
		'fa-bitbucket'                           => __( 'Bitbucket', 'flash-toolkit' ),
		'fa-bitbucket-square'                    => __( 'Bitbucket Square', 'flash-toolkit' ),
		'fa-bitcoin'                             => __( 'Bitcoin', 'flash-toolkit' ),
		'fa-black-tie'                           => __( 'Black Tie', 'flash-toolkit' ),
		'fa-blind'                               => __( 'Blind', 'flash-toolkit' ),
		'fa-bluetooth'                           => __( 'Bluetooth', 'flash-toolkit' ),
		'fa-bluetooth-b'                         => __( 'Bluetooth b', 'flash-toolkit' ),
		'fa-bold'                                => __( 'Bold', 'flash-toolkit' ),
		'fa-bolt'                                => __( 'Bolt', 'flash-toolkit' ),
		'fa-bluetooth-b'                         => __( 'Bluetooth b', 'flash-toolkit' ),
		'fa-bomb'                                => __( 'Bomb', 'flash-toolkit' ),
		'fa-book'                                => __( 'Book', 'flash-toolkit' ),
		'fa-bookmark'                            => __( 'Bookmark', 'flash-toolkit' ),
		'fa-bookmark-o'                          => __( 'Bookmark Holo', 'flash-toolkit' ),
		'fa-braille'                             => __( 'Braille', 'flash-toolkit' ),
		'fa-briefcase'                           => __( 'Briefcase', 'flash-toolkit' ),
		'fa-btc'                                 => __( 'Btc', 'flash-toolkit' ),
		'fa-bug'                                 => __( 'Bug', 'flash-toolkit' ),
		'fa-building'                            => __( 'Building', 'flash-toolkit' ),
		'fa-building-o'                          => __( 'Building O', 'flash-toolkit' ),
		'fa-bullhorn'                            => __( 'Bullhorn', 'flash-toolkit' ),
		'fa-bullseye'                            => __( 'Bullseye', 'flash-toolkit' ),
		'fa-bus'                                 => __( 'Bus', 'flash-toolkit' ),
		'fa-buysellads'                          => __( 'Buysellads', 'flash-toolkit' ),
		'fa-cab'                                 => __( 'Cab', 'flash-toolkit' ),
		'fa-calculator'                          => __( 'Calculator', 'flash-toolkit' ),
		'fa-calendar'                            => __( 'Calendar', 'flash-toolkit' ),
		'fa-calendar-check-o'                    => __( 'Calendar Check', 'flash-toolkit' ),
		'fa-calendar-minus-o'                    => __( 'Calendar Minus', 'flash-toolkit' ),
		'fa-calendar-o'                          => __( 'Calendar O', 'flash-toolkit' ),
		'fa-calendar-plus-o'                     => __( 'Calendar Plus', 'flash-toolkit' ),
		'fa-calendar-times-o'                    => __( 'Calendar Times', 'flash-toolkit' ),
		'fa-camera'                              => __( 'Camera', 'flash-toolkit' ),
		'fa-camera-retro'                        => __( 'Camera Retro', 'flash-toolkit' ),
		'fa-car'                                 => __( 'Car', 'flash-toolkit' ),
		'fa-caret-down'                          => __( 'Caret Down', 'flash-toolkit' ),
		'fa-caret-left'                          => __( 'Caret Left', 'flash-toolkit' ),
		'fa-caret-right'                         => __( 'Caret Right', 'flash-toolkit' ),
		'fa-caret-square-o-down'                 => __( 'Caret Square O Down', 'flash-toolkit' ),
		'fa-caret-square-o-left'                 => __( 'Caret Square O Left', 'flash-toolkit' ),
		'fa-caret-square-o-right'                => __( 'Caret Square O Right', 'flash-toolkit' ),
		'fa-caret-square-o-up'                   => __( 'Caret Square O Up', 'flash-toolkit' ),
		'fa-caret-up'                            => __( 'Caret Up', 'flash-toolkit' ),
		'fa-cart-arrow-down'                     => __( 'Cart Arrow Down', 'flash-toolkit' ),
		'fa-cart-plus'                           => __( 'Cart Plus', 'flash-toolkit' ),
		'fa-cc'                                  => __( 'Cc', 'flash-toolkit' ),
		'fa-cc-amex'                             => __( 'Cc Amex', 'flash-toolkit' ),
		'fa-cc-diners-club'                      => __( 'Cc Diners Club', 'flash-toolkit' ),
		'fa-cc-discover'                         => __( 'Cc Discover', 'flash-toolkit' ),
		'fa-cc-jcb'                              => __( 'Cc Jcb', 'flash-toolkit' ),
		'fa-cc-mastercard'                       => __( 'Cc mastercard', 'flash-toolkit' ),
		'fa-cc-paypal'                           => __( 'Cc Paypal', 'flash-toolkit' ),
		'fa-cc-stripe'                           => __( 'Cc Stripe', 'flash-toolkit' ),
		'fa-cc-visa'                             => __( 'Cc Visa', 'flash-toolkit' ),
		'fa-certificate'                         => __( 'Certificate', 'flash-toolkit' ),
		'fa-chain'                               => __( 'Cc Chain', 'flash-toolkit' ),
		'fa-chain-broken'                        => __( 'Chain Broken', 'flash-toolkit' ),
		'fa-check'                               => __( 'Check', 'flash-toolkit' ),
		'fa-check-circle'                        => __( 'Check Circle', 'flash-toolkit' ),
		'fa-check-circle-o'                      => __( 'Check Circle O', 'flash-toolkit' ),
		'fa-check-square'                        => __( 'Check Square ', 'flash-toolkit' ),
		'fa-check-square-o'                      => __( 'Check Square O', 'flash-toolkit' ),
		'fa-chevron-circle-down'                 => __( 'Chevron Circle Down', 'flash-toolkit' ),
		'fa-chevron-circle-left'                 => __( 'Chevron Circle Left', 'flash-toolkit' ),
		'fa-chevron-circle-right'                => __( 'Chevron Circle Right', 'flash-toolkit' ),
		'fa-chevron-circle-up'                   => __( 'Chevron Circle Up', 'flash-toolkit' ),
		'fa-chevron-down'                        => __( 'Chevron Down', 'flash-toolkit' ),
		'fa-chevron-left'                        => __( 'Chevron Left', 'flash-toolkit' ),
		'fa-chevron-right'                       => __( 'Chevron Right', 'flash-toolkit' ),
		'fa-chevron-up'                          => __( 'Chevron Up', 'flash-toolkit' ),
		'fa-child'                               => __( 'Child', 'flash-toolkit' ),
		'fa-chrome'                              => __( 'Chrome', 'flash-toolkit' ),
		'fa-circle'                              => __( 'Circle', 'flash-toolkit' ),
		'fa-circle-o'                            => __( 'Circle O', 'flash-toolkit' ),
		'fa-circle-o-notch'                      => __( 'Circle Notch', 'flash-toolkit' ),
		'fa-circle-thin'                         => __( 'Circle Thin', 'flash-toolkit' ),
		'fa-clipboard'                           => __( 'Clipboard', 'flash-toolkit' ),
		'fa-clock-o'                             => __( 'Clock O', 'flash-toolkit' ),
		'fa-clone'                               => __( 'Clone', 'flash-toolkit' ),
		'fa-close'                               => __( 'Close', 'flash-toolkit' ),
		'fa-cloud'                               => __( 'Cloud', 'flash-toolkit' ),
		'fa-cloud-download'                      => __( 'Cloud Download', 'flash-toolkit' ),
		'fa-cloud-upload'                        => __( 'Cloud Upload', 'flash-toolkit' ),
		'fa-cny'                                 => __( 'Cny', 'flash-toolkit' ),
		'fa-code'                                => __( 'Code', 'flash-toolkit' ),
		'fa-code-fork'                           => __( 'Code Fork', 'flash-toolkit' ),
		'fa-codepen'                             => __( 'Codepen', 'flash-toolkit' ),
		'fa-codiepie'                            => __( 'Codiepie', 'flash-toolkit' ),
		'fa-coffee'                              => __( 'Coffee', 'flash-toolkit' ),
		'fa-cog'                                 => __( 'Cog', 'flash-toolkit' ),
		'fa-cogs'                                => __( 'Cogs', 'flash-toolkit' ),
		'fa-columns'                             => __( 'Columns', 'flash-toolkit' ),
		'fa-comment'                             => __( 'Comment', 'flash-toolkit' ),
		'fa-comment-o'                           => __( 'Comment O', 'flash-toolkit' ),
		'fa-commenting'                          => __( 'Commenting', 'flash-toolkit' ),
		'fa-commenting-o'                        => __( 'Commenting O', 'flash-toolkit' ),
		'fa-comments'                            => __( 'Comments', 'flash-toolkit' ),
		'fa-comments-o'                          => __( 'Comments O', 'flash-toolkit' ),
		'fa-compass'                             => __( 'Compass', 'flash-toolkit' ),
		'fa-compress'                            => __( 'Compress', 'flash-toolkit' ),
		'fa-connectdevelop'                      => __( 'Connectdevelop', 'flash-toolkit' ),
		'fa-contao'                              => __( 'Contao', 'flash-toolkit' ),
		'fa-copy'                                => __( 'Copy', 'flash-toolkit' ),
		'fa-copyright'                           => __( 'Copyright', 'flash-toolkit' ),
		'fa-creative-commons'                    => __( 'Creative Commons', 'flash-toolkit' ),
		'fa-credit-card'                         => __( 'Credit Card', 'flash-toolkit' ),
		'fa-credit-card-alt'                     => __( 'Credit Card Alt', 'flash-toolkit' ),
		'fa-crop'                                => __( 'Crop', 'flash-toolkit' ),
		'fa-crosshairs'                          => __( 'Crosshairs', 'flash-toolkit' ),
		'fa-css3'                                => __( 'Css3', 'flash-toolkit' ),
		'fa-cube'                                => __( 'Cube', 'flash-toolkit' ),
		'fa-cubes'                               => __( 'Cubes', 'flash-toolkit' ),
		'fa-cut'                                 => __( 'Cut', 'flash-toolkit' ),
		'fa-cutlery'                             => __( 'Cutlery', 'flash-toolkit' ),
		'fa-dashboard'                           => __( 'Dashboard', 'flash-toolkit' ),
		'fa-dashcube'                            => __( 'Dashcube', 'flash-toolkit' ),
		'fa-database'                            => __( 'Database', 'flash-toolkit' ),
		'fa-deaf'                                => __( 'Deaf', 'flash-toolkit' ),
		'fa-dedent'                              => __( 'Dedent', 'flash-toolkit' ),
		'fa-delicious'                           => __( 'Delicious', 'flash-toolkit' ),
		'fa-desktop'                             => __( 'Desktop', 'flash-toolkit' ),
		'fa-deviantart'                          => __( 'Deviantart', 'flash-toolkit' ),
		'fa-diamond'                             => __( 'Diamond', 'flash-toolkit' ),
		'fa-digg'                                => __( 'Digg', 'flash-toolkit' ),
		'fa-dollar'                              => __( 'Doller', 'flash-toolkit' ),
		'fa-dot-circle-o'                        => __( 'Dot Circle O', 'flash-toolkit' ),
		'fa-dribbble'                            => __( 'Dribbble', 'flash-toolkit' ),
		'fa-dropbox'                             => __( 'Dropbox', 'flash-toolkit' ),
		'fa-drupal'                              => __( 'Drupal', 'flash-toolkit' ),
		'fa-edge'                                => __( 'Edge', 'flash-toolkit' ),
		'fa-edit'                                => __( 'Edit', 'flash-toolkit' ),
		'fa-eject'                               => __( 'Eject', 'flash-toolkit' ),
		'fa-ellipsis-h'                          => __( 'Ellipsis H', 'flash-toolkit' ),
		'fa-ellipsis-v'                          => __( 'Ellipsis V', 'flash-toolkit' ),
		'fa-empire'                              => __( 'Empire', 'flash-toolkit' ),
		'fa-envelope'                            => __( 'Envelope', 'flash-toolkit' ),
		'fa-envelope-o'                          => __( 'Envelope O', 'flash-toolkit' ),
		'fa-envelope-square'                     => __( 'Envelope Square', 'flash-toolkit' ),
		'fa-envira'                              => __( 'Envira', 'flash-toolkit' ),
		'fa-eraser'                              => __( 'Eraser', 'flash-toolkit' ),
		'fa-euro'                                => __( 'Euro', 'flash-toolkit' ),
		'fa-exchange'                            => __( 'Exchange', 'flash-toolkit' ),
		'fa-exclamation'                         => __( 'Exclamation ', 'flash-toolkit' ),
		'fa-exclamation-circle'                  => __( 'Exclamation Circle', 'flash-toolkit' ),
		'fa-exclamation-triangle'                => __( 'Exclamation Triangle', 'flash-toolkit' ),
		'fa-expand'                              => __( 'Expand', 'flash-toolkit' ),
		'fa-expeditedssl'                        => __( 'Expeditedssl', 'flash-toolkit' ),
		'fa-external-link'                       => __( 'External Link', 'flash-toolkit' ),
		'fa-external-link-square'                => __( 'External Link Square', 'flash-toolkit' ),
		'fa-eye'                                 => __( 'Eye', 'flash-toolkit' ),
		'fa-eye-slash'                           => __( 'Eye Slash', 'flash-toolkit' ),
		'fa-eyedropper'                          => __( 'Eyedropper', 'flash-toolkit' ),
		'fa-fa'                                  => __( 'Fa', 'flash-toolkit' ),
		'fa-facebook'                            => __( 'Facebook', 'flash-toolkit' ),
		'fa-facebook-f'                          => __( 'Facebook F', 'flash-toolkit' ),
		'fa-facebook-official'                   => __( 'Facebook Official', 'flash-toolkit' ),
		'fa-facebook-square'                     => __( 'Facebook Square', 'flash-toolkit' ),
		'fa-fast-backward'                       => __( 'Fast Backward', 'flash-toolkit' ),
		'fa-fast-forward'                        => __( 'Fast Forward', 'flash-toolkit' ),
		'fa-fax'                                 => __( 'Fax', 'flash-toolkit' ),
		'fa-feed'                                => __( 'Feed', 'flash-toolkit' ),
		'fa-female'                              => __( 'Female', 'flash-toolkit' ),
		'fa-fighter-jet'                         => __( 'Fighter Jet', 'flash-toolkit' ),
		'fa-file-archive-o'                      => __( 'File Archive O', 'flash-toolkit' ),
		'fa-file-audio-o'                        => __( 'File Audio O', 'flash-toolkit' ),
		'fa-file-code-o'                         => __( 'File Code O', 'flash-toolkit' ),
		'fa-file-excel-o'                        => __( 'File Excel O', 'flash-toolkit' ),
		'fa-file-image-o'                        => __( 'File Image O', 'flash-toolkit' ),
		'fa-file-movie-o'                        => __( 'File Movie O', 'flash-toolkit' ),
		'fa-file-o'                              => __( 'File O', 'flash-toolkit' ),
		'fa-file-pdf-o'                          => __( 'File Pdf O', 'flash-toolkit' ),
		'fa-file-photo-o'                        => __( 'File Photo O', 'flash-toolkit' ),
		'fa-file-picture-o'                      => __( 'File Picture O', 'flash-toolkit' ),
		'fa-file-powerpoint-o'                   => __( 'File Powerpoint O', 'flash-toolkit' ),
		'fa-file-sound-o'                        => __( 'File Sound O', 'flash-toolkit' ),
		'fa-file-text'                           => __( 'File Text', 'flash-toolkit' ),
		'fa-file-text-o'                         => __( 'File Text O', 'flash-toolkit' ),
		'fa-file-video-o'                        => __( 'File Video O', 'flash-toolkit' ),
		'fa-file-word-o'                         => __( 'File Word O', 'flash-toolkit' ),
		'fa-file-zip-o'                          => __( 'File Zip O', 'flash-toolkit' ),
		'fa-files-o'                             => __( 'Files O', 'flash-toolkit' ),
		'fa-film'                                => __( 'Film', 'flash-toolkit' ),
		'fa-filter'                              => __( 'Filter', 'flash-toolkit' ),
		'fa-fire'                                => __( 'Fire', 'flash-toolkit' ),
		'fa-fire-extinguisher'                   => __( 'Fire Extinguisher', 'flash-toolkit' ),
		'fa-firefox'                             => __( 'Firefox', 'flash-toolkit' ),
		'fa-first-order'                         => __( 'First Order', 'flash-toolkit' ),
		'fa-flag'                                => __( 'Flag', 'flash-toolkit' ),
		'fa-flag-checkered'                      => __( 'Flag Checkered', 'flash-toolkit' ),
		'fa-flag-o'                              => __( 'Flag O', 'flash-toolkit' ),
		'fa-flash'                               => __( 'Flash', 'flash-toolkit' ),
		'fa-flask'                               => __( 'Flask', 'flash-toolkit' ),
		'fa-flickr'                              => __( 'Flickr', 'flash-toolkit' ),
		'fa-floppy-o'                            => __( 'Floppy O', 'flash-toolkit' ),
		'fa-folder'                              => __( 'Folder', 'flash-toolkit' ),
		'fa-folder-o'                            => __( 'Folder O', 'flash-toolkit' ),
		'fa-folder-open'                         => __( 'Folder Open', 'flash-toolkit' ),
		'fa-folder-open-o'                       => __( 'Folder Open O', 'flash-toolkit' ),
		'fa-font'                                => __( 'Font', 'flash-toolkit' ),
		'fa-font-awesome'                        => __( 'Font Awesome', 'flash-toolkit' ),
		'fa-fonticons'                           => __( 'Fonticons', 'flash-toolkit' ),
		'fa-fort-awesome'                        => __( 'Fort Awesome', 'flash-toolkit' ),
		'fa-forumbee'                            => __( 'Forumbee', 'flash-toolkit' ),
		'fa-forward'                             => __( 'Forward', 'flash-toolkit' ),
		'fa-foursquare '                         => __( 'Foursquare', 'flash-toolkit' ),
		'fa-frown-o'                             => __( 'Frown O', 'flash-toolkit' ),
		'fa-futbol-o'                            => __( 'Futbol O', 'flash-toolkit' ),
		'fa-gamepad'                             => __( 'Gamepad', 'flash-toolkit' ),
		'fa-gavel'                               => __( 'Gavel', 'flash-toolkit' ),
		'fa-gbp'                                 => __( 'Gbp', 'flash-toolkit' ),
		'fa-ge'                                  => __( 'Ge', 'flash-toolkit' ),
		'fa-gear'                                => __( 'Gear', 'flash-toolkit' ),
		'fa-gears'                               => __( 'Gears', 'flash-toolkit' ),
		'fa-genderless'                          => __( 'Genderless', 'flash-toolkit' ),
		'fa-get-pocket'                          => __( 'Get Pocket', 'flash-toolkit' ),
		'fa-gg'                                  => __( 'Gg', 'flash-toolkit' ),
		'fa-gg-circle'                           => __( 'Gg Circle', 'flash-toolkit' ),
		'fa-gift '                               => __( 'Gift', 'flash-toolkit' ),
		'fa-git '                                => __( 'Git', 'flash-toolkit' ),
		'fa-git-square'                          => __( 'Git Square', 'flash-toolkit' ),
		'fa-github'                              => __( 'Github', 'flash-toolkit' ),
		'fa-github-alt'                          => __( 'Github Alt', 'flash-toolkit' ),
		'fa-github-square'                       => __( 'Github Square', 'flash-toolkit' ),
		'fa-gitlab'                              => __( 'Gitlab', 'flash-toolkit' ),
		'fa-gittip'                              => __( 'Gittip', 'flash-toolkit' ),
		'fa-glass'                               => __( 'Glass', 'flash-toolkit' ),
		'fa-glide'                               => __( 'Glide', 'flash-toolkit' ),
		'fa-glide-g'                             => __( 'Glide G', 'flash-toolkit' ),
		'fa-globe'                               => __( 'Globe', 'flash-toolkit' ),
		'fa-google'                              => __( 'Google', 'flash-toolkit' ),
		'fa-google-plus'                         => __( 'Google Plus', 'flash-toolkit' ),
		'fa-google-plus-circle'                  => __( 'Google Plus Circle', 'flash-toolkit' ),
		'fa-google-plus-official'                => __( 'Google Plus Official', 'flash-toolkit' ),
		'fa-google-plus-square'                  => __( 'Google Plus Square', 'flash-toolkit' ),
		'fa-google-wallet'                       => __( 'Google Wallet', 'flash-toolkit' ),
		'fa-graduation-cap'                      => __( 'Graduation Cap', 'flash-toolkit' ),
		'fa-gratipay'                            => __( 'Gratipay', 'flash-toolkit' ),
		'fa-group'                               => __( 'Group', 'flash-toolkit' ),
		'fa-h-square'                            => __( 'H Square', 'flash-toolkit' ),
		'fa-hacker-news'                         => __( 'Hacker News', 'flash-toolkit' ),
		'fa-hand-grab-o'                         => __( 'Hand Grab O', 'flash-toolkit' ),
		'fa-hand-lizard-o'                       => __( 'Hand Lizard O', 'flash-toolkit' ),
		'fa-hand-o-down'                         => __( 'Hand O Down', 'flash-toolkit' ),
		'fa-hand-o-left'                         => __( 'Hand O Left', 'flash-toolkit' ),
		'fa-hand-o-right'                        => __( 'Hand O Right', 'flash-toolkit' ),
		'fa-hand-o-up'                           => __( 'Hand O Up', 'flash-toolkit' ),
		'fa-hand-paper-o'                        => __( 'Hand Paper O', 'flash-toolkit' ),
		'fa-hand-peace-o'                        => __( 'Hand Peace O', 'flash-toolkit' ),
		'fa-hand-pointer-o'                      => __( 'Hand Pointer O', 'flash-toolkit' ),
		'fa-hand-rock-o'                         => __( 'Hand Rock O', 'flash-toolkit' ),
		'fa-hand-scissors-o'                     => __( 'Hand Scissors O', 'flash-toolkit' ),
		'fa-hand-spock-o'                        => __( 'Hand Spock O', 'flash-toolkit' ),
		'fa-hand-stop-o'                         => __( 'Hand Stop O', 'flash-toolkit' ),
		'fa-hard-of-hearing'                     => __( 'Hard Of Hearing', 'flash-toolkit' ),
		'fa-hashtag'                             => __( 'Hashtag', 'flash-toolkit' ),
		'fa-hdd-o'                               => __( 'Hdd O', 'flash-toolkit' ),
		'fa-header'                              => __( 'Header', 'flash-toolkit' ),
		'fa-headphones'                          => __( 'Headphones', 'flash-toolkit' ),
		'fa-heart'                               => __( 'Heart', 'flash-toolkit' ),
		'fa-heart-o'                             => __( 'Heart O', 'flash-toolkit' ),
		'fa-heartbeat'                           => __( 'Heartbeat', 'flash-toolkit' ),
		'fa-history'                             => __( 'History', 'flash-toolkit' ),
		'fa-home'                                => __( 'Home', 'flash-toolkit' ),
		'fa-hospital-o'                          => __( 'Hospital-o', 'flash-toolkit' ),
		'fa-hotel'                               => __( 'Hotel', 'flash-toolkit' ),
		'fa-hourglass'                           => __( 'Hourglass', 'flash-toolkit' ),
		'fa-hourglass-1'                         => __( 'Hourglass 1', 'flash-toolkit' ),
		'fa-hourglass-2'                         => __( 'Hourglass 2', 'flash-toolkit' ),
		'fa-hourglass-3'                         => __( 'Hourglass 3', 'flash-toolkit' ),
		'fa-hourglass-end'                       => __( 'Hourglass End', 'flash-toolkit' ),
		'fa-hourglass-half'                      => __( 'Hourglass Half', 'flash-toolkit' ),
		'fa-hourglass-o'                         => __( 'Hourglass O', 'flash-toolkit' ),
		'fa-hourglass-start'                     => __( 'Hourglass Start', 'flash-toolkit' ),
		'fa-houzz'                               => __( 'Houzz', 'flash-toolkit' ),
		'fa-html5'                               => __( 'Html5', 'flash-toolkit' ),
		'fa-i-cursor'                            => __( 'I Cursor', 'flash-toolkit' ),
		'fa-ils'                                 => __( 'Ils', 'flash-toolkit' ),
		'fa-image'                               => __( 'Image', 'flash-toolkit' ),
		'fa-inbox'                               => __( 'Inbox', 'flash-toolkit' ),
		'fa-indent'                              => __( 'Indent', 'flash-toolkit' ),
		'fa-industry'                            => __( 'Industry', 'flash-toolkit' ),
		'fa-info'                                => __( 'Info', 'flash-toolkit' ),
		'fa-info-circle'                         => __( 'Info Circle', 'flash-toolkit' ),
		'fa-inr'                                 => __( 'Inr', 'flash-toolkit' ),
		'fa-instagram'                           => __( 'Instagram', 'flash-toolkit' ),
		'fa-institution'                         => __( 'Institution', 'flash-toolkit' ),
		'fa-internet-explorer'                   => __( 'Internet Explorer', 'flash-toolkit' ),
		'fa-intersex'                            => __( 'Intersex', 'flash-toolkit' ),
		'fa-ioxhost'                             => __( 'Ioxhost', 'flash-toolkit' ),
		'fa-italic'                              => __( 'Italic', 'flash-toolkit' ),
		'fa-joomla'                              => __( 'Joomla', 'flash-toolkit' ),
		'fa-jpy'                                 => __( 'Jpy', 'flash-toolkit' ),
		'fa-jsfiddle'                            => __( 'Jsfiddle', 'flash-toolkit' ),
		'fa-key'                                 => __( 'Key', 'flash-toolkit' ),
		'fa-keyboard-o'                          => __( 'Keyboard O', 'flash-toolkit' ),
		'fa-krw'                                 => __( 'Krw', 'flash-toolkit' ),
		'fa-language'                            => __( 'Language', 'flash-toolkit' ),
		'fa-laptop'                              => __( 'Laptop', 'flash-toolkit' ),
		'fa-lastfm'                              => __( 'Lastfm', 'flash-toolkit' ),
		'fa-lastfm-square'                       => __( 'Lastfm Square', 'flash-toolkit' ),
		'fa-leaf'                                => __( 'Leaf', 'flash-toolkit' ),
		'fa-leanpub'                             => __( 'Leanpub', 'flash-toolkit' ),
		'fa-legal'                               => __( 'Legal', 'flash-toolkit' ),
		'fa-lemon-o'                             => __( 'Lemon O', 'flash-toolkit' ),
		'fa-level-down'                          => __( 'Level Down', 'flash-toolkit' ),
		'fa-level-up'                            => __( 'Level Up', 'flash-toolkit' ),
		'fa-life-bouy'                           => __( 'Life Bouy', 'flash-toolkit' ),
		'fa-life-buoy'                           => __( 'Life Buoy', 'flash-toolkit' ),
		'fa-life-ring'                           => __( 'Life Ring', 'flash-toolkit' ),
		'fa-life-saver'                          => __( 'Life Saver', 'flash-toolkit' ),
		'fa-lightbulb-o'                         => __( 'Lightbulb O', 'flash-toolkit' ),
		'fa-line-chart'                          => __( 'Line Chart', 'flash-toolkit' ),
		'fa-link'                                => __( 'Link', 'flash-toolkit' ),
		'fa-linkedin'                            => __( 'Linkedin', 'flash-toolkit' ),
		'fa-linkedin-square'                     => __( 'Linkedin Square', 'flash-toolkit' ),
		'fa-linux'                               => __( 'Linux', 'flash-toolkit' ),
		'fa-list'                                => __( 'List', 'flash-toolkit' ),
		'fa-list-alt'                            => __( 'List Alt', 'flash-toolkit' ),
		'fa-list-ol'                             => __( 'List Ol', 'flash-toolkit' ),
		'fa-list-ul'                             => __( 'List Ul', 'flash-toolkit' ),
		'fa-location-arrow'                      => __( 'Location Arrow', 'flash-toolkit' ),
		'fa-lock'                                => __( 'Lock', 'flash-toolkit' ),
		'fa-long-arrow-down'                     => __( 'Long Arrow Down', 'flash-toolkit' ),
		'fa-long-arrow-left'                     => __( 'Long Arrow Left', 'flash-toolkit' ),
		'fa-long-arrow-right'                    => __( 'Long Arrow Right', 'flash-toolkit' ),
		'fa-long-arrow-up'                       => __( 'Long Arrow Up', 'flash-toolkit' ),
		'fa-low-vision'                          => __( 'Low Vision', 'flash-toolkit' ),
		'fa-magic'                               => __( 'Magic', 'flash-toolkit' ),
		'fa-magnet'                              => __( 'Magnet', 'flash-toolkit' ),
		'fa-mail-forward'                        => __( 'Mail Forward', 'flash-toolkit' ),
		'fa-mail-reply'                          => __( 'Mail Reply', 'flash-toolkit' ),
		'fa-mail-reply-all'                      => __( 'Mail Reply All', 'flash-toolkit' ),
		'fa-male'                                => __( 'Male', 'flash-toolkit' ),
		'fa-map'                                 => __( 'Map', 'flash-toolkit' ),
		'fa-map-marker'                          => __( 'Map Marker', 'flash-toolkit' ),
		'fa-map-o'                               => __( 'Map O', 'flash-toolkit' ),
		'fa-map-pin'                             => __( 'Map Pin', 'flash-toolkit' ),
		'fa-map-signs'                           => __( 'Map Signs', 'flash-toolkit' ),
		'fa-mars'                                => __( 'Mars', 'flash-toolkit' ),
		'fa-mars-double'                         => __( 'Mars Double', 'flash-toolkit' ),
		'fa-mars-stroke'                         => __( 'Mars Stroke', 'flash-toolkit' ),
		'fa-mars-stroke-h'                       => __( 'Mars Stroke H', 'flash-toolkit' ),
		'fa-mars-stroke-v'                       => __( 'Mars Stroke V', 'flash-toolkit' ),
		'fa-maxcdn'                              => __( 'Maxcdn', 'flash-toolkit' ),
		'fa-meanpath'                            => __( 'Meanpath', 'flash-toolkit' ),
		'fa-medium'                              => __( 'Medium', 'flash-toolkit' ),
		'fa-medkit'                              => __( 'Medkit', 'flash-toolkit' ),
		'fa-meh-o'                               => __( 'Meh', 'flash-toolkit' ),
		'fa-mercury'                             => __( 'Mercury', 'flash-toolkit' ),
		'fa-microphone'                          => __( 'Microphone', 'flash-toolkit' ),
		'fa-microphone-slash'                    => __( 'Microphone Slash', 'flash-toolkit' ),
		'fa-minus'                               => __( 'Minus', 'flash-toolkit' ),
		'fa-minus-circle'                        => __( 'Minus Circle', 'flash-toolkit' ),
		'fa-minus-square'                        => __( 'Minus Square', 'flash-toolkit' ),
		'fa-minus-square-o'                      => __( 'Minus Square O', 'flash-toolkit' ),
		'fa-mixcloud'                            => __( 'Mixcloud', 'flash-toolkit' ),
		'fa-mobile'                              => __( 'Mobile', 'flash-toolkit' ),
		'fa-modx'                                => __( 'Modex', 'flash-toolkit' ),
		'fa-money'                               => __( 'Money', 'flash-toolkit' ),
		'fa-moon-o'                              => __( 'Moon O', 'flash-toolkit' ),
		'fa-mortar-board'                        => __( 'Mortar Board', 'flash-toolkit' ),
		'fa-motorcycle'                          => __( 'Motorcycle', 'flash-toolkit' ),
		'fa-mouse-pointer'                       => __( 'Mouse Pointer', 'flash-toolkit' ),
		'fa-music'                               => __( 'Music', 'flash-toolkit' ),
		'fa-navicon'                             => __( 'Navicon', 'flash-toolkit' ),
		'fa-neuter'                              => __( 'Neuter', 'flash-toolkit' ),
		'fa-newspaper-o'                         => __( 'Newspaper O', 'flash-toolkit' ),
		'fa-object-group'                        => __( 'Object group', 'flash-toolkit' ),
		'fa-object-ungroup'                      => __( 'Object ungroup', 'flash-toolkit' ),
		'fa-odnoklassniki'                       => __( 'Odnoklassniki', 'flash-toolkit' ),
		'fa-odnoklassniki-square'                => __( 'Odnoklassniki Square', 'flash-toolkit' ),
		'fa-opencart'                            => __( 'Opencart', 'flash-toolkit' ),
		'fa-openid'                              => __( 'Openid', 'flash-toolkit' ),
		'fa-opera'                               => __( 'Opera', 'flash-toolkit' ),
		'fa-optin-monster'                       => __( 'Option monster', 'flash-toolkit' ),
		'fa-outdent'                             => __( 'Outdent', 'flash-toolkit' ),
		'fa-pagelines'                           => __( 'Pagelines', 'flash-toolkit' ),
		'fa-paint-brush'                         => __( 'Paint brush', 'flash-toolkit' ),
		'fa-paper-plane'                         => __( 'Paper plane', 'flash-toolkit' ),
		'fa-paper-plane-o'                       => __( 'Paper plane O', 'flash-toolkit' ),
		'fa-paperclip'                           => __( 'Paperclip', 'flash-toolkit' ),
		'fa-paragraph'                           => __( 'Paragraph', 'flash-toolkit' ),
		'fa-paste'                               => __( 'Paste', 'flash-toolkit' ),
		'fa-pause'                               => __( 'Paush', 'flash-toolkit' ),
		'fa-pause-circle'                        => __( 'Paush Circle', 'flash-toolkit' ),
		'fa-pause-circle-o'                      => __( 'Paush Circle O', 'flash-toolkit' ),
		'fa-paw'                                 => __( 'Paw', 'flash-toolkit' ),
		'fa-paypal'                              => __( 'Paypal', 'flash-toolkit' ),
		'fa-pencil'                              => __( 'Pencil Square', 'flash-toolkit' ),
		'fa-pencil-square'                       => __( 'Paypal', 'flash-toolkit' ),
		'fa-pencil-square-o'                     => __( 'Pencil Square O', 'flash-toolkit' ),
		'fa-percent'                             => __( 'Percent', 'flash-toolkit' ),
		'fa-phone'                               => __( 'Phone', 'flash-toolkit' ),
		'fa-phone-square'                        => __( 'Phone Square', 'flash-toolkit' ),
		'fa-photo'                               => __( 'Photo', 'flash-toolkit' ),
		'fa-picture-o'                           => __( 'Picture O', 'flash-toolkit' ),
		'fa-pie-chart'                           => __( 'Pie Chart', 'flash-toolkit' ),
		'fa-pied-piper'                          => __( 'Pied Piper', 'flash-toolkit' ),
		'fa-pied-piper-alt'                      => __( 'Pied Piper Alt', 'flash-toolkit' ),
		'fa-pied-piper-pp'                       => __( 'Pied Piper Pp', 'flash-toolkit' ),
		'fa-pinterest'                           => __( 'Pinterest', 'flash-toolkit' ),
		'fa-pinterest-p'                         => __( 'Pinterest P', 'flash-toolkit' ),
		'fa-pinterest-square'                    => __( 'Pinterest Square', 'flash-toolkit' ),
		'fa-plane'                               => __( 'Plane', 'flash-toolkit' ),
		'fa-play'                                => __( 'Play', 'flash-toolkit' ),
		'fa-play-circle'                         => __( 'Play Circle', 'flash-toolkit' ),
		'fa-play-circle-o'                       => __( 'Play Circle O', 'flash-toolkit' ),
		'fa-plug'                                => __( 'Plug', 'flash-toolkit' ),
		'fa-plus'                                => __( 'Plus', 'flash-toolkit' ),
		'fa-plus-circle'                         => __( 'Plus Circle', 'flash-toolkit' ),
		'fa-plus-square'                         => __( 'Plus Square', 'flash-toolkit' ),
		'fa-plus-square-o'                       => __( 'Plus Square O', 'flash-toolkit' ),
		'fa-power-off'                           => __( 'Power Off', 'flash-toolkit' ),
		'fa-print'                               => __( 'Print', 'flash-toolkit' ),
		'fa-product-hunt'                        => __( 'Peoduct Hunt', 'flash-toolkit' ),
		'fa-puzzle-piece'                        => __( 'Puzzle Piece', 'flash-toolkit' ),
		'fa-qq'                                  => __( 'Qq', 'flash-toolkit' ),
		'fa-qrcode'                              => __( 'Qrcode', 'flash-toolkit' ),
		'fa-question'                            => __( 'Question', 'flash-toolkit' ),
		'fa-question-circle'                     => __( 'Question Ciecle', 'flash-toolkit' ),
		'fa-question-circle-o'                   => __( 'Question Circle O', 'flash-toolkit' ),
		'fa-quote-left'                          => __( 'Quote Left', 'flash-toolkit' ),
		'fa-quote-right'                         => __( 'Quote Right', 'flash-toolkit' ),
		'fa-ra'                                  => __( 'Ra', 'flash-toolkit' ),
		'fa-random'                              => __( 'Random', 'flash-toolkit' ),
		'fa-rebel'                               => __( 'Rebel', 'flash-toolkit' ),
		'fa-recycle'                             => __( 'Recycle', 'flash-toolkit' ),
		'fa-reddit'                              => __( 'Reddit', 'flash-toolkit' ),
		'fa-reddit-alien'                        => __( 'Reddit Alien', 'flash-toolkit' ),
		'fa-reddit-square'                       => __( 'Reddit Square', 'flash-toolkit' ),
		'fa-refresh'                             => __( 'Refresh', 'flash-toolkit' ),
		'fa-registered'                          => __( 'Registered', 'flash-toolkit' ),
		'fa-remove'                              => __( 'Remove', 'flash-toolkit' ),
		'fa-renren'                              => __( 'Renren', 'flash-toolkit' ),
		'fa-reorder'                             => __( 'Reorder', 'flash-toolkit' ),
		'fa-repeat'                              => __( 'Repeat', 'flash-toolkit' ),
		'fa-reply'                               => __( 'Reply', 'flash-toolkit' ),
		'fa-reply-all'                           => __( 'Reply All', 'flash-toolkit' ),
		'fa-retweet'                             => __( 'Retweet', 'flash-toolkit' ),
		'fa-rmb'                                 => __( 'Rmb', 'flash-toolkit' ),
		'fa-road'                                => __( 'Road', 'flash-toolkit' ),
		'fa-rocket'                              => __( 'rocket', 'flash-toolkit' ),
		'fa-rotate-left'                         => __( 'Rotate Left', 'flash-toolkit' ),
		'fa-rotate-right'                        => __( 'Rotate Right', 'flash-toolkit' ),
		'fa-rouble'                              => __( 'Rouble', 'flash-toolkit' ),
		'fa-rss'                                 => __( 'Rss', 'flash-toolkit' ),
		'fa-rss-square'                          => __( 'Rss Square', 'flash-toolkit' ),
		'fa-rupee'                               => __( 'Rupee', 'flash-toolkit' ),
		'fa-safari'                              => __( 'Safari', 'flash-toolkit' ),
		'fa-save'                                => __( 'Save', 'flash-toolkit' ),
		'fa-scissors'                            => __( 'Scissors', 'flash-toolkit' ),
		'fa-scribd'                              => __( 'Scribd', 'flash-toolkit' ),
		'fa-search'                              => __( 'Search', 'flash-toolkit' ),
		'fa-search-minus'                        => __( 'Search Minus', 'flash-toolkit' ),
		'fa-search-plus'                         => __( 'Search Plus', 'flash-toolkit' ),
		'fa-sellsy'                              => __( 'Sellsy', 'flash-toolkit' ),
		'fa-send'                                => __( 'Send', 'flash-toolkit' ),
		'fa-send-o'                              => __( 'Send O', 'flash-toolkit' ),
		'fa-server'                              => __( 'Server', 'flash-toolkit' ),
		'fa-share'                               => __( 'Share', 'flash-toolkit' ),
		'fa-share-alt'                           => __( 'Share Alt', 'flash-toolkit' ),
		'fa-share-alt-square'                    => __( 'Share Alt Square', 'flash-toolkit' ),
		'fa-share-square-o'                      => __( 'Share Square O', 'flash-toolkit' ),
		'fa-shekel'                              => __( 'Shekel', 'flash-toolkit' ),
		'fa-sheqel'                              => __( 'Sheqel', 'flash-toolkit' ),
		'fa-shield'                              => __( 'Shield', 'flash-toolkit' ),
		'fa-ship'                                => __( 'Ship', 'flash-toolkit' ),
		'fa-shirtsinbulk'                        => __( 'Shirtsinbulk', 'flash-toolkit' ),
		'fa-shopping-bag'                        => __( 'Shopping Bag', 'flash-toolkit' ),
		'fa-shopping-basket'                     => __( 'Shopping Basket', 'flash-toolkit' ),
		'fa-shopping-cart'                       => __( 'Shopping Cart', 'flash-toolkit' ),
		'fa-sign-in'                             => __( 'Sign In', 'flash-toolkit' ),
		'fa-sign-out'                            => __( 'Sign Out', 'flash-toolkit' ),
		'fa-signal'                              => __( 'Signal', 'flash-toolkit' ),
		'fa-simplybuilt'                         => __( 'Simplybuilt', 'flash-toolkit' ),
		'fa-sitemap'                             => __( 'Sitemap', 'flash-toolkit' ),
		'fa-skyatlas'                            => __( 'Skyatlas', 'flash-toolkit' ),
		'fa-skype'                               => __( 'Skype', 'flash-toolkit' ),
		'fa-slack'                               => __( 'Slack', 'flash-toolkit' ),
		'fa-sliders'                             => __( 'Sliders', 'flash-toolkit' ),
		'fa-slideshare'                          => __( 'Slideshare', 'flash-toolkit' ),
		'fa-smile-o'                             => __( 'Smile O', 'flash-toolkit' ),
		'fa-snapchat'                            => __( 'Snapchat', 'flash-toolkit' ),
		'fa-snapchat-ghost'                      => __( 'Snapchat Ghost', 'flash-toolkit' ),
		'fa-snapchat-square'                     => __( 'Snapchat Square', 'flash-toolkit' ),
		'fa-soccer-ball-o'                       => __( 'Soccer Ball', 'flash-toolkit' ),
		'fa-sort'                                => __( 'Sort', 'flash-toolkit' ),
		'fa-sort-alpha-asc'                      => __( 'Sort Alpha Asc', 'flash-toolkit' ),
		'fa-sort-alpha-desc'                     => __( 'Sort Alpha desc', 'flash-toolkit' ),
		'fa-sort-asc'                            => __( 'Sort Asc', 'flash-toolkit' ),
		'fa-sort-desc'                           => __( 'Sort Desc', 'flash-toolkit' ),
		'fa-sort-down'                           => __( 'Sort Down', 'flash-toolkit' ),
		'fa-sort-numeric-asc'                    => __( 'Sort Numeric Asc', 'flash-toolkit' ),
		'fa-sort-numeric-desc'                   => __( 'Sort Numeric Desc', 'flash-toolkit' ),
		'fa-sort-up'                             => __( 'Sort Up', 'flash-toolkit' ),
		'fa-soundcloud'                          => __( 'Soundcloud', 'flash-toolkit' ),
		'fa-space-shuttle'                       => __( 'Space Shuttle', 'flash-toolkit' ),
		'fa-spinner'                             => __( 'Spinner', 'flash-toolkit' ),
		'fa-spoon'                               => __( 'Spoon', 'flash-toolkit' ),
		'fa-spotify'                             => __( 'Spotify', 'flash-toolkit' ),
		'fa-square'                              => __( 'Square', 'flash-toolkit' ),
		'fa-square-o'                            => __( 'Square O', 'flash-toolkit' ),
		'fa-stack-exchange'                      => __( 'Stack Exchange', 'flash-toolkit' ),
		'fa-stack-overflow'                      => __( 'Stack Overflow', 'flash-toolkit' ),
		'fa-star'                                => __( 'Star', 'flash-toolkit' ),
		'fa-star-half'                           => __( 'Star Half', 'flash-toolkit' ),
		'fa-star-half-empty'                     => __( 'Star Half Empty', 'flash-toolkit' ),
		'fa-star-o'                              => __( 'Star O', 'flash-toolkit' ),
		'fa-steam'                               => __( 'Steam', 'flash-toolkit' ),
		'fa-steam-square'                        => __( 'Steam Square', 'flash-toolkit' ),
		'fa-step-backward'                       => __( 'Step Backward', 'flash-toolkit' ),
		'fa-step-forward'                        => __( 'Step Forward', 'flash-toolkit' ),
		'fa-stethoscope'                         => __( 'Stethoscope', 'flash-toolkit' ),
		'fa-sticky-note'                         => __( 'Sticky Note', 'flash-toolkit' ),
		'fa-sticky-note-o'                       => __( 'Sticky Note O', 'flash-toolkit' ),
		'fa-stop'                                => __( 'Stop', 'flash-toolkit' ),
		'fa-stop-circle'                         => __( 'Stop Circle', 'flash-toolkit' ),
		'fa-stop-circle-o'                       => __( 'Stop Circle O', 'flash-toolkit' ),
		'fa-street-view'                         => __( 'Street View', 'flash-toolkit' ),
		'fa-strikethrough'                       => __( 'Strikethrough', 'flash-toolkit' ),
		'fa-stumbleupon'                         => __( 'Stumbleupon', 'flash-toolkit' ),
		'fa-stumbleupon-circle'                  => __( 'Stumbleupon Circle', 'flash-toolkit' ),
		'fa-subscript'                           => __( 'Subscript', 'flash-toolkit' ),
		'fa-subway'                              => __( 'Subway', 'flash-toolkit' ),
		'fa-suitcase'                            => __( 'Suitcase', 'flash-toolkit' ),
		'fa-sun-o'                               => __( 'Sun', 'flash-toolkit' ),
		'fa-superscript'                         => __( 'Superscript', 'flash-toolkit' ),
		'fa-support'                             => __( 'Support', 'flash-toolkit' ),
		'fa-table'                               => __( 'Table', 'flash-toolkit' ),
		'fa-tablet'                              => __( 'Tablet', 'flash-toolkit' ),
		'fa-tachometer'                          => __( 'Tachometer', 'flash-toolkit' ),
		'fa-tag'                                 => __( 'Tag', 'flash-toolkit' ),
		'fa-tags'                                => __( 'Tags', 'flash-toolkit' ),
		'fa-tasks'                               => __( 'Tasks', 'flash-toolkit' ),
		'fa-taxi'                                => __( 'Taxi', 'flash-toolkit' ),
		'fa-television'                          => __( 'Television', 'flash-toolkit' ),
		'fa-tencent-weibo '                      => __( 'Tencent Weibo ', 'flash-toolkit' ),
		'fa-terminal'                            => __( 'Terminal', 'flash-toolkit' ),
		'fa-text-height'                         => __( 'Text Height', 'flash-toolkit' ),
		'fa-text-width'                          => __( 'Text Width', 'flash-toolkit' ),
		'fa-th'                                  => __( 'Th', 'flash-toolkit' ),
		'fa-th-large'                            => __( 'Th Large', 'flash-toolkit' ),
		'fa-th-list'                             => __( 'Th List', 'flash-toolkit' ),
		'fa-themeisle'                           => __( 'Themeisle', 'flash-toolkit' ),
		'fa-thumb-tack'                          => __( 'Thumb Tack', 'flash-toolkit' ),
		'fa-thumbs-down'                         => __( 'Thumbs Down', 'flash-toolkit' ),
		'fa-thumbs-o-down'                       => __( 'Thumbs O Down', 'flash-toolkit' ),
		'fa-thumbs-o-up'                         => __( 'Thumbs O Up', 'flash-toolkit' ),
		'fa-thumbs-up'                           => __( 'Thumbs Up', 'flash-toolkit' ),
		'fa-ticket '                             => __( 'Ticket ', 'flash-toolkit' ),
		'fa-times'                               => __( 'Times', 'flash-toolkit' ),
		'fa-times-circle'                        => __( 'Times Circle', 'flash-toolkit' ),
		'fa-times-circle-o'                      => __( 'Times Circle O', 'flash-toolkit' ),
		'fa-tint'                                => __( 'Tint', 'flash-toolkit' ),
		'fa-toggle-down'                         => __( 'Toggle Down', 'flash-toolkit' ),
		'fa-toggle-left'                         => __( 'Toggle Left', 'flash-toolkit' ),
		'fa-toggle-off'                          => __( 'Toggle Off', 'flash-toolkit' ),
		'fa-toggle-on'                           => __( 'Toggle On', 'flash-toolkit' ),
		'fa-toggle-right'                        => __( 'Toggle Right', 'flash-toolkit' ),
		'fa-toggle-up'                           => __( 'Toggle Up', 'flash-toolkit' ),
		'fa-trademark'                           => __( 'Trademark', 'flash-toolkit' ),
		'fa-train'                               => __( 'Train', 'flash-toolkit' ),
		'fa-transgender'                         => __( 'Transgender', 'flash-toolkit' ),
		'fa-transgender-alt'                     => __( 'Transgender Alt', 'flash-toolkit' ),
		'fa-trash'                               => __( 'Trash', 'flash-toolkit' ),
		'fa-trash-o'                             => __( 'Trash O', 'flash-toolkit' ),
		'fa-tree'                                => __( 'Tree', 'flash-toolkit' ),
		'fa-trello'                              => __( 'Trello', 'flash-toolkit' ),
		'fa-tripadvisor'                         => __( 'Tripadvisor', 'flash-toolkit' ),
		'fa-trophy'                              => __( 'Trophy', 'flash-toolkit' ),
		'fa-truck'                               => __( 'Truck', 'flash-toolkit' ),
		'fa-try'                                 => __( 'Try', 'flash-toolkit' ),
		'fa-tty'                                 => __( 'Tty', 'flash-toolkit' ),
		'fa-tumblr'                              => __( 'Tumblr', 'flash-toolkit' ),
		'fa-tumblr-square'                       => __( 'Tumblr Square', 'flash-toolkit' ),
		'fa-turkish-lira'                        => __( 'Turkish Lira', 'flash-toolkit' ),
		'fa-tv'                                  => __( 'Tv', 'flash-toolkit' ),
		'fa-twitch'                              => __( 'Twitch', 'flash-toolkit' ),
		'fa-twitter'                             => __( 'Twitter', 'flash-toolkit' ),
		'fa-twitter-square'                      => __( 'Twitter Square', 'flash-toolkit' ),
		'fa-umbrella'                            => __( 'Umbrella', 'flash-toolkit' ),
		'fa-underline'                           => __( 'Underline', 'flash-toolkit' ),
		'fa-undo'                                => __( 'Undo', 'flash-toolkit' ),
		'fa-universal-access'                    => __( 'Universal Access', 'flash-toolkit' ),
		'fa-university'                          => __( 'University', 'flash-toolkit' ),
		'fa-unlink'                              => __( 'Unlink', 'flash-toolkit' ),
		'fa-unlock'                              => __( 'Unlock', 'flash-toolkit' ),
		'fa-unlock-alt'                          => __( 'Unlock Alt', 'flash-toolkit' ),
		'fa-unsorted'                            => __( 'Unsorted', 'flash-toolkit' ),
		'fa-upload'                              => __( 'Upload', 'flash-toolkit' ),
		'fa-usb'                                 => __( 'Usb', 'flash-toolkit' ),
		'fa-usd'                                 => __( 'Usd', 'flash-toolkit' ),
		'fa-user'                                => __( 'User', 'flash-toolkit' ),
		'fa-user-md'                             => __( 'User Md', 'flash-toolkit' ),
		'fa-user-plus'                           => __( 'User Plus', 'flash-toolkit' ),
		'fa-user-secret'                         => __( 'User Secret', 'flash-toolkit' ),
		'fa-user-times'                          => __( 'User Times', 'flash-toolkit' ),
		'fa-users'                               => __( 'Users', 'flash-toolkit' ),
		'fa-venus'                               => __( 'Venus', 'flash-toolkit' ),
		'fa-venus-double'                        => __( 'Venus Double', 'flash-toolkit' ),
		'fa-venus-mars'                          => __( 'Venus Mars', 'flash-toolkit' ),
		'fa-viacoin'                             => __( 'Viacoin', 'flash-toolkit' ),
		'fa-viadeo'                              => __( 'Viadeo', 'flash-toolkit' ),
		'fa-viadeo-square'                       => __( 'Viadeo Square', 'flash-toolkit' ),
		'fa-video-camera'                        => __( 'Video Camera', 'flash-toolkit' ),
		'fa-vimeo'                               => __( 'Vimeo', 'flash-toolkit' ),
		'fa-vimeo-square'                        => __( 'Vimeo Square', 'flash-toolkit' ),
		'fa-vine'                                => __( 'Vine', 'flash-toolkit' ),
		'fa-vk'                                  => __( 'Vk', 'flash-toolkit' ),
		'fa-volume-control-phone'                => __( 'Volume Control Phone', 'flash-toolkit' ),
		'fa-volume-down'                         => __( 'Volume Down', 'flash-toolkit' ),
		'fa-volume-off'                          => __( 'Volume Off', 'flash-toolkit' ),
		'fa-volume-up'                           => __( 'Volume Up', 'flash-toolkit' ),
		'fa-warning'                             => __( 'Warning', 'flash-toolkit' ),
		'fa-wechat'                              => __( 'Wechat', 'flash-toolkit' ),
		'fa-weibo'                               => __( 'Weibo', 'flash-toolkit' ),
		'fa-weixin'                              => __( 'Weixin', 'flash-toolkit' ),
		'fa-whatsapp'                            => __( 'Whatsapp', 'flash-toolkit' ),
		'fa-wheelchair'                          => __( 'Wheelchair', 'flash-toolkit' ),
		'fa-wheelchair-alt'                      => __( 'Wheelchair Alt', 'flash-toolkit' ),
		'fa-wifi'                                => __( 'Wifi', 'flash-toolkit' ),
		'fa-wikipedia-w'                         => __( 'Wikipedia W', 'flash-toolkit' ),
		'fa-windows'                             => __( 'Windows', 'flash-toolkit' ),
		'fa-won'                                 => __( 'Won', 'flash-toolkit' ),
		'fa-wordpress'                           => __( 'Wordpress', 'flash-toolkit' ),
		'fa-wpbeginner '                         => __( 'Wpbeginner ', 'flash-toolkit' ),
		'fa-wpforms'                             => __( 'Wpforms', 'flash-toolkit' ),
		'fa-wrench'                              => __( 'Wrench', 'flash-toolkit' ),
		'fa-xing'                                => __( 'Xing', 'flash-toolkit' ),
		'fa-xing-square'                         => __( 'Xing Square', 'flash-toolkit' ),
		'fa-y-combinator'                        => __( 'Y Combinator', 'flash-toolkit' ),
		'fa-y-combinator-square'                 => __( 'Y Combinator Square', 'flash-toolkit' ),
		'fa-yahoo'                               => __( 'Yahoo', 'flash-toolkit' ),
		'fa-yc'                                  => __( 'Yc', 'flash-toolkit' ),
		'fa-yc-square'                           => __( 'Yc Square', 'flash-toolkit' ),
		'fa-yelp'                                => __( 'Yelp', 'flash-toolkit' ),
		'fa-yen'                                 => __( 'Yen', 'flash-toolkit' ),
		'fa-yoast'                               => __( 'Yoast', 'flash-toolkit' ),
		'fa-youtube'                             => __( 'Youtube', 'flash-toolkit' ),
		'fa-youtube-play'                        => __( 'Youtube Play', 'flash-toolkit' ),
		'fa-youtube-square'                      => __( 'Youtube Square', 'flash-toolkit' ),
	) );
}
