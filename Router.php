<?php

namespace app\core;

use app\core\exception\NotFoundException;

class Router {

    public Request $request;
    public Response $response;
    //pole registrovaných rout
    protected array $routes = [];

    public function __construct(Request $request, Response $response)
    {   
        $this->request = $request;
        $this->response = $response;
    }

    //zaregistruje routu typu GET
    public function get($path, $callback) {
        $this->routes['get'][$path] = $callback;              
    }

    //zaregistruje routu typu POST
    public function post($path, $callback) {
        $this->routes['post'][$path] = $callback;        
    }

    /**
     * Metoda zjistí z url routu a zavolá controller, případně middleware a view
     *
     * @return void
     */
    public function resolve() {
        //z globální proměnné můžeme získat potřebné info
        //var_dump($_SERVER);
        //to delegujeme na třídu Request, která vrátí ošetřenou hodnotu získanou z globální proměnné
        $path = $this->request->getPath();       
        //získej metodu
        $method = $this->request->method();        
        //získej callback na základě metody a cesty
        $callback = $this->routes[$method][$path] ?? false;
        //pokud callback neexistuje předpokládáme špatně zadanou url
        if ($callback === false) {            
            throw new NotFoundException();
        };

        if (is_string($callback)) {
            return Application::$app->view->renderView($callback);
        };   
        //pokud call_user_func přijímá první argument pole, volá potom třídu a metodu, které jsou v poli definovány
        //protože callback je uložen jako string, musíme ho převést na objekt, aby jsme na něm mohli volat metody
        if (is_array($callback)) {
            //Do vlastnosti třídy Application ulož hodnotu volaného controlleru       
            /**@var \app\core\Controller $controller */  
            $controller = new $callback[0]();
            Application::$app->controller = $controller; 
            //ulož hodnotu volané akce kontroleru
            $controller->action = $callback[1];
            $callback[0] = $controller; 
            foreach ($controller->getMiddlewares() as $middleware) {
                $middleware->execute();
            }
           
        }; 
        //2. argument je parametr volané funkce
        //v našem případě request       
        return call_user_func($callback, $this->request, $this->response);
    }
}