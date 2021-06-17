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

namespace TIG\GLS\Ui\Component\Listing\Column;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Escaper;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

/**
 * Class Address
 */
class GlsShippingInformation extends Column
{
    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ContextInterface       $context
     * @param UiComponentFactory     $uiComponentFactory
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param ScopeConfigInterface   $scopeConfig
     * @param array                  $components
     * @param array                  $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderCollectionFactory $orderCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);

        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->scopeConfig            = $scopeConfig;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $orderIds        = array_column($dataSource['data']['items'], 'entity_id');
        $orderCollection = $this->orderCollectionFactory->create();
        $orderCollection->addAttributeToFilter('entity_id', ['in' => $orderIds]);

        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as & $item) {
            // if shipping information matches method name, add parcel quantity.
            $strPos = strpos($item['shipping_information'], $this->scopeConfig->getValue('carriers/tig_gls/name'));

            if ($strPos !== false) {
                $order                        = $orderCollection->getItemById($item['entity_id']);
                $quantity = $order->getGlsParcelQuantity() ?: 1;
                $item['shipping_information'] .= sprintf(" | %s: %d", __("Parcel quantity"), $quantity);
            }
        }

        return $dataSource;
    }
}
