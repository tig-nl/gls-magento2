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
use TIG\GLS\Model\Config\Source\Carrier\Services as ServicesSource;

class Services
{
    const GLS_CARRIER_SERVICE_BUSINESS_PARCEL       = 'business_parcel';
    const GLS_CARRIER_SERVICE_BUSINESS_PARCEL_LABEL = 'Next Business Day';
    const GLS_CARRIER_SERVICE_EXPRESS               = [
        ServicesSource::GLS_CARRIER_SERVICE_EXPRESS_SATURDAY          => 'Saturday',
        ServicesSource::GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T9  => 'Before 9.00 AM',
        ServicesSource::GLS_CARRIER_SERVICE_EXPRESS_TIME_DEFINITE_T12 => 'Before 12.00 AM'
    ];

    /** @var Carrier $carrierConfig */
    private $carrierConfig;

    /** @var array $availableServices */
    private $availableServices = [];

    /** @var array $requiredData */
    private $requiredData = [
        'code',
        'label',
        'fee'
    ];

    /**
     * Services constructor.
     *
     * @param Carrier $carrierConfig
     */
    public function __construct(
        Carrier $carrierConfig
    ) {
        $this->carrierConfig = $carrierConfig;
    }

    /**
     * @return array
     */
    public function getAvailableServices()
    {
        $this->mapBusinessServices();
        $this->mapExpressServices();

        return $this->availableServices;
    }

    /**
     * @return array|null
     */
    private function mapBusinessServices()
    {
        if (!$this->carrierConfig->isBusinessParcelActive()) {
            return null;
        }

        $this->availableServices[] = array_combine(
            $this->requiredData,
            [
                self::GLS_CARRIER_SERVICE_BUSINESS_PARCEL,
                self::GLS_CARRIER_SERVICE_BUSINESS_PARCEL_LABEL,
                $this->carrierConfig->getBaseHandlingFee()
            ]
        );

        return $this->availableServices;
    }

    /**
     * @return array
     */
    private function mapExpressServices()
    {
        $services = $this->carrierConfig->getActiveExpressServices();
        $fees     = $this->carrierConfig->getExpressHandlingFees();

        foreach ($services as $service) {
            $this->availableServices[] = array_combine(
                $this->requiredData,
                [
                    $service,
                    self::GLS_CARRIER_SERVICE_EXPRESS[$service],
                    $this->getCorrespondingServiceFee($service, (array) $fees)
                ]
            );
        }

        return $this->availableServices;
    }

    /**
     * @param $code
     * @param $serviceFees
     *
     * @return mixed
     */
    private function getCorrespondingServiceFee($code, $serviceFees)
    {
        $fee = array_filter(
            $serviceFees,
            function ($value) use ($code) {
                return $code == $value->shipping_method;
            }
        );

        $fee = reset($fee);

        return $fee->additional_handling_fee;
    }
}
