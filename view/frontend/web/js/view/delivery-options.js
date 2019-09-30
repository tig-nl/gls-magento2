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
    'Magento_Checkout/js/model/quote',
    'TIG_GLS/js/helper/address-finder',
    'Magento_Catalog/js/price-utils',
    'TIG_GLS/js/view/checkout/shipping-information/parcel-shop'
], function (
    $,
    Component,
    ko,
    quote,
    AddressFinder,
    priceUtils,
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
            deliveryFee: ko.observable()
        },

        initObservable: function () {
            this.selectedMethod = ko.computed(function () {
                var method = quote.shippingMethod();
                var selectedMethod = method != null ? method.carrier_code + '_' + method.method_code : null;
                return selectedMethod;
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

                this.getAvailableServices(address.postcode, address.country);
                this.getParcelShops(address.postcode);
            }.bind(this));

            return this;
        },

        /**
         * Retrieve Delivery Options from GLS.
         */
        getAvailableServices: function (postcode, country) {
            $.ajax({
                method : 'GET',
                url    : '/gls/deliveryoptions/services',
                type   : 'jsonp',
                data   : {
                    postcode: postcode,
                    country: country
                }
            }).done(function (services) {
                this.availableServices(services);
            }.bind(this));
        },
    
        /**
         * Format fee if fee is higher than zero.
         *
         * @param fee
         * @returns {string}
         */
        formatAdditionalFee: function (fee) {
            var formattedFee = '';
            if (fee > 0) {
                formattedFee = '+ ' + priceUtils.formatPrice(fee, quote.getPriceFormat());
            }
            return formattedFee;
        },

        /**
         * Retrieve Parcel Shops from GLS.
         *
         * @param postcode
         */
        getParcelShops: function (postcode) {
            $.ajax({
                method : 'GET',
                url    : '/gls/deliveryoptions/parcelshops',
                type   : 'jsonp',
                data   : {
                    postcode: postcode
                }
            }).done(function (data) {
                this.parcelShops(data);
            }.bind(this));
        },

        /**
         * Sets the Delivery Option in gls_delivery_option
         *
         * @param type
         * @param details
         */
        setGlsDeliveryOption: function (type, details) {
            var deliveryOption = {
                type: type,
                details: details
            };

            // TODO: This should be done the Magento-way: shippingAddress.customAttributes.etc.
            $('input[name="custom_attributes[gls_delivery_option]"]').val(JSON.stringify(deliveryOption));

            $('.gls-delivery-options input[name="gls_delivery_option"]').parent().removeClass('active');
            $('.gls-delivery-options input[name="gls_delivery_option"]:checked').parent().addClass('active');

            this.deliveryFee(this.formatAdditionalFee(details.fee));
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