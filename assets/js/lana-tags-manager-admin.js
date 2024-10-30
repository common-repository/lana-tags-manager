jQuery(function () {

    var $lanaCustomMetaTagsTable = jQuery('.lana-custom-meta-tags-table');

    /**
     * Change meta tag name
     */
    jQuery(document).on('click', '.lana-custom-meta-tags-table .lana-custom-meta-tag-name-label', function () {
        /** hide label */
        jQuery(this).hide();

        /** change and show input */
        jQuery(this).closest('.lana-custom-meta-tag-name').find('.lana-custom-meta-tag-name-input').attr('type', 'text').val(jQuery.trim(jQuery(this).text())).show().focus();
    });

    /**
     * Save meta tag name input
     */
    jQuery(document).on('blur', '.lana-custom-meta-tags-table .lana-custom-meta-tag-name-input', function () {

        /** trim value */
        jQuery(this).val(jQuery.trim(jQuery(this).val()));

        /** check value is not empty */
        if (jQuery(this).val()) {

            /** change label text */
            jQuery(this).closest('.lana-custom-meta-tag-name').find('.lana-custom-meta-tag-name-label').text(jQuery(this).val());
        }

        /** hide input */
        jQuery(this).hide();

        /** show label */
        jQuery(this).closest('.lana-custom-meta-tag-name').find('.lana-custom-meta-tag-name-label').show();
    });

    /**
     * Add meta tag
     */
    $lanaCustomMetaTagsTable.find('.lana-add-meta-tag').on('click', function () {

        var addTemplate = _.template(jQuery('#tmpl-lana-tags-manager-lana-meta-tag-html').html(), {
            evaluate: /{{([\s\S]+?)}}/g,
            interpolate: /{{=([\s\S]+?)}}/g,
            escape: /{{-([\s\S]+?)}}/g,
            variable: 'data'
        });

        var ids = jQuery.map($lanaCustomMetaTagsTable.find('tbody').find('tr[data-id]'), function (v) {
            return parseInt(jQuery(v).data('id').toString().match(/\d+/));
        });

        var id_max = Math.max.apply(Math, ids);

        $lanaCustomMetaTagsTable.find('tbody').append(addTemplate({
            id: ++id_max
        }));

        return false;
    });

    /**
     * Remove meta tag
     */
    jQuery(document).on('click', '.lana-custom-meta-tags-table .actions .lana-remove-meta-tag', function () {
        jQuery(this).closest('tr').remove();

        return false;
    });

    /**
     * Add sortable
     */
    $lanaCustomMetaTagsTable.find('tbody').sortable({
        items: 'tr',
        axis: 'y',
        handle: '.lana-move-meta-tag',
        placeholder: 'ui-state-highlight',
        distance: 5,
        cursor: 'move',
        cursorAt: {
            top: 20,
            left: 0
        }
    });
});