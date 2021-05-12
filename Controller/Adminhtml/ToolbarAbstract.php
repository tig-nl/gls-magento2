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
namespace TIG\GLS\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use TIG\GLS\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

abstract class ToolbarAbstract extends Action
{
    const PARCELCOUNT_PARAM_KEY = 'change_parcel';

    /**
     * @var Filter
     */
    //@codingStandardsIgnoreLine
    protected $uiFilter;

    /**
     * @var OrderRepositoryInterface
     */
    //@codingStandardsIgnoreLine
    protected $orderRepository;

    /**
     * @var array
     */
    //@codingStandardsIgnoreLine
    protected $errors = [];

    /**
     * ToolbarAbstract constructor.
     *
     * @param Context                     $context
     * @param Filter                      $filter
     * @param OrderRepositoryInterface    $orderRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        OrderRepositoryInterface $orderRepository
    ) {
        parent::__construct($context);

        $this->uiFilter = $filter;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Order $order
     * @param       $parcelCount
     */
    //@codingStandardsIgnoreLine
    protected function orderChangeParcelCount(Order $order, $parcelCount)
    {
        $glsOrder = $this->getGlsOrder($order->getId());
        if (!$glsOrder) {
            $this->errors[] = __('Can not change parcel count for non GLS order %1', $order->getIncrementId());
            return;
        }

        $shipments = $order->getShipmentsCollection();
        $noError     = true;

        if ($shipments->getSize() > 0) {
            $noError = $this->shipmentsChangeParcelCount($shipments, $parcelCount);
        }

        if ($noError) {
            $glsOrder->setParcelCount($parcelCount);
            $this->orderRepository->save($glsOrder);
        }
    }

    /**
     * @param $shipments
     * @param $parcelCount
     *
     * @return bool
     */
    private function shipmentsChangeParcelCount($shipments, $parcelCount)
    {
        $error = false;
        foreach ($shipments as $shipment) {
            $error = $this->shipmentChangeParcelCount($shipment->getId(), $parcelCount);
        }

        return $error;
    }

    /**
     * @param $shipmentId
     * @param $parcelCount
     *
     * @return bool
     */
    private function shipmentChangeParcelCount($shipmentId, $parcelCount)
    {
        $shipment = $this->shipmentRepository->getByShipmentId($shipmentId);
        if (!$shipment->getId()) {
            return false;
        }

        if ($shipment->getMainBarcode()) {
            $this->resetService->resetShipment($shipmentId);
        }

        $shipment->setParcelCount($parcelCount);
        $this->shipmentRepository->save($shipment);
        return true;
    }

    /**
     * @return $this
     */
    //@codingStandardsIgnoreLine
    protected function handelErrors()
    {
        foreach ($this->errors as $error) {
            $this->messageManager->addWarningMessage($error);
        }

        return $this;
    }

    /**
     * @param $orderId
     *
     * @return mixed
     */
    private function getGlsOrder($orderId)
    {
        $glsOrder = $this->orderRepository->getByOrderId($orderId);
        if (!$glsOrder) {
            $this->errors[] = __('Could not find a GLS order for %1', $orderId);
        }

        return $glsOrder;
    }
}
