<?php

namespace SuttonBaker\Impresario\Controller\Quote;

use DaveBaker\Core\Definitions\Messages;
use SuttonBaker\Impresario\Definition\Roles;
use \SuttonBaker\Impresario\Controller\DownloadController;
use \SuttonBaker\Impresario\Definition\Quote as QuoteDefinition;

/**
 * Class EnquiryListController
 * @package SuttonBaker\Impresario\Controller\Enquiry
 */
class ReportController
    extends DownloadController
{
    protected function getFileName()
    {
        return 'enquiry-report.csv';
    }

    protected function outputFileContent()
    {
        /** @var \SuttonBaker\Impresario\Model\Db\Quote\Collection $enquiryCollection */
        $instanceCollection = $this->getQuoteHelper()->getDisplayQuotes()
        ->joinLeft(
            "{{client}}",
            "{{client}}.client_id={{enquiry}}.client_id",
            ['client_name' => 'client_name']
        )
        ->addOutputProcessors([
            'date_completed' => $this->getDateHelper()->getOutputProcessorShortDate(),
            'target_date' => $this->getDateHelper()->getOutputProcessorShortDate(),
            'date_received' => $this->getDateHelper()->getOutputProcessorShortDate(),
            'status' => $this->getEnquiryHelper()->getStatusOutputProcessor()
        ]);

        $output = fopen("php://output", "w");

        fputcsv($output, QuoteDefinition::REPORT_HEADERS);
        
        foreach($instanceCollection->getItems() as $item){
            $fields = [];
            foreach(QuoteDefinition::REPORT_HEADERS as $key => $header){
                $fields[] = $item->getOutputData($key);
            }
            fputcsv($output, $fields);
        }
        
        fclose($output);
        
    }
}