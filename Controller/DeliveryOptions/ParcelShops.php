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
use Magento\Framework\App\Action\Action;
use TIG\GLS\Model\Config\Provider\Carrier as CarrierConfig;
use TIG\GLS\Service\DeliveryOptions\ParcelShops as ParcelShopsService;

class ParcelShops extends Action
{
    /** @var Session $checkoutSession */
    private $checkoutSession;

    /** @var CarrierConfig $carrierConfig */
    private $carrierConfig;

    /** @var ParcelShopsService $parcelShops*/
    private $parcelShops;

    /**
     * @param Context             $context
     * @param Session             $checkoutSession
     * @param ParcelShopsService $parcelShopsEndpoint
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CarrierConfig $carrierConfig,
        ParcelShopsService $parcelShops
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->carrierConfig = $carrierConfig;
        $this->parcelShops = $parcelShops;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $results = $this->parcelShops->getParcelShops($params['postcode']);

        foreach ($results['parcelShops'] as &$parcelShop) {
            $parcelShop['fee'] = $this->carrierConfig->getShopDeliveryHandlingFee();
        }

        $responseBody = \Zend_Json::encode($results['parcelShops']);
        $response = $this->getResponse();

        return $response->representJson($responseBody);
    }
}
