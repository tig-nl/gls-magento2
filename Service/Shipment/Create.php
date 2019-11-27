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
 */

namespace TIG\GLS\Service\Shipment;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\Convert;
use Psr\Log\LoggerInterface;

class Create
{
    /**
     * @var Convert\Order
     */
    private $convertOrder;

    /**
     * @var InventorySource
     */
    private $inventorySource;

    /**
     * @var array
     */
    private $data = ['shipmentIds' => [], 'errors' => []];

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Create constructor.
     *
     * @param Convert\Order   $convertOrder
     * @param InventorySource $inventorySource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Convert\Order $convertOrder,
        InventorySource $inventorySource,
        LoggerInterface $logger
    ) {
        $this->convertOrder = $convertOrder;
        $this->inventorySource = $inventorySource;
        $this->logger = $logger;
    }

    /**
     * Only creates a shipment when the order doesn't already contain one
     *
     * @param $order
     */
    public function createShipment($order)
    {
        $shipmentsCollection = $order->getShipmentsCollection();
        if ($shipmentsCollection->getSize() > 0) {
            $this->setShipmentIds($order);

            return;
        }

        try {
            $this->orderCanShip($order);
            $shipment = $this->convertOrder->toShipment($order);
            $this->addShippingItems($order, $shipment);
            $this->saveShipment($order, $shipment);

            $this->data['shipmentIds'][] = $shipment->getEntityId();
        } catch (LocalizedException $exception) {
            $this->data['errors'][$order->getId()] = $exception->getMessage();
            $this->logger->critical($exception->getMessage());
        }
    }

    /**
     * @param $order
     */
    private function setShipmentIds($order)
    {
        foreach ($order->getShipmentsCollection() as $shipment) {
            $this->data['shipmentIds'][] = $shipment->getId();
        }
    }

    /**
     * @return array
     */
    public function getShipmentIds()
    {
        return $this->data['shipmentIds'];
    }

    /**
     * @param $order
     * @param $shipment
     *
     * @throws LocalizedException
     */
    private function addShippingItems($order, $shipment)
    {
        foreach ($order->getAllItems() as $orderItem) {
            $shipmentItem = $this->getShipmentItem($orderItem);
            !$shipmentItem ?: $shipment->addItem($shipmentItem);
        }
    }

    /**
     * @param $order
     *
     * @throws LocalizedException
     */
    private function orderCanShip($order)
    {
        if (!$order->canShip()) {
            // @codingStandardsIgnoreLine
            throw new LocalizedException(__('Unable to create shipment of order %s', [$order->getId()]));
        }
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->data['errors'];
    }

    /**
     * @param $order
     * @param $shipment
     */
    private function saveShipment($order, $shipment)
    {
        $shipment->register();

        $shipmentAttributes = $shipment->getExtensionAttributes();

        // This method only exists if you have the various Magento Inventory extensions installed.
        if (method_exists($shipmentAttributes, 'setSourceCode')) {
            $source = $this->inventorySource->getSource($order, $this->getShippingItems($shipment));
            $shipmentAttributes->setSourceCode($source);
            $shipment->setExtensionAttributes($shipmentAttributes);
        }

        $order->setIsInProcess(true);

        $shipment->save();
        $order->save();
    }

    /**
     * @param $shipment
     *
     * @return array
     */
    private function getShippingItems($shipment)
    {
        $shippingItems = [];

        foreach ($shipment->getItems() as $shipmentItem) {
            $shippingItems[$shipmentItem->getProductId()] = (string)$shipmentItem->getQty();
        }

        return $shippingItems;
    }

    /**
     * @param $orderItem
     *
     * @return \Magento\Sales\Model\Order\Shipment\Item|bool
     * @throws LocalizedException
     */
    private function getShipmentItem($orderItem)
    {
        if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
            return false;
        }

        $qty = $orderItem->getQtyToShip();
        $shipmentItem = $this->convertOrder->itemToShipmentItem($orderItem);
        $shipmentItem->setQty($qty);

        return $shipmentItem;
    }
}
