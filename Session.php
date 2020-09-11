<?php

namespace siil78\phpmvc;

class Session  {

    protected const FLASH_KEY = 'flash_messages';
    
    /**
     * Class constructor.
     */
    public function __construct()
    {
        session_start();

        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        //s $flashMessage je třeba pracovat jako s referencí, jinak nebude změna zapsána
        foreach ($flashMessages as $key => &$flashMessage) {
            //Označ flash zprávy, které budou samzány
            $flashMessage['remove'] = true;
        }
        //přiřad změněné $flashMessages do $_SESSION
        $_SESSION[self::FLASH_KEY] = $flashMessages;    
    }

    /**
     * Class destructor.
     */
    public function __destruct()
    {
        //Smaž flash zprávy
        $flashMessages = $_SESSION[self::FLASH_KEY] ?? [];
        foreach ($flashMessages as $key => &$flashMessage) {
            if ($flashMessage['remove']) {
                //odeber z pole zprávy s daným klíčem
                unset($flashMessages[$key]);
            }
        }

        $_SESSION[self::FLASH_KEY] = $flashMessages;
    }

    public function set($key, $value) 
    {        
        $_SESSION[$key] = $value;
        var_dump($_SESSION['user']);
    }

    public function get($key) 
    {
        return $_SESSION[$key] ?? false;
    }

    public function remove($key) 
    {
        unset($_SESSION[$key]);
    }
 
    public function setFlash($key, $message) 
    {

        //do globální proměnné ulož druh zprávy a zprávu
        $_SESSION[self::FLASH_KEY][$key] = [
            'remove' => false,
            'value' => $message
        ];
    }

    public function getFlash($key) 
    {
        return $_SESSION[self::FLASH_KEY][$key]['value'] ?? false;
    }
}