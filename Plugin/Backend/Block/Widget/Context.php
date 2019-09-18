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

use Magento\Backend\Block\Widget\Context as BackendContext;
use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\ButtonListFactory;
use Magento\Framework\App\Request\Http as Request;

class Context
{
    const GLS_ADMINHTML_SHIPMENT_CREATE_BUTTON  = 'gls_create_label';
    const GLS_ADMINHTML_SHIPMENT_CREATE_LABEL   = 'GLS - Create Label';
    const GLS_ADMINHTML_SHIPMENT_CONFIRM_BUTTON = 'gls_confirm_label';
    const GLS_ADMINHTML_SHIPMENT_CONFIRM_LABEL  = 'GLS - Confirm Label';
    const GLS_ADMINHTML_SHIPMENT_DELETE_BUTTON  = 'gls_delete_label';
    const GLS_ADMINHTML_SHIPMENT_DELETE_LABEL   = 'GLS - Delete Label';

    /** @var ButtonListFactory $buttonList */
    private $buttonList;

    /**
     * Context constructor.
     *
     * @param ButtonListFactory $buttonList
     */
    public function __construct(
        ButtonListFactory $buttonList
    ) {
        $this->buttonList = $buttonList;
    }

    /**
     * @param BackendContext $subject
     *
     * @return void|ButtonList
     */
    // @codingStandardsIgnoreLine
    public function afterGetButtonList(BackendContext $subject)
    {
        /** @var Request $request */
        $request = $subject->getRequest();

        if ($request->getFullActionName() !== 'adminhtml_order_shipment_view') {
            return;
        }

        $buttonList = $this->buttonList->create();

        // If no label has been created yet.
        $this->addButton(
            $buttonList,
            self::GLS_ADMINHTML_SHIPMENT_CREATE_BUTTON,
            self::GLS_ADMINHTML_SHIPMENT_CREATE_LABEL,
            '',
            'gls-create save primary',
            -1,
            0
        );
        // If a label is created, show confirm and delete button.

        // If label is confirmed, only show delete button.

        return $buttonList;
    }

    /**
     * Add a button
     *
     * @param ButtonList $list
     * @param            $code
     * @param            $label
     * @param            $controller
     * @param            $class
     * @param            $position
     * @param            $sortOrder
     */
    private function addButton(ButtonList $list, $code, $label, $controller, $class, $position, $sortOrder)
    {
        $list->add(
            $code,
            [
                'label'   => __($label),
                'onclick' => $controller,
                'class'   => $class
            ],
            $position,
            $sortOrder
        );
    }
}
