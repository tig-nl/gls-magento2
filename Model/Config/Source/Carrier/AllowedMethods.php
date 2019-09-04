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

namespace TIG\GLS\Model\Config\Source\Carrier;

use Magento\Framework\Option\ArrayInterface;

// @codingStandardsIgnoreFile
class AllowedMethods implements ArrayInterface
{
    const GLS_CARRIER_METHOD_DEFAULT              = [
        'gls_default' => 'Next Business Day'
    ];

    const GLS_CARRIER_METHOD_EXPRESS_T9           = [
        'gls_express_t9' => 'Express before 9.00 AM'
    ];

    const GLS_CARRIER_METHOD_EXPRESS_T12          = [
        'gls_express_t12' => 'Express before 12.00 AM'
    ];

    const GLS_CARRIER_METHOD_EXPRESS_T17          = [
        'gls_express_t17' => 'Express before 17.00 AM'
    ];

    const GLS_CARRIER_METHOD_SATURDAY             = [
        'gls_saturday' => 'Saturday Service'
    ];

    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T9  = [
        'gls_saturday_express_t9' => 'Saturday Express before 9.00 AM'
    ];

    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_12  = [
        'gls_saturday_express_t12' => 'Saturday Express before 12.00 AM'
    ];

    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T17 = [
        'gls_saturday_express_t17' => 'Saturday Express before 17.00 AM'
    ];

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $list    = new \ReflectionClass($this);
        $methods = $list->getConstants();

        $i = 0;

        foreach ($methods as $name => $method) {
            $options[$i]['value'] = key($method);
            $options[$i]['label'] = $method[key($method)];
            $i++;
        }

        return $options;
    }
}
