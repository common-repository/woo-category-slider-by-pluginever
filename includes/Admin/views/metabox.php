<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$navs = array(
	'Categories',
	'Display Settings',
	'Slider Settings',
	'Font Settings',
);

?>

<div class="ever-row">
	<div class="ever-col-12">
		<div class="ever-tabs">

			<?php

			$content = '';
			$active  = 'active';

			foreach ( $navs as $nav ) {

				$icon = ''; // Tab menu icons.

				switch ( $nav ) {
					case 'Categories':
						$icon = 'align-justify';
						break;
					case 'Display Settings':
						$icon = 'tv';
						break;
					case 'Slider Settings':
						$icon = 'sliders';
						break;
					case 'Font Settings':
						$icon = 'font';
						break;
				}

				// === tab nav label ===
				$label = sprintf(
					/* translators: 1: Nav Menu Item */
					__( '%1$s', 'woo-category-slider-by-pluginever' ),
					$nav
				);

				$template = sanitize_title( $nav );

				ob_start();

				echo wp_kses_post( "<div class='tab-content-item {$active}' id='{$template}'>" );
				// === include meta box template file ===
				include WC_CAT_SLIDER_INCLUDES . "/Admin/views/metabox-{$template}.php";
				echo '</div>';

				$content .= ob_get_clean();

				// === tab nav item ===
				printf(
					'<a href="#" class="tab-item %1$s" data-target="%2$s"><span class="fa fa-%3$s"></span> %4$s</a>',
					esc_attr( $active ),
					esc_attr( $template ),
					esc_attr( $icon ),
					esc_attr( $label )
				);

				$active = '';

			}

			?>
		</div>

		<div class="tab-content">
			<?php
			// === Meta box tab content ===
			echo $content;

			?>
		</div>

	</div>
</div>

<script>

	jQuery(document).ready(function ($) {
		//===  handle active tab ===
		function CategorySliderSetActiveTab($target) {
			$('.tab-item, .tab-content-item').removeClass('active');
			$('.tab-item[data-target="' + $target + '"]').addClass('active');
			$('.tab-content-item[id="' + $target + '"]').addClass('active');
			if (typeof(localStorage) !== 'undefined') {
				localStorage.setItem("wc_category_slider_active_tab", $target);
			}
		}

		var activeTab = 'categories';
		if (typeof(localStorage) !== 'undefined') {
			activeTab = localStorage.getItem('wc_category_slider_active_tab') || 'categories';
		}

		CategorySliderSetActiveTab(activeTab);

		$('.tab-item').on('click', function (e) {
			e.preventDefault();
			var $target = $(this).data('target');
			CategorySliderSetActiveTab($target);
		});

		//=== Custom css editor ===
		wp.codeEditor.initialize($('#custom_css'), WCS.codeEditor);

	});
</script>


