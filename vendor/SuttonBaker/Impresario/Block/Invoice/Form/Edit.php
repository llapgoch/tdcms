<?php

namespace SuttonBaker\Impresario\Block\Invoice\Form;

use DaveBaker\Core\Definitions\Api;
use \SuttonBaker\Impresario\Definition\Invoice as InvoiceDefintion;
use DaveBaker\Core\Definitions\Upload as CoreUploadDefinition;
use SuttonBaker\Impresario\Definition\Upload;

/**
 * Class Edit
 * @package SuttonBaker\Impresario\Block\Invoice\Form
 */
class Edit extends \SuttonBaker\Impresario\Block\Form\Base
{
    const ID_KEY = 'invoice_id';
    const PREFIX_KEY = 'invoice';
    const PREFIX_NAME = 'Invoice';

    /** @var \SuttonBaker\Impresario\Model\Db\Invoice */
    protected $modelInstance;
    /** @var \DaveBaker\Core\Model\Db\BaseInterface */
    protected $parentItem;

    /**
     * @return \DaveBaker\Core\Block\Template|void
     * @throws \DaveBaker\Core\App\Exception
     * @throws \DaveBaker\Core\Block\Exception
     * @throws \DaveBaker\Core\Helper\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \DaveBaker\Form\Exception
     */
    protected function _preDispatch()
    {
        wp_register_script('impresario_invoice', get_template_directory_uri() . '/assets/js/invoice-edit.js', ['jquery']);
        wp_enqueue_script('impresario_invoice');

        $this->addClass('js-invoice-form');

        $prefixKey = self::PREFIX_KEY;
        $prefixName = self::PREFIX_NAME;

        $heading = "Create {$prefixName}";
        $this->modelInstance = $this->getApp()->getRegistry()->get('model_instance');
        $this->parentItem =  $this->getApp()->getRegistry()->get('parent_item');

        $editMode = $this->modelInstance->getId() ? true : false;
        $invoiceTypeName = $this->getInvoiceHelper()->determineInvoiceTypeName($this->modelInstance);

        /** @var \DaveBaker\Form\Builder $builder */
        $builder = $this->createAppObject('\DaveBaker\Form\Builder')
            ->setFormName("{$prefixKey}_edit")->setGroupTemplate('form/group-vertical.phtml');


        $elements = $builder->build([
            [
                'name' => 'invoice_date',
                'labelName' => 'Invoice Date *',
                'class' => 'js-date-picker',
                'rowIdentifier' => 'invoice_date',
                'type' => 'Input\Text',
                'formGroup' => true,
                'formGroupSettings' => [
                    'class' => 'col-md-6'
                ],
                'value' => $this->getDateHelper()->currentDateShortLocalOutput(),
                'attributes' => [
                    'autocomplete' => 'off',
                    'data-date-settings' => json_encode(['minDate' => '-5Y', 'maxDate' => "0"])
                ],
            ], [
                'name' => 'invoice_number',
                'labelName' => 'Invoice Number *',
                'type' => 'Input\Text',
                'rowIdentifier' => 'invoice_number_val',
                'formGroup' => true,
                'formGroupSettings' => [
                    'class' => 'col-md-4'
                ],
            ], [
                'name' => 'value',
                'labelName' => 'Invoice Value *',
                'type' => 'Input\Text',
                'rowIdentifier' => 'invoice_number_val',
                'formGroup' => true,
                'class' => 'js-invoice-value',
                'formGroupSettings' => [
                    'class' => 'col-md-4'
                ],
            ], [
                'name' => 'amount_remaining',
                'labelName' => "Amount Remaining on {$invoiceTypeName}",
                'type' => 'Input\Text',
                'rowIdentifier' => 'invoice_number_val',
                'attributes' => ['readonly' => 'readonly'],
                'value' => $this->getLocaleHelper()->formatCurrency($this->parentItem->getInvoiceAmountRemaining()),
                'formGroup' => true,
                'formGroupSettings' => [
                    'class' => 'col-md-4'
                ],
            ], [
                'name' => 'submit',
                'type' => '\DaveBaker\Form\Block\Button',
                'data' => [
                    'button_name' => $this->getInvoiceHelper()->getActionVerb($this->modelInstance) . " Invoice",
                    'capabilities' => $this->getVariationHelper()->getEditCapabilities()
                ],
                'class' => 'btn-block'
            ], [
                'name' => 'invoice_id',
                'type' => 'Input\Hidden',
                'value' => $this->modelInstance->getId()
            ], [
                'name' => 'action',
                'type' => 'Input\Hidden',
                'value' => 'edit'
            ], [
                'name' => 'invoice_data',
                'type' => 'Input\Hidden',
                'value' => json_encode([
                    'amountRemaining' => (float) $this->parentItem->getInvoiceAmountRemaining()
                ]),
                'class' => 'js-invoice-data'
            ]
        ]);

        $this->addChildBlock(array_values($elements));

        // Create the file uploader
        $this->addChildBlock(
            $this->createBlock(
                '\SuttonBaker\Impresario\Block\Upload\TableContainer',
                "{$prefixKey}.file.upload.container"
            )->setOrder('before', "{$prefixKey}.edit.submit.element")
                ->setUploadType($this->modelInstance->getId() ? Upload::TYPE_INVOICE : CoreUploadDefinition::UPLOAD_TYPE_TEMPORARY)
                ->setIdentifier($this->modelInstance->getId() ? $this->modelInstance->getId() : $this->getUploadHelper()->getTemporaryIdForSession())
        );

        if($this->getInvoiceHelper()->currentUserCanEdit() == false) {
            $this->lock();
        }
    }

    /**
     * @return \SuttonBaker\Impresario\Block\Form\Base
     * @throws \DaveBaker\Core\Object\Exception
     */
    protected function _preRender()
    {
        $entityId = $this->getRequest()->getParam(self::ID_KEY);
        $prefixKey = self::PREFIX_KEY;
        $prefixName = self::PREFIX_NAME;
        $uploadTable = $this->getBlockManager()->getBlock('upload.tile.block');
        $uploadParams = [
            'upload_type' => $this->modelInstance->getId() ? Upload::TYPE_INVOICE: CoreUploadDefinition::UPLOAD_TYPE_TEMPORARY,
            'identifier' => $this->modelInstance->getId() ? $this->modelInstance->getId() : $this->getUploadHelper()->getTemporaryIdForSession()
        ];

        $uploadTable->addChildBlock(
            $uploadTable->createBlock(
                '\DaveBaker\Core\Block\Components\FileUploader',
                "{$prefixKey}.file.uploader",
                'header_elements'
            )->addJsDataItems(
                ['endpoint' => $this->getUrlHelper()->getApiUrl(
                    Api::ENDPOINT_FILE_UPLOAD,
                    $uploadParams
                )]
            )
        );
        return parent::_preRender();
    }

}