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

class Context
{
    const GLS_ADMIN_LABEL_CREATE_BUTTON  = 'gls_label_create';
    const GLS_ADMIN_LABEL_CREATE_LABEL   = 'GLS - Create Label';
    const GLS_ADMIN_LABEL_CREATE_URI     = 'gls/label/create';
    const GLS_ADMIN_LABEL_PRINT_BUTTON   = 'gls_label_print';
    const GLS_ADMIN_LABEL_PRINT_LABEL    = 'GLS - Print Label';
    const GLS_ADMIN_LABEL_PRINT_URI      = 'gls/label/print';
    const GLS_ADMIN_LABEL_CONFIRM_BUTTON = 'gls_label_confirm';
    const GLS_ADMIN_LABEL_CONFIRM_LABEL  = 'GLS - Confirm Label';
    const GLS_ADMIN_LABEL_CONFIRM_URI    = 'gls/label/confirm';
    const GLS_ADMIN_LABEL_DELETE_BUTTON  = 'gls_label_delete';
    const GLS_ADMIN_LABEL_DELETE_LABEL   = 'GLS - Delete Label';
    const GLS_ADMIN_LABEL_DELETE_URI     = 'gls/label/delete';

    /** @var BackendTemplate $template */
    private $template;

    /** @var ButtonListFactory $buttonList */
    private $buttonList;

    /** @var ShipmentRepositoryInterface $shipments */
    private $shipments;

    /**
     * Context constructor.
     *
     * @param ButtonListFactory $buttonList
     */
    public function __construct(
        BackendTemplate $template,
        ButtonListFactory $buttonList,
        ShipmentRepositoryInterface $shipments
    ) {
        $this->template   = $template;
        $this->buttonList = $buttonList;
        $this->shipments  = $shipments;
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

        $buttonList = $this->buttonList->create();

        // If no label has been created yet, only show create label.
        $this->addCreateButton($buttonList, $shipmentId);

        // If a label is created, show print, confirm and delete button.
        $this->addPrintButton($buttonList, $shipmentId);
        $this->addConfirmButton($buttonList, $shipmentId);
        $this->addDeleteButton($buttonList, $shipmentId);

        // If label is confirmed, only show print and delete button.

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
            -1,
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
            -1,
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
    private function addConfirmButton($buttonList, $shipmentId)
    {
        $this->addButton(
            $buttonList,
            self::GLS_ADMIN_LABEL_CONFIRM_BUTTON,
            self::GLS_ADMIN_LABEL_CONFIRM_LABEL,
            self::GLS_ADMIN_LABEL_CONFIRM_URI,
            'gls-confirm save primary',
            -2,
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
    private function addDeleteButton($buttonList, $shipmentId)
    {
        $this->addButton(
            $buttonList,
            self::GLS_ADMIN_LABEL_DELETE_BUTTON,
            self::GLS_ADMIN_LABEL_DELETE_LABEL,
            self::GLS_ADMIN_LABEL_DELETE_URI,
            'gls-delete save primary',
            -3,
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
    private function addButton(ButtonList $list, $code, $label, $controller, $class, $position, $sortOrder, $params = [])
    {
        $url = $this->template->getUrl($controller, $params);

        $list->add(
            $code,
            [
                'label'   => __($label),
                'onclick' => 'setLocation(\'' . $url . '\')',
                'class'   => $class
            ],
            $position,
            $sortOrder
        );
    }
}
