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
namespace TIG\GLS\Model\Config\Source\Carrier;

use TIG\GLS\Model\Carrier\GLS;

class ConditionName implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var GLS
     */
    private $carrierTablerate;

    /**
     * @param GLS $carrierTablerate
     */
    public function __construct(GLS $carrierTablerate)
    {
        $this->_arrierTablerate = $carrierTablerate;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $arr = [];
        foreach ($this->carrierTablerate->getCode('condition_name', '') as $key => $value) {
            $arr[] = ['value' => $key, 'label' => $value];
        }
        return $arr;
    }
}
