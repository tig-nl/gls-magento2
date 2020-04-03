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
/* jshint esversion: 6 */
define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote',
    'TIG_GLS/js/helper/address-finder',
    'TIG_GLS/js/view/checkout/shipping-information/parcel-shop'
], function (
    $,
    Component,
    ko,
    priceUtils,
    quote,
    AddressFinder,
    parcelShop
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'TIG_GLS/delivery/options',
            postcode: null,
            country: null,
            availableServices: ko.observableArray([]),
            parcelShops: ko.observableArray([]),
            deliveryFee: ko.observable(),
            pickupFee: ko.observable()
        },

        initObservable: function () {
            this.selectedMethod = ko.computed(function () {
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                return selectedMethod;
            }, this);

            this.tabClasses = ko.computed(function () {
                return this.parcelShops().length > 0 ? 'gls-tabs' : 'gls-tabs gls-one-available';
            }, this);

            this._super().observe([
                'postcode',
                'country',
                'availableServices',
                'parcelShops'
            ]);

            AddressFinder.subscribe(function (address, oldAddress) {
                if (!address || JSON.stringify(address) == JSON.stringify(oldAddress)) {
                    return;
                }

                // Reset frontend storage before triggering any new calls.
                this.toggleTab('.gls-tab-pickup', '.gls-tab-delivery', '.gls-parcel-shop', '.gls-delivery-service');
                this.deliveryFee(null);
                this.pickupFee(null);

                this.getAvailableServices(address.postcode, address.country);
                this.getParcelShops(address.postcode, address.country);
            }.bind(this));

            return this;
        },

        /**
         * Retrieve Delivery Options from GLS.
         */
        getAvailableServices: function (postcode, country) {
            $.ajax({
                method    : 'GET',
                url       : '/gls/deliveryoptions/services',
                type      : 'jsonp',
                showLoader: true,
                data      : {
                    postcode: postcode,
                    country : country
                }
            }).done(function (services) {
                this.availableServices(services);
            }.bind(this));
        },

        /**
         * @param fee
         * @returns {string}
         */
        formatAdditionalFee: function (fee) {
            var formattedFee = '';

            if (fee > 0) {
                formattedFee = '+ ' + priceUtils.formatPrice(fee, quote.getPriceFormat());
            }

            if (fee < 0) {
                formattedFee = '- ' + priceUtils.formatPrice(Math.abs(fee), quote.getPriceFormat());
            }

            return formattedFee;
        },

        /**
         * Retrieve ParcelShops from GLS.
         *
         * Since ParcelShops are only available in the Netherlands,
         * there's no need to execute the call if 'country' is anything else.
         *
         * @param postcode
         * @param country
         */
        getParcelShops: function (postcode, country) {
            if (country !== 'NL') {
                return this.parcelShops([]);
            }

            $.ajax({
                method    : 'GET',
                url       : '/gls/deliveryoptions/parcelshops',
                type      : 'jsonp',
                showLoader: true,
                data      : {
                    postcode: postcode
                }
            }).done(function (data) {
                this.parcelShops(data);
            }.bind(this));
        },

        /**
         * Sets the Delivery Option in the gls_delivery_option extension attribute.
         * There's no need to retrieve it from e.g. shippingAddress.customAttribute, since frontend
         * storage is handled entirely by Magento 2's extension attributes.
         *
         * @param type
         * @param details
         */
        setGlsDeliveryOption: function (type, details) {
            var deliveryOption = {
                type: type,
                details: details
            };

            var checkoutConfig = window.checkoutConfig;
            // Do not refactor this.
            checkoutConfig.quoteData.gls_delivery_option = JSON.stringify(deliveryOption);

            $('.gls-delivery-options input[name="gls_delivery_option"]').parents().removeClass('active');
            $('.gls-delivery-options input[name="gls_delivery_option"]:checked').parents().addClass('active');
        },

        /**
         * Needs to return true, otherwise KnockoutJS prevents default event.
         * The toggleParcelShopAddress is triggered to control display of the ship-to block.
         *
         * @param selectedAddress
         * @returns {boolean}
         */
        setParcelShopAddress: function (selectedAddress) {
            this.setGlsDeliveryOption('ParcelShop', selectedAddress);
            parcelShop().parcelShopAddress(selectedAddress);

            /**
             * Don't display additional fee in Parcel Shop tab if shipping method is free and a
             * discount for Parcel Shops is given.
             */
            let parcelShopFee = selectedAddress.fee;

            if (quote.shippingMethod().amount === 0 && parcelShopFee < 0) {
                return true;
            }

            this.pickupFee(this.formatAdditionalFee(parcelShopFee));

            return true;
        },

        /**
         * Needs to return true, otherwise KnockoutJS prevents default event.
         *
         * @param service
         * @param selectedOption
         * @returns {boolean}
         */
        setDeliveryService: function (service, selectedOption) {
            service.setGlsDeliveryOption(this, selectedOption);
            parcelShop().parcelShopAddress(null);

            service.deliveryFee(service.formatAdditionalFee(selectedOption.fee));

            return true;
        },

        /**
         * Toggles between Parcel Shops and Delivery Services
         *
         * @param previousTab
         * @param currentTab
         * @param previousContent
         * @param currentContent
         */
        toggleTab: function (previousTab, currentTab, previousContent, currentContent) {
            $(previousTab).removeClass('active');
            $(currentTab).addClass('active');
            $(previousContent).hide();
            $(currentContent).fadeIn('slow');
        },

        /**
         * Show Business Hours when link is clicked.
         */
        showBusinessHours: function () {
            $(this).hide();
            $(this).next('.table-container').fadeIn('slow');
        },

        /**
         * Close Business Hours when link is clicked.
         */
        closeBusinessHours: function () {
            $(this).parent('.table-container').hide();
            $(this).parent('.table-container').prev('.open-business-hours').fadeIn('slow');
        }

    });

});
