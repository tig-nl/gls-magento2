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
use TIG\GLS\Model\Shipment\Label;

abstract class AbstractLabel extends Action
{
    const ADMIN_ORDER_SHIPMENT_VIEW_URI = 'adminhtml/order_shipment/view';
    const ADMIN_ORDER_GRID_VIEW_URI = 'sales/order/index';

    /** @var $errorMessage */
    private $errorMessage;

    /** @var $successMessage */
    private $successMessage;

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
     * @param $shipmentId
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function redirectToOrderGrid()
    {
        $result = $this->resultRedirectFactory->create();

        return $result->setPath(self::ADMIN_ORDER_GRID_VIEW_URI);
    }

    /**
     * @param $response
     *
     * @return bool
     */
    public function callIsSuccess($response)
    {
        if (isset($response['status']) && $response['status'] !== '200') {
            $status  = $response['status'];
            $message = $response['message'];
            $this->messageManager->addErrorMessage(
                __($this->errorMessage) . " $message [Status: $status]"
            );

            return false;
        }

        if (!isset($response['units'])) {
            $this->messageManager->addErrorMessage(
                __($this->errorMessage)
            );

            return false;
        }

        $this->messageManager->addSuccessMessage(
            // @codingStandardsIgnoreLine
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
     * @param $notice
     */
    public function handleNotice($notice)
    {
        $this->messageManager->addNoticeMessage(
        // @codingStandardsIgnoreLine
            __($notice)
        );
    }

    /**
     * @return bool
     */
    public function errorsOccured($errors)
    {
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
    public function handleMissingOptions($errors)
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
    public function handleErrors($errors)
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
