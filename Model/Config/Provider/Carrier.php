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

namespace TIG\GLS\Model\Config\Provider;

// @codingStandardsIgnoreFile
class Carrier extends AbstractConfigProvider
{
    const GLS_DELIVERY_OPTION_EXPRESS_LABEL                        = 'ExpressService';
    const GLS_DELIVERY_OPTION_SATURDAY_LABEL                       = 'SaturdayService';
    const XPATH_CARRIER_ACTIVE                                     = 'carriers/tig_gls/active';
    const XPATH_CARRIER_HANDLING_FEE                               = 'carriers/tig_gls/handling_fee';
    const XPATH_CARRIER_BUSINESS_PARCEL_ACTIVE                     = 'carriers/tig_gls/business_parcel_active';
    const XPATH_CARRIER_EXPRESS_PARCEL_ACTIVE                      = 'carriers/tig_gls/express_parcel_active';
    const XPATH_CARRIER_BUSINESS_PARCEL_FLEX_DELIVERY              = 'carriers/tig_gls/business_parcel_services/flex_delivery_active';
    const XPATH_CARRIER_BUSINESS_PARCEL_SATURDAY_SERVICE           = 'carriers/tig_gls/business_parcel_services/saturday_active';
    const XPATH_CARRIER_BUSINESS_PARCEL_SATURDAY_HANDLING_FEE      = 'carriers/tig_gls/business_parcel_services/saturday_handling_fee';
    const XPATH_CARRIER_BUSINESS_PARCEL_SHOP_DELIVERY              = 'carriers/tig_gls/business_parcel_services/shop_delivery_active';
    const XPATH_CARRIER_BUSINESS_PARCEL_SHOP_DELIVERY_HANDLING_FEE = 'carriers/tig_gls/business_parcel_services/shop_delivery_handling_fee';
    const XPATH_CARRIER_BUSINESS_PARCEL_SHOP_AMOUNT                = 'carriers/tig_gls/business_parcel_services/shop_delivery_shop_amount';
    const XPATH_CARRIER_BUSINESS_PARCEL_SHOP_RETURN                = 'carriers/tig_gls/business_parcel_services/shop_return_active';
    const XPATH_CARRIER_EXPRESS_PARCEL_SERVICES                    = 'carriers/tig_gls/express_parcel_services/services_active';
    const XPATH_CARRIER_EXPRESS_PARCEL_HANDLING_FEES               = 'carriers/tig_gls/express_parcel_services/additional_handling_fee';

    /**
     * @return bool
     */
    public function isCarrierActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_ACTIVE);
    }

    /**
     * @return bool
     */
    public function getBaseHandlingFee()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_HANDLING_FEE);
    }

    /**
     * @return bool
     */
    public function isBusinessParcelActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isExpressParcelActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_EXPRESS_PARCEL_ACTIVE);
    }

    /**
     * @return bool
     */
    public function isFlexDeliveryActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_FLEX_DELIVERY);
    }

    /**
     * @return mixed
     */
    public function isSaturdayServiceActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_SATURDAY_SERVICE);
    }

    /**
     * @return float|int
     */
    public function getSaturdayHandlingFee()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_SATURDAY_HANDLING_FEE);
    }

    /**
     * @return bool
     */
    public function isShopDeliveryActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_SHOP_DELIVERY);
    }

    /**
     * @return float|int
     */
    public function getShopDeliveryHandlingFee()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_SHOP_DELIVERY_HANDLING_FEE);
    }

    /**
     * @return bool
     */
    public function isShopReturnActive()
    {
        return $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_SHOP_RETURN);
    }

    /**
     * @param null $store
     *
     * @return int|null
     */
    public function getShopDeliveryShopAmount()
    {
        if (!$this->isShopDeliveryActive()) {
            return null;
        }

        return (int) $this->getConfigValue(self::XPATH_CARRIER_BUSINESS_PARCEL_SHOP_AMOUNT);
    }

    /**
     * @return array|null
     */
    public function getActiveExpressServices()
    {
        if (!$this->isExpressParcelActive()) {
            return null;
        }

        return explode(',', $this->getConfigValue(self::XPATH_CARRIER_EXPRESS_PARCEL_SERVICES));
    }

    /**
     * @return array|null
     */
    public function getExpressHandlingFees()
    {
        if (!$this->isExpressParcelActive()) {
            return null;
        }

        return json_decode($this->getConfigValue(self::XPATH_CARRIER_EXPRESS_PARCEL_HANDLING_FEES));
    }
}
