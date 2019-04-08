<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 2.04.19
 * Time: 8:24
 */

namespace Sf4\ApiSecurity\Exception;

use Exception;

class InvalidGoogleTokenException extends Exception
{
    public const TRANSLATION_MESSAGE_KEY = 'error.invalid_google_token';
}
