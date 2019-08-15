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
 * to servicedesk@totalinternetgroup.nl so we can send you a copy immediately.
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
    'jquery'
], function ($) {
    "use strict";

    return function (configs, element) {
        element.on("click", function () {
            $.ajax({
                type : 'GET',
                url  : configs['url'],
            }).done(function (data) {
                if (data === 'ok') {
                    $('.validate-image').attr('src', configs['ok-image']);
                } else if (data === 'nok') {
                    $('.validate-image').attr('src', configs['nok-image']);
                } else {
                    $('.validate-image').attr('src', configs['unknown-image']);
                }
            }).fail(function (data) {
                $('.validate-image').attr('src', configs['unknown-image']);
            })
        });
    }
});
