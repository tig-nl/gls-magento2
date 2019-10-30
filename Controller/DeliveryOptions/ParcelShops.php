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
namespace TIG\GLS\Controller\DeliveryOptions;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use TIG\GLS\Controller\AbstractDeliveryOptions;
use TIG\GLS\Model\Config\Provider\Carrier as CarrierConfig;
use TIG\GLS\Service\DeliveryOptions\ParcelShops as ParcelShopsService;

class ParcelShops extends AbstractDeliveryOptions
{
    /** @var Session $checkoutSession */
    private $checkoutSession;

    /** @var ParcelShopsService $parcelShops*/
    private $parcelShops;

    /**
     * ParcelShops constructor.
     *
     * @param Context            $context
     * @param Session            $checkoutSession
     * @param CarrierConfig      $carrierConfig
     * @param ParcelShopsService $parcelShops
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CarrierConfig $carrierConfig,
        ParcelShopsService $parcelShops
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->parcelShops = $parcelShops;

        parent::__construct(
            $context,
            $carrierConfig
        );
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $results = $this->parcelShops->getParcelShops($params['postcode']);

        if (!isset($results['parcelShops'])) {
            return $this->jsonResponse([]);
        }

        foreach ($results['parcelShops'] as &$parcelShop) {
            $parcelShop['fee'] = $this->getCarrierConfig()->getShopDeliveryHandlingFee();
        }

        return $this->jsonResponse($results['parcelShops']);
    }
}
