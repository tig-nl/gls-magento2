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

namespace TIG\GLS\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use TIG\GLS\Controller\Adminhtml\Massaction\CreateAndPrint;
use Magento\Backend\App\Action;
use TIG\GLS\Service\Order\ParcelQuantity;

class MassChangeMultiColli extends Action
{
    const PARCEL_QUANTITY_PARAM_KEY = 'change_parcel';

    /**
     * @var OrderCollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CreateAndPrint
     */
    private CreateAndPrint $createAndPrint;

    /**
     * @var Filter
     */
    private Filter $uiFilter;

    /**
     * @var ParcelQuantity
     */
    private ParcelQuantity $parcelQuantity;

    /**
     * MassChangeMulticolli constructor.
     *
     * @param Context                $context
     * @param Filter                 $filter
     * @param OrderCollectionFactory $collectionFactory
     * @param CreateAndPrint         $createAndPrint
     * @param ParcelQuantity         $parcelQuantity
     */
    public function __construct(
        Context $context,
        Filter $filter,
        OrderCollectionFactory $collectionFactory,
        CreateAndPrint $createAndPrint,
        ParcelQuantity $parcelQuantity
    ) {
        parent::__construct($context);

        $this->collectionFactory = $collectionFactory;
        $this->createAndPrint    = $createAndPrint;
        $this->uiFilter          = $filter;
        $this->parcelQuantity    = $parcelQuantity;
    }

    /**
     * @return Redirect
     * @throws LocalizedException
     */
    public function execute()
    {
        $collection = $this->collectionFactory->create();
        $collection = $this->uiFilter->getCollection($collection);
        $collection = $this->createAndPrint->removeNonGLSMethods($collection);

        $newParcelCount = $this->getRequest()->getParam(self::PARCEL_QUANTITY_PARAM_KEY);

        $this->changeMultiColli($collection, $newParcelCount);

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('sales/*/');

        return $resultRedirect;
    }

    /**
     * @param            $collection
     * @param            $newParcelCount
     */
    private function changeMultiColli($collection, $newParcelCount)
    {
        $result = '';

        foreach ($collection as $order) {
            $result = $this->parcelQuantity->orderChangeParcelQuantity($order, $newParcelCount);
        }

        if (is_array($result) && array_key_exists('error', $result)) {
            $this->messageManager->addErrorMessage(
                __('Error changing parcel quantity')
            );
        }

        if ($result === true) {
            $this->messageManager->addSuccessMessage(
                __('Parcel quantity changed for %1 order(s)', $collection->count())
            );
        }
    }
}
