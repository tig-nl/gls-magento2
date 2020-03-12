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

namespace TIG\GLS\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\NonTransactionableInterface;

/**
 * In M2.3.4 it's not possible to remove unique constraints using db_schema.xml. That's why we've added this patch.
 *
 * Class RemoveUniqueConstraint
 * @package TIG\GLS
 * @since v1.3.0
 */
class RemoveUniqueConstraint implements DataPatchInterface, NonTransactionableInterface
{
    const TABLE_GLS_SHIPPING_TABLERATE                = 'gls_shipping_tablerate';
    const TABLE_GLS_SHIPPING_TABLERATE_CONSTRAINT_KEY = 'GLS_SHPP_TABLERATE_WS_ID_DEST_COUNTRY_ID_DEST_REGION_ID_DEST_ZIP';

    /** @var $moduleDataSetup $patchVersion */
    private $moduleDataSetup;

    /**
     * RemoveUniqueConstraint constructor.
     *
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /*
     *
     */
    public function apply()
    {
        $connection = $this->moduleDataSetup->getConnection();
        $connection->startSetup();

        $indexList  = $connection->getIndexList(self::TABLE_GLS_SHIPPING_TABLERATE);

        if (!isset($indexList[self::TABLE_GLS_SHIPPING_TABLERATE_CONSTRAINT_KEY])) {
            return;
        }

        $connection->dropIndex(self::TABLE_GLS_SHIPPING_TABLERATE, self::TABLE_GLS_SHIPPING_TABLERATE_CONSTRAINT_KEY);

        $connection->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
