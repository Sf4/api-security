<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 19.02.19
 * Time: 8:43
 */

namespace Sf4\ApiSecurity\EventSubscriber\Traits;

use Sf4\Api\Repository\RepositoryFactory;
use Sf4\Api\Request\RequestInterface;
use Sf4\ApiSecurity\Repository\UserRightRepository;
use Sf4\ApiSecurity\CacheAdapter\CacheKeysInterface;
use Sf4\ApiUser\Entity\UserInterface;

trait UserRightTrait
{
    /**
     * @param string $rightCode
     * @param array $rightCodes
     * @return bool
     */
    protected function rightCodeIsInRightCodes(string $rightCode, array $rightCodes): bool
    {
        foreach ($rightCodes as $right) {
            foreach ($right as $code) {
                if ($code === $rightCode) {
                    return true;
                }
            }
        }

        return false;
    }

    abstract public function getRepositoryFactory(): ?RepositoryFactory;

    /**
     * @param UserInterface $user
     * @param RequestInterface $request
     * @return array
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getUserRightCodes(UserInterface $user, RequestInterface $request): array
    {
        $requestHandler = $request->getRequestHandler();
        if (!$requestHandler) {
            return [];
        }

        return $requestHandler->getCacheDataOrAdd(
            CacheKeysInterface::KEY_USER_RIGHT_CODES . $user->getId(),
            function () use ($user) {
                $repositoryFactory = $this->getRepositoryFactory();
                if ($repositoryFactory) {
                    $repository = $repositoryFactory->create(
                        UserRightRepository::TABLE_NAME
                    );
                    if ($repository instanceof UserRightRepository) {
                        return $repository->getUserRights($user);
                    }
                }

                return [];
            },
            [
                CacheKeysInterface::TAG_USER_RIGHT
            ]
        );
    }
}
