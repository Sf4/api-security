<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 14.02.19
 * Time: 9:03
 */

namespace Sf4\ApiSecurity\Response;

use Sf4\Api\Response\AbstractResponse;
use Sf4\ApiSecurity\Dto\Response\AccessDeniedDto;

class AccessDeniedResponse extends AbstractResponse
{

    public function init()
    {
        $errorMessage = 'access_denied';
        $errorMessageTranslation = $this->getRequest()->getRequestHandler()->getTranslator()->trans($errorMessage);

        $this->setResponseDto(
            new AccessDeniedDto(
                $errorMessageTranslation
            )
        );
    }
}
