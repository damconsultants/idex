var config = {
    paths: {
        'bynderjs': 'DamConsultants_Idex/js/bynder',
        'select2': 'DamConsultants_Idex/js/select2'
    },
    shim: {
        'bynderjs': {
            deps: ['jquery']
        },
        'select2': {
            deps: ['jquery']
        },
    },
	map: {
        '*': {
            /*'Magento_PageBuilder/template/form/element/uploader/preview/image.html': 'DamConsultants_Idex/template/form/element/uploader/preview/image.html',
            'Magento_PageBuilder/template/form/element/uploader/image.html': 'DamConsultants_Idex/template/form/element/uploader/image.html',*/
            'Magento_PageBuilder/template/form/element/html-code.html': 'DamConsultants_Idex/template/form/element/html-code.html',
            /*'Magento_PageBuilder/js/form/element/image-uploader': 'DamConsultants_Idex/js/form/element/image-uploader',*/
            'Magento_PageBuilder/js/form/element/html-code': 'DamConsultants_Idex/js/form/element/html-code',
			'Magento_PageBuilder/template/content-type/video/default/master.html': 'DamConsultants_Idex/template/content-type/video/default/master.html',
			'Magento_PageBuilder/template/content-type/video/default/preview.html': 'DamConsultants_Idex/template/content-type/video/default/preview.html',
        },
    }
};