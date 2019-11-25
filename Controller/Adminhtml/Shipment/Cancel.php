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
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Service\Label\Delete;
use TIG\GLS\Service\Shipment\DeleteShipment;

class Cancel extends Action
{
    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipment;

    /**
     * @var OrderRepositoryInterface
     */
    private $orders;

    /**
     * @var Delete
     */
    private $deleteLabel;

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * @var DeleteShipment
     */
    private $shipmentService;

    /**
     * Cancel constructor.
     *
     * @param Action\Context              $context
     * @param ShipmentRepositoryInterface $shipment
     * @param OrderRepositoryInterface    $orders
     * @param Delete                      $deleteLabel
     * @param LabelRepositoryInterface    $labelRepository
     * @param DeleteShipment              $shipmentService
     */
    public function __construct(
        Action\Context $context,
        ShipmentRepositoryInterface $shipment,
        OrderRepositoryInterface $orders,
        Delete $deleteLabel,
        LabelRepositoryInterface $labelRepository,
        DeleteShipment $shipmentService
    ) {
        $this->shipment = $shipment;
        $this->orders = $orders;
        $this->deleteLabel = $deleteLabel;
        $this->labelRepository = $labelRepository;
        $this->shipmentService = $shipmentService;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $shipment = $this->getShipment();
        $order = $this->getOrder($shipment);
        $request = $this->getRequest();
        $controllerModule = $request->getControllerModule();
        $version = $request->getVersion();

        $label = $this->labelRepository->getByShipmentId($shipment->getId());

        if ($label) {
            $this->deleteLabel->deleteLabel($shipment->getId(), $controllerModule, $version);
        }

        return $this->shipmentService->cancelShipment($order, $shipment);
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
}
