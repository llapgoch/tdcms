<?php

namespace SuttonBaker\Impresario\Block\Form;

abstract class Filter  
extends \DaveBaker\Core\Block\Template
{
    /** @var \DaveBaker\Form\Block\Label */
    protected $label;
    /** @string */
    protected $setFormName = '';
    /** @string */
    protected $formName = '';

    public abstract function getMainElement();
    
    /**
     *
     * @return \DaveBaker\Form\Block\Label
     */
    public function getLabel()
    {
        if(!$this->label){
            $this->label = $this->createBlock(
                \DaveBaker\Form\Block\Label::class,
                null,
                'label'
            );

            $this->addChildBlock($this->label);
        }

        return $this->label;
    }

    /**
     *
     * @param string $text
     * @return $this
     */
    public function setLabelName($text)
    {
        $this->getLabel()->setLabelName($text);
        return $this;
    }

    public function setFormName($name)
    {
        $this->formName = $name;
        return $this;
    }

    public function applyFormNameToElements()
    {
        $name = $this->setFormName . "[" . $this->formName ."]";
        $id = $this->setFormName . "_" . $this->formName;

        $this->getMainElement()->setElementName(
            $name    
        );

        $this->getMainElement()->addAttribute(['id' => $id]);

        $this->getLabel()->setForId($id);

        return $this;
    }

    /**
     *
     * @param string $setFormName
     * @return $this
     */
    public function setSetFormName($setFormName)
    {
        $this->setFormName = $setFormName;
        $this->applyFormNameToElements();
        return $this;
    }

    protected function _preRender()
    {
        if(!$this->formName || !$this->setFormName){
            throw new \Exception('Set name or set form name not set');
        }
        $this->getLabel();
        $this->getMainElement();
        $this->applyFormNameToElements();
    }
}