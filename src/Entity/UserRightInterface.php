<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 15.02.19
 * Time: 9:50
 */

namespace Sf4\ApiSecurity\Entity;

interface UserRightInterface
{

    const RIGHT_API_SITE = 'api_site';

    /**
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void;
}
