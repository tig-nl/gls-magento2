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
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Store\Model\ScopeInterface;
use TIG\GLS\Model\Config\Provider\Carrier;
use TIG\GLS\Plugin\Quote\Model\QuoteManagement;
use TIG\GLS\Service\ShippingDate;
use TIG\GLS\Webservice\Endpoint\Label\Create as EndpointLabelCreate;

/**
 * Class Create
 * @package TIG\GLS\Service\Label
 */
class Create extends ShippingInformation
{
    const XPATH_CONFIG_TRANS_IDENT_SUPPORT_NAME                 = 'trans_email/ident_support/name';
    const XPATH_CONFIG_TRANS_IDENT_SUPPORT_EMAIL                = 'trans_email/ident_support/email';
    const XPATH_CONFIG_TRANS_IDENT_GENERAL_NAME                 = 'trans_email/ident_support/name';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_NAME           = 'general/store_information/name';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_STREET         = 'general/store_information/street_line1';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_HOUSE_NO       = 'general/store_information/street_line2';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_POSTCODE       = 'general/store_information/postcode';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_CITY           = 'general/store_information/city';
    const XPATH_CONFIG_GENERAL_STORE_INFORMATION_COUNTRY        = 'general/store_information/country_id';
    const XPATH_CONFIG_TIG_GLS_GENERAL_LABEL_TYPE               = 'tig_gls/general/label_type';
    const XPATH_CONFIG_TIG_GLS_GENERAL_LABEL_MARGIN_TOP         = 'tig_gls/general/label_margin_top_a4';
    const XPATH_CONFIG_TIG_GLS_GENERAL_LABEL_MARGIN_LEFT        = 'tig_gls/general/label_margin_left_a4';
    const XPATH_CONFIG_TIG_GLS_GENERAL_NON_GLS_MASSACTIONS      = 'tig_gls/general/non_gls_massactions';
    const XPATH_CONFIG_CARRIERS_TIG_GLS_DELIVERY_OPTIONS_ACTIVE = 'carriers/tig_gls/delivery_options_active';
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
     * @var QuoteManagement
     */
    private $quoteManagement;

    /**
     * @param EndpointLabelCreate         $createLabel
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param Carrier                     $carrierConfig
     * @param ScopeConfigInterface        $scopeConfig
     * @param ShippingDate                $shippingDate
     * @param QuoteManagement             $quoteManagement
     */
    public function __construct(
        EndpointLabelCreate $createLabel,
        ShipmentRepositoryInterface $shipmentRepository,
        Carrier $carrierConfig,
        ScopeConfigInterface $scopeConfig,
        ShippingDate $shippingDate,
        QuoteManagement $quoteManagement
    ) {
        $this->createLabel = $createLabel;
        $this->shipmentRepository = $shipmentRepository;
        $this->carrierConfig = $carrierConfig;
        $this->scopeConfig = $scopeConfig;
        $this->shippingDate = $shippingDate;
        $this->quoteManagement = $quoteManagement;
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
     * @return array|bool
     */
    private function mapLabelData($shipment, $controllerModule, $version)
    {
        $order           = $shipment->getOrder();
        $deliveryOption  = json_decode($order->getGlsDeliveryOption());
        // If no delivery options are available, check if non-GLS shipments are allowed,
        // or if delivery options are not enabled.
        if (!$deliveryOption && (
            !$this->scopeConfig->getValue(
                self::XPATH_CONFIG_CARRIERS_TIG_GLS_DELIVERY_OPTIONS_ACTIVE,
                ScopeInterface::SCOPE_STORE,
                $order->getStoreId()
            )
        ) ||
            $this->scopeConfig->getValue(self::XPATH_CONFIG_TIG_GLS_GENERAL_NON_GLS_MASSACTIONS)
        ) {
            $deliveryOption = $this->getDefaultGLSOptions($shipment);
        }

        if (!$deliveryOption) {
            return false;
        }

        $deliveryAddress = $deliveryOption->deliveryAddress;
        $labelType       = $this->getLabelType();

        $data                      = $this->addShippingInformation($controllerModule, $version);
        $data["services"]          = $this->mapServices($deliveryOption->details, $deliveryOption->type, $deliveryAddress->countryCode);
        $data["trackingLinkType"]  = 'u';
        $data['labelType']         = $labelType;
        $data['notificationEmail'] = $this->prepareNotificationEmail();
        $data['returnRoutingData'] = false;
        $data['addresses']         = [
            'deliveryAddress' => $deliveryAddress,
            'pickupAddress'   => $this->preparePickupAddress()
        ];
        $data['shippingDate']      = $this->shippingDate->calculate("Y-m-d", false);
        $data['reference']         = $order->getIncrementId();
        $data['units']             = [
            $this->prepareShippingUnit($shipment)
        ];

        if (in_array($labelType, ['pdf2A4','pdf4A4'])) {
            $data['labelA4MoveYMm'] = $this->getLabelMarginTop();
            $data['labelA4MoveXMm'] = $this->getLabelMarginLeft();
        }

        return $data;
    }

    /**
     * @param Order    $order
     * @param Shipment $shipment
     *
     * @return object
     */
    private function getDefaultGLSOptions($shipment)
    {
        return (object) $deliveryOption = [
            'type' => 'deliveryService',
            'details' => null,
            'deliveryAddress' => $this->quoteManagement->mapDeliveryAddress(
                $shipment->getShippingAddress(),
                $shipment->getBillingAddress()
            )
        ];
    }

    /**
     * @return mixed|string
     */
    private function getLabelType()
    {
        return $this->scopeConfig->getValue(self::XPATH_CONFIG_TIG_GLS_GENERAL_LABEL_TYPE) ?? 'pdfA6S';
    }

    /**
     * @return int|mixed
     */
    private function getLabelMarginTop()
    {
        return $this->scopeConfig->getValue(self::XPATH_CONFIG_TIG_GLS_GENERAL_LABEL_MARGIN_TOP) ?? 0;
    }

    /**
     * @return int|mixed
     */
    private function getLabelMarginLeft()
    {
        return $this->scopeConfig->getValue(self::XPATH_CONFIG_TIG_GLS_GENERAL_LABEL_MARGIN_LEFT) ?? 0;
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
    private function mapServices($details, $type = null, $countryCode = 'NL')
    {
        $service = [
            "shopReturnService" => (bool) ($this->carrierConfig->isShopReturnActive() && $countryCode == 'NL')
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
