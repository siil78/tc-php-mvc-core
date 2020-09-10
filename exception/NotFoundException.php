<?php

namespace app\core\exception;


/**
 * Třída přetěžuje některé metody PHP třídy Exception
 */
class NotFoundException extends \Exception
{
    protected $message = 'Page not found';
    protected $code = 404;
}