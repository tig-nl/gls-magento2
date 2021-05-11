/**
 *                  ___________       __            __
 *                  \__    ___/____ _/  |_ _____   |  |
 *                    |    |  /  _ \\   __\\__  \  |  |
 *                    |    | |  |_| ||  |   / __ \_|  |__
 *                    |____|  \____/ |__|  (____  /|____/
 *                                              \/
 *          ___          __                                   __
 *         |   |  ____ _/  |_   ____ _______   ____    ____ _/  |_
 *         |   | /    \\   __\_/ __ \\_  __ \ /    \ _/ __ \\   __\
 *         |   ||   |  \|  |  \  ___/ |  | \/|   |  \\  ___/ |  |
 *         |___||___|  /|__|   \_____>|__|   |___|  / \_____>|__|
 *                  \/                           \/
 *                  ________
 *                 /  _____/_______   ____   __ __ ______
 *                /   \  ___\_  __ \ /  _ \ |  |  \\____ \
 *                \    \_\  \|  | \/|  |_| ||  |  /|  |_| |
 *                 \______  /|__|    \____/ |____/ |   __/
 *                        \/                       |__|
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
 * @copyright Copyright (c) Total Internet Group B.V. https://tig.nl/copyright
 * @license   http://creativecommons.org/licenses/by-nc-nd/3.0/nl/deed.en_US
 */
/* browser: true */
/* global define */
define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'TIG_GLS/js/grid/dataprovider'
], function (
    $,
    Abstract,
    utils,
    DataProvider
) {
    return Abstract.extend({
        defaults: {
            pdfOnSeparatePage: DataProvider.getPdfOnSeperatePage()
        },
        /**
         * Getting the selections is based off the massactions.js. Updating the messages system is based off the giftmessage.js.
         * When GLS massactions should open a new tab, we use our own action instead of the default action,
         * as the default action simply redirects to a URL with the selections as parameters.
         *
         * @see defaultCallback in vendor/magento/module-ui/view/base/web/js/grid/massactions.js
         * @see saveGiftMessage in vendor/magento/module-sales/view/adminhtml/web/order/create/giftmessage.js
         * @param action
         * @param data
         */
        submit: function (action, data) {
            var itemsType = data.excludeMode ? 'excluded' : 'selected',
                selections = {};

            selections[itemsType] = data[itemsType];

            if (!selections[itemsType].length) {
                selections[itemsType] = false;
            }

            _.extend(selections, data.params || {});

            if (this.pdfOnSeparatePage == '1') {
                $('.action-select-wrap').removeClass('_active');
                $('.action-menu').removeClass('_active');
                $.ajax({
                    showLoader: true,
                    url: action.url,
                    data: selections,
                    success: function (data) {
                        this.handleResponse(data);
                    },
                    fail: function (data) {
                        this.handleResponse(data);
                    }
                });
            } else {
                utils.submit({
                    url: action.url,
                    data: selections
                });
            }
        },

        handleResponse: function (data) {
            // This array will be converted to a string with message dom elements
            var messages = [];
            $.each(data.messages, function (key, message) {
                messages.push(
                    '<div class="message message-' + message.type + ' ' + message.type + '">' +
                    message.text +
                    '<div data-ui-id="messages-message-' + message.type + '"></div></div>'
                );
            }.bind(messages));
            var message = '<div id="messages"><div class="messages">' + messages.join('') + '</div></div>';
            if ($('#messages').length) {
                $('#messages').html(message);
            } else {
                $(message).insertAfter('.page-main-actions');
            }
            $("html, body").animate({scrollTop: 0});

            if (!data.labels) {
                return;
            }

            var linkSource = "data:application/pdf;base64," + data.labels;
            var pdfiFrameWindow = window.open();
            if (pdfiFrameWindow) {
                pdfiFrameWindow.document.write(
                    '<iframe src="' + linkSource +
                    '" frameborder="0" style="border:0; top:0; left:0; bottom:0; right:0; width:100%; height:100%; position: fixed;" allowfullscreen></iframe>'
                );
            }
            var downloadLink = document.createElement("a");

            downloadLink.href = linkSource;
            downloadLink.download = "gls-labels.pdf";
            downloadLink.click();
        }
    });
});
