/**
 *
 *          ..::..
 *     ..::::::::::::..
 *   ::'''''':''::'''''::
 *   ::..  ..:  :  ....::
 *   ::::  :::  :  :   ::
 *   ::::  :::  :  ''' ::
 *   ::::..:::..::.....::
 *     ''::::::::::::''
 *          ''::''
 *
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Creative Commons License.
 * It is available through the world-wide-web at this URL:
 * http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 * If you are unable to obtain it through the world-wide-web, please send an email
 * to servicedesk@tig.nl so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future. If you wish to customize this module for your
 * needs please contact servicedesk@tig.nl for more information.
 *
 * @copyright   Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license     http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
/* eslint-disable strict */
define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/grid/toolbar',
    'mageUtils',
    'TIG_GLS/js/grid/dataprovider',
    'mage/translate'
], function (
    $,
    ko,
    _,
    Toolbar,
    utils,
    DataProvider,
    $t
) {
    'use strict';
    return Toolbar.extend({
        defaults : {
            currentSelected : ko.observable('change_product'),
            selectProvider: 'ns = ${ $.ns }, index = ids',
            modules: {
                selections: '${ $.selectProvider }'
            },
            actionList : ko.observableArray([
                {text: $t('Change parcel quantity'), value: 'change_parcel'}
            ]),
            showToolbar : ko.observable(DataProvider.getShowToolbar()),
            jsLoaded : true,
            showTimeOptions : ko.observable(false),
            timeOptionSelected : ko.observable('1000')
        },

        /**
         * Init.
         *
         * @returns {exports}
         */
        initObservable : function () {
            this._super().observe([
                'currentSelected',
                'showTimeOptions'
            ]);

            this.currentSelected.subscribe(function (value) {
                if (value === 'change_parcel') {
                    self.showTimeOptions(false);
                }
            });

            var self = this;

            return this;
        },

        /**
         * The GLS toolbar should only be visable on the order and shipment grid.
         *
         * @returns {boolean}
         */
        showGLSToolbarActions : function () {
            return this.showToolbar() == 1 && (this.ns === 'sales_order_grid' || this.ns === 'sales_order_shipment_grid');
        },

        /**
         * Submit selected items and gls form data to controllers
         * - MassChangeMulticolli
         */
        submit : function (isSticky) {
            // Grab the input values of the regular toolbar or the sticky toolbar
            var selector = $('.' + this.currentSelected() + '_toolbar');
            if (isSticky) {
                selector = $('.' + this.currentSelected() + '_sticky');
            }

            var data = this.getSelectedItems();
            if (data.selected === false) {
                alert($.mage.__('Please select item(s)'));
                return;
            }

            var value = selector[0].value;
            if (isNaN(parseInt(value))) {
                alert(DataProvider.getInputWarningMessage(this.currentSelected()));
                return;
            }

            data[this.currentSelected()] = value;

            utils.submit({
                url: DataProvider.getSubmitUrl(this.currentSelected(), this.ns),
                data: data
            });
        },

        isNumeric : function (value) {
            return !isNaN(parseInt(value)) && isFinite(value);
        },

        /**
         * Obtain and return the selected items from the grid
         *
         * @returns {*}
         */
        getSelectedItems : function () {
            var provider = this.selections();
            var selections = provider && provider.getSelections();
            var itemsType = selections.excludeMode ? 'excluded' : 'selected';

            var selectedItems = {};
            selectedItems[itemsType] = selections[itemsType];

            if (!selectedItems[itemsType].length) {
                selectedItems[itemsType] = false;
            }

            // Params includes extra data like filters
            _.extend(selectedItems, selections.params || {});

            return selectedItems;
        }
    });
});
