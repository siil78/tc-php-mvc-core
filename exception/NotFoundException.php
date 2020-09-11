<?php

namespace siil78\phpmvc\exception;


/**
 * Třída přetěžuje některé metody PHP třídy Exception
 */
class NotFoundException extends \Exception
{
    protected $message = 'Page not found';
    protected $code = 404;
}