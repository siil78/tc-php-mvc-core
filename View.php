<?php

namespace app\core;

class View 
{
    public string $title = '';

    public function renderView($view, $params = []) {
        $viewContent = $this->renderOnlyView($view, $params);
        //získej layout stránek pomocí bufferu
        $layoutContent = $this->layoutContent();
        //nahraď v načtených řetězcích content
        return str_replace('{{content}}', $viewContent, $layoutContent);    
    }

    public function renderContent($viewContent) {
        $layoutContent = $this->layoutContent();
        return str_replace('{{content}}', $viewContent, $layoutContent);
    }

    protected function layoutContent() {
        //výchozí hodnota layoutu
        $layout = Application::$app->layout;
        //získej kontrolerem nastavenou hodnotu layout
        if (Application::$app->controller) {
            $layout = Application::$app->controller->layout;
        }        
        //zapni buffer, výstup include_once se zapíše do bufferu
        ob_start();
        //voláme statickou vlasntost třídy Application, která obsahuje hodnotu kořenového adresáře
        include_once Application::$ROOT_DIR."/views/layouts/$layout.php";
        //vrať obsah bufferu a vyčisti ho
        return ob_get_clean();
    }

    protected function renderOnlyView($view, $params) {
        
        foreach ($params as $key => $value) {
            //z názvu klíče v poli udělej proměnnou
            //řetězec key převede na název proměnné
            $$key = $value;
        }    
        //proměnné jsou díky include_once přístupné i v souboru $view.php
        ob_start();
        include_once Application::$ROOT_DIR."/views/$view.php";
        return ob_get_clean();
    }
}