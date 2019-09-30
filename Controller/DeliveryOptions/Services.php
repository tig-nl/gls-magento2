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
use TIG\GLS\Model\Config\Provider\Carrier;
use TIG\GLS\Service\DeliveryOptions\Services as ServicesService;

class Services extends Action
{
    /** @var Session $checkoutSession */
    private $checkoutSession;

    /** @var CarrierConfig $config */
    private $carrierConfig;

    /** @var ServicesService $services */
    private $services;

    /**
     * Services constructor.
     *
     * @param Context         $context
     * @param Session         $checkoutSession
     * @param CarrierConfig   $carrierConfig
     * @param ServicesService $services
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CarrierConfig $carrierConfig,
        ServicesService $services
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->carrierConfig   = $carrierConfig;
        $this->services        = $services;

        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Zend_Http_Client_Exception
     */
    public function execute()
    {
        $request  = $this->getRequest();
        $country  = $request->getParam('country');
        $postcode = $request->getParam('postcode');

        $services        = $this->services->getDeliveryOptions($country, 'NL', $postcode);
        $deliveryOptions = $services['deliveryOptions'];

        foreach ($deliveryOptions as &$option) {
            $option['isService']     = isset($option['service']);
            $option['hasSubOptions'] = isset($option['subDeliveryOptions']);
            $option['fee']           = $this->getAdditionalHandlingFee($option);

            if ($option['hasSubOptions']) {
                $this->filterExpressServices($option['subDeliveryOptions']);
                $this->addExpressAdditionalHandlingFees($option['subDeliveryOptions']);
            }
        }

        return $this->jsonResponse($deliveryOptions);
    }

    /**
     * @param $option
     *
     * @return string|null
     */
    private function getAdditionalHandlingFee($option)
    {
        if ($option['isService'] && $option['service'] == CarrierConfig::GLS_DELIVERY_OPTION_SATURDAY_LABEL) {
            return (string) $this->carrierConfig->getSaturdayHandlingFee();
        }

        return null;
    }

    /**
     * @param $services
     *
     * @return array
     */
    private function filterExpressServices(&$services)
    {
        $allowedServices = $this->carrierConfig->getActiveExpressServices();

        $services = array_filter(
            $services,
            function ($details) use ($allowedServices) {
                return in_array($details['service'], $allowedServices);
            }
        );

        $services = array_values($services);

        return $services;
    }

    /**
     * @param $options
     *
     * @return mixed
     */
    private function addExpressAdditionalHandlingFees(&$options)
    {
        $fees = (array) $this->carrierConfig->getExpressHandlingFees();

        foreach ($options as &$option) {
            array_filter(
                $fees,
                function ($value) use (&$option) {
                    if ($value->shipping_method == $option['service']) {
                        $option['fee'] = $value->additional_handling_fee;
                    }
                }
            );
        }

        return $options;
    }

    /**
     * @param string $data
     * @param null   $code
     *
     * @return mixed
     */
    private function jsonResponse($data = '', $code = null)
    {
        $response = $this->getResponse();

        if ($code !== null) {
            $response->setStatusCode($code);
        }

        return $response->representJson(
            \Zend_Json::encode($data)
        );
    }
}
