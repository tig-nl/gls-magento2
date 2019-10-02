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

namespace TIG\GLS\Controller\Adminhtml\Label;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Controller\Adminhtml\AbstractLabel;
use TIG\GLS\Model\Config\Provider\Carrier;
use TIG\GLS\Model\Shipment\Label;
use TIG\GLS\Model\Shipment\LabelFactory;
use TIG\GLS\Service\ShippingDate;
use TIG\GLS\Webservice\Endpoint\Label\Create as CreateLabelEndpoint;

class Create extends AbstractLabel
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

    /** @var ScopeConfigInterface $scopeConfig */
    private $scopeConfig;

    /** @var ShipmentRepositoryInterface $shipments */
    private $shipments;

    /** @var OrderRepositoryInterface $orders */
    private $orders;

    /** @var ShippingDate $shippingDate */
    private $shippingDate;

    /** @var CreateLabelEndpoint $createLabel */
    private $createLabel;

    /** @var $missingData */
    private $missingData = null;

    /**
     * Create constructor.
     *
     * @param Context                     $context
     * @param ScopeConfigInterface        $scopeConfig
     * @param ShipmentRepositoryInterface $shipments
     * @param OrderRepositoryInterface    $orders
     * @param LabelRepositoryInterface    $labelRepository
     * @param LabelFactory                $label
     * @param ShippingDate                $shippingDate
     * @param CreateLabelEndpoint         $createLabel
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ShipmentRepositoryInterface $shipments,
        OrderRepositoryInterface $orders,
        LabelRepositoryInterface $labelRepository,
        LabelFactory $label,
        ShippingDate $shippingDate,
        CreateLabelEndpoint $createLabel
    ) {
        parent::__construct(
            $context,
            $label,
            $labelRepository
        );

        $this->scopeConfig  = $scopeConfig;
        $this->shipments    = $shipments;
        $this->orders       = $orders;
        $this->shippingDate = $shippingDate;
        $this->createLabel  = $createLabel;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|Create|void
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $shipmentId = $this->getShipmentId();
        $shipment   = $this->shipments->get($shipmentId);

        if (!$shipment) {
            return;
        }

        $orderId = $shipment->getOrderId();
        $order   = $this->orders->get($orderId);

        if (!$order) {
            return;
        }

        $data = $this->mapLabelData($shipment, $order);

        if ($this->missingData) {
            return $this->redirectToShipmentView($shipmentId);
        }

        $this->createLabel->setRequestData($data);
        $this->setErrorMessage('An error occurred while creating the label.');
        $this->setSuccessMessage('Label created successfully.');
        $label = $this->createLabel->call();

        if ($this->callIsSuccess($label)) {
            $this->saveLabelData($shipmentId, $label['units'][0]);
        }

        return $this->redirectToShipmentView($shipmentId);
    }

    /**
     * @param $shipment
     * @param $order
     *
     * @return array
     */
    private function mapLabelData($shipment, $order)
    {
        $deliveryOption = json_decode($order->getGlsDeliveryOption());

        $data                      = $this->addShippingInformation();
        $data["services"]          = $this->mapServices($deliveryOption->details, $deliveryOption->type);
        $data["trackingLinkType"]  = 'u';
        $data['labelType']         = 'pdf';
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
     * @param       $shipmentId
     * @param array $labelData
     *
     * @throws \Exception
     * TODO: Use LabelRepositoryInterface for saving.
     */
    private function saveLabelData($shipmentId, array $labelData)
    {
        $labelFactory = $this->createLabelFactory();
        $labelFactory->setData(
            [
                Label::GLS_SHIPMENT_LABEL_SHIPMENT_ID        => $shipmentId,
                Label::GLS_SHIPMENT_LABEL_UNIT_ID            => $labelData['unitId'],
                Label::GLS_SHIPMENT_LABEL_UNIT_NO            => $labelData['unitNo'],
                Label::GLS_SHIPMENT_LABEL_UNIQUE_NO          => $labelData['uniqueNo'],
                Label::GLS_SHIPMENT_LABEL_LABEL              => $labelData['label'],
                Label::GLS_SHIPMENT_LABEL_UNIT_TRACKING_LINK => $labelData['unitTrackingLink']
            ]
        );
        $labelFactory->save();
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
        switch ($type) {
            case Carrier::GLS_DELIVERY_OPTION_PARCEL_SHOP_LABEL:
                return [
                    "shopDeliveryParcelShopId" => $details->parcelShopId
                ];
            case Carrier::GLS_DELIVERY_OPTION_EXPRESS_LABEL:
                return [
                    $type => $details->service
                ];
            case Carrier::GLS_DELIVERY_OPTION_SATURDAY_LABEL:
                return [
                    $type => true
                ];
            default:
                return (object) null;
        }
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
            "EmailSubject"       => __('Your order has been shipped.')
        ];

        $this->missingData = $this->isDataMissing($email);

        if ($this->missingData) {
            $this->addErrorMessage(
                $this->missingData,
                'General Contact and a Customer Support Contact',
                'Stores > Configuration > General > Store Email Addresses'
            );

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

        $this->missingData = $this->isDataMissing($address);

        if ($this->missingData) {
            $this->addErrorMessage(
                $this->missingData,
                'Pickup Address',
                'Stores > Configuration > General > General > Store Information'
            );

            return [];
        }

        return $address;
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
     * @param $missingCode
     * @param $missingOption
     * @param $configurationPath
     *
     * @return \Magento\Framework\Message\ManagerInterface
     */
    private function addErrorMessage($missingCode, $missingOption, $configurationPath)
    {
        return $this->messageManager->addErrorMessage(
        // @codingStandardsIgnoreLine
            "Label could not be created, because $missingCode is not configured. Please make sure you've configured a $missingOption in $configurationPath."
        );
    }

    /**
     * TODO: getTotalWeight() return null too often. How to trigger calculation?
     *
     * @param $shipment
     *
     * @return array
     */
    private function prepareShippingUnit($shipment)
    {
        $totalWeight = $shipment->getTotalWeight();

        if ($totalWeight > self::GLS_PARCEL_MAX_WEIGHT) {
            $this->messageManager->addErrorMessage(
                "Label could not be created, because the shipment is too heavy."
            );

            return [];
        }

        $weight = $totalWeight != 0 ? $totalWeight : 1;

        return [
            "unitId"   => $shipment->getIncrementId(),
            "unitType" => "cO",
            "weight"   => $weight
        ];
    }
}
