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
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Api\Shipment\Data\LabelInterface;
use TIG\GLS\Api\Shipment\Data\LabelInterfaceFactory;
use TIG\GLS\Webservice\Endpoint\Label\Delete as DeleteLabelEndpoint;

class Delete extends AbstractLabel
{
    /** @var DeleteLabelEndpoint $delete */
    private $delete;

    /**
     * Delete constructor.
     *
     * @param Context                  $context
     * @param LabelRepositoryInterface $labelRepository
     * @param LabelInterfaceFactory    $labelInterfaceFactory
     * @param DeleteLabelEndpoint      $delete
     */
    public function __construct(
        Context $context,
        LabelRepositoryInterface $labelRepository,
        LabelInterfaceFactory $labelInterfaceFactory,
        DeleteLabelEndpoint $delete
    ) {
        parent::__construct(
            $context,
            $labelRepository,
            $labelInterfaceFactory
        );

        $this->delete = $delete;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $label          = $this->getLabelByShipmentId();
        $data           = $this->addShippingInformation();
        $data['unitNo'] = $label->getUnitNo();

        $this->delete->setRequestData($data);
        $this->setErrorMessage('Label could not be deleted.');
        $this->setSuccessMessage('Label successfully deleted.');
        $deleteCall = $this->delete->call();

        if ($this->callIsSuccess($deleteCall)) {
            $this->deleteLabel($label);
        }

        return $this->redirectToShipmentView($this->getShipmentId());
    }
}
