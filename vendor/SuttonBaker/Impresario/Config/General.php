<?php

namespace SuttonBaker\Impresario\Config;
/**
 * Class Element
 * @package SuttonBaker\Impresario\Config
 */
class General extends \DaveBaker\Core\Config\General
{
    public function __construct()
    {
        $this->mergeConfig([
            'uploadAllowedMimeTypes' => 'image/jpeg, 
            image/png, 
            image/gif, 
            image/bmp,
            application/pdf,
            application/rtf,
            text/richtext,
            text/plain,
            text/rtf,
            application/vnd.openxmlformats-officedocument.wordprocessingml.document,
            application/msword,
            application/vnd.ms-word.document.macroenabled.12,
            application/vnd.ms-excel,
            application/vnd.ms-excel.sheet.macroenabled.12,
            application/vnd.openxmlformats-officedocument.presentationml.presentation,
            application/vnd.ms-powerpoint,
            application/x-msaccess'
        ]);
    }
}