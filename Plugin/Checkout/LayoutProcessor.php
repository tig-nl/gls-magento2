<?php
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

namespace TIG\GLS\Plugin\Checkout;

class LayoutProcessor
{
    const GLS_PARCEL_SHOP_ADDRESS_FIELD = 'gls_delivery_option';

    public function afterProcess($subject, array $jsLayout)
    {
        $customField = $this->createDeliveryOptionField();

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']['shippingAddress']['children']['shipping-address-fieldset']['children'][self::GLS_PARCEL_SHOP_ADDRESS_FIELD] = $customField;

        return $jsLayout;
    }

    // @codingStandardsIgnoreLine
    private function createDeliveryOptionField()
    {
        return [
            'component'   => 'Magento_Ui/js/form/element/abstract',
            'config'      => [
                'customScope' => 'shippingAddress.custom_attributes',
                'customEntry' => null,
                'template'    => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
            ],
            'dataScope'   => 'shippingAddress.custom_attributes' . '.' . self::GLS_PARCEL_SHOP_ADDRESS_FIELD,
            'label'       => 'GLS Delivery Option',
            'provider'    => 'checkoutProvider',
            'sortOrder'   => 0,
            'validation'  => [
                'required-entry' => false
            ],
            'options'     => [],
            'filterBy'    => null,
            'customEntry' => null,
            'visible'     => false,
            'value'       => ''
        ];
    }
}
