<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 15.02.19
 * Time: 9:50
 */

namespace Sf4\ApiSecurity\Entity;

use Sf4\Api\Request\DefaultRequest;
use Sf4\Api\Request\SiteRequest;
use Sf4\ApiSecurity\Request\GoogleLoginRequest;

interface UserRightInterface
{

    public const RIGHT_API_SITE = SiteRequest::ROUTE;
    public const RIGHT_API_DEFAULT = DefaultRequest::ROUTE;
    public const RIGHT_API_SECURITY_GOOGLE_LOGIN = GoogleLoginRequest::ROUTE;

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
    public function getSite(): ?string;

    /**
     * @param string $site
     */
    public function setSite(?string $site): void;
}
