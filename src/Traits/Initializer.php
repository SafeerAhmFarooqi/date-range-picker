<?php
  namespace Saf\DateRangePicker\Traits;
  
  trait Initializer {

    public function initialize(){
        $this->setInputAttributes();
        $this->initializeDates();
        $this->getLivewireHoverData($this->initialMonthYear);
    }

    public function setInputAttributes(){
        $this->filterInputAttributes('wire:model','type');
        $this->removeDuplicationFromInputAttributes();
        $this->setInputAttribute('placeholder','Please Select Date',true);
    }

    public function filterInputAttributes(...$attributes){
        $inputAttributes = collect();
        foreach ($this->inputAttributes as $key => $attribute) {
            //These attributes will be removed
            if (in_array(strtolower($key),$attributes)
            || is_array($attribute)
            && strtolower($key) != 'dates') {
                continue;
            }

            $inputAttributes->put($key , $attribute);
        }
        $this->inputAttributes = $inputAttributes->toArray();
    }

        
  
    public function removeDuplicationFromInputAttributes(){
        $inputAttributes = collect();
        $keys = [];
        foreach ($this->inputAttributes as $key => $attribute) {
            if (!in_array(strtolower($key),$keys)) {
                $inputAttributes->put($key , $attribute);
                $keys[] = strtolower($key);
            }
        }
        $this->inputAttributes = $inputAttributes->toArray();
    }

    public function setInputAttribute( $key,$value,$toLower = false){
        if ($toLower) {
            foreach ($this->inputAttributes as $keyAttribute => $inputAttribute) {
                if ( strtolower($keyAttribute) == strtolower($key) ) {
                    unset($this->inputAttributes[$keyAttribute]);
                    $this->inputAttributes[strtolower($keyAttribute)] = $inputAttribute;
                    break;
                }
            }
        }

        $isKeyExists = false;

        foreach ($this->inputAttributes as $keyAttribute => $inputAttribute) {
            if ( strtolower($keyAttribute) == strtolower($key) ) {
                $isKeyExists = true;
                break;
            }
        }

        if (!$isKeyExists) {
            $this->inputAttributes[$toLower ? strtolower($key) : $key] = $value;
        }

        if ($isKeyExists) {
            foreach ($this->inputAttributes as $keyAttribute => $inputAttribute) {
                if ( strtolower($keyAttribute) == strtolower($key) ) {
                    if(!$this->inputAttributes[$keyAttribute]){
                        $this->inputAttributes[$keyAttribute] = $value;
                    }
                }
            }
        }
    }

  }