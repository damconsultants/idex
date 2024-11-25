require([
    'jquery',
    'select2'
], function ($) {
    jQuery(document).ready(function () {
        jQuery('#bynder_property').select2();
        jQuery('#bynder_property_image_role').select2();
        jQuery('#bynder_property_alt_tax').select2();
		jQuery('#bynder_property_customer_visibility').select2();
		jQuery('#bynder_property_brands').select2();
		jQuery('#bynder_property_style').select2();
		jQuery('#bynder_property_file_category').select2();
		jQuery('#bynder_property_search_visibility').select2();
		jQuery('#bynder_property_asset').select2();
		jQuery('#bynder_property_media_order').select2();
		jQuery('#bynder_property_asset_sub_type').select2();
		jQuery('#bynder_property_file_title').select2();
		/*jQuery('#bynder_property_5').select2();
		jQuery('#bynder_property_6').select2();
		jQuery('#bynder_property_7').select2();*/
    });
});