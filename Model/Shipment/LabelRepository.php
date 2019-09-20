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

namespace TIG\GLS\Model\Shipment;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use TIG\GLS\Api\Shipment\Data\LabelInterface;
use TIG\GLS\Api\Shipment\LabelRepositoryInterface;
use TIG\GLS\Api\Shipment\Data\LabelSearchResultsInterface;
use TIG\GLS\Api\Shipment\Data\LabelSearchResultsInterfaceFactory;
use TIG\GLS\Model\ResourceModel\Shipment\Label\Collection;
use TIG\GLS\Model\ResourceModel\Shipment\Label\CollectionFactory as LabelCollectionFactory;

class LabelRepository implements LabelRepositoryInterface
{
    /** @var LabelFactory $labelFactory */
    private $labelFactory;

    /** @var LabelSearchResultsInterfaceFactory $searchResultsFactory */
    private $searchResultsFactory;

    /** @var LabelCollectionFactory $labelCollectionFactory */
    private $labelCollectionFactory;

    /**
     * LabelRepository constructor.
     *
     * @param LabelFactory                       $labelFactory
     * @param LabelSearchResultsInterfaceFactory $labelSearchResultsInterfaceFactory
     * @param LabelCollectionFactory             $labelCollectionFactory
     */
    public function __construct(
        LabelFactory $labelFactory,
        LabelSearchResultsInterfaceFactory $labelSearchResultsInterfaceFactory,
        LabelCollectionFactory $labelCollectionFactory
    ) {
        $this->labelFactory = $labelFactory;
        $this->searchResultsFactory = $labelSearchResultsInterfaceFactory;
        $this->labelCollectionFactory = $labelCollectionFactory;
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($id)
    {
        $label = $this->labelFactory->create();
        $label->getResource();
        $label->load($label, $id);

        if (!$label->getId()) {
            return null;
        }

        return $label;
    }

    public function getByShipmentId($shipmentId)
    {
        $label = $this->labelFactory->create();
        $label->load($shipmentId, Label::GLS_SHIPMENT_LABEL_SHIPMENT_ID);

        if (!$label->getId()) {
            return null;
        }

        return $label;
    }

    /**
     * @param LabelInterface $label
     *
     * @return LabelInterface
     */
    public function save(LabelInterface $label)
    {
        $label->getResource();
        $label->save($label);
        return $label;
    }

    /**
     * @param LabelInterface $label
     */
    public function delete(LabelInterface $label)
    {
        $label->getResource();
        $label->delete($label);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     *
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->labelCollectionFactory->create();

        $this->addFiltersToCollection($searchCriteria, $collection);

        $collection->load();

        return $this->buildSearchResults($searchCriteria, $collection);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection              $collection
     */
    private function addFiltersToCollection(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            $fields = $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $fields[] = $filter->getField();
                $conditions[] = [$filter->getConditionType() => $filter->getValue()];
            }
            $collection->addFieldToFilter($fields, $conditions);
        }
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @param Collection              $collection
     *
     * @return mixed
     */
    private function buildSearchResults(SearchCriteriaInterface $searchCriteria, Collection $collection)
    {
        $searchResults = $this->searchResultsFactory->create();

        $searchResults->setSearchCriteria($searchCriteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }
}
