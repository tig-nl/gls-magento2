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

class AccountConfigProvider extends AbstractConfigProvider
{
    const XPATH_GENERAL_STATUS_MODE = 'tig_gls/general/mode';

    /**
     * Checks if the extension is on status off.
     * @param null|int $store
     * @return bool
     */
    public function isModeOff($store = null)
    {
        if ($this->getMode($store) == '0' || false == $this->getMode()) {
            return true;
        }

        return false;
    }

    /**
     * @param null|int $store
     * Should return on of these values
     *  '1' => live ||
     *  '2' => test ||
     *  '0' => off
     *
     * @return mixed
     */
    public function getMode($store = null)
    {
        if (!$this->isModuleOutputEnabled()) {
            return '0';
        }

        return $this->getConfigValue(self::XPATH_GENERAL_STATUS_MODE, $store);
    }

    /**
     * @param null $store
     *
     * @return bool
     */
    public function isValidatedSuccesfully($store = null)
    {
        return true;
    }
}