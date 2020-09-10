<?php

namespace app\core\exception;


/**
 * Třída přetěžuje některé metody PHP třídy Exception
 */
class ForbiddenException extends \Exception
{
    protected $message = 'You don\'t have permission to acces this page';
    protected $code = 403;
}