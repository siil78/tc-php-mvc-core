<?php

namespace siil78\phpmvc\middlewares;

use siil78\phpmvc\Application;
use siil78\phpmvc\exception\ForbiddenException;

class AuthMiddleware extends BaseMiddleware

{

    public array $actions = [];

    /**
     * Class constructor.
     */
    public function __construct(array $actions = [])
    {
        $this->actions = $actions;  
    }

    public function execute() 
    {
        if (Application::isGuest()) {
            if (empty($this->actions) || in_array(Application::$app->controller->action, $this->actions)) {
                throw new ForbiddenException();
            }
        }
    }
}