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

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Encryption\Encryptor;
use Magento\Framework\Module\Manager;

class AccountConfigProvider extends AbstractConfigProvider
{
    const XPATH_GENERAL_STATUS_MODE      = 'tig_gls/general/mode';
    const XPATH_GENERAL_USERNAME         = 'tig_gls/general/username';
    const XPATH_GENERAL_PASSWORD         = 'tig_gls/general/password';
    const XPATH_GENERAL_SUBSCRIPTION_KEY = 'tig_gls/general/subscription_key';
    const XPATH_API_LIVE_BASE_URL        = 'tig_gls/api/live_base_url';
    const XPATH_API_TEST_BASE_URL        = 'tig_gls/api/test_base_url';

    /** @var Encryptor $encryptor */
    private $encryptor;

    /**
     * AccountConfigProvider constructor.
     *
     * @param ScopeConfig $scopeConfig
     * @param Manager     $moduleManager
     * @param Encryptor   $encryptor
     */
    public function __construct(
        ScopeConfig $scopeConfig,
        Manager $moduleManager,
        Encryptor $encryptor
    ) {
        parent::__construct($scopeConfig, $moduleManager);

        $this->encryptor = $encryptor;
    }

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

    public function isValidatedSuccesfully($store = null)
    {
        return true;
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
     * @param int|null $store
     *
     * @return string
     */
    public function getBaseUrl($store = null)
    {
        if ($this->getMode($store) == 1) {
            return $this->getConfigValue(self::XPATH_API_LIVE_BASE_URL);
        }

        return $this->getConfigValue(self::XPATH_API_TEST_BASE_URL);
    }

    /**
     * @param int|null $store
     */
    public function getUsername($store = null)
    {
        return $this->getConfigValue(self::XPATH_GENERAL_USERNAME, $store);
    }

    /**
     * @param int|null $store
     *
     * @return bool|string
     */
    public function getPassword($store = null)
    {
        $encryptedPassword = $this->getConfigValue(self::XPATH_GENERAL_PASSWORD, $store);

        try {
            return $this->encryptor->decrypt($encryptedPassword);
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * @param int|null $store
     *
     * @return string
     */
    public function getSubscriptionKey($store = null)
    {
        $encryptedSubscriptionKey = $this->getConfigValue(self::XPATH_GENERAL_SUBSCRIPTION_KEY, $store);

        try {
            return $this->encryptor->decrypt($encryptedSubscriptionKey);
        } catch (\Exception $exception) {
            return false;
        }
    }
}
