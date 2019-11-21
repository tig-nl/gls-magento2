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
 *
 * @codingStandardsIgnoreFile
 */
namespace TIG\GLS\Service\Label;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use TIG\GLS\Model\Config\Provider\Carrier;
use TIG\GLS\Service\ShippingDate;
use TIG\GLS\Webservice\Endpoint\Label\Create as EndpointLabelCreate;

class Create extends ShippingInformation
{
    const XPATH_CONFIG_TRANS_IDENT_SUPPORT_NAME           = 'trans_email/ident_support/name';
    const XPATH_CONFIG_TRANS_IDENT_SUPPORT_EMAIL          = 'trans_email/ident_support/email';
    const XPATH_CONFIG_TRANS_IDENT_GENERAL_NAME           = 'trans_email/ident_support/name';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_NAME     = 'general/store_information/name';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_STREET   = 'general/store_information/street_line1';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_HOUSE_NO = 'general/store_information/street_line2';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_POSTCODE = 'general/store_information/postcode';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_CITY     = 'general/store_information/city';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_COUNTRY  = 'general/store_information/country_id';
    const GLS_PARCEL_MAX_WEIGHT                           = 31.9;

    /**
     * @var Create $createLabel
     */
    private $createLabel;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var $errors
     */
    private $errors = null;

    /**
     * @var Carrier
     */
    private $carrierConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ShippingDate
     */
    private $shippingDate;

    /**
     * @param EndpointLabelCreate         $createLabel
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param Carrier                     $carrierConfig
     * @param ScopeConfigInterface        $scopeConfig
     * @param ShippingDate                $shippingDate
     */
    public function __construct(
        EndpointLabelCreate $createLabel,
        ShipmentRepositoryInterface $shipmentRepository,
        Carrier $carrierConfig,
        ScopeConfigInterface $scopeConfig,
        ShippingDate $shippingDate
    ) {
        $this->createLabel = $createLabel;
        $this->shipmentRepository = $shipmentRepository;
        $this->carrierConfig = $carrierConfig;
        $this->scopeConfig = $scopeConfig;
        $this->shippingDate = $shippingDate;
    }

    /**
     * @param $requestData
     *
     * @return mixed
     * @throws \Zend_Http_Client_Exception
     */
    public function createLabel($requestData)
    {
        $this->createLabel->setRequestData($requestData);

        return $this->createLabel->call();
    }

    /**
     * @param $shipmentId
     * @param $controllerModule
     * @param $version
     *
     * @return array|bool
     */
    public function getRequestData($shipmentId, $controllerModule, $version)
    {
        $shipment   = $this->shipmentRepository->get($shipmentId);

        if (!$shipment) {
            return false;
        }

        return $this->mapLabelData($shipment, $controllerModule, $version);
    }

    /**
     * @param $shipment
     * @param $controllerModule
     * @param $version
     *
     * @return array
     */
    private function mapLabelData($shipment, $controllerModule, $version)
    {
        $order = $shipment->getOrder();
        $deliveryOption = json_decode($order->getGlsDeliveryOption());

        $data                      = $this->addShippingInformation($controllerModule, $version);
        $data["services"]          = $this->mapServices($deliveryOption->details, $deliveryOption->type);
        $data["trackingLinkType"]  = 'u';
        $data['labelType']         = $this->carrierConfig->isShopReturnActive() ? 'pdfA6U' : 'pdf';
        $data['notificationEmail'] = $this->prepareNotificationEmail();
        $data['returnRoutingData'] = false;
        $data['addresses']         = [
            'deliveryAddress' => $deliveryOption->deliveryAddress,
            'pickupAddress'   => $this->preparePickupAddress()
        ];
        $data['shippingDate']      = $this->shippingDate->calculate("Y-m-d", false);
        $data['units']             = [
            $this->prepareShippingUnit($shipment)
        ];

        return $data;
    }

    /**
     * The General Contact is used as
     *
     * @return array
     */
    private function prepareNotificationEmail()
    {
        $email = [
            "sendMail"           => true,
            "senderName"         => $this->scopeConfig->getValue(self::XPATH_CONFIG_TRANS_IDENT_GENERAL_NAME),
            "senderReplyAddress" => $this->scopeConfig->getValue(self::XPATH_CONFIG_TRANS_IDENT_SUPPORT_EMAIL),
            "senderContactName"  => $this->scopeConfig->getValue(self::XPATH_CONFIG_TRANS_IDENT_SUPPORT_NAME),
            // @codingStandardsIgnoreLine
            "EmailSubject"       => __('Your order has been shipped.')
        ];

        $missing = $this->isDataMissing($email);
        if ($missing) {
            $this->errors['missing'][] = [
                'missingCode' => $missing,
                'missingOption' => 'General Contact and a Customer Support Contact',
                'configurationPath' => 'Stores > Configuration > General > Store Email Addresses'
            ];

            return [];
        }

        return $email;
    }

    /**
     * Returns the Pickup Address AKA Sender Address: we're using the information from
     * Stores > Configuration > General > Store Information.
     *
     * @return array
     */
    private function preparePickupAddress()
    {
        $address = [
            "name1"       => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_NAME),
            "street"      => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_STREET),
            "houseNo"     => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_HOUSE_NO),
            "zipCode"     => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_POSTCODE),
            "city"        => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_CITY),
            "countryCode" => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_COUNTRY)
        ];

        $missing = $this->isDataMissing($address);
        if ($missing) {
            $this->errors['missing'][] = [
                'missingCode' => $missing,
                'missingOption' => 'Pickup Address',
                'configurationPath' => 'Stores > Configuration > General > General > Store Information'
            ];

            return [];
        }

        return $address;
    }

    /**
     * When an empty object is returned, the default BusinessParcel product is used
     * in CreateLabel.
     *
     * @param      $details
     * @param null $type
     *
     * @return array|object
     */
    private function mapServices($details, $type = null)
    {
        $service = [
            "shopReturnService" => (bool) $this->carrierConfig->isShopReturnActive()
        ];

        switch ($type) {
            case Carrier::GLS_DELIVERY_OPTION_PARCEL_SHOP_LABEL:
                return $service + ["shopDeliveryParcelShopId" => $details->parcelShopId];
            case Carrier::GLS_DELIVERY_OPTION_EXPRESS_LABEL:
                return $service + [$type => $details->service];
            case Carrier::GLS_DELIVERY_OPTION_SATURDAY_LABEL:
                return $service + [$type => true];
            default:
                return $service;
        }
    }

    /**
     * @param $data
     *
     * @return bool
     */
    private function isDataMissing($data)
    {
        $missing = array_search(null, $data);

        if ($missing) {
            return $missing;
        }

        return false;
    }

    /**
     * TODO: getTotalWeight() returns null too often. How to trigger calculation?
     *
     * @param $shipment
     *
     * @return array
     */
    private function prepareShippingUnit($shipment)
    {
        $totalWeight = $shipment->getTotalWeight();

        if ($totalWeight > self::GLS_PARCEL_MAX_WEIGHT) {
            $this->errors['errors'][] = "Label could not be created, because the shipment is too heavy.";

            return [];
        }

        $weight = $totalWeight != 0 ? $totalWeight : 1;

        return [
            "unitId"   => $shipment->getIncrementId(),
            "unitType" => "cO",
            "weight"   => $weight
        ];
    }

    /**
     * @return array|null
     */
    public function getErrors()
    {
        return $this->errors;
    }
}
