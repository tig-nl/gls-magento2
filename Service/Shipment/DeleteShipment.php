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

use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Order;

class DeleteShipment
{
    const ADMIN_ORDER_ORDER_VIEW_URI = 'sales/order/view';

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * Delete constructor.
     *
     * @param RedirectFactory $redirectFactory
     */
    public function __construct(
        RedirectFactory $redirectFactory
    ) {
        $this->redirectFactory = $redirectFactory;
    }

    /**
     * @param $order
     */
    public function deleteShipments($order)
    {
        $shipments = $order->getShipmentsCollection();

        foreach ($shipments as $shipment) {
            $shipment->delete();
        }
    }

    /**
     * @param $order
     *
     * @param $shipment
     *
     * @return Redirect
     */
    public function cancelShipment($order, $shipment)
    {
        $this->deleteShipments($order);
        $this->resetItems($shipment->getItems());

        $order->setState(Order::STATE_PROCESSING);
        $order->save();

        return $this->redirectToOrderView($order->getId());
    }

    /**
     * @param $items
     */
    public function resetItems($items)
    {
        foreach ($items as $item) {
            $item->setQtyShipped(0);
            $item->save();
        }
    }

    /**
     * @param $orderId
     *
     * @return Redirect
     */
    public function redirectToOrderView($orderId)
    {
        $result = $this->redirectFactory->create();

        return $result->setPath(self::ADMIN_ORDER_ORDER_VIEW_URI, ['order_id' => $orderId]);
    }
}
