<?php
/**
 * Created by PhpStorm.
 * User: john
 * Date: 27.03.19
 * Time: 9:29
 */

namespace Sf4\ApiSecurity\Request;

use Sf4\Api\Request\AbstractRequest;
use Sf4\ApiSecurity\Response\GoogleLoginResponse;
use Sf4\ApiUser\CacheAdapter\CacheKeysInterface;

class GoogleLoginRequest extends AbstractRequest
{
    public const ROUTE = 'sf4_api_security_google_login';

    public function __construct()
    {
        $this->init(
            new GoogleLoginResponse()
        );
    }

    /**
     * @param \Closure $closure
     * @param string|null $cacheKey
     * @param array $tags
     * @param int|null $expiresAfter
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function getCachedResponse(
        \Closure $closure,
        string $cacheKey = null,
        array $tags = [],
        int $expiresAfter = null
    ) {
        if (null !== $cacheKey) {
            $cacheKey = null;
        }
        parent::getCachedResponse($closure, $cacheKey, $tags, $expiresAfter);
    }

    /**
     * @return array
     */
    protected function getCacheTags(): array
    {
        return [
            CacheKeysInterface::TAG_USER,
            CacheKeysInterface::TAG_USER_DETAIL
        ];
    }
}
