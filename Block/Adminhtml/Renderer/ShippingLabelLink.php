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
namespace TIG\GLS\Block\Adminhtml\Renderer;

use Magento\Backend\Model\UrlInterface;
use TIG\GLS\Model\Shipment\LabelRepository;

class ShippingLabelLink
{
    const GLS_PRINT_PDF_URL = 'gls/label/printPdf';

    /** @var LabelRepository */
    private $labelRepositorty;

    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * DeepLink constructor.
     *
     * @param LabelRepository $labelRepository
     * @param UrlInterface    $urlInterface
     */
    public function __construct(
        LabelRepository $labelRepository,
        UrlInterface $urlInterface
    ) {
        $this->labelRepositorty = $labelRepository;
        $this->urlInterface = $urlInterface;
    }

    /**
     * @param $shipmentId
     *
     * @return string
     */
    public function render($shipmentId)
    {
        $label = $this->labelRepositorty->getByShipmentId($shipmentId);

        if (!isset($label)) {
            return '';
        }

        $printPdfUrl = $this->urlInterface->getUrl(static::GLS_PRINT_PDF_URL, ['shipment_id' => $shipmentId]);
        $output = "<a href='$printPdfUrl' target='_blank'>Label</a>";
        $output .= $this->addJavascript();

        return $output;
    }

    /**
     * @return string
     */
    private function addJavascript()
    {
        //@codingStandardsIgnoreLine
        return "<script type='text/javascript'>jQuery('.gls_shipping_label').unbind('click');</script>";
    }
}
