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
use TIG\GLS\Service\Label\Create as LabelCreator;
use TIG\GLS\Service\Label\Save;

class Create extends AbstractLabel
{
    /** @var Create $createLabel */
    private $createLabel;

    /**
     * @var Save $saveLabel
     */
    private $saveLabel;

    /**
     * Create constructor.
     *
     * @param Context                  $context
     * @param LabelRepositoryInterface $labelRepository
     * @param LabelInterfaceFactory    $labelInterface
     * @param LabelCreator             $createLabel
     * @param Save                     $saveLabel
     */
    public function __construct(
        Context $context,
        LabelRepositoryInterface $labelRepository,
        LabelInterfaceFactory $labelInterface,
        LabelCreator $createLabel,
        Save $saveLabel
    ) {
        parent::__construct($context, $labelRepository, $labelInterface);
        $this->createLabel = $createLabel;
        $this->saveLabel = $saveLabel;
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

        $this->setErrorMessage('An error occurred while creating the label.');
        $this->setSuccessMessage('Label created successfully.');

        $shipmentId = $this->getShipmentId();
        $requestData = $this->createLabel->getRequestData($shipmentId, $controllerModule, $version);
        if ($this->errorsOccured()) {
            return $this->redirectToShipmentView($shipmentId);
        }

        $label = $this->createLabel->createLabel($requestData);
        if ($this->callIsSuccess($label)) {
            $this->saveLabel->saveLabel($shipmentId, $label['units']);
        }

        return $this->redirectToShipmentView($shipmentId);
    }

    /**
     * @return bool
     */
    private function errorsOccured()
    {
        $errors = $this->createLabel->getErrors();
        if (!empty($errors)) {
            $this->handleMissingOptions($errors);
            $this->handleErrors($errors);

            return true;
        }

        return false;
    }

    /**
     * @param array $errors
     */
    private function handleMissingOptions($errors)
    {
        if (!isset($errors['missing'])) {
            return;
        }

        foreach ($errors['missing'] as $error) {
            $this->messageManager->addErrorMessage(
                 // @codingStandardsIgnoreLine
                __(
                    "Label could not be created, because %1 is not configured. " .
                    "Please make sure you've configured a %2 in %3.",
                    array_values($error)
                )
            );
        }
    }

    /**
     * @param array $errors
     */
    private function handleErrors($errors)
    {
        if (!isset($errors['errors'])) {
            return;
        }

        foreach ($errors['errors'] as $error) {
            $this->messageManager->addErrorMessage(
                // @codingStandardsIgnoreLine
                __($error)
            );
        }
    }
}
