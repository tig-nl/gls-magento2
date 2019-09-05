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

namespace TIG\GLS\Controller\Adminhtml\Credentials;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use TIG\GLS\Service\Label\Label;

class Validate extends Action
{
    /** @var Label $labelService */
    private $labelService;

    /**
     * Validate constructor.
     *
     * @param Context $context
     * @param Label   $labelService
     */
    public function __construct(
        Context $context,
        Label $labelService
    ) {
        parent::__construct($context);

        $this->labelService = $labelService;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // TO DO: Change htis to the authorize call whenever it's available.
        $label = $this->labelService->createLabel($this->getRequestData());

        $this->_response->setBody('nok');
        if (isset($label['status']) && $label['status'] == 200) {
            $this->_response->setBody('ok');
        }

        return $this->_response->setStatusHeader(200, '1.1', 'Succesfully authorized');
    }

    // @codingStandardsIgnoreStart
    private function getRequestData()
    {
        return [
            'ShippingSystemName' => 'Magento',
            'ShippingSystemVersion' => '2.0',
            'ShipType' => 'P',
            'ShippingDate' => '2019-08-23',
            'Reference' => 'ORD0000123',
            'LabelType' => 'pdf',
            'TrackingLinkType' => 'U',
            'Addresses' => [
                'DeliveryAddress' => [
                    'Name1' => 'My-Customer',
                    'Street' => 'Kalverstraat',
                    'HouseNo' => '17',
                    'CountryCode' => 'NL',
                    'ZipCode' => '1042AB',
                    'City' => 'Amsterdam',
                    'Contact' => 'Joe Black',
                    'Phone' => '030-2417800',
                    'Email' => 'dennis+customer@tig.nl',
                    'AddresseeType' => 'P'
                ]
            ],
            'Units' => [
                [
                    'UnitID' => 'A',
                    'Weight' => 2.5,
                    'AdditionalInfo1' => 'InvoiceNo: P10050432',
                    'AdditionalInfo2' => 'Additional info2',
                ]
            ],
            'NotificationEmail' => [
                'SendMail' => true,
                'SenderName' => 'Top Products BV',
                'SenderReplyAddress' => 'dennis@tig.nl',
                'SenderContactName' => 'Customer Service',
                'SenderPhoneNo' => '+31885503000',
                'EmailSubject' => 'Your order has been shipped!',
                'EmailCc' => 'dennis.van.der.hammen@tig.nl'
            ]
        ];
    }
    // @codingStandardsIgnoreEnd
}
