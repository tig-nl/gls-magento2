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

namespace TIG\GLS\Api\Shipment\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

// @codingStandardsIgnoreFile
interface LabelInterface extends ExtensibleDataInterface
{
    public function getShipmentId();

    public function setShipmentId($shipmentId);

    public function getUnitId();

    public function setUnitId($unitId);

    public function getUnitNo();

    public function setUnitNo($unitNo);

    public function getUniqueNo();

    public function setUniqueNo($uniqueNo);

    public function getIsConfirmed();

    public function isConfirmed($confirmed);

    public function getLabel();

    public function setLabel($label);

    public function getUnitNoShopReturn();

    public function setUnitNoShopReturn($unitNo);

    public function getUnitTrackingLink();

    public function setUnitTrackingLink($url);
}
