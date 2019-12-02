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
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;

class DeleteShipment
{
    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * Delete constructor.
     *
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        ManagerInterface $messageManager
    ) {
        $this->messageManager = $messageManager;
    }

    /**
     * @param $order
     *
     * @param $shipment
     *
     * @return void
     * @throws LocalizedException
     */
    public function cancelShipment($order, $shipment)
    {
        $this->updateItemQty($order, $shipment);

        try {
            $shipment->delete();
        } catch (LocalizedException $exception) {
            throw new LocalizedException(__('Could not cancel shipment' . $exception));
        }

        $this->messageManager->addSuccessMessage(__('Shipment canceled successfully'));
    }

    /**
     * @param $order
     * @param $shipment
     */
    private function updateItemQty($order, $shipment)
    {
        $shipmentQty = $this->getShipmentQtyToShip($shipment);

        foreach ($shipmentQty as $key => $item) {
            $orderItem = $order->getItemById($key);
            $newQtyToShip = $orderItem->getQtyShipped() - $item;
            $orderItem->setQtyShipped($newQtyToShip);
        }

        $order->setState(Order::STATE_PROCESSING);
        $order->save();
    }

    /**
     * @param $shipment
     *
     * @return array
     */
    private function getShipmentQtyToShip($shipment)
    {
        $qty = [];

        foreach ($shipment->getItems() as $item) {
            $itemId = $item->getOrderItemId();
            $qty[$itemId] = $item->getQty();
        }

        return $qty;
    }
}
