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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use TIG\GLS\Webservice\Endpoint\Label\Create as CreateLabelEndpoint;

class Create extends Action
{
    const ADMIN_ORDER_SHIPMENT_VIEW_URI                   = 'adminhtml/order_shipment/view';
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

    /** @var CreateLabelEndpoint $createLabel */
    private $createLabel;

    /**
     * Create constructor.
     *
     * @param Context             $context
     * @param CreateLabelEndpoint $createLabel
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        ShipmentRepositoryInterface $shipments,
        OrderRepositoryInterface $orders,
        CreateLabelEndpoint $createLabel
    ) {
        parent::__construct($context);

        $this->scopeConfig = $scopeConfig;
        $this->shipments   = $shipments;
        $this->orders      = $orders;
        $this->createLabel = $createLabel;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $shipmentId = $this->getRequest()->getParam('shipment_id');
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
        $this->createLabel->setRequestData($data);
        $label = $this->createLabel->call();

        return $this->validateStatus($label, $shipmentId);
    }

    /**
     * @param $shipment
     * @param $order
     *
     * @return array
     */
    // @codingStandardsIgnoreLine
    private function mapLabelData($shipment, $order)
    {
        $deliveryOption = json_decode($order->getGlsDeliveryOption());

        return [
            "services"              => $this->mapServices($deliveryOption->details, $deliveryOption->type),
            "trackingLinkType"      => "u",
            "labelType"             => "pdf",
            "notificationEmail"     => $this->prepareNotificationEmail(),
            "returnRoutingData"     => false,
            "addresses"             => [
                "deliveryAddress" => $deliveryOption->deliveryAddress,
                "pickupAddress"   => $this->preparePickupAddress(),
            ],
            "shippingDate"          => date("Y-m-d"),
            "units"                 => [
                $this->prepareShippingUnit($shipment, $order)
            ],
            "shippingSystemName"    => $this->getRequest()->getControllerModule(),
            "shippingSystemVersion" => $this->getRequest()->getVersion(),
            "shiptype"              => "p"
        ];
    }

    /**
     * @param $label
     * @param $shipmentId
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    private function validateStatus($label, $shipmentId)
    {
        $result = $this->resultRedirectFactory->create();

        if ($label['error']) {
            $status = $label['status'];
            $message = $label['message'];
            $this->messageManager->addErrorMessage(
                __('An error occurred while creating the label. ') . "$message [Status: $status]"
            );

            return $result->setPath(self::ADMIN_ORDER_SHIPMENT_VIEW_URI, ['shipment_id' => $shipmentId]);
        }

        $this->messageManager->addSuccessMessage(
            __('Label created successfully.')
        );

        return $result->setPath(self::ADMIN_ORDER_SHIPMENT_VIEW_URI, ['shipment_id' => $shipmentId]);
    }

    /**
     * When an empty array is returned, the default BusinessParcel product is used
     * in CreateLabel.
     *
     * @param null $type
     * @param      $details
     *
     * @return array
     */
    private function mapServices($details, $type = null)
    {
        switch ($type) {
            case 'parcelShop':
                return [
                    "shopDeliveryParcelShopId" => $details->parcelShopId
                ];
            case 'expressService':
                return [
                    $type => $details->service
                ];
            case 'saturdayService':
                return [
                    $type => true
                ];
            default:
                return [];
        }
    }

    /**
     * The General Contact is used as
     *
     * @return array
     */
    private function prepareNotificationEmail()
    {
        return [
            "sendMail"           => true,
            "senderName"         => $this->scopeConfig->getValue(self::XPATH_CONFIG_TRANS_IDENT_GENERAL_NAME),
            "senderReplyAddress" => $this->scopeConfig->getValue(self::XPATH_CONFIG_TRANS_IDENT_SUPPORT_EMAIL),
            "senderContactName"  => $this->scopeConfig->getValue(self::XPATH_CONFIG_TRANS_IDENT_SUPPORT_NAME),
            "EmailSubject"       => __('Your order has been shipped.')
        ];
    }

    /**
     * Returns the Pickup Address AKA Sender Address: we're using the information from
     * Stores > Configuration > General > Store Information.
     *
     * @return array
     */
    private function preparePickupAddress()
    {
        return [
            "name1"       => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_NAME),
            "street"      => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_STREET),
            "houseNo"     => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_HOUSE_NO),
            "zipCode"     => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_POSTCODE),
            "city"        => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_CITY),
            "countryCode" => $this->scopeConfig->getValue(self::XPATH_CONFIG_GENERAL_STORE_INFORMATION_COUNTRY)
        ];
    }

    /**
     * @param $shipment
     * @param $order
     *
     * @return array
     */
    private function prepareShippingUnit($shipment, $order)
    {
        return [
            "unitId"   => $shipment->getIncrementId(),
            "unitType" => "cO",
            "weight"   => $order->getWeight() <= self::GLS_PARCEL_MAX_WEIGHT ? $order->getWeight() : self::GLS_PARCEL_MAX_WEIGHT
        ];
    }
}
