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
define([
    'jquery',
    'uiComponent',
    'ko',
    'TIG_GLS/js/Helper/AddressFinder',
], function (
    $,
    Component,
    ko,
    AddressFinder
) {
    'use strict';

    return Component.extend ({
        defaults: {
            template: 'TIG_GLS/DeliveryOptions/Options',
            postcode: null,
            country: null,
            deliverydays: ko.observableArray([]),
            parcelshops: ko.observableArray([])
        },

        initObservable: function () {
            this._super().observe([
                'postcode',
                'country',
                'deliverydays',
                'parcelshops'
            ]);

            AddressFinder.subscribe(function (address, oldAddress) {
                if (!address || JSON.stringify(address) == JSON.stringify(oldAddress)) {
                    return;
                }

                if (address.country !== 'NL') {
                    return;
                }

                this.getDeliveryDays();
                this.getParcelShops(address.postcode);
            }.bind(this));

            return this;
        },

        /**
         * Retrieve the Deliverydays from GLS.
         *
         * @param address
         */
        getDeliveryDays: function () {
            $.ajax({
                method : 'GET',
                url    : '/gls/deliveryoptions/deliverydays',
                type   : 'jsonp'
            }).done(function (data) {
                this.deliverydays(data);
            }.bind(this));
        },

        getParcelShops: function (postcode) {
            $.ajax({
                method : 'GET',
                url    : '/gls/deliveryoptions/parcelshops',
                type   : 'jsonp',
                data   : {postcode: postcode}
            }).done(function (data) {
                debugger;
                this.parcelshops(data);
            }.bind(this));
        }
    });
});
