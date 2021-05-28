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

use Magento\Framework\DB\Select;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * This is a stripped version of \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery
 *
 * Class RateQuery
 * @package TIG\GLS\Model\ResourceModel\Carrier\GLS
 * @version Magento 2.3.4
 * @since   1.3.0
 */
class RateQuery extends \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery
{
    /** @var $request */
    private $request;

    /**
     * RateQuery constructor.
     *
     * @param RateRequest $request
     */
    public function __construct(
        RateRequest $request
    ) {
        $this->request = $request;

        parent::__construct(
            $request
        );
    }

    /**
     * @param Select $select
     *
     * @return Select
     */
    // @codingStandardsIgnoreLine
    public function prepareSelect(Select $select)
    {
        $select->where(
            'website_id = :website_id'
        );
        $select->order(
            [
                'dest_country_id DESC',
                'dest_region_id DESC',
                'dest_zip DESC',
                'condition_value DESC'
            ]
        );
        $select->limit(
            1
        );

        $wheres = [
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode",
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = :postcode_prefix",
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = ''",

            // Handle asterisk in dest_zip field
            "dest_country_id = :country_id AND dest_region_id = :region_id AND dest_zip = '*'",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = '*'",
            "dest_country_id = '0' AND dest_region_id = :region_id AND dest_zip = '*'",
            "dest_country_id = '0' AND dest_region_id = 0 AND dest_zip = '*'",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = ''",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode",
            "dest_country_id = :country_id AND dest_region_id = 0 AND dest_zip = :postcode_prefix"
        ];

        // Render destination condition
        $orWhere = '(' . implode(') OR (', $wheres) . ')';
        $select->where($orWhere);

        // condition_name is retrieved from the core_config_data, that's why we only need to filter by condition_value.
        $select->where('condition_value <= :condition_value');

        return $select;
    }
}
