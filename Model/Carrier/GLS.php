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
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Shipping\Model\Rate\ResultFactory;
use Psr\Log\LoggerInterface;
use TIG\GLS\Model\Config\Provider\Account;
use TIG\GLS\Model\Config\Source\Carrier\CalculateHandlingFee;
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
        $this->accountConfigProvider = $accountConfigProvider;
        $this->rateResultFactory     = $rateResultFactory;
        $this->rateMethodFactory     = $rateMethodFactory;
        $this->glsFactory            = $glsFactory;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $data
        );
    }

    /**
     * Collect and get rates.
     *
     * @param RateRequest $request
     *
     * @return bool|\Magento\Framework\DataObject|Result|null
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

        if ($request->getFreeShipping() === true) {
            $result = $this->rateResultFactory->create();
            $method = $this->createShippingMethod(0, 0);
            $result->append($method);

            return $result;
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
     * @param RateRequest $request
     *
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRate(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        $calculateHandlingFee = $this->getConfigData('calculate_handling_fee');

        if ($calculateHandlingFee !== CalculateHandlingFee::CARRIER_CALCULATE_PRICE_DESTINATION) {
            return [
                'price' => '0.0000',
                'cost' => '0.0000'
            ];
        }

        $glsFactory = $this->glsFactory->create();

        return $glsFactory->getRate($request);
    }

    /**
     * @param        $type
     * @param string $code
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getCode($type, $code = 'package_value_with_discount')
    {
        $codes = [
            'condition_name' => [
                'package_value_with_discount' => __('Prices vs. Destination')
            ],
            'condition_name_short' => [
                'package_value_with_discount' => __('Order Subtotal (and above)')
            ]
        ];

        if (!isset($codes[$type])) {
            throw new LocalizedException(
                __('The "%1" code for GLS is incorrect. Verify the type and try again.', $type)
            );
        }

        if ($code === '') {
            return $codes[$type];
        }

        if (!isset($codes[$type][$code])) {
            throw new LocalizedException(
                __('The "%1: %2" code type for GLS is incorrect. Verify the type and try again.', $type, $code)
            );
        }

        return $codes[$type][$code];
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
