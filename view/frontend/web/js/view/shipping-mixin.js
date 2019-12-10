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
/*global alert*/
define([
    'jquery',
    'Magento_Checkout/js/model/quote',
    'mage/translate'
], function (
    $,
    quote,
    $t
) {
    'use strict';

    return function (Component) {
        return Component.extend({
            validateShippingInformation: function () {
                var originalResult = this._super();

                if (quote.shippingMethod().carrier_code !== 'tig_gls') {
                    return originalResult;
                }

                var shippingAddress = quote.shippingAddress();
                // Returns undefined if no option is checked.
                var checkedOption   = $('input[name="gls_delivery_option"]:checked').val();

                if (checkedOption === undefined || shippingAddress.extension_attributes === undefined || shippingAddress.extension_attributes.gls_delivery_option === undefined) {
                    this.errorValidationMessage(
                        $t('Please select a GLS delivery option. If no options are visible, please make sure you\'ve entered your address information correctly.')
                    );

                    return false;
                }

                return originalResult;
            }
        });
    };
});