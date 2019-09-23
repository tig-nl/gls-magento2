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

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as Request;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Model\Shipment\Label;
use TIG\GLS\Model\Shipment\LabelFactory;

abstract class AbstractLabel extends Action
{
    const ADMIN_ORDER_SHIPMENT_VIEW_URI = 'adminhtml/order_shipment/view';

    /** @var Label $label */
    private $label;

    /** @var LabelRepositoryInterface $labelRepository */
    private $labelRepository;

    /** @var $errorMessage */
    private $errorMessage;

    /** @var $successMessage */
    private $successMessage;

    /**
     * AbstractLabel constructor.
     *
     * @param Context                  $context
     * @param LabelFactory             $label
     * @param LabelRepositoryInterface $labelRepository
     */
    public function __construct(
        Context $context,
        LabelFactory $label,
        LabelRepositoryInterface $labelRepository
    ) {
        parent::__construct($context);

        $this->label = $label;
        $this->labelRepository = $labelRepository;
    }

    /**
     * @return Label
     */
    public function createLabelFactory()
    {
        return $this->label->create();
    }

    /**
     * @return \TIG\Gls\Api\Shipment\Data\LabelInterface
     */
    public function getLabelByShipmentId()
    {
        $shipmentId = $this->getShipmentId();

        return $this->labelRepository->getByShipmentId($shipmentId);
    }

    /**
     * @return int
     */
    public function getShipmentId()
    {
        return $this->getRequest()->getParam(Label::GLS_SHIPMENT_LABEL_SHIPMENT_ID);
    }

    /**
     * @param $shipmentId
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function redirectToShipmentView($shipmentId)
    {
        $result = $this->resultRedirectFactory->create();
        return $result->setPath(self::ADMIN_ORDER_SHIPMENT_VIEW_URI, ['shipment_id' => $shipmentId]);
    }

    /**
     * @param $response
     *
     * @return bool
     */
    public function callIsSuccess($response)
    {
        if (isset($response['error']) && $response['error']) {
            $status  = $response['status'];
            $message = $response['message'];
            $this->messageManager->addErrorMessage(
                __($this->errorMessage) . " $message [Status: $status]"
            );

            return false;
        }

        $this->messageManager->addSuccessMessage(
            __($this->successMessage)
        );

        return true;
    }

    /**
     * @param $message
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }

    /**
     * @param $message
     */
    public function setSuccessMessage($message)
    {
        $this->successMessage = $message;
    }

    /**
     * @return array
     */
    public function addShippingInformation()
    {
        /** @var Request $request */
        $request = $this->getRequest();

        return [
            "shippingSystemName"    => $request->getControllerModule(),
            "shippingSystemVersion" => $request->getVersion(),
            "shiptype"              => "p"
        ];
    }
}
