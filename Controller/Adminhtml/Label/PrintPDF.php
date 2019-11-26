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

use Magento\Backend\App\Action;
use TIG\GLS\Api\Shipment\Data\LabelInterfaceFactory;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Controller\Adminhtml\AbstractLabel;
use TIG\GLS\Service\Label\GetPDF;

class PrintPDF extends AbstractLabel
{
    /**
     * @var GetPDF
     */
    private $getPDF;

    /**
     * PrintPDF constructor.
     *
     * @param Action\Context           $context
     * @param LabelRepositoryInterface $labelRepository
     * @param LabelInterfaceFactory    $labelInterface
     * @param GetPDF                   $getPDF
     */
    public function __construct(
        Action\Context $context,
        LabelRepositoryInterface $labelRepository,
        LabelInterfaceFactory $labelInterface,
        GetPDF $getPDF
    ) {
        parent::__construct($context, $labelRepository, $labelInterface);
        $this->getPDF = $getPDF;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $pdf = $this->getPDF->getPdf($this->getShipmentId());

        $resultPage = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        $resultPage->setHeader('Content-Type', 'application/pdf');
        $resultPage->setContents($pdf);

        return $resultPage;
    }
}
