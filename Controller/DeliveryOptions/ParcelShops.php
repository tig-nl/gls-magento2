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
use TIG\GLS\Webservice\Endpoint\DeliveryOptions\ParcelShops as ParcelShopsEndpoint;

class ParcelShops extends Action
{
    /** @var Session $checkoutSession */
    private $checkoutSession;

    /** @var ParcelShops $parcelShopsEndpoint */
    private $parcelShopsEndpoint;

    /**
     * @param Context             $context
     * @param Session             $checkoutSession
     * @param ParcelShopsEndpoint $parcelShopsEndpoint
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        ParcelShopsEndpoint $parcelShopsEndpoint
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->parcelShopsEndpoint = $parcelShopsEndpoint;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        $this->parcelShopsEndpoint->setRequestData(['zipcode' => $params['postcode'], 'amountOfShops' => 5]);
        $results = $this->parcelShopsEndpoint->call();

        $responseBody = \Zend_Json::encode($results['parcelShops']);
        $response = $this->getResponse();

        return $response->representJson($responseBody);
    }
}
