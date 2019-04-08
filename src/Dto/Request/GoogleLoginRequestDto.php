<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 4.04.19
 * Time: 9:26
 */

namespace Sf4\ApiSecurity\Dto\Request;

use Sf4\Api\Dto\Request\AbstractRequestDto;

class GoogleLoginRequestDto extends AbstractRequestDto
{
    protected const ID_TOKEN = 'id_token';

    /** @var string|null $id_token */
    protected $id_token;

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            static::ID_TOKEN => $this->getIdToken()
        ];
    }

    /**
     * @return string|null
     */
    public function getIdToken(): ?string
    {
        return $this->id_token;
    }

    /**
     * @param string|null $id_token
     */
    public function setIdToken(?string $id_token): void
    {
        $this->id_token = $id_token;
    }
}
