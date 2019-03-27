<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 27.03.19
 * Time: 9:30
 */

namespace Sf4\ApiSecurity\Response;

use Sf4\Api\Response\AbstractResponse;
use Sf4\ApiSecurity\Dto\Response\GoogleLoginResponseDto;

class GoogleLoginResponse extends AbstractResponse
{

    public function init()
    {
        $dto = new GoogleLoginResponseDto();
        $this->populateResponseDto($dto);
        $this->setResponseDto($dto);
    }

    protected function populateResponseDto(GoogleLoginResponseDto $dto): void
    {
        $data = [
            'id' => '1234',
            'name' => 'Test',
            'email' => 'email',
            'image' => 'Image'
        ];
        $this->populateDto($dto, $data);
    }
}
