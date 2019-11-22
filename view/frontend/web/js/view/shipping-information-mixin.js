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
define(
    [
        'uiComponent',
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Checkout/js/model/sidebar',
        'TIG_GLS/js/view/checkout/shipping-information/parcel-shop'
    // @codingStandardsIgnoreLine
    ], function (
        uiComponent,
        $,
        ko,
        quote,
        stepNavigator,
        sidebar,
        parcelShop
    ) {
        'use strict';

        var mixin = {
            /**
             * Hide Ship-To Block if Parcel Shop is selected.
             *
             * @returns {boolean|*}
             */
            isVisible: function () {
                if (parcelShop().parcelShopAddress() !== null) {
                    return false;
                }

                return !quote.isVirtual() && stepNavigator.isProcessed('shipping');
            }
        };

        return function (target) {
            return target.extend(mixin);
        };
    }
);
