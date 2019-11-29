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

use Magento\Shipping\Model\Shipping\LabelGenerator;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;

class GetPDF
{
    /**
     * @var LabelRepositoryInterface
     */
    private $labelRepository;

    /**
     * @var LabelGenerator
     */
    private $labelGenerator;

    /**
     * PrintPDF constructor.
     *
     * @param LabelRepositoryInterface $labelRepository
     * @param LabelGenerator           $labelGenerator
     */
    public function __construct(
        LabelRepositoryInterface $labelRepository,
        LabelGenerator $labelGenerator
    ) {
        $this->labelRepository = $labelRepository;
        $this->labelGenerator = $labelGenerator;
    }

    /**
     * @param $shipmentId
     *
     * @return false|string
     */
    public function getPdf($shipmentId)
    {
        $label = $this->labelRepository->getByShipmentId($shipmentId);

        if (!$label) {
            return null;
        }

        // @codingStandardsIgnoreLine
        return base64_decode($label->getLabel());
    }

    /**
     * @param $shipmentIds
     *
     * @return \Zend_Pdf
     */
    public function createMassLabel($shipmentIds)
    {
        $labels = [];
        foreach ($shipmentIds as $shipmentId) {
            $label = $this->getPdf($shipmentId);

            if (!$label) {
                continue;
            }
            // @codingStandardsIgnoreLine
            // TODO - Remove this line when GLS removes whitespaces from PDF output
            $content = substr($label, 0, strpos($label, 'EOF')) . 'EOF';
            $labels[] = $content;
        }

        return $this->labelGenerator->combineLabelsPdf($labels);
    }
}
