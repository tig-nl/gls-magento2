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
    const GLS_CARRIER_LABEL_OPERATOR                    = '_LABEL';
    const GLS_CARRIER_METHOD_DEFAULT                    = 'gls_default';
    const GLS_CARRIER_METHOD_DEFAULT_LABEL              = 'Next Business Day';
    const GLS_CARRIER_METHOD_EXPRESS_T9                 = 'gls_express_t9';
    const GLS_CARRIER_METHOD_EXPRESS_T9_LABEL           = 'Express before 9.00 AM';
    const GLS_CARRIER_METHOD_EXPRESS_T12                = 'gls_express_t12';
    const GLS_CARRIER_METHOD_EXPRESS_T12_LABEL          = 'Express before 12.00 AM';
    const GLS_CARRIER_METHOD_EXPRESS_T17                = 'gls_express_t17';
    const GLS_CARRIER_METHOD_EXPRESS_T17_LABEL          = 'Express before 17.00 AM';
    const GLS_CARRIER_METHOD_SATURDAY                   = 'gls_saturday';
    const GLS_CARRIER_METHOD_SATURDAY_LABEL             = 'Saturday Service';
    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T9        = 'gls_saturday_express_t9';
    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T9_LABEL  = 'Saturday Express before 9.00 AM';
    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T12       = 'gls_saturday_express_t12';
    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T12_LABEL = 'Saturday Express before 12.00 AM';
    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T17       = 'gls_saturday_express_t17';
    const GLS_CARRIER_METHOD_SATURDAY_EXPRESS_T17_LABEL = 'Saturday Express before 17.00 AM';

    /**
     * @return array|mixed
     * @throws \ReflectionException
     */
    public function toOptionArray()
    {
        $list      = new \ReflectionClass($this);
        $constants = $list->getConstants();

        $methods = $this->listAvailableMethods();

        $i = 0;

        foreach ($methods as $name => $method) {
            $options[$i]['value'] = $method;
            $options[$i]['label'] = $constants[$name . self::GLS_CARRIER_LABEL_OPERATOR];
            $i++;
        }

        return $options;
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function listAvailableMethods()
    {
        $list      = new \ReflectionClass($this);
        $constants = $list->getConstants();

        $methods = array_filter($constants, function ($key) {
            return strpos($key, self::GLS_CARRIER_LABEL_OPERATOR) === false;
        }, ARRAY_FILTER_USE_KEY);

        return $methods;
    }
}
