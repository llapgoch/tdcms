<?php

namespace SuttonBaker\Impresario\Layout;
/**
 * Class Quote
 * @package SuttonBaker\Impresario\Layout
 */
class Quote extends Base
{
    const ID_KEY = 'quote_id';

    /** @var string  */
    protected $blockPrefix = 'quote';
    /** @var string  */
    protected $headingName = 'Quotes';
    protected $icon = \SuttonBaker\Impresario\Definition\Quote::ICON;

    /**
     * @throws \DaveBaker\Core\App\Exception
     * @throws \DaveBaker\Core\Block\Exception
     * @throws \DaveBaker\Core\Db\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Model\Db\Exception
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \Zend_Db_Adapter_Exception
     */
    public function quoteEditHandle()
    {
        /** @var \SuttonBaker\Impresario\Model\Db\Quote $entityInstance */
        $entityInstance = $this->createAppObject('\SuttonBaker\Impresario\Model\Db\Quote');

        if($entityId = $this->getRequest()->getParam(self::ID_KEY)){
            /** @var \SuttonBaker\Impresario\Model\Db\Quote $entityInstance */
            $entityInstance->load($entityId);
        }

        $this->addHeading()->addMessages();

        $this->addBlock(
        /** @var \SuttonBaker\Impresario\Block\Core\Tile\Black $mainTile */
            $mainTile = $this->createBlock(
                '\SuttonBaker\Impresario\Block\Core\Tile\Black',
                "{$this->getBlockPrefix()}.tile.main")
                ->setHeading($this->getQuoteHelper()->getActionVerb($entityInstance) . " Quote")
                ->setShortcode('body_content')
                ->addChildBlock($this->getQuoteHelper()->getTabBarForQuote($entityInstance))
        );

        $mainTile->addChildBlock(
            $this->createBlock(
                '\SuttonBaker\Impresario\Block\Quote\Form\Edit',
                "{$this->getBlockPrefix()}.form.edit",
                'content'
            )->setElementName('enquiry_edit_form')

        );

    }

    /**
     * @throws \DaveBaker\Core\App\Exception
     * @throws \DaveBaker\Core\Block\Exception
     * @throws \DaveBaker\Core\Event\Exception
     * @throws \DaveBaker\Core\Model\Db\Exception
     * @throws \DaveBaker\Core\Object\Exception
     */
    public function quoteListHandle()
    {
        $this->addHeading()->addMessages();

        $this->addBlock(
        /** @var \SuttonBaker\Impresario\Block\Core\Tile\Black $mainTile */
            $mainTile = $this->createBlock(
                '\SuttonBaker\Impresario\Block\Core\Tile\Black',
                "{$this->getBlockPrefix()}.tile.main")
                ->setHeading("<strong>Quote</strong> Register")
                ->setShortcode('body_content')
                ->setTileBodyClass('nopadding')
        );


        $mainTile->addChildBlock(
            $mainTile->createBlock(
                '\SuttonBaker\Impresario\Block\Quote\QuoteList',
                "{$this->getBlockPrefix()}.list",
                'content'
            )
        );
    }
}