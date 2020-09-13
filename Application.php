<?php

namespace siil78\phpmvc;

use siil78\phpmvc\db\Database;
use siil78\phpmvc\db\DbModel;

class Application {

    const EVENT_BEFORE_REQUEST = 'beforeRequest';
    const EVENT_AFTER_REQUEST = 'afterRequest';

    protected array $eventListeners = [];

    public static string $ROOT_DIR;
    //ukázka použití typovaných vlastností třídy
    //proměnná router je typu Router
    public Router $router;
    public Request $request;
    public Response $response;
    //udělej $app přístupnou staticky odevšad
    public static Application $app;
    //hodnota volaného kontroleru
    public ?Controller $controller = null;
    //databáze
    public Database $db;
    public Session $session;
    //? může být typu DbModel, ale i např. null
    public ?UserModel $user;
    //protože třídy z core by neměli používat třídy mimo core, userClass jsme definovali v $config v index.php
    public ?string $userClass;
    //výchozí hodnota pro layout
    public string $layout = 'main';
    //třída pro tvorbu view. nastaví hlavičky html (title, meta...) a definuje všechny metody ohledně view.
    public View $view;

    public function getController() {
        return $this->controller;
    }

    public function setController(Controller $controller) {
        $this->controller = $controller;
    }

    public function __construct($rootPath, array $config)
    {
        //pro refenci na statické vlastnosti se používá self, protože self se neváže k instanci třídy        
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        //this se váže k instanci třídy
        $this->request = new Request();
        $this->response = new Response();
        $this->session = new Session();
        $this->router = new Router($this->request, $this->response); 
        $this->db = new Database($config['db']);
        $this->view = new View();
        //která třída definuje model uživatele
        $this->userClass = $config['userClass'];
        
        //hodnota primary key ze session
        $primaryValue = $this->session->get('user');
        if ($primaryValue) {
            //jaké pole používá model User pro primary key            
            $primaryKey = $this->userClass::primaryKey();     
            $this->user = $this->userClass::findOne([$primaryKey => $primaryValue]);       
        } else {
            $this->user = null;
        }     
    }

    /**
     * Volá metodu Routeru resolve(). Zachytává případné výjimky, které může
     * vyhodit např. autentikační middleware.
     *
     * @return void
     */
    public function run() {
        //zavolej event
        $this->triggerEvent(self::EVENT_BEFORE_REQUEST);
        //zavoláme metodu resolve
        try {
            echo $this->router->resolve();
        } catch(\Exception $e) {
            //nastav http response kod       
            $this->response->setStatusCode($e->getCode());
            echo $this->view->renderView("_error", [
                'exception' => $e,
            ]);
        };
        
    }

    /**
     * Uloží hodnotu primary key uživatele do session.
     *
     * @param DbModel $user
     * @return void
     */
    public function login(UserModel $user) 
    {
        $this->user = $user;
        $primaryKey = $user->primaryKey();
        $primaryValue = $user->{$primaryKey};        
        $this->session->set('user', $primaryValue);       

        return true;
    }
    
    /**
     * Odhlásí uživatele. 
     * Nastaví vlastnost user třídy Application na null a odebere klíč user ze session.
     *
     * @return void
     */
    public function logout() {
        $this->user = null;
        $this->session->remove('user');
    }

    public static function isGuest()
    {
        return !self::$app->user;
    }

    /**
     * Metoda registruje událost a k ní callback
     *
     * @param string $eventName
     * @param function $callback
     * @return void
     */
    public function on($eventName, $callback)
    {
        $this->eventListeners[$eventName][] = $callback;
    }

    /**
     * Volání funkce přiřazené určité události. 
     *
     * @param string $eventName
     * @return void
     */
    public function triggerEvent($eventName) 
    {
        $callbacks = $this->eventListeners[$eventName] ?? [];

        foreach ($callbacks as $callback) {
            call_user_func($callback);
        }
    }
}