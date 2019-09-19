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
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

// @codingStandardsIgnoreFile
class InstallSchema implements InstallSchemaInterface
{
    const GLS_DELIVERY_OPTION                = 'gls_delivery_option';
    const GLS_DELIVERY_OPTION_LABEL          = 'GLS Delivery Option';
    const GLS_DELIVERY_OPTION_COLUMN         = [
        // @codingStandardsIgnoreLine
        'type'     => Table::TYPE_TEXT,
        'nullable' => true,
        'default'  => null,
        'comment'  => self::GLS_DELIVERY_OPTION_LABEL,
        'after'    => 'shipping_method'
    ];
    const GLS_DELIVERY_OPTION_INSTALL_TABLES = [
        'quote_address',
        'sales_order'
    ];
    const GLS_TABLE_SHIPMENT_LABEL           = 'gls_shipment_label';

    /**
     * @param SchemaSetupInterface   $setup
     * @param ModuleContextInterface $context
     *
     * @throws \Zend_Db_Exception
     */
    // @codingStandardsIgnoreLine
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $connection = $installer->getConnection();

        foreach (self::GLS_DELIVERY_OPTION_INSTALL_TABLES as $table) {
            $this->addColumn($connection, $installer, $table);
        }

        $labelTable = $setup->getTable(self::GLS_TABLE_SHIPMENT_LABEL);
        if ($connection->isTableExists($labelTable) != true) {
            $this->createTable($connection, $installer);
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

    /**
     * @param AdapterInterface     $connection
     * @param SchemaSetupInterface $installer
     *
     * @throws \Zend_Db_Exception
     */
    private function createTable(AdapterInterface $connection, SchemaSetupInterface $installer)
    {
        $table = $connection->newTable(self::GLS_TABLE_SHIPMENT_LABEL);

        $this->addInteger($table, 'entity_id', 10, true, true);
        $this->addInteger($table, 'shipment_id', 10);

        $foreignKey = $installer->getFkName(
            self::GLS_TABLE_SHIPMENT_LABEL,
            'shipment_id',
            'sales_shipment',
            'entity_id'
        );

        $table->addForeignKey(
            $foreignKey,
            'shipment_id',
            self::GLS_TABLE_SHIPMENT_LABEL,
            'entity_id',
            Table::ACTION_CASCADE
        );

        $this->addText($table, 'unit_id', 50, 'Unit ID');
        $this->addText($table, 'unit_no', 50, 'Unit Number');
        $this->addText($table, 'unique_no', 50, 'Unique Number');

        $table->addColumn(
            'label',
            Table::TYPE_BLOB,
            null,
            [
                'identity' => false,
                'unsigned' => false,
                'nullable' => true,
                'primary'  => false,
                'default'  => null,
            ],
            'GLS Label (Base64)'
        );

        $this->addText($table, 'unit_tracking_link', 256, 'GLS Tracking Link');

        $connection->createTable($table);
    }

    /**
     * @param Table $table
     * @param       $name
     * @param       $size
     * @param bool  $primary
     * @param bool  $autoIncrement
     * @param bool  $comment
     *
     * @throws \Zend_Db_Exception
     */
    private function addInteger(Table $table, $name, $size, $primary = false, $autoIncrement = false, $comment = false)
    {
        $table->addColumn(
            $name,
            Table::TYPE_INTEGER,
            $size,
            [
                'primary' => $primary,
                'auto_increment' => $autoIncrement,
                'nullable' => false,
                'unsigned' => true
            ]
        );
    }

    /**
     * @param Table $table
     * @param       $name
     * @param       $size
     * @param bool  $comment
     *
     * @throws \Zend_Db_Exception
     */
    private function addText(Table $table, $name, $size, $comment = false)
    {
        $table->addColumn(
            $name,
            Table::TYPE_TEXT,
            $size,
            [
                'nullable' => true,
                'default'  => null
            ],
            $comment
        );
    }
}
