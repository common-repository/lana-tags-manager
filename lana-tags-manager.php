<?php
/**
 * Plugin Name: Lana Tags Manager
 * Description: Add and manage custom meta tags.
 * Version: 1.0.0
 * Author: Lana Codes
 * Author URI: http://lana.codes/
 * Text Domain: lana-tags-manager
 * Domain Path: /languages
 */

defined( 'ABSPATH' ) or die();
define( 'LANA_TAGS_MANAGER_VERSION', '1.0.0' );
define( 'LANA_TAGS_MANAGER_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'LANA_TAGS_MANAGER_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Language
 * load
 */
load_plugin_textdomain( 'lana-tags-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

/**
 * Add plugin action links
 *
 * @param $links
 *
 * @return mixed
 */
function lana_tags_manager_add_plugin_action_links( $links ) {

	$settings_url = esc_url( admin_url( 'options-general.php?page=lana-tags-manager-settings.php' ) );

	/** add settings link */
	$settings_link = sprintf( '<a href="%s">%s</a>', $settings_url, __( 'Settings', 'lana-tags-manager' ) );
	array_unshift( $links, $settings_link );

	return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'lana_tags_manager_add_plugin_action_links' );

/**
 * Styles
 * load in admin
 */
function lana_tags_manager_admin_styles() {

	wp_register_style( 'lana-tags-manager', LANA_TAGS_MANAGER_DIR_URL . '/assets/css/lana-tags-manager-admin.css', array(), LANA_TAGS_MANAGER_VERSION );
	wp_enqueue_style( 'lana-tags-manager' );
}

add_action( 'admin_enqueue_scripts', 'lana_tags_manager_admin_styles' );

/**
 * JavaScript
 * load in admin
 */
function lana_tags_manager_admin_scripts() {

	wp_register_script( 'lana-tags-manager', LANA_TAGS_MANAGER_DIR_URL . '/assets/js/lana-tags-manager-admin.js', array(
		'jquery',
		'underscore',
		'jquery-ui-core',
		'jquery-ui-sortable',
	), LANA_TAGS_MANAGER_VERSION, true );
	wp_enqueue_script( 'lana-tags-manager' );
}

add_action( 'admin_enqueue_scripts', 'lana_tags_manager_admin_scripts' );

/**
 * Lana Tags Manager
 * add admin page
 */
function lana_tags_manager_admin_menu() {
	add_options_page( __( 'Lana Tags Manager Settings', 'lana-tags-manager' ), __( 'Lana Tags Manager', 'lana-tags-manager' ), 'manage_options', 'lana-tags-manager-settings.php', 'lana_tags_manager_settings_page' );

	/** call register settings function */
	add_action( 'admin_init', 'lana_tags_manager_register_settings' );
}

add_action( 'admin_menu', 'lana_tags_manager_admin_menu' );

/**
 * Register settings
 */
function lana_tags_manager_register_settings() {
	register_setting( 'lana-tags-manager-settings-group', 'lana_tags_manager_google_analytics_id' );
	register_setting( 'lana-tags-manager-settings-group', 'lana_tags_manager_google_tagmanager_id' );
	register_setting( 'lana-tags-manager-settings-group', 'lana_tags_manager_google_site_verification_code' );

	register_setting( 'lana-tags-manager-settings-group', 'lana_tags_manager_allow_in_post_type', array(
		'type'              => 'array',
		'sanitize_callback' => 'lana_tags_manager_sanitize_array',
	) );
	register_setting( 'lana-tags-manager-settings-group', 'lana_tags_manager_allow_in_taxonomy', array(
		'type'              => 'array',
		'sanitize_callback' => 'lana_tags_manager_sanitize_array',
	) );

	register_setting( 'lana-tags-manager-settings-group', 'lana_tags_manager_custom_meta_tags', array(
		'type'              => 'array',
		'sanitize_callback' => 'lana_tags_manager_sanitize_custom_meta_tags_array',
	) );
}

/**
 * Lana Tags Manager
 * get allow in post types
 * @return mixed
 */
function lana_tags_manager_get_allow_in_post_types() {
	$allow_in_post_types = get_option( 'lana_tags_manager_allow_in_post_type', array( 'post', 'page', 'attachment' ) );

	return (array) apply_filters( 'lana_tags_manager_allow_in_post_types', $allow_in_post_types );
}

/**
 * Lana Tags Manager
 * post types by support allow in post types
 *
 * @param $allow_in_post_types
 *
 * @return array
 */
function lana_tags_manager_post_types_by_support_allow_in_post_types( $allow_in_post_types ) {

	/**
	 * add supported post types to allow in post types
	 * @var array $allow_in_post_types
	 */
	$allow_in_post_types = array_merge( $allow_in_post_types, get_post_types_by_support( 'lana-tags-manager' ) );

	return $allow_in_post_types;
}

add_filter( 'lana_tags_manager_allow_in_post_types', 'lana_tags_manager_post_types_by_support_allow_in_post_types' );

/**
 * Lana Tags Manager
 * get allow in taxonomies
 * @return mixed
 */
function lana_tags_manager_get_allow_in_taxonomies() {
	$allow_in_taxonomies = get_option( 'lana_tags_manager_allow_in_taxonomy', array( 'category', 'post_tag' ) );

	return (array) apply_filters( 'lana_tags_manager_allow_in_taxonomies', $allow_in_taxonomies );
}

/**
 * Lana Tags Manager
 * sanitize array
 *
 * @param $values
 *
 * @return array
 */
function lana_tags_manager_sanitize_array( $values ) {

	if ( ! is_array( $values ) ) {
		if ( empty( $values ) ) {
			return array();
		}

		$values = array( $values );
	}

	return $values;
}

/**
 * Lana Archive Titles
 * sanitize custom meta tags array
 *
 * @param $values
 *
 * @return array
 */
function lana_tags_manager_sanitize_custom_meta_tags_array( $values ) {

	if ( ! is_array( $values ) ) {
		if ( empty( $values ) ) {
			return array();
		}

		$values = array( $values );
	}

	foreach ( $values as $i => &$tag ) {

		/** sanitize value */
		if ( isset( $tag['value'] ) ) {
			$tag['value'] = wp_kses( $tag['value'], lana_tags_manager_meta_allowed_html() );
		}

		/** unset empty value */
		if ( empty( $tag['value'] ) && ( empty( $tag['name'] ) || $tag['name'] == esc_attr__( 'Meta tag', 'lana-tags-manager' ) ) ) {
			unset( $values[ $i ] );
		}
	}

	return array_filter( $values );
}

/**
 * Lana Tags Manager
 * meta allowed html
 *
 * @return array
 */
function lana_tags_manager_meta_allowed_html() {
	return array(
		'meta' => array(
			'name'       => array(),
			'content'    => array(),
			'http-equiv' => array(),
			'charset'    => array(),
			'id'         => array(),
			'data-*'     => array(),
		),
	);
}

/**
 * Lana Tags Manager Settings page
 */
function lana_tags_manager_settings_page() {
	global $wp_post_types, $wp_taxonomies;

	/**
	 * Get
	 * post types
	 */
	$post_types = get_post_types( array(
		'public' => true,
	) );

	/**
	 * Get
	 * taxonomies
	 */
	$taxonomies = get_taxonomies( array(
		'public' => true,
	) );

	unset( $taxonomies['post_format'] );

	/**
	 * Get
	 * options
	 */
	$allow_in_post_types  = lana_tags_manager_get_allow_in_post_types();
	$supported_post_types = get_post_types_by_support( 'lana-tags-manager' );
	$allow_in_taxonomies  = lana_tags_manager_get_allow_in_taxonomies();

	/**
	 * get custom meta tags
	 * @var array $lana_tags_manager_custom_meta_tags
	 */
	$lana_tags_manager_custom_meta_tags = get_option( 'lana_tags_manager_custom_meta_tags', array(
		array( 'name' => '', 'value' => '' ),
	) );

	?>
    <div class="wrap">
        <h2><?php _e( 'Lana Tags Manager Settings', 'lana-tags-manager' ); ?></h2>

        <hr/>
        <a href="<?php echo esc_url( 'http://lana.codes/' ); ?>" target="_blank">
            <img src="<?php echo esc_url( LANA_TAGS_MANAGER_DIR_URL . '/assets/img/plugin-header.png' ); ?>"
                 alt="<?php esc_attr_e( 'Lana Codes', 'lana-tags-manager' ); ?>"/>
        </a>
        <hr/>

        <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
			<?php settings_fields( 'lana-tags-manager-settings-group' ); ?>

            <h2 class="title"><?php _e( 'Identifier Settings', 'lana-tags-manager' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="lana-tags-manager-google-analytics-id">
							<?php _e( 'Google Analytics ID', 'lana-tags-manager' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_tags_manager_google_analytics_id"
                               id="lana-tags-manager-google-analytics-id" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'lana_tags_manager_google_analytics_id', '' ) ); ?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="lana-tags-manager-google-tagmanager-id">
							<?php _e( 'Google Tag Manager ID', 'lana-tags-manager' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_tags_manager_google_tagmanager_id"
                               id="lana-tags-manager-google-tagmanager-id" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'lana_tags_manager_google_tagmanager_id', '' ) ); ?>"/>
                    </td>
                </tr>
            </table>

            <h2 class="title"><?php _e( 'Verification Settings', 'lana-tags-manager' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="lana-tags-manager-google-site-verification-code">
							<?php _e( 'Google Site Verification Code', 'lana-tags-manager' ); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text" name="lana_tags_manager_google_site_verification_code"
                               id="lana-tags-manager-google-site-verification-code" class="regular-text"
                               value="<?php echo esc_attr( get_option( 'lana_tags_manager_google_site_verification_code', '' ) ); ?>"/>
                    </td>
                </tr>
            </table>

            <br/>

            <h2 class="title"><?php _e( 'Custom Meta Tags Settings', 'lana-tags-manager' ); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label>
							<?php _e( 'Allow in Post Types', 'lana-tags-manager' ); ?>
                        </label>
                    </th>
                    <td>
                        <fieldset>
							<?php if ( ! empty( $post_types ) ) : ?>
								<?php foreach ( $post_types as $post_type ) : ?>
                                    <label for="<?php echo esc_attr( $post_type ); ?>">
                                        <input type="checkbox" name="lana_tags_manager_allow_in_post_type[]"
                                               id="lana-tags-manager-allow-in-post-type-<?php echo esc_attr( $post_type ); ?>"
                                               value="<?php echo esc_attr( $post_type ); ?>" <?php checked( in_array( $post_type, $allow_in_post_types ) ); ?>>
										<?php $post_type_object = $wp_post_types[ $post_type ]; ?>
										<?php echo esc_html( $post_type_object->labels->singular_name ); ?>
                                        <code><?php _e( 'post type', 'lana-tags-manager' ); ?></code>
                                        <small>
                                            (<?php echo esc_html( $post_type ); ?>)
                                        </small>
										<?php if ( in_array( $post_type, $supported_post_types ) ): ?>
                                            <span title="<?php esc_attr_e( 'This checkbox is disabled because it can only be changed using PHP code', 'lana-tags-manager' ); ?>">
											    <?php echo sprintf( __( ' - feature by <code>add_post_type_support()</code>', 'lana-tags-manager' ) ); ?>
                                            </span>
										<?php endif; ?>
                                    </label>
                                    <br/>
								<?php endforeach; ?>
							<?php endif; ?>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label>
							<?php _e( 'Allow in Taxonomies', 'lana-tags-manager' ); ?>
                        </label>
                    </th>
                    <td>
                        <fieldset>
							<?php if ( ! empty( $taxonomies ) ) : ?>
								<?php foreach ( $taxonomies as $taxonomy ) : ?>
                                    <label for="<?php echo esc_attr( $taxonomy ); ?>">
                                        <input type="checkbox" name="lana_tags_manager_allow_in_taxonomy[]"
                                               id="lana-tags-manager-allow_in-taxonomy-<?php echo esc_attr( $taxonomy ); ?>"
                                               value="<?php echo esc_attr( $taxonomy ); ?>" <?php checked( in_array( $taxonomy, $allow_in_taxonomies ) ); ?>>
										<?php $taxonomy_object = $wp_taxonomies[ $taxonomy ]; ?>

										<?php
										/** get taxonomy post types */
										$taxonomy_post_type_names = array_map( function ( $taxonomy_post_type ) use ( $wp_post_types ) {
											$post_type_object = $wp_post_types[ $taxonomy_post_type ];

											return $post_type_object->labels->singular_name;
										}, $taxonomy_object->object_type );

										echo esc_html( implode( ', ', $taxonomy_post_type_names ) );
										?>
                                        <code><?php _e( 'post type', 'lana-tags-manager' ); ?></code>
                                        - <?php echo esc_html( $taxonomy_object->labels->singular_name ); ?>
                                        <code><?php _e( 'taxonomy', 'lana-tags-manager' ); ?></code>
                                        <small>
                                            (<?php echo esc_html( $taxonomy ); ?>)
                                        </small>
                                    </label>
                                    <br/>
								<?php endforeach; ?>
							<?php endif; ?>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <br/>

            <h2 class="title"><?php _e( 'Custom Meta Tags', 'lana-tags-manager' ); ?></h2>
            <p class="description">
				<?php _e( 'these custom meta tags will be visible on all pages', 'lana-tags-manager' ); ?>
            </p>
            <table class="form-table lana-custom-meta-tags-table">
                <tbody>
				<?php if ( ! empty( $lana_tags_manager_custom_meta_tags ) ): ?>
					<?php foreach ( $lana_tags_manager_custom_meta_tags as $lana_tags_manager_custom_meta_tag_id => $lana_tags_manager_custom_meta_tag ): ?>

						<?php
						$lana_tags_manager_custom_meta_tag_name = $lana_tags_manager_custom_meta_tag['name'];

						/** default meta tag name */
						if ( empty( $lana_tags_manager_custom_meta_tag_name ) ) {
							$lana_tags_manager_custom_meta_tag_name = __( 'Meta tag', 'lana-tags-manager' );
						}
						?>

                        <tr data-id="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>">
                            <th scope="row" class="lana-custom-meta-tag-name">
                                <label class="lana-custom-meta-tag-name-label">
									<?php echo esc_html( $lana_tags_manager_custom_meta_tag_name ); ?>
                                </label>
                                <input type="hidden"
                                       name="lana_tags_manager_custom_meta_tags[<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>][name]"
                                       class="lana-custom-meta-tag-name-input"
                                       value="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_name ); ?>"/>
                            </th>
                            <td>
                                <input type="text"
                                       name="lana_tags_manager_custom_meta_tags[<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>][value]"
                                       class="regular-text"
                                       value="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag['value'] ); ?>"
                                       placeholder="<?php echo esc_attr( '<meta name="lana-id" content="lana.codes" />' ); ?>"
                                       aria-label="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>

                                <ul class="actions hidden">
                                    <li>
                                        <a href="#" class="lana-move-meta-tag">
                                            <span class="dashicons dashicons-move"></span> <?php _e( 'Move', 'lana-tags-manager' ); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="lana-remove-meta-tag">
                                            <span class="dashicons dashicons-trash"></span> <?php _e( 'Delete', 'lana-tags-manager' ); ?>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
					<?php endforeach; ?>
				<?php endif; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <button type="button" class="button-secondary lana-add-meta-tag">
                            <span class="dashicons dashicons-plus"></span>
							<?php _e( 'Add Meta Tag', 'lana-tags-manager' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>

            <p class="submit">
                <input type="submit" class="button-primary"
                       value="<?php esc_attr_e( 'Save Changes', 'lana-tags-manager' ); ?>"/>
            </p>

        </form>
    </div>

    <script type="text/html" id="tmpl-lana-tags-manager-lana-meta-tag-html">
        <tr data-id="{{-data.id}}">
            <th scope="row" class="lana-custom-meta-tag-name">
                <label class="lana-custom-meta-tag-name-label">
					<?php _e( 'Meta tag', 'lana-tags-manager' ); ?>
                </label>
                <input type="hidden" name="lana_tags_manager_custom_meta_tags[{{-data.id}}][name]"
                       class="lana-custom-meta-tag-name-input"
                       value="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>
            </th>
            <td>
                <input type="text"
                       name="lana_tags_manager_custom_meta_tags[{{-data.id}}][value]"
                       class="regular-text"
                       value=""
                       placeholder="<?php echo esc_attr( '<meta name="lana-id" content="lana.codes" />' ); ?>"
                       aria-label="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>

                <ul class="actions hidden">
                    <li>
                        <a href="#" class="lana-move-meta-tag">
                            <span class="dashicons dashicons-move"></span> <?php _e( 'Move', 'lana-tags-manager' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="lana-remove-meta-tag">
                            <span class="dashicons dashicons-trash"></span> <?php _e( 'Delete', 'lana-tags-manager' ); ?>
                        </a>
                    </li>
                </ul>
            </td>
        </tr>
    </script>
	<?php
}

/**
 * Callback to register
 * Lana Tags Manager Metabox
 */
function lana_tags_manager_add_meta_box() {

	$post_types = lana_tags_manager_get_allow_in_post_types();

	add_meta_box( 'lana-tags-manager-metabox', __( 'Lana Tags Manager', 'lana-tags-manager' ), 'lana_tags_manager_custom_meta_tags_meta_box_render', $post_types );
}

add_action( 'add_meta_boxes', 'lana_tags_manager_add_meta_box' );

/**
 * Render
 * Lana Tags Manager - Custom Meta Tags metabox
 *
 * @param $post
 */
function lana_tags_manager_custom_meta_tags_meta_box_render( $post ) {

	$allow_in_post_types = lana_tags_manager_get_allow_in_post_types();

	/** check allow in option */
	if ( ! in_array( $post->post_type, $allow_in_post_types ) ) :
		?>
        <p>
			<?php printf( __( 'Lana Tags Manager is not allowed in this post type. Go to the <a href="%s">Settings</a> page to allow it.', 'lana-tags-manager' ), esc_url( admin_url( 'options-general.php?page=lana-tags-manager-settings.php' ) ) ); ?>
        </p>
		<?php
		return;
	endif;

	wp_nonce_field( basename( __FILE__ ), 'lana_tags_manager_custom_meta_tags_nonce_field' );

	$lana_tags_manager_custom_meta_tags = get_post_meta( $post->ID, 'lana_tags_manager_custom_meta_tags', true );

	/** default custom meta tags */
	if ( empty( $lana_tags_manager_custom_meta_tags ) ) {
		$lana_tags_manager_custom_meta_tags = array(
			array( 'name' => '', 'value' => '' ),
		);
	}
	?>
    <table class="form-table lana-custom-meta-tags-table">
        <tbody>
		<?php if ( ! empty( $lana_tags_manager_custom_meta_tags ) ): ?>
			<?php foreach ( $lana_tags_manager_custom_meta_tags as $lana_tags_manager_custom_meta_tag_id => $lana_tags_manager_custom_meta_tag ): ?>

				<?php
				$lana_tags_manager_custom_meta_tag_name = $lana_tags_manager_custom_meta_tag['name'];

				/** default meta tag name */
				if ( empty( $lana_tags_manager_custom_meta_tag_name ) ) {
					$lana_tags_manager_custom_meta_tag_name = __( 'Meta tag', 'lana-tags-manager' );
				}
				?>

                <tr data-id="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>">
                    <th scope="row" class="lana-custom-meta-tag-name">
                        <label class="lana-custom-meta-tag-name-label">
							<?php echo esc_html( $lana_tags_manager_custom_meta_tag_name ); ?>
                        </label>
                        <input type="hidden"
                               name="lana_tags_manager_custom_meta_tags[<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>][name]"
                               class="lana-custom-meta-tag-name-input"
                               value="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_name ); ?>"/>
                    </th>
                    <td>
                        <input type="text"
                               name="lana_tags_manager_custom_meta_tags[<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>][value]"
                               class="regular-text"
                               value="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag['value'] ); ?>"
                               placeholder="<?php echo esc_attr( '<meta name="lana-id" content="lana.codes" />' ); ?>"
                               aria-label="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>

                        <ul class="actions hidden">
                            <li>
                                <a href="#" class="lana-move-meta-tag">
                                    <span class="dashicons dashicons-move"></span> <?php _e( 'Move', 'lana-tags-manager' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="lana-remove-meta-tag">
                                    <span class="dashicons dashicons-trash"></span> <?php _e( 'Delete', 'lana-tags-manager' ); ?>
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
			<?php endforeach; ?>
		<?php endif; ?>
        </tbody>
        <tfoot>
        <tr>
            <th scope="row"></th>
            <td>
                <button type="button" class="button-secondary lana-add-meta-tag">
                    <span class="dashicons dashicons-plus"></span>
					<?php _e( 'Add Meta Tag', 'lana-tags-manager' ); ?>
                </button>
            </td>
        </tr>
        </tfoot>
    </table>

    <script type="text/html" id="tmpl-lana-tags-manager-lana-meta-tag-html">
        <tr data-id="{{-data.id}}">
            <th scope="row" class="lana-custom-meta-tag-name">
                <label class="lana-custom-meta-tag-name-label">
					<?php _e( 'Meta tag', 'lana-tags-manager' ); ?>
                </label>
                <input type="hidden" name="lana_tags_manager_custom_meta_tags[{{-data.id}}][name]"
                       class="lana-custom-meta-tag-name-input"
                       value="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>
            </th>
            <td>
                <input type="text"
                       name="lana_tags_manager_custom_meta_tags[{{-data.id}}][value]"
                       class="regular-text"
                       value=""
                       placeholder="<?php echo esc_attr( '<meta name="lana-id" content="lana.codes" />' ); ?>"
                       aria-label="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>

                <ul class="actions hidden">
                    <li>
                        <a href="#" class="lana-move-meta-tag">
                            <span class="dashicons dashicons-move"></span> <?php _e( 'Move', 'lana-tags-manager' ); ?>
                        </a>
                    </li>
                    <li>
                        <a href="#" class="lana-remove-meta-tag">
                            <span class="dashicons dashicons-trash"></span> <?php _e( 'Delete', 'lana-tags-manager' ); ?>
                        </a>
                    </li>
                </ul>
            </td>
        </tr>
    </script>
	<?php
}

/**
 * Lana Tags Manager - Custom Meta Tags
 * edit form field
 *
 * @param WP_Term $taxonomy
 */
function lana_tags_manager_custom_meta_tags_edit_term_form_fields( $taxonomy ) {

	$allow_in_taxonomies = lana_tags_manager_get_allow_in_taxonomies();

	/** check allow in option */
	if ( ! in_array( $taxonomy->taxonomy, $allow_in_taxonomies ) ) {
		return;
	}

	$lana_tags_manager_custom_meta_tags = get_option( $taxonomy->term_id . '_lana_tags_manager_custom_meta_tags' );

	/** default custom meta tags */
	if ( empty( $lana_tags_manager_custom_meta_tags ) ) {
		$lana_tags_manager_custom_meta_tags = array(
			array( 'name' => '', 'value' => '' ),
		);
	}
	?>

    <tr class="form-field lana-tags-manager-title-wrap">
        <th colspan="2">
            <h2>
				<?php _e( 'Lana Custom Meta Tags', 'lana-tags-manager' ); ?>
            </h2>
        </th>
    </tr>
    <tr class="form-field lana-tags-manager-custom-meta-tags-wrap">
        <td colspan="2">
            <table class="form-table lana-custom-meta-tags-table">
                <tbody>
				<?php if ( ! empty( $lana_tags_manager_custom_meta_tags ) ): ?>
					<?php foreach ( $lana_tags_manager_custom_meta_tags as $lana_tags_manager_custom_meta_tag_id => $lana_tags_manager_custom_meta_tag ): ?>

						<?php
						$lana_tags_manager_custom_meta_tag_name = $lana_tags_manager_custom_meta_tag['name'];

						/** default meta tag name */
						if ( empty( $lana_tags_manager_custom_meta_tag_name ) ) {
							$lana_tags_manager_custom_meta_tag_name = __( 'Meta tag', 'lana-tags-manager' );
						}
						?>

                        <tr data-id="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>">
                            <th scope="row" class="lana-custom-meta-tag-name">
                                <label class="lana-custom-meta-tag-name-label">
									<?php echo esc_html( $lana_tags_manager_custom_meta_tag_name ); ?>
                                </label>
                                <input type="hidden"
                                       name="lana_tags_manager_custom_meta_tags[<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>][name]"
                                       class="lana-custom-meta-tag-name-input"
                                       value="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_name ); ?>"/>
                            </th>
                            <td>
                                <input type="text"
                                       name="lana_tags_manager_custom_meta_tags[<?php echo esc_attr( $lana_tags_manager_custom_meta_tag_id ); ?>][value]"
                                       class="regular-text"
                                       value="<?php echo esc_attr( $lana_tags_manager_custom_meta_tag['value'] ); ?>"
                                       placeholder="<?php echo esc_attr( '<meta name="lana-id" content="lana.codes" />' ); ?>"
                                       aria-label="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>

                                <ul class="actions hidden">
                                    <li>
                                        <a href="#" class="lana-move-meta-tag">
                                            <span class="dashicons dashicons-move"></span> <?php _e( 'Move', 'lana-tags-manager' ); ?>
                                        </a>
                                    </li>
                                    <li>
                                        <a href="#" class="lana-remove-meta-tag">
                                            <span class="dashicons dashicons-trash"></span> <?php _e( 'Delete', 'lana-tags-manager' ); ?>
                                        </a>
                                    </li>
                                </ul>
                            </td>
                        </tr>
					<?php endforeach; ?>
				<?php endif; ?>
                </tbody>
                <tfoot>
                <tr>
                    <th scope="row"></th>
                    <td>
                        <button type="button" class="button-secondary lana-add-meta-tag">
                            <span class="dashicons dashicons-plus"></span>
							<?php _e( 'Add Meta Tag', 'lana-tags-manager' ); ?>
                        </button>
                    </td>
                </tr>
                </tfoot>
            </table>

            <script type="text/html" id="tmpl-lana-tags-manager-lana-meta-tag-html">
                <tr data-id="{{-data.id}}">
                    <th scope="row" class="lana-custom-meta-tag-name">
                        <label class="lana-custom-meta-tag-name-label">
							<?php _e( 'Meta tag', 'lana-tags-manager' ); ?>
                        </label>
                        <input type="hidden" name="lana_tags_manager_custom_meta_tags[{{-data.id}}][name]"
                               class="lana-custom-meta-tag-name-input"
                               value="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>
                    </th>
                    <td>
                        <input type="text"
                               name="lana_tags_manager_custom_meta_tags[{{-data.id}}][value]"
                               class="regular-text"
                               value=""
                               placeholder="<?php echo esc_attr( '<meta name="lana-id" content="lana.codes" />' ); ?>"
                               aria-label="<?php esc_attr_e( 'Meta tag', 'lana-tags-manager' ); ?>"/>

                        <ul class="actions hidden">
                            <li>
                                <a href="#" class="lana-move-meta-tag">
                                    <span class="dashicons dashicons-move"></span> <?php _e( 'Move', 'lana-tags-manager' ); ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" class="lana-remove-meta-tag">
                                    <span class="dashicons dashicons-trash"></span> <?php _e( 'Delete', 'lana-tags-manager' ); ?>
                                </a>
                            </li>
                        </ul>
                    </td>
                </tr>
            </script>
        </td>
    </tr>
	<?php
}

add_action( 'category_edit_form_fields', 'lana_tags_manager_custom_meta_tags_edit_term_form_fields', 100 );
add_action( 'post_tag_edit_form_fields', 'lana_tags_manager_custom_meta_tags_edit_term_form_fields', 100 );

/**
 * Lana Tags Manager
 * save custom meta tags
 *
 * @param $post_id
 * @param WP_Post $post
 */
function lana_tags_manager_custom_meta_tags_save_post( $post_id, $post ) {

	if ( ! isset( $_POST['lana_tags_manager_custom_meta_tags_nonce_field'] ) || ! wp_verify_nonce( $_POST['lana_tags_manager_custom_meta_tags_nonce_field'], basename( __FILE__ ) ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	$allow_in_post_types = lana_tags_manager_get_allow_in_post_types();

	if ( ! in_array( $post->post_type, $allow_in_post_types ) ) {
		return;
	}

	$custom_meta_tags = lana_tags_manager_sanitize_custom_meta_tags_array( $_POST['lana_tags_manager_custom_meta_tags'] );

	/** check meta tags */
	if ( ! empty( $custom_meta_tags ) ) {

		/**
		 * Update
		 * custom meta tags
		 */
		update_post_meta( $post->ID, 'lana_tags_manager_custom_meta_tags', $custom_meta_tags );
	} else {

		/**
		 * Delete
		 * custom meta tags
		 */
		delete_post_meta( $post->ID, 'lana_tags_manager_custom_meta_tags' );
	}
}

add_action( 'save_post', 'lana_tags_manager_custom_meta_tags_save_post', 10, 2 );

/**
 * Lana Tags Manager
 * save custom meta tags
 *
 * @param $term_id
 * @param $tt_id
 * @param $taxonomy
 */
function lana_tags_manager_custom_meta_tags_save_term( $term_id, $tt_id, $taxonomy ) {

	$allow_in_taxonomies = lana_tags_manager_get_allow_in_taxonomies();

	/** check allow in option */
	if ( ! in_array( $taxonomy, $allow_in_taxonomies ) ) {
		return;
	}

	if ( empty( $tt_id ) ) {
		return;
	}

	$custom_meta_tags = lana_tags_manager_sanitize_custom_meta_tags_array( $_POST['lana_tags_manager_custom_meta_tags'] );

	/** check meta tags */
	if ( ! empty( $custom_meta_tags ) ) {

		/**
		 * Update
		 * custom meta tags
		 */
		update_option( $term_id . '_lana_tags_manager_custom_meta_tags', $custom_meta_tags );
	} else {

		/**
		 * Delete
		 * custom meta tags
		 */
		delete_option( $term_id . '_lana_tags_manager_custom_meta_tags' );
	}
}

add_action( 'edit_term', 'lana_tags_manager_custom_meta_tags_save_term', 10, 3 );

/**
 * Add google tagmanager
 * to head
 */
function lana_tags_manager_add_google_tagmanager_to_head() {

	$google_tagmanager_id = get_option( 'lana_tags_manager_google_tagmanager_id', '' );

	/** check tagmanager id */
	if ( empty( $google_tagmanager_id ) ) {
		return;
	}
	?>
    <!-- Google Tag Manager -->
    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                'gtm.start': new Date().getTime(), event: 'gtm.js'
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s), dl = l !== 'dataLayer' ? '&l=' + l : '';
            j.async = true;
            j.src =
                'https://www.googletagmanager.com/gtm.js?id=' + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, 'script', 'dataLayer', '<?php echo esc_js( $google_tagmanager_id ); ?>');
    </script>
    <!-- End Google Tag Manager -->
	<?php
}

add_action( 'wp_head', 'lana_tags_manager_add_google_tagmanager_to_head' );

/**
 * Add google tagmanager
 * to body
 */
function lana_tags_manager_add_google_tagmanager_to_body() {

	$google_tagmanager_id = get_option( 'lana_tags_manager_google_tagmanager_id', '' );

	/** check tagmanager id */
	if ( empty( $google_tagmanager_id ) ) {
		return;
	}
	?>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $google_tagmanager_id ); ?>"
                height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
	<?php
}

add_action( 'wp_body_open', 'lana_tags_manager_add_google_tagmanager_to_body' );

/**
 * Add google analytics
 * to head
 */
function lana_tags_manager_add_google_analytics_to_head() {

	$google_analytics_id = get_option( 'lana_tags_manager_google_analytics_id', '' );

	/** check analytics id */
	if ( empty( $google_analytics_id ) ) {
		return;
	}
	?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async
            src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_attr( $google_analytics_id ); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];

        function gtag() {
            dataLayer.push(arguments);
        }

        gtag('js', new Date());
        gtag('config', '<?php echo esc_js( $google_analytics_id ); ?>');
    </script>
	<?php
}

add_action( 'wp_head', 'lana_tags_manager_add_google_analytics_to_head' );

/**
 * Add google site verification
 * to head
 */
function lana_tags_manager_add_google_site_verification_to_head() {

	$google_site_verification_code = get_option( 'lana_tags_manager_google_site_verification_code', '' );

	/** check analytics id */
	if ( empty( $google_site_verification_code ) ) {
		return;
	}
	?>
    <meta name="google-site-verification" content="<?php echo esc_attr( $google_site_verification_code ); ?>"/>
	<?php
}

add_action( 'wp_head', 'lana_tags_manager_add_google_site_verification_to_head' );

/**
 * Add custom meta tags
 * to head
 */
function lana_tags_manager_add_global_custom_meta_tags_to_head() {
	/**
	 * get custom meta tags
	 * @var array $custom_meta_tags
	 */
	$custom_meta_tags = get_option( 'lana_tags_manager_custom_meta_tags', array() );

	/** check custom meta tags */
	if ( ! empty( $custom_meta_tags ) ) {
		foreach ( $custom_meta_tags as $custom_meta_tag ) {

			/** check value */
			if ( ! empty( $custom_meta_tag['value'] ) ) {
				echo wp_kses( $custom_meta_tag['value'], lana_tags_manager_meta_allowed_html() );
			}
		}
	}
}

add_action( 'wp_head', 'lana_tags_manager_add_global_custom_meta_tags_to_head' );

/**
 * Add post custom meta tags
 * to head
 */
function lana_tags_manager_add_post_custom_meta_tags_to_head() {
	global $post;

	/** check page */
	if ( ! is_single() ) {
		return;
	}

	/** check post */
	if ( ! is_a( $post, 'WP_Post' ) ) {
		return;
	}

	/** check post type */
	if ( 'post' != $post->post_type ) {
		return;
	}

	$allow_in_post_types = lana_tags_manager_get_allow_in_post_types();

	/** check allow in option */
	if ( ! in_array( $post->post_type, $allow_in_post_types ) ) {
		return;
	}

	/**
	 * get custom meta tags
	 * @var array $custom_meta_tags
	 */
	$custom_meta_tags = get_post_meta( $post->ID, 'lana_tags_manager_custom_meta_tags', true );

	/** check custom meta tags */
	if ( ! empty( $custom_meta_tags ) ) {
		foreach ( $custom_meta_tags as $custom_meta_tag ) {

			/** check value */
			if ( ! empty( $custom_meta_tag['value'] ) ) {
				echo wp_kses( $custom_meta_tag['value'], lana_tags_manager_meta_allowed_html() );
			}
		}
	}
}

add_action( 'wp_head', 'lana_tags_manager_add_post_custom_meta_tags_to_head' );

/**
 * Add page custom meta tags
 * to head
 */
function lana_tags_manager_add_page_custom_meta_tags_to_head() {
	global $post;

	/** check page */
	if ( ! is_home() && ! is_front_page() && ! is_page() ) {
		return;
	}

	/** check post */
	if ( ! is_a( $post, 'WP_Post' ) ) {
		return;
	}

	/** check post type */
	if ( 'page' != $post->post_type ) {
		return;
	}

	$allow_in_post_types = lana_tags_manager_get_allow_in_post_types();

	/** check allow in option */
	if ( ! in_array( $post->post_type, $allow_in_post_types ) ) {
		return;
	}

	/**
	 * get custom meta tags
	 * @var array $custom_meta_tags
	 */
	$custom_meta_tags = get_post_meta( $post->ID, 'lana_tags_manager_custom_meta_tags', true );

	/** check custom meta tags */
	if ( ! empty( $custom_meta_tags ) ) {
		foreach ( $custom_meta_tags as $custom_meta_tag ) {

			/** check value */
			if ( ! empty( $custom_meta_tag['value'] ) ) {
				echo wp_kses( $custom_meta_tag['value'], lana_tags_manager_meta_allowed_html() );
			}
		}
	}
}

add_action( 'wp_head', 'lana_tags_manager_add_page_custom_meta_tags_to_head' );

/**
 * Add attachment custom meta tags
 * to head
 */
function lana_tags_manager_add_attachment_custom_meta_tags_to_head() {
	global $post;

	/** check post */
	if ( ! is_a( $post, 'WP_Post' ) ) {
		return;
	}

	/** check post type */
	if ( ! is_single() || 'attachment' != $post->post_type ) {
		return;
	}

	$allow_in_post_types = lana_tags_manager_get_allow_in_post_types();

	/** check allow in option */
	if ( ! in_array( $post->post_type, $allow_in_post_types ) ) {
		return;
	}

	/**
	 * get custom meta tags
	 * @var array $custom_meta_tags
	 */
	$custom_meta_tags = get_post_meta( $post->ID, 'lana_tags_manager_custom_meta_tags', true );

	/** check custom meta tags */
	if ( ! empty( $custom_meta_tags ) ) {
		foreach ( $custom_meta_tags as $custom_meta_tag ) {

			/** check value */
			if ( ! empty( $custom_meta_tag['value'] ) ) {
				echo wp_kses( $custom_meta_tag['value'], lana_tags_manager_meta_allowed_html() );
			}
		}
	}
}

add_action( 'wp_head', 'lana_tags_manager_add_attachment_custom_meta_tags_to_head' );

/**
 * Add term custom meta tags
 * to head
 */
function lana_tags_manager_add_term_custom_meta_tags_to_head() {

	/** check page */
	if ( ! is_tax() && ! is_category() && ! is_tag() ) {
		return;
	}

	/** @var WP_Term $term */
	$term = get_queried_object();

	if ( ! is_a( $term, 'WP_Term' ) ) {
		return;
	}

	$allow_in_taxonomies = lana_tags_manager_get_allow_in_taxonomies();

	/** check allow in option */
	if ( ! in_array( $term->taxonomy, $allow_in_taxonomies ) ) {
		return;
	}

	/**
	 * get custom meta tags
	 * @var array $custom_meta_tags
	 */
	$custom_meta_tags = get_option( $term->term_id . '_lana_tags_manager_custom_meta_tags', array() );

	/** check custom meta tags */
	if ( ! empty( $custom_meta_tags ) ) {
		foreach ( $custom_meta_tags as $custom_meta_tag ) {

			/** check value */
			if ( ! empty( $custom_meta_tag['value'] ) ) {
				echo wp_kses( $custom_meta_tag['value'], lana_tags_manager_meta_allowed_html() );
			}
		}
	}
}

add_action( 'wp_head', 'lana_tags_manager_add_term_custom_meta_tags_to_head' );
