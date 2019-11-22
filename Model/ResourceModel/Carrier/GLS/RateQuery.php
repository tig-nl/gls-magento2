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

use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * This is a stripped version of \Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate\RateQuery
 *
 * Class RateQuery
 * @package TIG\GLS\Model\ResourceModel\Carrier\GLS
 * @version Magento 2.3.3
 * @since   1.2.0
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
     * Prepare select
     *
     * @param \Magento\Framework\DB\Select $select
     *
     * @return \Magento\Framework\DB\Select
     */
    public function prepareSelect(\Magento\Framework\DB\Select $select)
    {
        $select->where(
            'website_id = :website_id'
        )->order(
            [
                'dest_country_id DESC',
                'dest_region_id DESC',
                'dest_zip DESC'
            ]
        )->limit(
            1
        );

        // Render destination condition
        $orWhere = '(' . implode(
                ') OR (',
                [
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
                ]
            ) . ')';
        $select->where($orWhere);

        return $select;
    }

    /**
     * Returns query bindings
     *
     * @return array
     */
    public function getBindings()
    {
        $bind = [
            ':website_id'      => (int) $this->request->getWebsiteId(),
            ':country_id'      => $this->request->getDestCountryId(),
            ':region_id'       => (int) $this->request->getDestRegionId(),
            ':postcode'        => $this->request->getDestPostcode(),
            ':postcode_prefix' => $this->getDestPostcodePrefix()
        ];

        return $bind;
    }

    /**
     * Returns the entire postcode if it contains no dash or the part of it prior to the dash in the other case
     *
     * @return string
     */
    private function getDestPostcodePrefix()
    {
        if (!preg_match("/^(.+)-(.+)$/", $this->request->getDestPostcode(), $zipParts)) {
            return $this->request->getDestPostcode();
        }

        return $zipParts[1];
    }
}
