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

namespace TIG\GLS\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

class InstallSchema implements InstallSchemaInterface
{
    const GLS_DELIVERY_OPTION                = 'gls_delivery_option';
    const GLS_DELIVERY_OPTION_LABEL          = 'GLS Delivery Option';
    const GLS_DELIVERY_OPTION_COLUMN         = [
        'type'     => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        'nullable' => true,
        'default'  => null,
        'comment'  => self::GLS_DELIVERY_OPTION_LABEL,
        'after'    => 'shipping_method'
    ];
    const GLS_DELIVERY_OPTION_INSTALL_TABLES = [
        'quote_address',
        'sales_order'
    ];

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     */
    public function install(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $installer = $setup;
        $installer->startSetup();

        $connection = $installer->getConnection();

        foreach (self::GLS_DELIVERY_OPTION_INSTALL_TABLES as $table) {
            $this->addColumn($connection, $installer, $table);
        }

        $installer->endSetup();
    }

    /**
     * @param AdapterInterface     $connection
     * @param SchemaSetupInterface $installer
     * @param                      $table
     */
    private function addColumn(AdapterInterface $connection, SchemaSetupInterface $installer, $table)
    {
        $connection->addColumn(
            $installer->getTable($table),
            self::GLS_DELIVERY_OPTION,
            self::GLS_DELIVERY_OPTION_COLUMN
        );
    }
}
