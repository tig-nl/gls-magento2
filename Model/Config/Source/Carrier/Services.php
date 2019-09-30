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
use TIG\GLS\Service\ServicesReflectionClass;

// @codingStandardsIgnoreFile
class Services implements ArrayInterface
{
    const GLS_CARRIER_SERVICE_LABEL_OPERATOR                  = '_LABEL';
    const GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T9        = 'T9';
    const GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T9_LABEL  = 'TimeDefiniteService (Before 9.00 AM)';
    const GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T12       = 'T12';
    const GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T12_LABEL = 'TimeDefiniteService (Before 12.00 AM)';
    const GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T17       = 'T17';
    const GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T17_LABEL = 'TimeDefiniteService (Before 17.00 AM)';
    const GLS_CARRIER_SERVICE_EXPRESS_SATURDAY_T9             = 'S9';
    const GLS_CARRIER_SERVICE_EXPRESS_SATURDAY_T9_LABEL       = 'SaturdayService (Before 9.00 AM)';
    const GLS_CARRIER_SERVICE_EXPRESS_SATURDAY_T12            = 'S12';
    const GLS_CARRIER_SERVICE_EXPRESS_SATURDAY_T12_LABEL      = 'SaturdayService (Before 12.00 AM)';
    const GLS_CARRIER_SERVICE_EXPRESS_SATURDAY_T17            = 'S17';
    const GLS_CARRIER_SERVICE_EXPRESS_SATURDAY_T17_LABEL      = 'SaturdayService (Before 17.00 AM)';

    /** @var ServicesReflectionClass $servicesReflection */
    private $servicesReflection;

    public function __construct(ServicesReflectionClass $servicesReflection)
    {
        $this->servicesReflection = $servicesReflection;
    }

    /**
     * @return array|mixed
     * @throws \ReflectionException
     */
    public function toOptionArray()
    {
        $constants = $this->servicesReflection->getConstants();

        $methods = $this->listAvailableMethods();

        $i = 0;

        foreach ($methods as $name => $method) {
            $options[$i]['value'] = $method;
            $options[$i]['label'] = __($constants[$name . self::GLS_CARRIER_SERVICE_LABEL_OPERATOR]);
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
        $constants = $this->servicesReflection->getConstants();

        $methods = array_filter($constants, function ($key) {
            return strpos($key, self::GLS_CARRIER_SERVICE_LABEL_OPERATOR) === false;
        }, ARRAY_FILTER_USE_KEY);

        return $methods;
    }
}
