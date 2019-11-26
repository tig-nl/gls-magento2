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

namespace TIG\GLS\Controller\Adminhtml\Massaction;

use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use TIG\GLS\Controller\Adminhtml\AbstractLabel;
use TIG\GLS\Service\Label\Create as LabelCreate;
use TIG\GLS\Service\Label\GetPDF as LabelPrint;
use TIG\GLS\Service\Label\Save as LabelSave;
use TIG\GLS\Service\Shipment\Create as ShipmentCreate;

class CreateAndPrint extends AbstractLabel
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var LabelCreate
     */
    private $labelGenerator;

    /**
     * @var array
     */
    private $shipmentIds = [];

    /**
     * @var ShipmentCreate
     */
    private $shipmentCreate;

    /**
     * @var LabelPrint
     */
    private $labelPrinter;

    /**
     * @var LabelSave
     */
    private $labelSaver;

    /**
     * CreateAndPrint constructor.
     *
     * @param Context           $context
     * @param Filter            $filter
     * @param CollectionFactory $collectionFactory
     * @param ShipmentCreate    $shipmentCreate
     * @param LabelCreate       $labelGenerator
     * @param LabelPrint        $labelPrinter
     * @param LabelSave         $labelSaver
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ShipmentCreate $shipmentCreate,
        LabelCreate $labelGenerator,
        LabelPrint $labelPrinter,
        LabelSave $labelSaver
    ) {
        parent::__construct($context);

        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->shipmentCreate = $shipmentCreate;
        $this->labelGenerator = $labelGenerator;
        $this->labelPrinter = $labelPrinter;
        $this->labelSaver = $labelSaver;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Http_Client_Exception
     * @throws \Zend_Pdf_Exception
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collection = $this->filter->getCollection($collection);

        $this->massCreateShipment($collection->getItems());

        foreach ($collection->getItems() as $order) {
            $this->getShipmentIds($order);
        }

        $this->massCreateLabels();
        $massLabel = $this->massPrintLabel();

        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultPage->setHeader('Content-Type', 'application/pdf');
        $resultPage->setContents($massLabel);

        return $resultPage;
    }

    /**
     * @param $order
     */
    private function getShipmentIds($order)
    {
        foreach ($order->getShipmentsCollection() as $shipment) {
            $this->shipmentIds[] = $shipment->getId();
        }
    }

    /**
     * @param $orders
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function massCreateShipment($orders)
    {
        foreach ($orders as $order) {
            $this->shipmentCreate->createShipment($order);
        }
    }

    /**
     * @throws \Zend_Http_Client_Exception
     */
    private function massCreateLabels()
    {
        $request = $this->getRequest();
        $controllerModule = $request->getControllerModule();
        $version = $request->getVersion();

        foreach ($this->shipmentIds as $shipmentId) {
            $requestData = $this->labelGenerator->getRequestData($shipmentId, $controllerModule, $version);
            $this->createLabel($shipmentId, $requestData);
        }

        $this->errorsOccured($this->labelGenerator->getErrors());
    }

    /**
     * @param $shipmentId
     * @param $requestData
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Zend_Http_Client_Exception
     */
    private function createLabel($shipmentId, $requestData)
    {
        $label = $this->labelGenerator->createLabel($requestData);
        if ($this->callIsSuccess($label)) {
            $this->labelSaver->saveLabel($shipmentId, $label['units']);
        }
    }

    /**
     * @return string
     * @throws \Zend_Pdf_Exception
     */
    private function massPrintLabel()
    {
        $massLabel = $this->labelPrinter->createMassLabel($this->shipmentIds);

        return $massLabel->render();
    }
}
