<?php

namespace app\core;

/**
 * abstract zabrání vytvoření třídy jako instance
 * může být tedy volána jen potomky
 */
abstract class Model {

    public const RULE_REQUIRED = 'required';
    public const RULE_EMAIL = 'email';
    public const RULE_MIN = 'min';
    public const RULE_MAX = 'max';
    public const RULE_MATCH = 'match';
    public const RULE_UNIQUE = 'unique';
    public array $errors = [];

    
    /**
     * Abstraktní metoda musí být implementována v dědících třídách. Definuje
     * pravidla pro formuláře. Dostupná pravidla: RULE_REQUIRED, RULE_EMAIL,
     * RULE_MIN, RULE_MAX, RULE_MATCH, RULE_UNIQUE.
     *
     * @return array pole ve tvaru [ 'key' => [self::RULE_...] ], kde 'key' je
     * pole formuláře a hodnota je požadované pravidlo.
     */
    abstract public function rules(): array;

    /**
     * Metoda pro definování štítků atributů. 
     * Může být přetíženo u modelu (např. User, ContactForm) a pak použito u 
     * formulářového pole - jednotlivým atributům přiřadíme štítky a tak i 
     * vstupním polím formuláře. 
     *
     * @return array
     */
    public function labels(): array
    {
        return [];
    }

    public function getLabel($attribute) {
        return $this->labels()[$attribute] ?? $attribute;
    }

    public function loadData($data) {

        foreach ($data as $key => $value) {
            //zkontroluje jestli volající dědicí model obsahuje dodané vlastnosti
            if (property_exists($this, $key)) {                
                $this->{$key} = $value;
            }
        }

    }    

    public function hasError($attribute) {
        /**
         * null coallescing operator ?? https://www.php.net/manual/en/migration70.new-features.php
         */
        return $this->errors[$attribute] ?? false;
    }

    public function getFirstError($attribute) {        
        return $this->errors[$attribute][0] ?? false;
    }

    public function validate() {
        //procházej pole pravidel pro formulář
        foreach ($this->rules() as $attribute => $rules) {
            //přiřaď hodnotu pole z formuláře
            $value = $this->{$attribute};
            foreach ($rules as $rule) {
                //zjisti název pravidla
                $ruleName = $rule;
                //zjisti název pravidla s paramatrem jako druhý index pole
                if (!is_string($rule)) {
                    $ruleName = $rule[0];
                }
                //vyhodnocení pravidel formuláře
                //pokud není vyplněno pole formuláře
                if ($ruleName === self::RULE_REQUIRED && !$value) {
                    $this->addErrorForRule($attribute, self::RULE_REQUIRED);
                }
                //email není platné pole pro email
                if ($ruleName === self::RULE_EMAIL && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->addErrorForRule($attribute, self::RULE_EMAIL);
                }
                //minimální delka znaků pole
                if ($ruleName == self::RULE_MIN && strlen($value) < $rule['min']) {
                    $this->addErrorForRule($attribute, self::RULE_MIN, $rule);
                }
                //maximální délka znaků pole
                if ($ruleName == self::RULE_MAX && strlen($value) > $rule['max']) {
                    $this->addErrorForRule($attribute, self::RULE_MAX, $rule);
                }  
                //hodnoty si odpovídají
                if ($ruleName == self::RULE_MATCH && $value !== $this->{$rule['match']}) {
                    $rule['match'] = $this->getLabel($rule['match']);
                    $this->addErrorForRule($attribute, self::RULE_MATCH, $rule);
                }     
                //unikátní hodnoty
                if ($ruleName == self::RULE_UNIQUE) {
                    $className = $rule['class']; //např. string 'app\models\User' (length=15)
                    //jaký atribut chceme kontrolovat
                    $uniqueAttr = $rule['attribute'] ?? $attribute;
                    //získej jméno tabulky třídy
                    $tableName = $className::tableName();
                    //dotaz na db
                    $statement = Application::$app->db->prepare("SELECT * FROM $tableName WHERE $uniqueAttr = :attr");
                    $statement->bindValue(":attr", $value);
                    $statement->execute();
                    $record = $statement->fetchObject();
                    if ($record) {
                        $this->addErrorForRule($attribute, self::RULE_UNIQUE, ['field' => $this->getLabel($attribute)]);
                    }
                }                           
            }
        }

        //Vrací bool jestli exitstují chyby
        return empty($this->errors);
    }

    //Do pole errors přiřad k atributu formuláře (což je název pole) zprávu podle chyby 
    //třetí argument definuje dynamické parametry chybové zprávy
    private function addErrorForRule(string $attribute, string $rule, $params = []) {
        $message = $this->errorMessages()[$rule] ?? '';
        //nahraď ve zprávě token hodnotou 
        foreach ($params as $key => $value) {
            $message = str_replace("{".$key."}", $value, $message);
        }
        $this->errors[$attribute][] = $message;
    }

    /**
     * k atributu formuláře přiřaď libovolnou chybovou zprávu
     *
     * @param string $attribute
     * @param string $message
     * @return void
     */
    public function addError(string $attribute, string $message) {
        $this->errors[$attribute][] = $message;
    }

    //Pole zpráv podle typu chyby
    public function errorMessages() {
        return [
            self::RULE_REQUIRED => 'This field is required',
            self::RULE_EMAIL => 'This field must be valid email address',
            self::RULE_MIN => 'Min length of this field must be {min}',
            self::RULE_MAX => 'Max length of this field must be {max}',
            self::RULE_MATCH => 'This field must be the same as {match}',
            self::RULE_UNIQUE => 'Record with this {field} already exists!'
        ];
    }
}

?>