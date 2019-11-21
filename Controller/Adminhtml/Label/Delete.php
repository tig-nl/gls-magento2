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
use TIG\GLS\Api\Shipment\Data\LabelInterfaceFactory;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Controller\Adminhtml\AbstractLabel;
use TIG\GLS\Model\Shipment\Label as ShipmentLabel;
use TIG\GLS\Service\Label;

class Delete extends AbstractLabel
{
    /**
     * @var Label\Delete
     */
    private $deleteLabel;

    public function __construct(
        Context $context,
        LabelRepositoryInterface $labelRepository,
        LabelInterfaceFactory $labelInterface,
        Label\Delete $deleteLabel
    ) {
        parent::__construct($context, $labelRepository, $labelInterface);

        $this->deleteLabel = $deleteLabel;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $request = $this->getRequest();
        $controllerModule = $request->getControllerModule();
        $version = $request->getVersion();
        $shipmentId = $request->getParam(ShipmentLabel::GLS_SHIPMENT_LABEL_SHIPMENT_ID);

        $this->setErrorMessage('Label could not be deleted.');
        $this->setSuccessMessage('Label succesfully deleted.');

        $deleteCall = $this->deleteLabel->deleteLabel($shipmentId, $controllerModule, $version);

        if ($this->callIsSuccess($deleteCall)) {
            $this->deleteLabel->deleteLabelByShipmentId($shipmentId);
        } elseif (strpos($deleteCall['message'], 'V032') !== false) {
            // V032 equals to 'Unit has already been deleted', implying the error

            $this->messageManager->addNoticeMessage(
                __('This label was already deleted at GLS therefore the Label has been deleted in Magento')
            );
            $this->deleteLabel->deleteLabelByShipmentId($shipmentId);
        }

        return $this->redirectToShipmentView($this->getShipmentId());
    }
}
