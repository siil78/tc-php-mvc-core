<?php

namespace siil78\phpmvc;

use siil78\phpmvc\middlewares\BaseMiddleware;

/**
 * Třída pro zjednodušení přístupu k vykreslení view
 */

class Controller {

    //hodnota pro změnu layoutu stránky
    public string $layout = 'main';
    //drží hodnotu volanou při registraci routy jako index 1
    public string $action = '';
    /**
     * @var app/core/middlewares/BaseMiddleware[]
     */
    protected array $middlewares = [];

    public function render($view, $params = []) {
        return Application::$app->view->renderView($view, $params);
    }

    //metoda pro nastavení layoutu
    public function setLayout($layout) {
        $this->layout = $layout;
    }

    public function registerMiddleware(BaseMiddleware $middleware) {
        $this->middlewares[] = $middleware;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }
}

?>