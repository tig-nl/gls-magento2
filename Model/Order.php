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
namespace TIG\GLS\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Model\OrderRepository;
use TIG\GLS\Api\Data\OrderInterface;

class Order extends AbstractModel implements OrderInterface
{
    const FIELD_ORDER_ID = 'order_id';
    const FIELD_QUOTE_ID = 'quote_id';
    const FIELD_TYPE = 'type';
    const FIELD_PARCEL_COUNT = 'parcel_count';

    /**
     * @var OrderRepository $orderRepository
     */
    protected $orderRepository;

    /**
     * @var QuoteRepository $quoteRepository
     */
    private $quoteRepository;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * Order constructor.
     *
     * @param Context               $context
     * @param Registry              $registry
     * @param AbstractResource|null $resource
     * @param AbstractDb|null       $resourceCollection
     * @param OrderRepository       $orderRepository
     * @param QuoteRepository       $quoteRepository
     * @param DateTime              $dateTime
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        OrderRepository $orderRepository,
        QuoteRepository $quoteRepository,
        DateTime $dateTime,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
        $this->quoteRepository = $quoteRepository;
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $dateTime, $resource, $resourceCollection, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('TIG\GLS\Model\ResourceModel\Order');
    }

    /**
     * @param $value
     *
     * @return \TIG\GLS\Api\Data\OrderInterface
     */
    public function setOrderId($value)
    {
        return $this->setData(static::FIELD_ORDER_ID, $value);
    }

    /**
     * @return int
     */
    public function getOrderId()
    {
        return $this->getData(static::FIELD_ORDER_ID);
    }

    /**
     * @param $value
     *
     * @return \TIG\GLS\Api\Data\OrderInterface
     */
    public function setQuoteId($value)
    {
        return $this->setData(static::FIELD_QUOTE_ID, $value);
    }

    /**
     * @return int
     */
    public function getQuoteId()
    {
        return $this->getData(static::FIELD_QUOTE_ID);
    }

    /**
     * @param $value
     *
     * @return \TIG\GLS\Api\Data\OrderInterface
     */
    public function setType($value)
    {
        return $this->setData(static::FIELD_TYPE, $value);
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->getData(static::FIELD_TYPE);
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getShippingAddress()
    {
        $shippingAddress = null;

        if (!$this->getOrderId()) {
            $addresses = $this->getShippingAddressFromQuote();

            return reset($addresses);
        }

        try {
            $order = $this->orderRepository->get($this->getOrderId());

            $addresses = $order->getAddresses();
            unset($addresses[$order->getBillingAddressId()]);
            unset($addresses[$this->getPgOrderAddressId()]);
        } catch (\Error $exception) {
            $addresses = $this->getShippingAddressFromQuote();
        }

        return reset($addresses);
    }

    /**
     * @throws NoSuchEntityException
     */
    public function getShippingAddressFromQuote()
    {
        $quote = $this->quoteRepository->get($this->getQuoteId());

        $addresses = $quote->getAllShippingAddresses();
        array_walk($addresses, function ($address, $key) use (&$addresses) {
            if ($address->getId() == $this->getPgOrderAddressId()) {
                unset($addresses[$key]);
            }
        });

        return $addresses;
    }

    /**
     * @return \Magento\Sales\Api\Data\OrderAddressInterface;
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBillingAddress()
    {
        $order = $this->orderRepository->get($this->getOrderId());

        return $order->getBillingAddress();
    }

    /**
     * @return mixed
     */
    public function getParcelCount()
    {
        return $this->getData(static::FIELD_PARCEL_COUNT);
    }

    /**
     * @param int $value
     *
     * @return \TIG\GLS\Api\Data\OrderInterface
     */
    public function setParcelCount($value)
    {
        return $this->setData(static::FIELD_PARCEL_COUNT, $value);
    }
}
