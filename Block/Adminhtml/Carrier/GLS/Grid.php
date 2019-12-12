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

namespace TIG\GLS\Block\Adminhtml\Carrier\GLS;

/**
 * This is a stripped version of \Magento\OfflineShipping\Block\Adminhtml\Carrier\Tablerate\Grid.
 *
 * Class Grid
 * @package TIG\GLS\Block\Adminhtml\Carrier\GLS
 * @version Magento 2.3.3
 * @since   1.2.0
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /** @var \TIG\GLS\Model\Carrier\GLS $glsCarrier */
    private $glsCarrier;

    /** @var \TIG\GLS\Model\ResourceModel\Carrier\GLS\CollectionFactory */
    private $collectionFactory;

    /**
     * Grid constructor.
     *
     * @param \Magento\Backend\Block\Template\Context                    $context
     * @param \Magento\Backend\Helper\Data                               $backendHelper
     * @param \TIG\GLS\Model\ResourceModel\Carrier\GLS\CollectionFactory $collectionFactory
     * @param \TIG\GLS\Model\Carrier\GLS                                 $glsCarrier
     * @param array                                                      $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \TIG\GLS\Model\ResourceModel\Carrier\GLS\CollectionFactory $collectionFactory,
        \TIG\GLS\Model\Carrier\GLS $glsCarrier,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->glsCarrier        = $glsCarrier;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    // @codingStandardsIgnoreLine
    protected function _construct()
    {
        parent::_construct();
        $this->setId('glsShippingTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    // @codingStandardsIgnoreLine
    protected function _prepareCollection()
    {
        /** @var $collection \TIG\GLS\Model\ResourceModel\Carrier\GLS\Collection */
        $collection = $this->collectionFactory->create();

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     * @throws \Exception
     */
    // @codingStandardsIgnoreLine
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            [
                // @codingStandardsIgnoreLine
                'header'  => 'Country',
                'index'   => 'dest_country',
                'default' => '*'
            ]
        );

        $this->addColumn(
            'dest_region',
            [
                'header'  => 'Region/State',
                'index'   => 'dest_region',
                'default' => '*'
            ]
        );

        $this->addColumn(
            'dest_zip',
            [
                'header'  => 'Zip/Postal Code',
                'index'   => 'dest_zip',
                'default' => '*'
            ]
        );

        $this->addColumn(
            'price',
            [
                'header' => 'Shipping Price',
                'index'  => 'price'
            ]
        );

        return parent::_prepareColumns();
    }
}
