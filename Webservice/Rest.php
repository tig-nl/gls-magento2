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

namespace TIG\GLS\Webservice;

use Magento\Framework\HTTP\ZendClient;
use TIG\GLS\Model\Config\Provider\Account;
use TIG\GLS\Service\Software\Data as SoftwareData;
use TIG\GLS\Webservice\Endpoint\EndpointInterface;

class Rest
{
    /** @var ZendClient $zendClient */
    private $zendClient;
    /** @var SoftwareData $softwareData */
    private $softwareData;
    /** @var Account $accountConfigProvider */
    private $accountConfigProvider;

    /**
     * @param ZendClient   $zendClient
     * @param SoftwareData $softwareData
     * @param Account      $accountConfigProvider
     */
    public function __construct(
        ZendClient $zendClient,
        SoftwareData $softwareData,
        Account $accountConfigProvider
    ) {
        $this->zendClient            = $zendClient;
        $this->softwareData          = $softwareData;
        $this->accountConfigProvider = $accountConfigProvider;
    }

    /**
     * @param EndpointInterface $endpoint
     *
     * @return array|\Zend_Http_Response
     * @throws \Zend_Http_Client_Exception
     */
    public function getRequest(EndpointInterface $endpoint)
    {
        $this->zendClient->resetParameters(true);

        $this->setUri($endpoint->getEndpointUrl());
        $this->setHeaders();
        $this->setParameters($endpoint);

        try {
            $response = $this->zendClient->request();
            $response = $this->formatResponse($response->getBody());
        } catch (\Zend_Http_Client_Exception $exception) {
            $response = [
                'success' => false,
                'error'   => __('%1 : Zend Http Client exception', $exception->getCode())
            ];
        }

        return $response;
    }

    /**
     * @param string $endpointUrl
     *
     * @throws \Zend_Http_Client_Exception
     */
    private function setUri($endpointUrl)
    {
        $uri = $this->accountConfigProvider->getBaseUrl() . $endpointUrl;

        $this->zendClient->setUri($uri);
    }

    /**
     * @throws \Zend_Http_Client_Exception
     */
    private function setHeaders()
    {
        $headers = [
            'Accept'                    => 'application/json',
            'Content-Type'              => 'application/json; charset=UTF-8',
            'User-Agent'                => 'GLSMagento2Plugin/' . $this->softwareData->getVersionNumber(),
            'Ocp-Apim-Subscription-Key' => $this->accountConfigProvider->getSubscriptionKey()
        ];

        $this->zendClient->setHeaders($headers);
    }

    /**
     * @param EndpointInterface $endpoint
     *
     * @throws \Zend_Http_Client_Exception
     */
    private function setParameters(EndpointInterface $endpoint)
    {
        $endpointMethod = $endpoint->getMethod();

        $endpointData             = $endpoint->getRequestData();
        $endpointData['Username'] = $this->accountConfigProvider->getUsername();
        $endpointData['Password'] = $this->accountConfigProvider->getPassword();

        $this->zendClient->setMethod($endpointMethod);

        switch ($endpointMethod) {
            case ZendClient::GET:
                $this->zendClient->setParameterGet($endpointData);
                break;
            case ZendClient::POST:
            case ZendClient::PUT:
            default:
                $this->zendClient->setRawData(json_encode($endpointData), 'application/json');
                break;
        }
    }

    /**
     * @param $response
     *
     * @return array
     */
    private function formatResponse($response)
    {
        if (is_string($response)) {
            $response = json_decode($response, true);
        }

        if (!is_array($response)) {
            $response = [$response];
        }

        return $response;
    }
}
