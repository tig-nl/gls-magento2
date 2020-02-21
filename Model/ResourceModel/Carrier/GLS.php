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

namespace TIG\GLS\Model\ResourceModel\Carrier;

use Magento\Framework\App\Config\Value;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\Import;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\RateQuery;
use TIG\GLS\Model\ResourceModel\Carrier\GLS\RateQueryFactory;

/**
 * This is a stripped version of \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate
 *
 * Class GLS
 * @package TIG\GLS\Model\ResourceModel\Carrier
 * @version Magento 2.3.3
 * @since   1.2.0
 */
// @codingStandardsIgnoreFile
class GLS extends Tablerate
{
    /** @var Import $import */
    private $import;

    /** @var RateQueryFactory $rateQueryFactory */
    private $rateQueryFactory;

    /**
     * GLS constructor.
     *
     * @param \Magento\Framework\Model\ResourceModel\Db\Context  $context
     * @param \Psr\Log\LoggerInterface                           $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\OfflineShipping\Model\Carrier\Tablerate   $carrierTablerate
     * @param \Magento\Framework\Filesystem                      $filesystem
     * @param Tablerate\Import                                   $magentoImport
     * @param Tablerate\RateQueryFactory                         $magentoRateQueryFactory
     * @param RateQueryFactory                                   $rateQueryFactory
     * @param Import                                             $import
     * @param null                                               $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $coreConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\OfflineShipping\Model\Carrier\Tablerate $carrierTablerate,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\Import $magentoImport,
        \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQueryFactory $magentoRateQueryFactory,
        RateQueryFactory $rateQueryFactory,
        Import $import,

        $connectionName = null
    ) {
        $this->import           = $import;
        $this->rateQueryFactory = $rateQueryFactory;

        parent::__construct(
            $context,
            $logger,
            $coreConfig,
            $storeManager,
            $carrierTablerate,
            $filesystem,
            $magentoImport,
            $magentoRateQueryFactory,
            $connectionName
        );
    }

    /**
     * Define main table and id field name
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('gls_shipping_tablerate', 'pk');
    }

    /**
     * Return table rate array or false by rate request
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable());
        /** @var RateQuery $rateQuery */
        $rateQuery = $this->rateQueryFactory->create(['request' => $request]);

        $rateQuery->prepareSelect($select);
        $bindings = $rateQuery->getBindings();

        $result = $connection->fetchRow($select, $bindings);

        // Normalize destination zip code
        if ($result && $result['dest_zip'] == '*') {
            $result['dest_zip'] = '';
        }

        return $result;
    }

    /**
     * @param array $fields
     * @param array $values
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function importData(array $fields, array $values)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();

        if (!count($fields) && !count($values)) {
            $connection->commit();
        }

        try {
            $this->getConnection()->insertArray($this->getMainTable(), $fields, $values);
            $this->_importedRows += count($values);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $connection->rollBack();
            throw new \Magento\Framework\Exception\LocalizedException(__('Unable to import data'), $e);
        } catch (\Exception $e) {
            $connection->rollBack();
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing GLS rates.')
            );
        }
        $connection->commit();
    }

    /**
     * @param array $condition
     *
     * @return $this|Tablerate
     * @throws LocalizedException
     */
    private function deleteByCondition($condition)
    {
        $connection = $this->getConnection();
        $connection->beginTransaction();
        $connection->delete($this->getMainTable(), $condition);
        $connection->commit();
        return $this;
    }

    /**
     * Upload table rate file and import data from it
     *
     * @param \Magento\Framework\DataObject $object
     *
     * @return \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate|void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function uploadAndImport(\Magento\Framework\DataObject $object)
    {
        /**
         * @var \Magento\Framework\App\Config\Value $object
         */
        if (empty($_FILES['groups']['tmp_name']['tig_gls']['fields']['import']['value'])) {
            return $this;
        }
        $filePath  = $_FILES['groups']['tmp_name']['tig_gls']['fields']['import']['value'];

        $websiteId     = $this->storeManager->getWebsite($object->getScopeId())->getId();
        $conditionName = $this->getConditionName($object);

        $file          = $this->getCsvFile($filePath);
        try {
            $condition = [
                'website_id = ?' => $websiteId
            ];
            $this->deleteByCondition($condition);

            $columns = $this->import->getColumns();
            $conditionFullName = $this->_getConditionFullName($conditionName);
            foreach ($this->import->_getData($file, $websiteId, $conditionName, $conditionFullName) as $bunch) {
                $this->importData($columns, $bunch);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Something went wrong while importing GLS rates.')
            );
        } finally {
            $file->close();
        }

        if ($this->import->hasErrors()) {
            $error = __(
                'We couldn\'t import this file because of these errors: %1',
                implode(" \n", $this->import->getErrors())
            );
            throw new \Magento\Framework\Exception\LocalizedException($error);
        }
    }

    /**
     * @param string $filePath
     *
     * @return \Magento\Framework\Filesystem\File\ReadInterface
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    private function getCsvFile($filePath)
    {
        $pathInfo = pathinfo($filePath);
        $dirName  = isset($pathInfo['dirname']) ? $pathInfo['dirname'] : '';
        $fileName = isset($pathInfo['basename']) ? $pathInfo['basename'] : '';

        $directoryRead = $this->filesystem->getDirectoryReadByPath($dirName);

        return $directoryRead->openFile($fileName);
    }
}
