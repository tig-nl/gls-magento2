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
    'mage/url'
], function (
    $,
    url
) {
    var pdfOnSeparatePage = 0;
    var showGridToolbar = 1;

    return {
        getPdfOnSeperatePage: function () {
            return pdfOnSeparatePage;
        },

        setPdfOnSeperatePage: function (separatePdf) {
            pdfOnSeparatePage = separatePdf;
        },

        setShowToolbar: function (showToolbar) {
            showGridToolbar = showToolbar;
        },

        getShowToolbar: function () {
            return showGridToolbar;
        },

        getInputWarningMessage: function (option) {
            if (option === 'change_parcel') {
                return $.mage.__('Parcel quantity should be a number');
            }
        },

        getSubmitUrl : function (option, grid) {
            var action = '' + 'gls/' + this.getCurrentGrid(grid) + '/' + this.getCurrentAction(option);
            return url.build(action);
        },

        /**
         * Gets the controller based on the currently selected action.
         *
         * @returns {*}
         */
        getCurrentAction : function (option) {
            if (option === 'change_parcel') {
                return 'MassChangeMultiColli';
            }
        },

        /**
         * Retuns the controller directory bases on the current grid.
         *
         * @returns {*}
         */
        getCurrentGrid : function (grid) {
            if (grid === 'sales_order_grid') {
                return 'order';
            }

            if (grid === 'sales_order_shipment_grid') {
                return 'shipment';
            }
        }
    };
});
