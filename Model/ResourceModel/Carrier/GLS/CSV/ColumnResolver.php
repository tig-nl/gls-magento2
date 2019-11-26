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

namespace TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV;

use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnNotFoundException;

/**
 * This is a stripped version of Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolver
 *
 * Class ColumnResolver
 * @package TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV
 * @version Magento 2.3.3
 * @since   1.2.0
 */
// @codingStandardsIgnoreFile
class ColumnResolver
{
    const COLUMN_COUNTRY = 'Country';
    const COLUMN_REGION  = 'Region/State';
    const COLUMN_ZIP     = 'Zip/Postal Code';
    const COLUMN_PRICE   = 'Shipping Price';

    /** @var array */
    private $nameToPositionIdMap = [
        self::COLUMN_COUNTRY => 0,
        self::COLUMN_REGION  => 1,
        self::COLUMN_ZIP     => 2,
        self::COLUMN_PRICE   => 4,
    ];

    /** @var array */
    private $headers;

    /**
     * ColumnResolver constructor.
     *
     * @param array $headers
     * @param array $columns
     */
    public function __construct(array $headers, array $columns = [])
    {
        $this->nameToPositionIdMap = array_merge($this->nameToPositionIdMap, $columns);
        $this->headers             = array_map('trim', $headers);
    }

    /**
     * @param       $column
     * @param array $values
     *
     * @return string
     * @throws ColumnNotFoundException
     */
    public function getColumnValue($column, array $values)
    {
        $column      = (string) $column;
        $columnIndex = array_search($column, $this->headers, true);

        if (false === $columnIndex) {
            if (array_key_exists($column, $this->nameToPositionIdMap)) {
                $columnIndex = $this->nameToPositionIdMap[$column];
            } else {
                throw new ColumnNotFoundException(__('Requested column "%1" cannot be resolved', $column));
            }
        }

        if (!array_key_exists($columnIndex, $values)) {
            throw new ColumnNotFoundException(__('Column "%1" not found', $column));
        }

        return trim($values[$columnIndex]);
    }
}
