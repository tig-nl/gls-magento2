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

namespace TIG\GLS\Model\Shipment;

use Magento\Framework\Model\AbstractModel;
use TIG\GLS\Api\Shipment\Data\LabelInterface;

// @codingStandardsIgnoreFile
class Label extends AbstractModel implements LabelInterface
{
    /** @var string */
    protected $_idFieldName = 'entity_id';

    const GLS_SHIPMENT_LABEL_SHIPMENT_ID         = 'shipment_id';
    const GLS_SHIPMENT_LABEL_UNIT_ID             = 'unit_id';
    const GLS_SHIPMENT_LABEL_UNIT_NO             = 'unit_no';
    const GLS_SHIPMENT_LABEL_UNIQUE_NO           = 'unique_no';
    const GLS_SHIPMENT_LABEL_CONFIRMED           = 'confirmed';
    const GLS_SHIPMENT_LABEL_LABEL               = 'label';
    const GLS_SHIPMENT_LABEL_UNIT_NO_SHOP_RETURN = 'unit_no_shop_return';
    const GLS_SHIPMENT_LABEL_LABEL_SHOP_RETURN   = 'label_shop_return';
    const GLS_SHIPMENT_LABEL_UNIT_TRACKING_LINK  = 'unit_tracking_link';

    public function _construct()
    {
        $this->_init("\TIG\GLS\Model\ResourceModel\Shipment\Label");
    }

    public function getShipmentId()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_SHIPMENT_ID);
    }

    public function setShipmentId($shipmentId)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_SHIPMENT_ID, $shipmentId);
    }

    public function getUnitId()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_UNIT_ID);
    }

    public function setUnitId($unitId)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_UNIT_ID, $unitId);
    }

    public function getUnitNo()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_UNIT_NO);
    }

    public function setUnitNo($unitNo)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_UNIT_NO, $unitNo);
    }

    public function getUniqueNo()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_UNIQUE_NO);
    }

    public function setUniqueNo($uniqueNo)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_UNIQUE_NO, $uniqueNo);
    }

    /**
     * Is not used in this extension, but we'll keep it available for 3rd Parties
     * who might need it through an API connection.
     *
     * @return bool
     */
    public function getIsConfirmed()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_CONFIRMED);
    }

    /**
     * Is not used in this extension, but we'll keep it available for 3rd Parties
     * who might need it through an API connection.
     *
     * @return mixed
     */
    public function isConfirmed($confirmed)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_CONFIRMED, $confirmed);
    }

    public function getLabel()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_LABEL);
    }

    public function setLabel($label)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_LABEL, $label);
    }

    public function getUnitNoShopReturn()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_UNIT_NO_SHOP_RETURN);
    }

    public function setUnitNoShopReturn($unitNo)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_UNIT_NO_SHOP_RETURN, $unitNo);
    }

    public function getLabelShopReturn()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_LABEL_SHOP_RETURN);
    }

    public function setLabelShopReturn($label)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_LABEL_SHOP_RETURN, $label);
    }

    public function getUnitTrackingLink()
    {
        return $this->_getData(self::GLS_SHIPMENT_LABEL_UNIT_TRACKING_LINK);
    }

    public function setUnitTrackingLink($url)
    {
        return $this->setData(self::GLS_SHIPMENT_LABEL_UNIT_TRACKING_LINK, $url);
    }
}
