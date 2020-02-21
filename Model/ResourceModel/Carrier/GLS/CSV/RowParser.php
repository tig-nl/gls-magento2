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
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowException;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\LocationDirectory;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV\ColumnResolver;

/**
 * This is a stripped version of \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowParser
 *
 * Class RowParser
 * @package TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV
 * @version Magento 2.3.3
 * @since   1.2.0
 */
// @codingStandardsIgnoreFile
class RowParser
{
    /**
     * @var LocationDirectory
     */
    private $locationDirectory;

    /**
     * RowParser constructor.
     *
     * @param LocationDirectory $locationDirectory
     */
    public function __construct(
        LocationDirectory $locationDirectory
    ) {
        $this->locationDirectory = $locationDirectory;
    }

    /**
     * Retrieve columns.
     *
     * @return array
     */
    public function getColumns()
    {
        return [
            'website_id',
            'dest_country_id',
            'dest_region_id',
            'dest_zip',
            'condition_value',
            'price',
        ];
    }

    /**
     * @param array                                                       $rowData
     * @param                                                             $rowNumber
     * @param                                                             $websiteId
     * @param                                                             $conditionFullName
     * @param \TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV\ColumnResolver $columnResolver
     *
     * @return array
     * @throws ColumnNotFoundException
     * @throws RowException
     */
    public function parse(
        array $rowData,
        $rowNumber,
        $websiteId,
        $conditionFullName,
        ColumnResolver $columnResolver
    ) {
        if (count($rowData) < 4) {
            throw new RowException(
                __(
                    'The GLS Table Rates File Format is incorrect in row number "%1". Verify the format and try again.',
                    $rowNumber
                )
            );
        }

        $countryId      = $this->getCountryId($rowData, $rowNumber, $columnResolver);
        $regionIds      = $this->getRegionIds($rowData, $rowNumber, $columnResolver, $countryId);
        $zipCode        = $this->getZipCode($rowData, $columnResolver);
        $conditionValue = $this->getConditionValue($rowData, $rowNumber, $conditionFullName, $columnResolver);
        $price          = $this->getPrice($rowData, $rowNumber, $columnResolver);

        $rates = [];
        foreach ($regionIds as $regionId) {
            $rates[] = [
                'website_id'      => $websiteId,
                'dest_country_id' => $countryId,
                'dest_region_id'  => $regionId,
                'dest_zip'        => $zipCode,
                'condition_value' => $conditionValue,
                'price'           => $price
            ];
        }

        return $rates;
    }

    /**
     * Get country id from provided row data.
     *
     * @param array          $rowData
     * @param int            $rowNumber
     * @param ColumnResolver $columnResolver
     *
     * @return null|string
     * @throws ColumnNotFoundException
     * @throws RowException
     */
    private function getCountryId(array $rowData, $rowNumber, ColumnResolver $columnResolver)
    {
        $countryCode = $columnResolver->getColumnValue(ColumnResolver::COLUMN_COUNTRY, $rowData);
        // validate country
        if ($this->locationDirectory->hasCountryId($countryCode)) {
            $countryId = $this->locationDirectory->getCountryId($countryCode);
        } elseif ($countryCode === '*' || $countryCode === '') {
            $countryId = '0';
        } else {
            throw new RowException(
                __(
                    'The "%1" country in row number "%2" is incorrect. Verify the country and try again.',
                    $countryCode,
                    $rowNumber
                )
            );
        }

        return $countryId;
    }

    /**
     * Retrieve region id from provided row data.
     *
     * @param array          $rowData
     * @param int            $rowNumber
     * @param ColumnResolver $columnResolver
     * @param int            $countryId
     *
     * @return array
     * @throws ColumnNotFoundException
     * @throws RowException
     */
    private function getRegionIds(array $rowData, $rowNumber, ColumnResolver $columnResolver, $countryId)
    {
        $regionCode = $columnResolver->getColumnValue(ColumnResolver::COLUMN_REGION, $rowData);
        if ($countryId !== '0' && $this->locationDirectory->hasRegionId($countryId, $regionCode)) {
            $regionIds = $this->locationDirectory->getRegionIds($countryId, $regionCode);
        } elseif ($regionCode === '*' || $regionCode === '') {
            $regionIds = [0];
        } else {
            throw new RowException(
                __(
                    'The "%1" region or state in row number "%2" is incorrect. '
                    . 'Verify the region or state and try again.',
                    $regionCode,
                    $rowNumber
                )
            );
        }

        return $regionIds;
    }

    /**
     * Retrieve zip code from provided row data.
     *
     * @param array          $rowData
     * @param ColumnResolver $columnResolver
     *
     * @return float|int|null|string
     * @throws ColumnNotFoundException
     */
    private function getZipCode(array $rowData, ColumnResolver $columnResolver)
    {
        $zipCode = $columnResolver->getColumnValue(ColumnResolver::COLUMN_ZIP, $rowData);
        if ($zipCode === '') {
            $zipCode = '*';
        }

        return $zipCode;
    }

    /**
     * @param array                                                       $rowData
     * @param                                                             $rowNumber
     * @param                                                             $conditionFullName
     * @param \TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV\ColumnResolver $columnResolver
     *
     * @return bool|float
     * @throws ColumnNotFoundException
     * @throws RowException
     */
    private function getConditionValue(array $rowData, $rowNumber, $conditionFullName, ColumnResolver $columnResolver)
    {
        // validate condition value.
        $conditionValue = $columnResolver->getColumnValue($conditionFullName, $rowData);
        $value          = $this->_parseDecimalValue($conditionValue);

        if ($value === false) {
            throw new RowException(
                __(
                    'Please correct %1 "%2" in the Row #%3.',
                    $conditionFullName,
                    $conditionValue,
                    $rowNumber
                )
            );
        }

        return $value;
    }

    /**
     * Retrieve price from provided row data.
     *
     * @param array          $rowData
     * @param int            $rowNumber
     * @param ColumnResolver $columnResolver
     *
     * @return bool|float
     * @throws ColumnNotFoundException
     * @throws RowException
     */
    private function getPrice(array $rowData, $rowNumber, ColumnResolver $columnResolver)
    {
        $priceValue = $columnResolver->getColumnValue(ColumnResolver::COLUMN_PRICE, $rowData);
        $price      = $this->_parseDecimalValue($priceValue);
        if ($price === false) {
            throw new RowException(
                __(
                    'The "%1" shipping price in row number "%2" is incorrect. Verify the shipping price and try again.',
                    $priceValue,
                    $rowNumber
                )
            );
        }

        return $price;
    }

    /**
     * Parse and validate positive decimal value
     *
     * Return false if value is not decimal or is not positive
     *
     * @param string $value
     *
     * @return bool|float
     */
    private function _parseDecimalValue($value)
    {
        $result = false;
        if (is_numeric($value)) {
            $value = (double) sprintf('%.4F', $value);
            if ($value >= 0.0000) {
                $result = $value;
            }
        }

        return $result;
    }
}
