<?php

namespace SuttonBaker\Impresario\Helper;

use \SuttonBaker\Impresario\Definition\Quote as QuoteDefinition;
use \SuttonBaker\Impresario\Definition\Task as TaskDefinition;
use SuttonBaker\Impresario\Definition\Roles;
/**
 * Class Quote
 * @package SuttonBaker\Impresario\Helper
 */
class Quote extends Base
{
    /** @var array  */
    protected $editCapabilities = [Roles::CAP_ALL, Roles::CAP_EDIT_QUOTE];
    protected $viewCapabilities = [Roles::CAP_ALL, Roles::CAP_EDIT_QUOTE, Roles::CAP_VIEW_QUOTE];
    /**
     * @var array
     *
     * Values to bring across when creating a quote from an enquiry
     */
    protected $enquiryDataValues = [
        'date_received',
        'client_id',
        'enquiry_id',
        'site_name',
        'po_number',
        'mi_number',
        'nm_mw_number',
        'client_reference'
    ];

    /**
     * @return \SuttonBaker\Impresario\Model\Db\Quote\Collection
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Adapter_Exception
     */
    public function getQuoteCollection()
    {
        /** @var \SuttonBaker\Impresario\Model\Db\Quote\Collection $collection */
        $collection = $this->createAppObject(
            QuoteDefinition::DEFINITION_COLLECTION
        );

        $userTable = $this->getApp()->getHelper('Db')->getTableName('users', false);
        $collection->where('{{quote}}.is_deleted=?', '0');

        $collection->joinLeft(
            ['project_manager_user' => $userTable],
            "project_manager_user.ID={{quote}}.project_manager_id",
            ['project_manager_name' => 'user_login']
        )->joinLeft(
            ['estimator_user' => $userTable],
            "estimator_user.ID={{quote}}.estimator_id",
            ['estimator_name' => 'user_login']
        )->joinLeft(
            ['created_by_user' => $userTable],
            "created_by_user.ID={{quote}}.created_by_id",
            ['created_by_name' => 'user_login']
        )->joinLeft(
            "{{client}}",
            "{{client}}.client_id={{quote}}.client_id",
            ['client_name' => 'client_name']
        );

        $collection->order(new \Zend_Db_Expr(sprintf(
                "FIELD({{quote}}.status,'%s', '%s', '%s', '%s')",
                QuoteDefinition::STATUS_OPEN,
                QuoteDefinition::STATUS_WON,
                QuoteDefinition::STATUS_CANCELLED,
                QuoteDefinition::STATUS_CLOSED_OUT)
        ))->order('{{quote}}.date_required');

        return $collection;
    }

    /**
     * @return \SuttonBaker\Impresario\Model\Db\Quote\Collection
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Adapter_Exception
     * @throws \Zend_Db_Select_Exception
     */
    public function getOpenQuotes()
    {
        return $this->getDisplayQuotes()->where('status=?', QuoteDefinition::STATUS_OPEN);
    }

    /**
     * @param $status
     * @return string
     */
    public function getStatusDisplayName($status)
    {
        return $this->getDisplayName($status, QuoteDefinition::getStatuses());
    }

    /**
     * @return \SuttonBaker\Impresario\Model\Db\Quote\Collection
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Select_Exception
     *
     * Gets a list of the most recent quotes for display
     */
    public function getDisplayQuotes()
    {
        return $this->getQuoteCollection()->where('is_superseded=0');
    }

    /**
     * @param string $entity
     * @param string $status
     * @return \SuttonBaker\Impresario\Model\Db\Quote\Collection
     * @throws \DaveBaker\Core\Object\Exception
     */
    public function getQuoteCollectionForEnquiry($enquiryId, $status)
    {
        $collection = $this->getQuoteCollection();
        $collection->getSelect()->where('enquiry_id=?', $enquiryId);

        if($status) {
            $collection->getSelect()->where('status=?', $status);
        }

        return $collection;
    }

