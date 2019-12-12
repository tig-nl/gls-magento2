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
use TIG\GLS\Controller\Adminhtml\AbstractLabel;
use TIG\GLS\Service\Label;

class Delete extends AbstractLabel
{
    /**
     * @var Label\Delete
     */
    private $deleteLabel;

    /**
     * Delete constructor.
     *
     * @param Context      $context
     * @param Label\Delete $deleteLabel
     */
    public function __construct(
        Context $context,
        Label\Delete $deleteLabel
    ) {
        parent::__construct($context);

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
        $shipmentId = $this->getShipmentId();

        $this->setErrorMessage('Label could not be deleted.');
        $this->setSuccessMessage('Label succesfully deleted.');

        $deleteCall = $this->deleteLabel->deleteLabel($shipmentId, $controllerModule, $version);

        $this->deleteLabel($deleteCall, $shipmentId);

        return $this->redirectToShipmentView($shipmentId);
    }

    /**
     * @param array $deleteCall
     * @param int $shipmentId
     */
    private function deleteLabel($deleteCall, $shipmentId)
    {
        if ($this->callIsSuccess($deleteCall)) {
            $this->deleteLabel->deleteLabelByShipmentId($shipmentId);
        }

        if (!$this->callIsSuccess($deleteCall) && strpos($deleteCall['message'], 'V032') !== false) {
            // V032 equals to 'Unit has already been deleted', implying the error

            $this->messageManager->addNoticeMessage(
                // @codingStandardsIgnoreFile
                __('This label was already deleted at GLS therefore the Label has been deleted in Magento')
            );
            $this->deleteLabel->deleteLabelByShipmentId($shipmentId);
        }
    }
}
