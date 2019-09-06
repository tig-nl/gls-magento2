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

namespace TIG\GLS\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class FieldArray extends AbstractFieldArray
{
    const GLS_HANDLING_FEE_COLUMN_METHOD = 'shipping_method';
    const GLS_HANDLING_FEE_COLUMN_FEE    = 'additional_handling_fee';

    /** @var array $_columns */
    protected $_columns = [];

    /** @var  $methodRenderer */
    protected $methodRenderer;

    /** @var bool $_addAfter */
    protected $_addAfter = true;

    /** @var $_addButtonLabel */
    protected $_addButtonLabel;

    /**
     * FieldArray Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_addButtonLabel = __('Add Additional Handling Fee');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function listMethods()
    {
        if (!$this->methodRenderer) {
            $this->methodRenderer = $this->getLayout()->createBlock(
                '\TIG\GLS\Block\Adminhtml\Form\Field\Services',
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->methodRenderer;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            self::GLS_HANDLING_FEE_COLUMN_METHOD,
            [
                'label'    => __('Shipping Method'),
                'renderer' => $this->listMethods(),
            ]
        );
        $this->addColumn(
            self::GLS_HANDLING_FEE_COLUMN_FEE,
            [
                'label' => __('Additional Fee')
            ]
        );
        $this->_addAfter       = false;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $type    = $row->getShippingMethod();
        $options = [];

        if ($type) {
            $options['option_' . $this->listMethods()->calcOptionHash($type)] = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
    }

    /**
     * @param string $columnName
     *
     * @return string
     * @throws \Exception
     */
    public function renderCellTemplate($columnName)
    {
        if ($columnName == self::GLS_HANDLING_FEE_COLUMN_METHOD) {
            $this->_columns[$columnName]['class'] = 'input-select required-entry';
        }

        if ($columnName == self::GLS_HANDLING_FEE_COLUMN_FEE) {
            $this->_columns[$columnName]['class'] = 'input-text required-entry';
            $this->_columns[$columnName]['style'] = 'width: 50px';
        }

        return parent::renderCellTemplate($columnName);
    }
}
