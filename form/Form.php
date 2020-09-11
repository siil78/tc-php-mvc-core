<?php

namespace siil78\phpmvc\form;

use siil78\phpmvc\Model;

class Form {

    //metoda inicializujem html formuláře a vrátí instanci třídy Form
    public static function begin($action, $method)
    {
        echo sprintf('<form action="%s" method="%s">', $action, $method);

        return new Form();
    }

    public static function end()
    {
        echo '</form>';
    }

    public function field(Model $model, $attribute) {

        return new InputField($model, $attribute);
    }
}


?>