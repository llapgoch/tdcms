<?php

namespace SuttonBaker\Impresario\WP\Block\Job;

class Table
    extends \DaveBaker\Core\WP\Block\Base
    implements \DaveBaker\Core\WP\Block\BlockInterface
{
    public function toHtml(){
        return "Block to HTML";
    }

    public function preDispatch()
    {
        var_dump("pre dispatch");
    }

    public function postDispatch()
    {
        parent::postDispatch();
        var_dump("post dispatch");
    }
}