    /**
     * @param $enquiryId
     * @return \SuttonBaker\Impresario\Model\Db\Enquiry
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Select_Exception
     */
    public function getNewestQuoteForEnquiry($enquiryId)
    {
        $collection = $this->getDisplayQuotes()
            ->where('enquiry_id=?', $enquiryId);

        $items = $collection->load();

        if(count($items)){
            return $items[0];
        }

        return $this->getQuote();
    }

    /**
     * @param $enquiryId
     * @return \DaveBaker\Core\Model\Db\Base|null|\SuttonBaker\Impresario\Model\Db\Quote|null
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Model\Db\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Select_Exception
     * @throws \Exception
     */
    public function createQuoteFromEnquiry($enquiryId)
    {
        $enquiry = $this->getEnquiryHelper()->getEnquiry($enquiryId);

        if(!$enquiry->getId()){
            return null;
        }

        $quote = $this->getNewestQuoteForEnquiry($enquiryId);

        if($quote->getId()){
            throw new \Exception("Quote has already been created for enquiry {$enquiryId}");
        }

        foreach($this->enquiryDataValues as $key){
            $quote->setData($key, $enquiry->getData($key));
        }

        $currentUserId = $this->getUserHelper()->getCurrentUserId();

        $quote->setLastEditedById($currentUserId)
            ->setCreatedById($currentUserId)
            ->setCreatedBy($currentUserId)
            ->setStatus(QuoteDefinition::STATUS_OPEN);

        return $quote->save();
    }

    /**
     * @param $status
     * @return bool
     */
    public function isValidStatus($status)
    {
        return in_array($status, array_keys(\SuttonBaker\Impresario\Definition\Quote::getStatuses()));
    }

    /**
     * @param $entityId
     * @return \SuttonBaker\Impresario\Model\Db\Quote
     * @throws \DaveBaker\Core\Object\Exception
     */
    public function getQuote($entityId = null)
    {
        $entity = $this->createAppObject(QuoteDefinition::DEFINITION_MODEL);

        if($entityId){
            $entity->load($entityId);
        }

        return $entity;
    }

    /**
     * @param \SuttonBaker\Impresario\Model\Db\Quote $quote
     * @return \SuttonBaker\Impresario\Model\Db\Quote
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Adapter_Exception
     */
    public function duplicateQuote(
        \SuttonBaker\Impresario\Model\Db\Quote $quote
    ) {
        if(!$quote->getId()){
            return $quote;
        }

        $newQuote = clone $quote;

        $newQuote->setParentId($quote->getId())
            ->unsQuoteId()
            ->save();

        $quote->setIsSuperseded(1)->save();

        $taskItems = $this->getTaskHelper()->getTaskCollectionForEntity(
            $quote->getId(), TaskDefinition::TASK_TYPE_QUOTE
        )->load();

        foreach($taskItems as $taskItem){
            $taskItem->setIsSuperseded(1)->save();
        }


        return $newQuote;
    }

    /**
     * @param \SuttonBaker\Impresario\Model\Db\Quote $quote
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Adapter_Exception
     */
    public function deleteQuote(
        \SuttonBaker\Impresario\Model\Db\Quote $quote
    ) {
        $tasks = $this->getTaskHelper()->getTaskCollectionForEntity(
            $quote->getId(),
            TaskDefinition::TASK_TYPE_QUOTE
        )->load();

        foreach($tasks as $task){
            $task->setIsDeleted(1)->save();
        }

        $quote->setIsDeleted(1)->save();
    }

    /**
     * @param $parentId
     * @return \SuttonBaker\Impresario\Model\Db\Quote\Collection
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Adapter_Exception
     */
    public function getSupersededQuotesForParent($parentId)
    {
        $collection = $this->getQuoteCollection()
            ->where('parent_id=?', $parentId)
            ->where('is_superseded', 1);

        return $collection;
    }

    /**
     * @return \SuttonBaker\Impresario\Helper\OutputProcessor\Quote\Status
     * @throws \DaveBaker\Core\Object\Exception
     */
    public function getStatusOutputProcessor()
    {
        return $this->createAppObject('\SuttonBaker\Impresario\Helper\OutputProcessor\Quote\Status');
    }

}