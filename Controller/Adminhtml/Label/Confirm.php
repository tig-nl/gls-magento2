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
use TIG\GLS\Model\Shipment\Label;
use TIG\GLS\Model\Shipment\LabelFactory;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Webservice\Endpoint\Label\Confirm as ConfirmLabelEndpoint;

class Confirm extends AbstractLabel
{
    /** @var ConfirmLabelEndpoint $confirm */
    private $confirm;

    /**
     * Confirm constructor.
     *
     * @param Context                  $context
     * @param LabelFactory             $label
     * @param ConfirmLabelEndpoint     $confirm
     * @param LabelRepositoryInterface $labelRepository
     */
    public function __construct(
        Context $context,
        LabelFactory $label,
        ConfirmLabelEndpoint $confirm,
        LabelRepositoryInterface $labelRepository
    ) {
        parent::__construct($context, $label, $labelRepository);

        $this->confirm = $confirm;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|Confirm
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $label          = $this->getLabelByShipmentId();
        $data           = $this->addShippingInformation();
        $data['unitNo'] = $label->getUnitNo();

        $this->confirm->setRequestData($data);
        $this->setSuccessMessage('Label confirmed successfully.');
        $this->setErrorMessage('An error occurred while confirming the label.');
        $confirmCall = $this->confirm->call();

        if ($this->callIsSuccess($confirmCall)) {
            $label->isConfirmed(true);
            $label->save();
        }

        return $this->redirectToShipmentView($this->getShipmentId());
    }
}
