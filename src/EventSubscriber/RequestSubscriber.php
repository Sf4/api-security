<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 14.02.19
 * Time: 8:56
 */

namespace Sf4\ApiSecurity\EventSubscriber;

use Sf4\Api\Repository\RepositoryFactory;
use Sf4\Api\Request\RequestInterface;
use Sf4\Api\RequestHandler\Traits\RepositoryFactoryTrait;
use Sf4\ApiSecurity\Entity\UserRoleInterface;
use Sf4\ApiSecurity\EventSubscriber\Traits\UserRightTrait;
use Sf4\ApiUser\Repository\UserRepository;
use Sf4\ApiSecurity\Response\AccessDeniedResponse;
use Sf4\ApiUser\CacheAdapter\CacheKeysInterface;
use Sf4\ApiUser\Entity\UserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sf4\Api\Event\RequestCreatedEvent;

class RequestSubscriber implements EventSubscriberInterface
{

    use UserRightTrait;
    use RepositoryFactoryTrait;

    const REQUEST_ATTRIBUTE = 'token';
    const CURRENT_USER_CACHE_TIME = 600;

    /** @var RepositoryFactory $repositoryFactory */
    protected $repositoryFactory;

    /**
     * RequestSubscriber constructor.
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(RepositoryFactory $repositoryFactory)
    {
        $this->setRepositoryFactory($repositoryFactory);
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            RequestCreatedEvent::NAME => 'handleRequestCreated'
        ];
    }

    /**
     * @param RequestCreatedEvent $event
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function handleRequestCreated(RequestCreatedEvent $event)
    {
        $request = $event->getRequest();

        if (false === $this->isGranted($request)) {
            $this->setAccessDeniedResponse($event);
        }
    }

    /**
     * @param RequestInterface $request
     * @return bool
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function isGranted(RequestInterface $request): bool
    {
        $route = $request->getRoute();
        $user = $this->getCurrentUser($request);

        if (!$user) {
            $user = $this->getAnonymousUser($request);
        }

        if ($route && $user) {
            $roles = $user->getRoles();
            if (in_array(UserRoleInterface::ROLE_SUPER_ADMIN, $roles)) {
                return true;
            }

            $userRightCodes = $this->getUserRightCodes($user, $request);
            return $this->rightCodeIsInRightCodes($route, $userRightCodes);
        }

        return false;
    }

    /**
     * @param RequestInterface $request
     * @return UserInterface|null
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getAnonymousUser(RequestInterface $request): ?UserInterface
    {
        return $request->getRequestHandler()->getCacheDataOrAdd(
            'anonymous_user',
            function () {
                /** @var UserRepository $userRepository */
                $userRepository = $this->getRepositoryFactory()->create(
                    UserRepository::TABLE_NAME
                );

                return $userRepository->getUserByRole(UserRoleInterface::ROLE_ANONYMOUS);
            },
            [
                CacheKeysInterface::TAG_USER
            ],
            null
        );
    }

    /**
     * @param RequestInterface $request
     * @return UserInterface|null
     * @throws \Psr\Cache\CacheException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getCurrentUser(RequestInterface $request): ?UserInterface
    {
        $token = $request->getRequest()->attributes->get(static::REQUEST_ATTRIBUTE);
        if (!$token) {
            return null;
        }

        return $request->getRequestHandler()->getCacheDataOrAdd(
            'user_by_token_' . $token,
            function () use ($token) {
                /** @var UserRepository $userRepository */
                $userRepository = $this->getRepositoryFactory()->create(
                    UserRepository::TABLE_NAME
                );

                return $userRepository->getUserByToken($token);
            },
            [
                CacheKeysInterface::TAG_USER,
                CacheKeysInterface::TAG_USER_DETAIL
            ],
            null
        );
    }

    /**
     * @param RequestCreatedEvent $event
     */
    protected function setAccessDeniedResponse(RequestCreatedEvent $event)
    {
        $request = $event->getRequest();
        $response = new AccessDeniedResponse();
        $response->setRequest($request);
        $response->init();
        $event->setResponse($response);
    }
}
