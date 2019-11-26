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

namespace TIG\GLS\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use TIG\GLS\Model\Config\Provider\Account;
use TIG\GLS\Model\ResourceModel\Carrier\GLS as GLSCarrier;
use TIG\GLS\Model\ResourceModel\Carrier\GLSFactory;

/**
 * Class GLS
 * @package TIG\GLS\Model\Carrier
 *
 * All properties are needed, and some need to be compatible with its parent.
 */
// @codingStandardsIgnoreFile
class GLS extends AbstractCarrier implements CarrierInterface
{
    const GLS_CARRIER_METHOD = 'tig_gls';

    /** @var string $_code */
    protected $_code = 'tig_gls';

    /** @var string $_defaultConditionName -- We'll only use Price vs. Destination */
    protected $_defaultConditionName = 'package_value_with_discount';

    /** @var Account $accountConfigProvider */
    private $accountConfigProvider;

    /** @var ResultFactory $rateResultFactory */
    private $rateResultFactory;

    /** @var MethodFactory $rateMethodFactory */
    private $rateMethodFactory;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var GLSCarrier $glsFactory */
    private $glsFactory;

    /**
     * GLS constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param ErrorFactory         $rateErrorFactory
     * @param LoggerInterface      $logger
     * @param Account              $accountConfigProvider
     * @param ResultFactory        $rateResultFactory
     * @param MethodFactory        $rateMethodFactory
     * @param array                $data
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ErrorFactory $rateErrorFactory,
        LoggerInterface $logger,
        Account $accountConfigProvider,
        ResultFactory $rateResultFactory,
        MethodFactory $rateMethodFactory,
        GLSFactory $glsFactory,
        array $data = []
    ) {
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $data
        );

        $this->accountConfigProvider = $accountConfigProvider;
        $this->rateResultFactory     = $rateResultFactory;
        $this->rateMethodFactory     = $rateMethodFactory;
        $this->glsFactory            = $glsFactory;
        $this->scopeConfig           = $scopeConfig;
    }

    /**
     * Collect and get rates
     *
     * @param RateRequest $request
     *
     * @return \Magento\Framework\DataObject|bool|null|Result
     * @api
     */
    // @codingStandardsIgnoreLine
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        if (!$this->accountConfigProvider->isValidatedSuccesfully()) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->rateResultFactory->create();
        $rate   = $this->getRate($request);

        $shippingPrice = $this->getFinalPriceWithHandlingFee($rate['price']);
        $method        = $this->createShippingMethod($shippingPrice, $rate['cost']);
        $result->append($method);

        return $result;
    }

    /**
     * Get rate.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return array|bool
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $glsFactory = $this->glsFactory->create();

        return $glsFactory->getRate($request);
    }

    /**
     * Get the method object based on the shipping price and cost
     *
     * @param float $shippingPrice
     * @param float $cost
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createShippingMethod($shippingPrice, $cost)
    {
        /** @var  \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->rateMethodFactory->create();

        $method->setCarrier(self::GLS_CARRIER_METHOD);
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($cost);

        return $method;
    }

    /**
     * Get allowed shipping methods
     *
     * @return array
     * @api
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }
}
