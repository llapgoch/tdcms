<?php

namespace SuttonBaker\Impresario\Controller;
/**
 * Class EnquiryEditController
 * @package SuttonBaker\Impresario\Controller
 */
class EnquiryEditController
    extends \DaveBaker\Core\Controller\Base
    implements \DaveBaker\Core\Controller\ControllerInterface
{
    /** @var \DaveBaker\Form\Block\Form $enquiryEditForm */
    protected $enquiryEditForm;

    /**
     * @throws \DaveBaker\Core\App\Exception
     * @throws \DaveBaker\Core\Block\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Model\Db\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \DaveBaker\Form\Exception
     * @throws \DaveBaker\Form\Validation\Rule\Configurator\Exception
     */
    public function execute()
    {
        if(!($this->enquiryEditForm = $this->getApp()->getBlockManager()->getBlock('enquiry.form.edit'))){
            return;
        }

        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style('jquery-style', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');

        $modelInstance = $this->createAppObject('\SuttonBaker\Impresario\Model\Db\Enquiry');

        // Form submission
        if($this->getRequest()->getPostParam('action')){
            $postParams = $this->getRequest()->getPostParams();

            /** @var \DaveBaker\Form\Validation\Rule\Configurator\ConfiguratorInterface $configurator */
            $configurator = $this->createAppObject('\SuttonBaker\Impresario\Form\EnquiryConfigurator');

            /** @var \DaveBaker\Form\Validation\Validator $validator */
            $validator = $this->createAppObject('\DaveBaker\Form\Validation\Validator')
                ->setValues($postParams)
                ->configurate($configurator);

            if(!$validator->validate()){
                return $this->prepareFormErrors($validator);
            }

            $this->saveFormValues();
            $this->redirectToPage(\SuttonBaker\Impresario\Definition\Page::ENQUIRY_LIST);
        }


        if($enquiryId = (int) $this->getRequest()->getParam('enquiry_id')){
            // We're loading, fellas!

            $modelInstance->load($enquiryId);

            if(!$modelInstance->getId()){
                $this->redirectToPage(\SuttonBaker\Impresario\Definition\Page::ENQUIRY_LIST);
            }
        }

        /** @var \DaveBaker\Form\BlockApplicator $applicator */
        $applicator = $this->createAppObject('\DaveBaker\Form\BlockApplicator');

        // Apply the values to the form element
        if($modelInstance->getId()) {
            $applicator->configure(
                $this->enquiryEditForm,
                $modelInstance->getData()
            );
        }
    }

    /**
     * @return $this
     * @throws \DaveBaker\Core\Object\Exception
     */
    protected function saveFormValues()
    {
        $data = $this->getRequest()->getPostParams();

        // Don't save a completed user if status isn't completed
        if(isset($data['status'])){
            if($data['status'] !== \SuttonBaker\Impresario\Definition\Enquiry::STATUS_COMPLETE){
                $data['completed_by_id'] = null;
            }
        }


        /** @var \SuttonBaker\Impresario\Model\Db\Enquiry $enquiry */
        $enquiry = $this->createAppObject('\SuttonBaker\Impresario\Model\Db\Enquiry');

        $this->addMessage("The enquiry has been " . ($data['enquiry_id'] ? 'updated' : 'added'));

        $enquiry->setData($data)->save();

        return $this;
    }

    /**
     * @param \DaveBaker\Form\Validation\Validator $validator
     * @throws \DaveBaker\Core\App\Exception
     * @throws \DaveBaker\Core\Block\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \DaveBaker\Form\Exception
     */
    protected function prepareFormErrors(
        \DaveBaker\Form\Validation\Validator $validator
    ) {
        /** @var \DaveBaker\Form\BlockApplicator $applicator */
        $applicator = $this->createAppObject('\DaveBaker\Form\BlockApplicator');

        // Create main error block
        /** @var \DaveBaker\Form\Block\Error\Main $errorBlock */
        $errorBlock = $this->getApp()->getBlockManager()->createBlock(
            '\DaveBaker\Form\Block\Error\Main',
            'enquiry.edit.form.errors'
        )->setOrder('after', 'enquiry.form.edit.heading')->addErrors($validator->getErrors());

        $this->enquiryEditForm->addChildBlock($errorBlock);

        // Sets the values back onto the form element
        $applicator->configure(
            $this->enquiryEditForm,
            $this->getRequest()->getPostParams()
        );
    }
}