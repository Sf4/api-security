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
    const RIGHT_API_DEFAULT = 'api_default';

    /**
     * @return string|null
     */
    public function getCode(): ?string;

    /**
     * @param string|null $code
     */
    public function setCode(?string $code): void;

    /**
     * @return string
     */
    public function getSite(): string;

    /**
     * @param string $site
     */
    public function setSite(string $site): void;
}
