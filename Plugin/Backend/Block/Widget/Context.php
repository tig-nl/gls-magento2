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

namespace TIG\GLS\Plugin\Backend\Block\Widget;

use Magento\Backend\Block\Template as BackendTemplate;
use Magento\Backend\Block\Widget\Context as BackendContext;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ButtonListFactory;
use Magento\Framework\App\Request\Http as Request;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;

class Context
{
    const GLS_ADMIN_LABEL_CREATE_BUTTON  = 'gls_label_create';
    const GLS_ADMIN_LABEL_CREATE_LABEL   = 'GLS - Create Label';
    const GLS_ADMIN_LABEL_CREATE_URI     = 'gls/label/create';
    const GLS_ADMIN_LABEL_PRINT_BUTTON   = 'gls_label_print';
    const GLS_ADMIN_LABEL_PRINT_LABEL    = 'GLS - Print Label';
    const GLS_ADMIN_LABEL_PRINT_URI      = 'gls/label/printPdf';
    const GLS_ADMIN_LABEL_DELETE_BUTTON  = 'gls_label_delete';
    const GLS_ADMIN_LABEL_DELETE_LABEL   = 'GLS - Delete Label';
    const GLS_ADMIN_LABEL_DELETE_URI     = 'gls/label/delete';

    /** @var BackendTemplate $template */
    private $template;

    /** @var ButtonListFactory $buttonList */
    private $buttonList;

    /** @var ShipmentRepositoryInterface $shipments */
    private $shipments;

    /** @var LabelRepositoryInterface $labelRepository */
    private $labelRepository;

    /**
     * Context constructor.
     *
     * @param ButtonListFactory $buttonList
     */
    public function __construct(
        BackendTemplate $template,
        ButtonListFactory $buttonList,
        ShipmentRepositoryInterface $shipments,
        LabelRepositoryInterface $labelRepository
    ) {
        $this->template        = $template;
        $this->buttonList      = $buttonList;
        $this->shipments       = $shipments;
        $this->labelRepository = $labelRepository;
    }

    /**
     * @param BackendContext $subject
     * @param                $buttonList
     *
     * @return ButtonList
     */
    public function afterGetButtonList(BackendContext $subject, $buttonList)
    {
        /** @var Request $request */
        $request = $subject->getRequest();

        if ($request->getFullActionName() !== 'adminhtml_order_shipment_view') {
            return $buttonList;
        }

        $shipmentId = $request->getParam('shipment_id');
        $shipment   = $this->shipments->get($shipmentId);
        $order      = $shipment->getOrder();

        if ($order->getShippingMethod() !== 'tig_gls_tig_gls') {
            return $buttonList;
        }

        return $this->getButtonList($shipmentId);
    }

    /**
     * @param $shipmentId
     *
     * @return mixed
     */
    private function getButtonList($shipmentId)
    {
        $buttonList = $this->buttonList->create();
        $label      = $this->labelRepository->getByShipmentId($shipmentId);

        if (!$label) {
            $this->addCreateButton($buttonList, $shipmentId);

            return $buttonList;
        }

        $this->addDeleteButton($buttonList, $shipmentId);
        $this->addPrintButton($buttonList, $shipmentId);

        return $buttonList;
    }

    /**
     * @param $buttonList
     * @param $shipmentId
     */
    private function addCreateButton($buttonList, $shipmentId)
    {
        $this->addButton(
            $buttonList,
            self::GLS_ADMIN_LABEL_CREATE_BUTTON,
            self::GLS_ADMIN_LABEL_CREATE_LABEL,
            self::GLS_ADMIN_LABEL_CREATE_URI,
            'gls-create save primary',
            0,
            0,
            [
                'shipment_id' => $shipmentId
            ]
        );
    }

    /**
     * @param $buttonList
     * @param $shipmentId
     */
    private function addPrintButton($buttonList, $shipmentId)
    {
        $this->addButton(
            $buttonList,
            self::GLS_ADMIN_LABEL_PRINT_BUTTON,
            self::GLS_ADMIN_LABEL_PRINT_LABEL,
            self::GLS_ADMIN_LABEL_PRINT_URI,
            'gls-print save primary',
            0,
            0,
            [
                'shipment_id' => $shipmentId
            ],
            '_blank'
        );
    }

    /**
     * @param $buttonList
     * @param $shipmentId
     */
    private function addDeleteButton($buttonList, $shipmentId)
    {
        $this->addButton(
            $buttonList,
            self::GLS_ADMIN_LABEL_DELETE_BUTTON,
            self::GLS_ADMIN_LABEL_DELETE_LABEL,
            self::GLS_ADMIN_LABEL_DELETE_URI,
            'gls-delete save primary',
            1,
            0,
            [
                'shipment_id' => $shipmentId
            ]
        );
    }

    /**
     * @param ButtonList $list
     * @param            $code
     * @param            $label
     * @param            $controller
     * @param            $class
     * @param            $position
     * @param            $sortOrder
     * @param array      $params
     */
    // @codingStandardsIgnoreLine
    private function addButton(ButtonList $list, $code, $label, $controller, $class, $position, $sortOrder, $params = [], $target = '_self')
    {
        $url     = $this->template->getUrl($controller, $params);
        $onClick = "window.open('$url', '$target')";

        $list->add(
            $code,
            [
                'label'   => __($label),
                'onclick' => $onClick,
                'class'   => $class
            ],
            $position,
            $sortOrder
        );
    }
}
