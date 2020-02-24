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

namespace TIG\GLS\Model\ResourceModel\Carrier\GLS;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\File\ReadInterface;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowException;
use Magento\Store\Model\StoreManagerInterface;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV\ColumnResolver;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV\ColumnResolverFactory;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\CSV\RowParser;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\DataHashGenerator;

/**
 * This is a stripped version of \Magento\OfflineShipping\Model\ResourceModel\Carrier\TableRate\Import
 *
 * Class Import
 * @package TIG\GLS\Model\ResourceModel\Carrier\GLS
 * @version Magento OS 2.3.3
 * @since   1.2.0
 */
// @codingStandardsIgnoreFile
class Import extends \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import
{
    /** @var array $errors */
    private $errors = [];

    /** @var RowParser $rowParser */
    private $rowParser;

    /** @var DataHashGenerator $dataHashGenerator */
    private $dataHashGenerator;

    /** @var array $uniqueHash */
    private $uniqueHash = [];

    /** @var ColumnResolverFactory $columnResolverFactory */
    private $columnResolverFactory;

    /**
     * Import constructor.
     *
     * @param StoreManagerInterface                                                                    $storeManager
     * @param Filesystem                                                                               $filesystem
     * @param ScopeConfigInterface                                                                     $coreConfig
     * @param \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowParser             $magentoRowParser
     * @param \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolverFactory $magentoColumnResolverFactory
     * @param \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\DataHashGenerator         $magentoDataHashGenerator
     * @param RowParser                                                                                $rowParser
     * @param ColumnResolverFactory                                                                    $columnResolverFactory
     * @param \TIG\GLS\Model\ResourceModel\Carrier\GLS\DataHashGenerator                               $dataHashGenerator
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Filesystem $filesystem,
        ScopeConfigInterface $coreConfig,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\RowParser $magentoRowParser,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnResolverFactory $magentoColumnResolverFactory,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\DataHashGenerator $magentoDataHashGenerator,
        RowParser $rowParser,
        ColumnResolverFactory $columnResolverFactory,
        DataHashGenerator $dataHashGenerator
    ) {
        $this->rowParser             = $rowParser;
        $this->columnResolverFactory = $columnResolverFactory;
        $this->dataHashGenerator     = $dataHashGenerator;

        parent::__construct(
            $storeManager,
            $filesystem,
            $coreConfig,
            $magentoRowParser,
            $magentoColumnResolverFactory,
            $magentoDataHashGenerator
        );
    }

    /**
     * Retrieve columns.
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->rowParser->getColumns();
    }

    /**
     * Get data from file
     *
     * @param ReadInterface $file
     * @param               $websiteId
     * @param int           $bunchSize
     *
     * @return \Generator
     * @throws LocalizedException
     * @throws \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\CSV\ColumnNotFoundException
     */
    public function _getData(ReadInterface $file, $websiteId, $conditionShortName, $conditionFullName, $bunchSize = 5000)
    {
        $this->errors = [];

        $headers = $this->getHeaders($file);
        /** @var ColumnResolver $columnResolver */
        $columnResolver = $this->columnResolverFactory->create(['headers' => $headers]);

        $rowNumber = 1;
        $items     = [];

        while (false !== ($csvLine = $file->readCsv())) {
            try {
                $rowNumber++;
                if (empty($csvLine)) {
                    continue;
                }
                $rowsData = $this->rowParser->parse(
                    $csvLine,
                    $rowNumber,
                    $websiteId,
                    $conditionFullName,
                    $columnResolver
                );

                foreach ($rowsData as $rowData) {
                    // protect from duplicate
                    $hash = $this->dataHashGenerator->getHash($rowData);
                    if (array_key_exists($hash, $this->uniqueHash)) {
                        throw new RowException(
                            __(
                                'Duplicate Row #%1 (duplicates row #%2)',
                                $rowNumber,
                                $this->uniqueHash[$hash]
                            )
                        );
                    }
                    $this->uniqueHash[$hash] = $rowNumber;

                    $items[] = $rowData;
                }
                if (count($rowsData) > 1) {
                    $bunchSize += count($rowsData) - 1;
                }
                if (count($items) === $bunchSize) {
                    yield $items;
                    $items = [];
                }
            } catch (RowException $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        if (count($items)) {
            yield $items;
        }
    }

    /**
     * Retrieve column headers.
     *
     * @param ReadInterface $file
     *
     * @return array|bool
     * @throws LocalizedException
     */
    private function getHeaders(ReadInterface $file)
    {
        $headers = $file->readCsv();
        if ($headers === false || count($headers) < 4) {
            throw new LocalizedException(
                __('The GLS Rates File Format is incorrect. Verify the format and try again.')
            );
        }

        return $headers;
    }
}
