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

namespace TIG\GLS\Service\DeliveryOptions;

use TIG\GLS\Model\Config\Provider\Carrier;
use TIG\GLS\Webservice\Endpoint\DeliveryOptions\GetDeliveryOptions as DeliveryOptionsEndpoint;

class Services
{
    /** @var Carrier $carrierConfig */
    private $carrierConfig;

    /** @var DeliveryOptionsEndpoint $deliveryOptions */
    private $deliveryOptions;

    /**
     * Services constructor.
     *
     * @param Carrier $carrierConfig
     */
    public function __construct(
        Carrier $carrierConfig,
        DeliveryOptionsEndpoint $deliveryOptions
    ) {
        $this->carrierConfig   = $carrierConfig;
        $this->deliveryOptions = $deliveryOptions;
    }

    /**
     * @param $countryCode
     * @param $languageCode
     * @param $postCode
     * @param $shippingDate
     *
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getDeliveryOptions($countryCode, $languageCode, $postCode)
    {
//        $shippingDate = date("Y-m-d");
        $shippingDate = '2019-09-27';

        // TODO: Implement configuration logic for cut-off time and processing time (verwerkingsduur).

        $this->deliveryOptions->setRequestData(
            [
                "countryCode"  => $countryCode,
                "langCode"     => $languageCode,
                "zipCode"      => $postCode,
                "shippingDate" => $shippingDate
            ]
        );

        return $this->deliveryOptions->call();
    }
}
