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

namespace TIG\GLS\Plugin\Quote\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class QuoteManagement
{
    /** @var CartRepositoryInterface $cartRepository */
    private $cartRepository;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /**
     * QuoteManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->cartRepository  = $cartRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param $subject
     * @param $cartId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    // @codingStandardsIgnoreLine
    public function beforePlaceOrder($subject, $cartId)
    {
        $quote          = $this->cartRepository->getActive($cartId);
        $address        = $quote->getShippingAddress();
        $deliveryOption = $address->getGlsDeliveryOption();

        if (!$deliveryOption) {
            return;
        }

        $deliveryOption = json_decode($deliveryOption);
        $type           = $deliveryOption->type;

        if ($type == 'parcel_shop') {
            $deliveryOption->deliveryAddress = $this->mapDeliveryAddress($address);
            $address->setGlsDeliveryOption(json_encode($deliveryOption));
            $this->changeShippingAddress($deliveryOption->details, $address);
        }
    }

    /**
     * @param $address
     *
     * @return object
     */
    private function mapDeliveryAddress($address)
    {
        return (object) [
            'name1'         => $address->getName(),
            'street'        => $address->getStreetLine(1),
            'houseNo'       => $address->getStreetLine(2),
            'countryCode'   => $address->getCountryId(),
            'zipCode'       => $address->getPostcode(),
            'city'          => $address->getCity(),
            'email'         => $address->getEmail(),
            'addresseeType' => $address->getCompany() ? 'b' : 'p'
        ];
    }

    /**
     * @param $subject
     * @param $orderId
     * @param $quoteId
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    // @codingStandardsIgnoreLine
    public function afterPlaceOrder($subject, $orderId, $quoteId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getGlsDeliveryOption()) {
            return $orderId;
        }

        $quote          = $this->cartRepository->get($quoteId);
        $address        = $quote->getShippingAddress();
        $deliveryOption = $address->getGlsDeliveryOption();

        if (!$deliveryOption) {
            return $orderId;
        }

        $order->setGlsDeliveryOption($deliveryOption);
        $order->save();

        return $orderId;
    }

    /**
     * @param $newAddress
     * @param $shippingAddress
     *
     * @return mixed
     */
    private function changeShippingAddress($newAddress, $shippingAddress)
    {
        $shippingAddress->setStreet($newAddress->street . ' ' . $newAddress->houseNo);
        $shippingAddress->setCompany($newAddress->name);
        $shippingAddress->setPostcode($newAddress->zipcode);
        $shippingAddress->setCity($newAddress->city);
        $shippingAddress->setCountryId($newAddress->countryCode);

        return $shippingAddress;
    }
}
