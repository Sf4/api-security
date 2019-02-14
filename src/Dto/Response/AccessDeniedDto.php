<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 14.02.19
 * Time: 9:06
 */

namespace Sf4\ApiSecurity\Dto\Response;

use Sf4\Api\Dto\Response\ErrorDto;

class AccessDeniedDto extends ErrorDto
{
    public function __construct(string $errorMessage)
    {
        $this->error = $errorMessage;
    }
}
