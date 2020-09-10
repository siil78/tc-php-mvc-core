<?php

namespace app\core\form;

use app\core\Model;

abstract class BaseField 
{

    public Model $model;
    public string $attribute;  


    /**
     * Class constructor.
     */
    public function __construct(Model $model, string $attribute)
    {
        
        $this->model = $model;
        $this->attribute = $attribute;
    }

    abstract public function renderInput(): string;


    /**
     * Metoda převede pole formuláře na html řetězec.
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('
            <div class="form-group">
                <label>%s</label>
                %s
                <div class="invalid-feedback">
                %s
                </div>
            </div>    
        ', 
            //vykresli štítek pole
            //pokud není definován, tak jméno atributu
            $this->model->getLabel($this->attribute),      
            $this->renderInput(),      
            $this->model->getFirstError($this->attribute)
        );
    }
}