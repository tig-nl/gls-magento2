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
namespace TIG\GLS\Service\Label;

use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Model\Shipment\LabelFactory;

class Save
{
    /**
     * @var LabelFactory
     */
    private $labelFactory;

    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * Save constructor.
     *
     * @param LabelFactory             $labelFactory
     * @param LabelRepositoryInterface $labelRepository
     */
    public function __construct(
        LabelFactory $labelFactory,
        LabelRepositoryInterface $labelRepository
    ) {
        $this->labelFactory = $labelFactory;
        $this->labelRepository = $labelRepository;
    }

    /**
     * @param       $shipmentId
     * @param array $labelData
     */
    public function saveLabel($shipmentId, array $labelData)
    {
        $labelUnits = $labelData['units'];

        foreach ($labelUnits as $label) {
            $createdLabel = $this->labelFactory->create();
            $createdLabel->setShipmentId($shipmentId);
            $createdLabel->setUnitId($label['unitId']);
            $createdLabel->setUnitNo($label['unitNo']);
            $createdLabel->setUniqueNo($label['uniqueNo']);
            !isset($labelData['labels']) ?: $createdLabel->setLabel($labelData['labels']); //pdf2A4, pdf4A4,pdfA6S
            !isset($label['label']) ?: $createdLabel->setLabel($label['label']); //pdfA6U
            !isset($label['unitNoShopReturn']) ?: $createdLabel->setUnitNoShopReturn($label['unitNoShopReturn']);
            $createdLabel->setUnitTrackingLink($label['unitTrackingLink']);
            // @codingStandardsIgnoreFile
            $this->labelRepository->save($createdLabel);
        }
    }
}
