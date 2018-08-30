<?php

namespace SuttonBaker\Impresario\Block\Task\Form;

use \SuttonBaker\Impresario\Definition\Task as TaskDefinition;

/**
 * Class Edit
 * @package SuttonBaker\Impresario\Block\Client\Form
 */
class Edit extends \SuttonBaker\Impresario\Block\Form\Base
{
    const ID_KEY = 'task_id';
    const PREFIX_KEY = 'task';
    const PREFIX_NAME = 'Task';

    /**
     * @return \DaveBaker\Form\Block\Form|void
     * @throws \DaveBaker\Core\App\Exception
     * @throws \DaveBaker\Core\Block\Exception
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \DaveBaker\Form\Exception
     * @throws \DaveBaker\Form\SelectConnector\Exception
     */
    protected function _preDispatch()
    {
        $prefixKey = self::PREFIX_KEY;
        $prefixName = self::PREFIX_NAME;

        $heading = "Create {$prefixName}";
        $editMode = false;

        $entityInstance = $this->getApp()->getRegistry()->get('model_instance');
        $parentItem = $this->getApp()->getRegistry()->get('parent_item');
        $taskType = $this->getApp()->getRegistry()->get('task_type');

        if($entityInstance->getId()){
            $heading = "Update {$prefixName}";
            $editMode = true;
        }elseif( $parentItem && $parentItem->getId()){
            $heading = "Create Task For " . TaskDefinition::getTaskTypeLabel($taskType) . " '{$parentItem->getSiteName()}'";
        }

        $this->addChildBlock(
            $this->createBlock('\DaveBaker\Core\Block\Html\Heading', "{$prefixKey}.form.edit.heading")
                ->setHeading($heading)
        );

        /** @var \DaveBaker\Form\Builder $builder */
        $builder = $this->createAppObject('\DaveBaker\Form\Builder')
            ->setFormName("{$prefixKey}_edit");

        $elements = $builder->build([
            [
                'name' => 'assigned_to_id',
                'labelName' => 'Assigned To',
                'type' => 'Select'
            ], [
                'name' => 'target_date',
                'labelName' => 'Target Date',
                'class' => 'js-date-picker',
                'type' => 'Input\Text',
                'attributes' => [
                    'readonly' => 'readonly',
                    'autocomplete' => 'off',
                    'data-date-settings' => json_encode(['minDate' => '', 'maxDate' => "+5Y"])
                ],
            ], [
                'name' => 'description',
                'labelName' => 'Description',
                'type' => 'TextArea'
            ], [
                'name' => 'notes',
                'labelName' => 'Notes',
                'type' => 'TextArea'
            ], [
                'name' => 'priority',
                'labelName' => 'Priority',
                'type' => 'Select'
            ],[
                'name' => 'status',
                'labelName' => 'Status',
                'type' => 'Select'
            ], [
                'name' => 'completed_by_id',
                'labelName' => 'Completed By',
                'type' => 'Select'
            ], [
                'name' => 'date_completed',
                'labelName' => 'Date Completed',
                'type' => 'Select',
                'class' => 'js-date-picker',
                'type' => 'Input\Text',
                'attributes' => ['autocomplete' => 'off']
            ], [
                'name' => 'submit',
                'type' => 'Input\Submit',
                'value' => $editMode ? 'Update Task' : 'Create Task'
            ], [
                'name' => 'task_id',
                'type' => 'Input\Hidden',
                'value' => $entityInstance->getId()
            ], [
                'name' => 'action',
                'type' => 'Input\Hidden',
                'value' => 'edit'
            ]
        ]);

        // Set up special values

        // Assigned To
        $assignedToUsers = $this->getApp()->getHelper('User')->getUserCollection();
        $this->createCollectionSelectConnector()
            ->configure($assignedToUsers , 'ID', 'user_login', $elements['assigned_to_id_element']);

        // Completed by Users
        $completedUsers = $this->getApp()->getHelper('User')->getUserCollection();
        $this->createCollectionSelectConnector()
            ->configure($completedUsers, 'ID', 'user_login', $elements['completed_by_id_element']);

        // Statuses
        $this->createArraySelectConnector()
            ->configure(TaskDefinition::getStatuses(), $elements['status_element']);

        // Priority
        $this->createArraySelectConnector()
            ->configure(TaskDefinition::getPriorities(), $elements['priority_element']);

        $elements['status_element']->setShowFirstOption(false);
        $this->addChildBlock(array_values($elements));
    }

}