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
            street: null,
            deliverydays: ko.observableArray([]),
        },

        initObservable: function () {
            this._super().observe([
                'postcode',
                'country',
                'street',
                'deliverydays'
            ]);

            AddressFinder.subscribe(function (address, oldAddress) {
                if (!address || JSON.stringify(address) == JSON.stringify(oldAddress)) {
                    return;
                }

                if (address.country !== 'NL') {
                    return;
                }

                this.getDeliveryDays(address);
            }.bind(this));

            return this;
        },

        /**
         * Retrieve the Deliverydays from PostNL.
         *
         * @param address
         */
        getDeliveryDays: function (address) {
            $.ajax({
                method: 'POST',
                url : '',
                data : {address: address}
            }).done(function (data) {

                data = ko.utils.arrayMap(data.timeframes, function (day) {
                    return ko.utils.arrayMap(day, function (timeFrame) {
                        timeFrame.address = address;
                        return new TimeFrame(timeFrame);
                    });
                });

                this.deliverydays(data);
            });

            this.deliverydays(['test', 'test2']);
            $('#label_method_tig_gls_tig_gls').closest('.row').after($('.gls-delivery-days'));
        },
    });
});
