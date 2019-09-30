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

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use TIG\GLS\Model\Config\Provider\Carrier;
use TIG\GLS\Webservice\Endpoint\DeliveryOptions\GetDeliveryOptions as DeliveryOptionsEndpoint;

class Services
{
    /** @var TimezoneInterface $timezone */
    private $timezone;

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
        TimezoneInterface $timezone,
        Carrier $carrierConfig,
        DeliveryOptionsEndpoint $deliveryOptions
    ) {
        $this->timezone        = $timezone;
        $this->carrierConfig   = $carrierConfig;
        $this->deliveryOptions = $deliveryOptions;
    }

    /**
     * @param $countryCode
     * @param $languageCode
     * @param $postCode
     *
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function getDeliveryOptions($countryCode, $languageCode, $postCode)
    {
        $this->deliveryOptions->setRequestData(
            [
                "countryCode"  => $countryCode,
                "langCode"     => $languageCode,
                "zipCode"      => $postCode,
                "shippingDate" => $this->calculateShippingDate('Y-m-d')
            ]
        );

        return $this->deliveryOptions->call();
    }

    /**
     * TODO: Move to separate class.
     *
     * @param $format
     *
     *
     * @return string
     */
    private function calculateShippingDate($format)
    {
        $currentTime    = $this->timezone->date();
        $currentTime    = $currentTime->format('H:m:s');
        $cutOffTime     = $this->carrierConfig->getCutOffTime();
        $shippingDate   = $this->timezone->date(null, null, true, false);
        $processingTime = $this->carrierConfig->getProcessingTime();
        $shippingDate->modify("+ $processingTime days");

        if ($currentTime > $cutOffTime) {
            $shippingDate->modify("+ 1 days");
        }

        return $shippingDate->format($format);
    }
}
