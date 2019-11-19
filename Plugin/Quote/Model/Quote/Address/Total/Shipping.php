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

namespace TIG\GLS\Plugin\Quote\Model\Quote\Address\Total;

use Magento\Quote\Model\Quote;
use Magento\Quote\Api\Data\ShippingAssignmentInterface as ShippingAssignmentApi;
use Magento\Quote\Model\Quote\Address\Total as QuoteAddressTotal;
use TIG\GLS\Model\Config\Provider\Carrier;

class Shipping
{
    /**
     * @param                       $subject
     * @param                       $result
     * @param Quote                 $quote
     * @param ShippingAssignmentApi $shippingAssignment
     * @param QuoteAddressTotal     $total
     *
     * @return void|mixed
     */
    // @codingStandardsIgnoreLine
    public function afterCollect($subject, $result, Quote $quote, ShippingAssignmentApi $shippingAssignment, QuoteAddressTotal $total)
    {
        $shipping = $shippingAssignment->getShipping();
        $address  = $shipping->getAddress();
        $rates    = $address->getAllShippingRates();

        if (!$rates) {
            return $result;
        }

        $deliveryOption = $this->getDeliveryOption($address);

        if (!$deliveryOption) {
            return $result;
        }

        $rate    = $this->extractRate($shipping->getMethod(), $rates);
        $details = $deliveryOption->details;
        $fee     = $this->calculateFee($rate['price'], $details->fee ?? 0);
        $title   = isset($details->title) ? $details->title : Carrier::GLS_DELIVERY_OPTION_PARCEL_SHOP_LABEL;

        $this->adjustTotals($rate['method_title'], $subject->getCode(), $address, $total, $fee, $title);
    }

    /**
     * @param $address
     *
     * @return mixed|null
     */
    private function getDeliveryOption($address)
    {
        $option = $address->getGlsDeliveryOption();

        if (!$option) {
            return null;
        }

        $option = json_decode($option);

        return $option;
    }

    /**
     * @param $method
     * @param $rates
     *
     * @return array|null
     */
    private function extractRate($method, $rates)
    {
        if ($method != 'tig_gls_tig_gls') {
            return null;
        }

        $rate = array_filter($rates, function (Quote\Address\Rate $rate) use ($method) {
            return $rate->getCode() == $method;
        });

        if (!$rate) {
            return null;
        }

        $rate = reset($rate);

        return $rate->getData();
    }

    /**
     * @param $ratePrice
     * @param $additionalFee
     *
     * @return mixed
     */
    private function calculateFee($ratePrice, $additionalFee)
    {
        if (!$additionalFee) {
            return $ratePrice;
        }

        return $ratePrice + $additionalFee;
    }

    /**
     * @param $name
     * @param $code
     * @param $address
     * @param $total
     * @param $fee
     * @param $description
     */
    private function adjustTotals($name, $code, $address, $total, $fee, $description)
    {
        $total->setTotalAmount($code, $fee);
        $total->setBaseTotalAmount($code, $fee);
        $total->setBaseShippingAmount($fee);
        $total->setShippingAmount($fee);
        $total->setShippingDescription($name . ' - ' . $description);
        $address->setShippingDescription($name . ' - ' . $description);
    }
}
