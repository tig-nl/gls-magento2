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
namespace TIG\GLS\Controller\Adminhtml\Shipment;

use Magento\Backend\App\Action;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Controller\Adminhtml\Label\Delete;

class Cancel extends Action
{
    const ADMIN_ORDER_ORDER_VIEW_URI = 'sales/order/view';

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipment;

    /**
     * @var OrderRepositoryInterface
     */
    private $orders;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var Delete
     */
    private $deleteLabel;

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * Cancel constructor.
     *
     * @param Action\Context              $context
     * @param ShipmentRepositoryInterface $shipment
     * @param OrderRepositoryInterface    $orders
     * @param Registry                    $registry
     * @param Delete                      $deleteLabel
     * @param LabelRepositoryInterface    $labelRepository
     */
    public function __construct(
        Action\Context $context,
        ShipmentRepositoryInterface $shipment,
        OrderRepositoryInterface $orders,
        Registry $registry,
        Delete $deleteLabel,
        LabelRepositoryInterface $labelRepository
    ) {
        $this->shipment = $shipment;
        $this->orders = $orders;
        $this->registry = $registry;
        $this->deleteLabel = $deleteLabel;
        $this->labelRepository = $labelRepository;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $shipment = $this->getShipment();
        $order = $this->getOrder($shipment);

        if ($this->registry->registry('isSecureArea')) {
            $this->registry->unregister('isSecureArea');
        }

        $this->registry->register('isSecureArea', true);

        $label = $this->labelRepository->getByShipmentId($shipment->getId());

        if (!$label) {
            $this->cancelShipment($order);
            return $this->cancelShipment($order);
        }

        $this->deleteLabel->deleteLabel($label);

        return $this->cancelShipment($order);
    }

    /**
     * @param $orderId
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function redirectToOrderView($orderId)
    {
        $result = $this->resultRedirectFactory->create();

        return $result->setPath(self::ADMIN_ORDER_ORDER_VIEW_URI, ['order_id' => $orderId]);
    }

    /**
     * @return \Magento\Sales\Api\Data\ShipmentInterface
     */
    public function getShipment()
    {
        $request = $this->getRequest();
        $params = $request->getParams();
        $shipmentId = $params['shipment_id'];

        return $this->shipment->get($shipmentId);
    }

    /**
     * @param $shipment
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function getOrder($shipment)
    {
        $orderId = $shipment->getOrderId();

        return $this->orders->get($orderId);
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
     * @param $order
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function cancelShipment($order)
    {
        $this->deleteShipments($order);
        $this->resetItems($order->getItems());

        $order->setState(Order::STATE_PROCESSING);
        $order->save();

        return $this->redirectToOrderView($order->getId());
    }
}
