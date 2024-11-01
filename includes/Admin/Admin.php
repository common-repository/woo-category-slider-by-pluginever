<?php
namespace WooCommerceCategorySlider\Admin;

use WooCommerceCategorySlider\Controllers\SliderElements;

defined( 'ABSPATH' ) || exit();

/**
 * Admin class.
 *
 * @since 1.0.1
 * @package WooCommerceVariationSwatchesPro\Admin
 */
class Admin {
	/**
	 * Meta Unique Key prefix.
	 *
	 * @var string
	 */
	protected $uniq_key;

	/**
	 * Admin constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'wc_slider_load_admin_assets' ) );

		add_action( 'save_post_wc_category_slider', array( $this, 'wc_category_slider_update_settings' ) );
		add_action( 'add_meta_boxes', array( $this, 'wc_slider_register_meta_boxes' ), 10 );
		add_action( 'add_meta_boxes', array( $this, 'wc_slider_remove_meta_boxes' ), 10 );

		$this->uniq_key = sanitize_key( 'wc-category-slider-v4_0_9' );
		add_action( 'admin_notices', array( $this, 'promotional_offer' ) );
		add_action( 'wp_ajax_' . $this->uniq_key . '-dismiss-promotional-offer-notice', array( $this, 'dismiss_promotional_offer' ) );

		add_action( 'wp_ajax_wc_slider_get_categories', array( $this, 'wc_slider_get_categories_ajax_callback' ) );
		add_action( 'admin_footer', array( $this, 'wc_category_slider_print_js_template' ) );
		add_filter( 'admin_footer_text', array( $this, 'admin_footer_text' ), PHP_INT_MAX );
		add_filter( 'update_footer', array( $this, 'update_footer' ), PHP_INT_MAX );
	}

	/**
	 * Add Scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_category_slider_print_js_template() {
		global $current_screen;
		if ( empty( $current_screen->id ) || ( 'wc_category_slider' !== $current_screen->id ) ) {
			return;
		}

		$disabled = is_plugin_active( 'wc-category-slider-pro/wc-category-slider-pro.php' ) ? '' : 'disabled';

		?>
		<script type="text/html" id="tmpl-wc-category-slide">
			<div class="ever-col-6 ever-slider-container">
				<div class="ever-slide">
					<div class="ever-slide-header">
						<div class="ever-slide-headerleft">{{data.name}}</div>
					</div>
					<div class="ever-slide-main">
						<div class="ever-slide-thumbnail">
							<img src="{{data.image}}" class="img-prev" alt="{{data.name}}">
							<input type="hidden" name="categories[{{data.term_id}}][image_id]" class="wccs-slider img-id" value="{{data.image_id}}">
							<div class="ever-slide-thumbnail-tools">
								<?php if ( ! is_plugin_active( 'wc-category-slider-pro/wc-category-slider-pro.php' ) ) { ?>
									<div class="promotion-text">
										<span>Upgrade to <a href="https://www.pluginever.com/plugins/woocommerce-category-slider-pro/">PRO</a>, to change the Image</span>
									</div>
								<?php } ?>
								<div class="image-action">
									<a href="javascript:void(0)" class="edit-image"><span class="dashicons dashicons-edit" title="<?php esc_html_e( 'Change Image', 'woo-category-slider-by-pluginever' ); ?>"></span></a>
									<a href="javascript:void(0)" class="delete-image" title="<?php esc_html_e( 'Delete Image', 'woo-category-slider-by-pluginever' ); ?>"><span class="dashicons dashicons-trash"></span></a>
								</div>

							</div>
						</div>
						<div class="ever-slide-inner">

							<!--title-->
							<div class="ever-slide-title">
								<input class="ever-slide-url-inputbox regular-text" name="categories[{{data.term_id}}][name]" placeholder="{{data.name}}" type="text" value="{{data.name}}" <?php echo $disabled; ?>>
							</div>

							<!--description-->
							<div class="ever-slide-captionarea">
								<textarea name="categories[{{data.term_id}}][description]" id="caption-{{data.term_id}}" class="ever-slide-captionarea-textfield" data-gramm_editor="false" placeholder="Description" <?php echo $disabled; ?>>{{data.description}}</textarea>
							</div>

							<!--icon-->
							<div class="ever-slide-icon">
								<select name="categories[{{data.term_id}}][icon]" id="categories-{{data.term_id}}-icon" class="select-2">
									<option value=""><?php esc_html_e( 'No Icon', 'woo-category-slider-by-pluginever' ); ?></option>
									<?php

									$icons = wc_slider_get_icon_list();

									ob_start();

									if ( ! is_plugin_active( 'wc-category-slider-pro/wc-category-slider-pro.php' ) ) {

										for ( $a = 0; $a < 2; $a++ ) {

											$offset       = 0 === $a ? 0 : 10;
											$length       = 0 === $a ? 10 : - 1;
											$sliced_icons = array_slice( $icons, $offset, $length );

											$label    = sprintf( __( '%s Icons', 'woo-category-slider-by-pluginever' ), 0 === $a ? 'Free' : 'Pro' );
											$disabled = 0 === $a ? '' : 'disabled';

											echo "<optgroup label='{$label}'>";

											foreach ( $sliced_icons as $key => $value ) {
												printf( '<option value="%s" %s  <# if( data.icon == "' . $key . '" ){ #> selected <# } #> >&#x%s; &nbsp; %1$s</option>', $key, $disabled, $value );
											}

											echo '</optgroup>';

										}
									} else {
										foreach ( $icons as $key => $value ) {
											printf( '<option value="%s"  <# if( data.icon == "' . $key . '" ){ #> selected <# } #> >&#x%s; &nbsp; %1$s</option>', $key, $value );
										}
									}

									$output = ob_get_clean();

									echo $output;

									?>

								</select>


							</div>

							<!--url-->
							<div class="ever-slide-url">
								<input name="categories[{{data.term_id}}][url]" class="ever-slide-url-inputbox regular-text" placeholder="{{data.url}}" value="{{data.url}}" type="url" <?php echo esc_attr( $disabled ); ?>>
							</div>

						</div>
					</div>
				</div>
			</div>

			<?php
			if ( is_plugin_active( 'wc-category-slider-pro/wc-category-slider-pro.php' ) ) {
				// === category image change js scripts ===
				?>
				<#

				jQuery(document).on('click', '.edit-image', function (e) {			e.preventDefault();			e.stopPropagation();			e.stopImmediatePropagation();


				var $parent = jQuery(this).parentsUntil('.ever-slide-thumbnail');

				var $img_prev = $parent.siblings('.img-prev');			var $img_id = $parent.siblings('.img-id');

				var image = wp.media({			title: 'Upload Image'			})			.open().on('select', function () {			var uploaded_image = image.state().get('selection').first();			var image_url = uploaded_image.toJSON().url;			var image_id = uploaded_image.toJSON().id;			$img_prev.prop('src', image_url);			$img_id.val(image_id);			});

				});

				jQuery(document).on('click', '.delete-image', function(e){			e.preventDefault();			e.stopPropagation();			e.stopImmediatePropagation();

				var $parent = jQuery(this).parentsUntil('.ever-slide-thumbnail');

				var $img_prev = $parent.siblings('.img-prev');			var $img_id = $parent.siblings('.img-id');			$img_prev.prop('src', '<?php echo WC_CAT_SLIDER_ASSETS_URL . '/images/no-image-placeholder.jpg'; ?>');			$img_id.val('');

				});

				#>

			<?php } ?>

		</script>
		<?php
	}

	/**
	 * Get Categories.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_slider_get_categories_ajax_callback() {
		$selection_type      = empty( $_REQUEST['selection_type'] ) ? 'all' : sanitize_key( $_REQUEST['selection_type'] );
		$selected_categories = empty( $_REQUEST['selected_categories'] ) ? array() : wp_parse_id_list( $_REQUEST['selected_categories'] );
		$include_child       = empty( $_REQUEST['include_child'] ) || 'on' !== $_REQUEST['include_child'] ? false : true;
		$number              = empty( $_REQUEST['number'] ) ? 10 : intval( $_REQUEST['number'] );
		$orderby             = empty( $_REQUEST['orderby'] ) ? 'name' : sanitize_key( $_REQUEST['orderby'] );
		$order               = empty( $_REQUEST['order'] ) ? 'ASC' : sanitize_key( $_REQUEST['order'] );
		$slider_id           = empty( $_REQUEST['slider_id'] ) ? null : sanitize_key( $_REQUEST['slider_id'] );
		$hide_empty          = ! empty( $_REQUEST['hide_empty'] ) && 'on' === $_REQUEST['hide_empty'] ? false : true;

		if ( 'all' === $selection_type ) {
			$selected_categories = array();
		}

		$categories = wc_category_slider_get_categories(
			array(
				'number'     => $number,
				'orderby'    => $orderby,
				'order'      => $order,
				'hide_empty' => $hide_empty,
				'include'    => $selected_categories,
				'exclude'    => array(),
				'child_of'   => 0,
				'post_id'    => $slider_id,
			)
		);

		$categories = apply_filters( 'wc_category_slider_categories', $categories, $slider_id );
		foreach ( $categories as $key => $category ) {
			$image        = WC_CAT_SLIDER_ASSETS_URL . '/images/no-image-placeholder.jpg';
			$thumbnail_id = $category['image_id'];
			if ( ! empty( $thumbnail_id ) ) {
				$attachment = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
				if ( is_array( $attachment ) && ! empty( $attachment[0] ) ) {
					$image = esc_url( $attachment[0] );
				}
			}

			$categories[ $key ]['image'] = $image;
		}
		wp_send_json_success( $categories );
	}

	/**
	 * Add all the assets required by the plugin.
	 *
	 * @param string $hook Current page hook.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_slider_load_admin_assets( $hook ) {
		wc_category_slider()->scripts->register_style( 'wc-category-slider-halloween', 'css/halloween.css' );
		// Enqueue the bytekit styles.
		wp_enqueue_style( 'bytekit-components' );

		if ( ! in_array( $hook, array( 'post-new.php', 'post.php' ), true ) ) {
			return;
		}

		global $post;

		if ( 'wc_category_slider' !== $post->post_type ) {
			return;
		}

		wp_register_style( 'wccs-fontawesome', WC_CAT_SLIDER_ASSETS_URL . 'vendor/font-awesome/css/font-awesome.css', array(), WC_CAT_SLIDER_VERSION );

		wp_register_style(
			'wc-category-slider',
			WC_CAT_SLIDER_ASSETS_URL . 'css/admin.css',
			array(
				'woocommerce_admin_styles',
				'wccs-fontawesome',
			),
			WC_CAT_SLIDER_VERSION
		);
		wp_register_script(
			'wc-category-slider',
			WC_CAT_SLIDER_ASSETS_URL . 'js/wc-category-slider-admin.js',
			array(
				'jquery',
				'wp-util',
				'select2',
				'wp-color-picker',
			),
			WC_CAT_SLIDER_VERSION,
			true
		);
		wp_localize_script(
			'wc-category-slider',
			'WCS',
			array(
				'ajaxurl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => 'wc-category-slider',
				'codeEditor' => wp_enqueue_code_editor( array( 'type' => 'text/css' ) ),
			)
		);
		wp_enqueue_style( 'wc-category-slider' );
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script(
			'wp-color-picker-alpha',
			WC_CAT_SLIDER_ASSETS_URL . 'js/wp-color-picker-alpha.min.js',
			array(
				'jquery',
				'wp-color-picker',
			),
			WC_CAT_SLIDER_VERSION,
			true
		);
		wp_enqueue_script( 'wc-category-slider' );
	}

	/**
	 * Update slider settings
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return bool|null
	 */
	public function wc_category_slider_update_settings( $post_id ) {

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return false;
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			return $post_id;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// save post meta
		$posted = empty( $_POST ) ? array() : $_POST;

		$categories = array();

		if ( ! empty( $posted['categories'] ) ) {
			foreach ( $posted['categories'] as $term_id => $meta ) {
				$categories[ $term_id ] = apply_filters(
					'wc_category_slider_custom_category_attributes',
					array(
						'name'        => '',
						'url'         => '',
						'description' => '',
						'image_id'    => '',
						'icon'        => sanitize_key( $meta['icon'] ),
					),
					$term_id,
					$posted['categories'][ $term_id ]
				);
			}
		}

		update_post_meta( $post_id, 'categories', empty( $posted['categories'] ) ? array() : $categories );
		update_post_meta( $post_id, 'selection_type', empty( $posted['selection_type'] ) ? '' : sanitize_key( $posted['selection_type'] ) );
		update_post_meta( $post_id, 'selected_categories', empty( $posted['selected_categories'] ) ? '' : $posted['selected_categories'] );
		update_post_meta( $post_id, 'limit_number', empty( $posted['limit_number'] ) ? '' : intval( $posted['limit_number'] ) );
		update_post_meta( $post_id, 'include_child', empty( $posted['include_child'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_empty', empty( $posted['hide_empty'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_image', empty( $posted['hide_image'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_content', empty( $posted['hide_content'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_button', empty( $posted['hide_button'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_icon', empty( $posted['hide_icon'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_name', empty( $posted['hide_name'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_count', empty( $posted['hide_count'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_nav', empty( $posted['hide_nav'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_paginate', empty( $posted['hide_paginate'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hide_border', empty( $posted['hide_border'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'hover_style', empty( $posted['hover_style'] ) ? '' : sanitize_key( $posted['hover_style'] ) );
		update_post_meta( $post_id, 'theme', empty( $posted['theme'] ) ? '' : sanitize_key( $posted['theme'] ) );
		update_post_meta( $post_id, 'autoplay', empty( $posted['autoplay'] ) ? 'off' : 'on' );
		update_post_meta( $post_id, 'rtl', empty( $posted['rtl'] ) ? 'off' : 'on' );

		do_action( 'wc_category_slider_settings_update', $post_id, $posted );
	}

	/**
	 * Category settings meta box
	 *
	 * @param \WP_Post $post Post Object.
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function wc_slider_render_category_settings_metabox( $post ) {

		echo SliderElements::select(
			array(
				'label'            => esc_html__( 'Selection Type', 'woo-category-slider-by-pluginever' ),
				'name'             => 'selection_type',
				'placeholder'      => '',
				'show_option_all'  => '',
				'show_option_none' => '',
				'double_columns'   => false,
				'options'          => array(
					'all'    => 'All',
					'custom' => 'Custom',
				),
				'required'         => true,
				'selected'         => wc_category_slider_get_meta( $post->ID, 'selection_type' ),
				'desc'             => esc_html__( 'Select all categories or any custom categories', 'woo-category-slider-by-pluginever' ),
			)
		);

		echo SliderElements::select(
			array(
				'label'            => esc_html__( 'Select Categories', 'woo-category-slider-by-pluginever' ),
				'name'             => 'selected_categories',
				'class'            => 'select-2 select-categories',
				'show_option_all'  => '',
				'show_option_none' => '',
				'double_columns'   => false,
				'multiple'         => true,
				'options'          => wc_slider_get_category_list(),
				'required'         => false,
				'selected'         => wc_category_slider_get_meta( $post->ID, 'selected_categories' ),
				'desc'             => esc_html__( '', 'woo-category-slider-by-pluginever' ),
				'attrs'            => array(
					'multiple' => 'multiple',
				),
			)
		);

		echo SliderElements::input(
			array(
				'name'           => 'limit_number',
				'label'          => esc_html__( 'Limit Items', 'woo-category-slider-by-pluginever' ),
				'double_columns' => false,
				'type'           => 'number',
				'value'          => wc_category_slider_get_meta( $post->ID, 'limit_number', 10 ),
				'desc'           => esc_html__( 'Limit the number of category appear on the slider', 'woo-category-slider-by-pluginever' ),
			)
		);

		echo SliderElements::switcher(
			array(
				'label'          => esc_html__( 'Include Children', 'woo-category-slider-by-pluginever' ),
				'name'           => 'include_child',
				'double_columns' => false,
				'value'          => wc_category_slider_get_meta( $post->ID, 'include_child', 'on' ),
				'desc'           => esc_html__( 'Will include subcategories of the selected categories', 'woo-category-slider-by-pluginever' ),
			)
		);

		echo SliderElements::switcher(
			array(
				'name'           => 'hide_empty',
				'double_columns' => false,
				'value'          => wc_category_slider_get_meta( $post->ID, 'hide_empty', 'on' ),
				'label'          => esc_html__( 'Empty Categories', 'woo-category-slider-by-pluginever' ),
				'desc'           => esc_html__( 'Show/hide Category without products', 'woo-category-slider-by-pluginever' ),
			)
		);

		echo SliderElements::select(
			apply_filters(
				'wc_category_slider_orderby_args',
				array(
					'label'            => esc_html__( 'Order By', 'woo-category-slider-by-pluginever' ),
					'name'             => 'orderby',
					'class'            => 'orderby',
					'show_option_all'  => '',
					'show_option_none' => '',
					'double_columns'   => false,
					'options'          => array(
						'term_id'     => esc_html__( 'Term ID', 'woo-category-slider-by-pluginever' ),
						'term_name'   => esc_html__( 'Term Name', 'woo-category-slider-by-pluginever' ),
						'description' => esc_html__( 'Term Description', 'woo-category-slider-by-pluginever' ),
						'term_group'  => esc_html__( 'Term Group', 'woo-category-slider-by-pluginever' ),
						'count'       => esc_html__( 'Count', 'woo-category-slider-by-pluginever' ),
						'none'        => esc_html__( 'None', 'woo-category-slider-by-pluginever' ),
					),
					'disabled'         => true,
					'required'         => false,
					'desc'             => esc_html__( 'Order category slider according to the selection type', 'woo-category-slider-by-pluginever' ),

				),
				esc_attr( $post->ID )
			)
		);

		echo SliderElements::select(
			apply_filters(
				'wc_category_slider_order_args',
				array(
					'label'            => esc_html__( 'Order', 'woo-category-slider-by-pluginever' ),
					'name'             => 'order',
					'class'            => 'order',
					'show_option_all'  => '',
					'show_option_none' => '',
					'double_columns'   => false,
					'options'          => array(
						'asc'  => 'ASC',
						'desc' => 'DESC',
					),
					'required'         => false,
					'disabled'         => true,
					'desc'             => esc_html__( 'Order category slider according to the selection type', 'woo-category-slider-by-pluginever' ),

				),
				esc_attr( $post->ID )
			)
		);

		$action = empty( $_GET['action'] ) ? '' : wp_unslash( esc_attr( $_GET['action'] ) );

		?>
		<div id="submitpost" class="submitbox ever-submitbox">
			<input type="hidden" name="hidden_post_status" id="hidden_post_status" value="publish"/>
			<div id="publishing-action">
				<span class="spinner"></span>
				<?php if ( 'edit' !== $action ) { ?>
					<input name="original_publish" type="hidden" id="original_publish"
							value="<?php esc_attr_e( 'Publish', 'woo-category-slider-by-pluginever' ); ?>"/>
					<?php submit_button( __( 'Create Slider', 'woo-category-slider-by-pluginever' ), 'primary button-large wccs-save-button', 'publish', false ); ?>
										<?php
				} else {
					?>
					<input name="original_publish" type="hidden" id="original_publish" value="Update"/>
					<?php
					submit_button( esc_html__( 'Update Slider', 'woo-category-slider-by-pluginever' ), 'primary button-large wccs-save-button', 'save', false );
				}
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Settings meta box
	 *
	 * @param \WP_Post $post Post Object.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function wc_slider_render_shortcode_metabox( $post ) {
		echo SliderElements::input(
			array(
				'name'           => 'shortcode',
				'label'          => '',
				'double_columns' => false,
				'readonly'       => true,
				'value'          => wc_category_slider_get_meta( $post->ID, 'shortcode', "[woo_category_slider id='$post->ID']" ),
				'desc'           => esc_html__( 'Use the shortocode to render the slider anywhere in the page or post.', 'woo-category-slider-by-pluginever' ),
			)
		);
	}

	/**
	 * Settings meta box
	 *
	 * @param \WP_Post $post Post Object.
	 *
	 * @since 1.0.0
	 * @return mixed
	 */
	public function wc_slider_settings_metabox( $post ) {
		ob_start();
		include WC_CAT_SLIDER_INCLUDES . '/Admin/views/metabox.php';
		$html = ob_get_clean();
		echo $html;
	}

	/**
	 * register meta boxes
	 *
	 * @since 4.0.0
	 */
	public function wc_slider_register_meta_boxes() {
		$post_type = 'wc_category_slider';

		add_meta_box( 'wc-slider-settings', __( 'Settings', 'woo-category-slider-by-pluginever' ), array( $this, 'wc_slider_settings_metabox' ), $post_type, 'normal', 'high' );
		add_meta_box( 'wc_slider_category_settings', __( 'Category Settings', 'woo-category-slider-by-pluginever' ), array( $this, 'wc_slider_render_category_settings_metabox' ), $post_type, 'side', 'high' );
		add_meta_box( 'wc_slider_shortcode', __( 'Shortcode', 'woo-category-slider-by-pluginever' ), array( $this, 'wc_slider_render_shortcode_metabox' ), $post_type, 'side', 'high' );
	}

	/**
	 * remove meta boxes
	 *
	 * @since 4.0.0
	 * @return void
	 */
	public function wc_slider_remove_meta_boxes() {
		$post_type = 'wc_category_slider';
		remove_meta_box( 'submitdiv', $post_type, 'side' );
	}

	/**
	 * Promotional Offer.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function promotional_offer() {
		// Show only to Admins.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		// Check if it has already been dismissed.
		$hide_notice = get_option( '' . esc_attr( $this->uniq_key ) . '_initial_upsell_promotion', 'no' );

		if ( 'hide' === $hide_notice || is_plugin_active( 'wc-category-slider-pro/wc-category-slider-pro.php' ) ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible" id="<?php echo esc_attr( $this->uniq_key ); ?>-promotional-offer-notice">
			<p>Thank you for installing <strong><a href="https://www.pluginever.com/plugins/woocommerce-category-slider-pro" target="_blank">WooCommerce Category Slider</a></strong>. <strong>WooCommerce Category slider</strong> is now powering <strong>2000+</strong> stores. Use the coupon code <strong>WCSLIDER4TOPRO</strong> for 20% discount on PRO. <a href="https://www.pluginever.com/plugins/woocommerce-category-slider-pro/?utm_source=plugin_activation_promotion&utm_medium=link&utm_campaign=wc-category-slider&utm_content=Upgrade" target="_blank" style="text-decoration: none;"><span
						class="dashicons dashicons-smiley" style="margin-left: 10px;"></span> Get the Offer</a></p>
			<span class="dashicons dashicons-megaphone"></span>
		</div><!-- #<?php echo esc_attr( $this->uniq_key ); ?>-promotional-offer-notice -->

		<style>

			#<?php echo esc_attr( $this->uniq_key ); ?>-promotional-offer-notice p {
				color: #000;
				font-size: 14px;
				margin-bottom: 10px;
				-webkit-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-moz-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				-o-text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				text-shadow: 0.1px 0.1px 0px rgba(250, 250, 250, 0.24);
				padding-left: 30px;
			}


			#<?php echo esc_attr( $this->uniq_key ); ?>-promotional-offer-notice span.dashicons-megaphone {
				position: absolute;
				top: 8px;
				left: 0;
				color: #0073aa;
				font-size: 36px;
				transform: rotate(-21deg);
			}
		</style>

		<script type='text/javascript'>
			jQuery('body').on('click', '#<?php echo esc_attr( $this->uniq_key ); ?>-promotional-offer-notice .notice-dismiss', function (e) {
				e.preventDefault();

				wp.ajax.post('<?php echo esc_attr( $this->uniq_key ); ?>-dismiss-promotional-offer-notice', {
					dismissed: true
				});
			});
		</script>
		<?php
	}


	/**
	 * Dismiss promotion notice
	 *
	 * @return void
	 * @since  2.5
	 */
	public function dismiss_promotional_offer() {
		if ( ! empty( $_POST['dismissed'] ) ) {
			$offer_key = $this->uniq_key . '_initial_upsell_promotion';
			update_option( $offer_key, 'hide' );
		}
	}

	/**
	 * Admin footer text.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 4.2.6
	 * @return string
	 */
	public function admin_footer_text( $footer_text ) {
		$screen_ids = array( 'wc_category_slider', 'edit-wc_category_slider' );
		if ( wc_category_slider()->get_review_url() && in_array( get_current_screen()->id, $screen_ids, true ) ) {
			$footer_text = sprintf(
			/* translators: 1: Plugin name 2: WordPress */
				__( 'Thank you for using %1$s. If you like it, please leave us a %2$s rating. A huge thank you from PluginEver in advance!', 'woo-category-slider-by-pluginever' ),
				'<strong>' . esc_html( wc_category_slider()->get_name() ) . '</strong>',
				'<a href="' . esc_url( wc_category_slider()->get_review_url() ) . '" target="_blank" class="wc-category-slider-rating-link" data-rated="' . esc_attr__( 'Thanks :)', 'woo-category-slider-by-pluginever' ) . '">&#9733;&#9733;&#9733;&#9733;&#9733;</a>'
			);
		}

		return $footer_text;
	}

	/**
	 * Update footer.
	 *
	 * @param string $footer_text Footer text.
	 *
	 * @since 1.0.0
	 * @return string
	 */
	public function update_footer( $footer_text ) {
		$screen_ids = array( 'wc_category_slider', 'edit-wc_category_slider' );
		if ( in_array( get_current_screen()->id, $screen_ids, true ) ) {
			/* translators: 1: Plugin version */
			$footer_text = sprintf( esc_html__( 'Version %s', 'woo-category-slider-by-pluginever' ), wc_category_slider()->get_version() );
		}

		return $footer_text;
	}
}
