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

use Magento\Quote\Model\ShippingAddressManagement as QuoteShippingAddressManagement;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use TIG\GLS\Model\Config\Provider\Carrier;

class ShippingAddressManagement
{
    /** @var CartRepositoryInterface $quoteRepository */
    private $quoteRepository;

    /** @var Carrier $carrierConfig */
    private $carrierConfig;

    /**
     * ShippingAddressManagement constructor.
     *
     * @param CartRepositoryInterface $quoteRepository
     * @param Carrier                 $carrierConfig
     */
    public function __construct(
        CartRepositoryInterface $quoteRepository,
        Carrier $carrierConfig
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->carrierConfig   = $carrierConfig;
    }

    /**
     * @param QuoteShippingAddressManagement $subject
     * @param                                $cartId
     * @param AddressInterface               $address
     *
     * @return \Exception|QuoteShippingAddressManagement|void
     */
    // @codingStandardsIgnoreLine
    public function beforeAssign(QuoteShippingAddressManagement $subject, $cartId, AddressInterface $address = null)
    {
        $extensionAttributes = null;
        if ($address) {
            $extensionAttributes = $address->getExtensionAttributes();
        }

        $deliveryOption = null;
        if (!empty($extensionAttributes)) {
            $deliveryOption = $extensionAttributes->getGlsDeliveryOption();
        }

        try {
            $address->setGlsDeliveryOption($deliveryOption);
        } catch (\Exception $error) {
            return $error;
        }
    }
}
