<?php

namespace SuttonBaker\Impresario\Form;

use \SuttonBaker\Impresario\Definition\Quote as QuoteDefinition;
/**
 * Class QuoteConfigurator
 * @package SuttonBaker\Impresario\Form\Rules
 */
class QuoteConfigurator
    extends \DaveBaker\Form\Validation\Rule\Configurator\Base
    implements \DaveBaker\Form\Validation\Rule\Configurator\ConfiguratorInterface
{
    /**
     * @throws \DaveBaker\Core\Object\Exception
     * @throws \DaveBaker\Form\Validation\Rule\Configurator\Exception
     */
    protected function _collate()
    {
        $this->addRule(
            $this->createRule('DateCompare\Past', 'date_received', 'Date Received')
        );

        $this->addRule(
            $this->createRule('Required', 'project_name', 'Project Name')
        );

        $this->addRule(
            $this->createRule('Required', 'site_name', 'Site Name')
        );
        $this->addRule(
            $this->createRule('Required', 'client_requested_by', 'Client Requested By')
        );

        $this->addRule(
            $this->createRule('Required', 'client_reference', 'Client Reference')
        );

        $this->addRule(
            $this->createRule('Date', 'date_required', 'Required By Date')
        );

        $this->addRule(
            $this->createRule('User', 'project_manager_id', 'Project Manager')
        );

        $this->addRule(
            $this->createRule('User', 'estimator_id', 'Estimator')
        );

        $this->addRule(
            $this->createRule('Date', 'date_return_by', 'Return By Date')
        );

        $this->addRule(
            $this->createRule('Numeric', 'net_cost', 'Net Cost')
        );

        $this->addRule(
            $this->createRule('Numeric', 'net_sell', 'Net Sell')
        );

        if($this->getValue('date_returned')) {
            $this->addRule(
                $this->createRule('DateCompare\Past', 'date_returned', 'Date Returned')
            );
        }

        if($this->getValue('date_completed')) {
            $this->addRule(
                $this->createRule('DateCompare\Past', 'date_completed', 'Date Completed')
            );
        }

        if($this->getValue('completed_by_id')) {
            $this->addRule(
                $this->createRule('User', 'completed_by_id', 'Completed By')
            );
        }

        // Conditional Rules
        $dateCompleted = $this->getValue('date_completed');
        $completedById = $this->getValue('completed_by_id');
        $statusIsClosed = $this->getValue('status') !== QuoteDefinition::STATUS_OPEN;

        if($statusIsClosed || $this->getValue('date_completed')){
            $this->addRule(
                $this->createRule('User', 'completed_by_id', 'Completed By')
            );
        }

        if($statusIsClosed || $this->getValue('completed_by_id')){
            $this->addRule(
                $this->createRule('DateCompare\Past', 'date_completed', 'Date Completed')
            );
        }

        if($this->getValue('completed_by_id') || $this->getValue('date_completed')){
            $statusRule = $this->createRule('Custom', 'status', 'Status');
            $statusRule->setMainError('Status must not be \'Open\' if \'Completed By\' or \'Date Completed\' have been set')
                ->setInputError('This must be set to \'Complete\'');

            $this->addRule($statusRule->setValidationMethod(
                function($value, $ruleInstance) use($statusIsClosed) {

                    if($statusIsClosed == false){
                        return $ruleInstance->createError();
                    }

                    return true;
                }
            ));
        }

        $netCost = $this->getValue('net_cost');
        $netSell = $this->getValue('net_sell');

        $sellRule = $this->createRule('Custom', 'net_sell', 'Net Sell');
        $sellRule->setMainError('\'{{niceName}}\' cannot be lower than \'Net Cost\'')
            ->setInputError('This must be higher than Net Cost');

        $this->addRule($sellRule->setValidationMethod(
            function($value, $ruleInstance) use($netCost) {

                if((float) $value < (float) $netCost){
                    return $ruleInstance->createError();
                }

                return true;
            }
        ));

    }

}