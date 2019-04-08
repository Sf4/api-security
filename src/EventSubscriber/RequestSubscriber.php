<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 14.02.19
 * Time: 8:56
 */

namespace Sf4\ApiSecurity\EventSubscriber;

use Psr\Cache\CacheException;
use Psr\Cache\InvalidArgumentException;
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

    public const REQUEST_ATTRIBUTE = 'token';
    public const CURRENT_USER_CACHE_TIME = 600;

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
    public static function getSubscribedEvents(): array
    {
        return [
            RequestCreatedEvent::NAME => 'handleRequestCreated'
        ];
    }

    /**
     * @param RequestCreatedEvent $event
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    public function handleRequestCreated(RequestCreatedEvent $event): void
    {
        $request = $event->getRequest();

        if ($request && false === $this->isGranted($request)) {
            $this->setAccessDeniedResponse($event);
        }
    }

    /**
     * @param RequestInterface $request
     * @return bool
     * @throws CacheException
     * @throws InvalidArgumentException
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
            if (in_array(UserRoleInterface::ROLE_SUPER_ADMIN, $roles, true)) {
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
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    protected function getAnonymousUser(RequestInterface $request): ?UserInterface
    {
        $requestHandler = $request->getRequestHandler();
        if ($requestHandler) {
            return $requestHandler->getCacheDataOrAdd(
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
                ]
            );
        }

        return null;
    }

    /**
     * @param RequestInterface $request
     * @return UserInterface|null
     * @throws CacheException
     * @throws InvalidArgumentException
     */
    protected function getCurrentUser(RequestInterface $request): ?UserInterface
    {
        $token = $request->getRequest()->attributes->get(static::REQUEST_ATTRIBUTE);
        $requestHandler = $request->getRequestHandler();
        if (!$token || !$requestHandler) {
            return null;
        }

        return $requestHandler->getCacheDataOrAdd(
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
            ]
        );
    }

    /**
     * @param RequestCreatedEvent $event
     */
    protected function setAccessDeniedResponse(RequestCreatedEvent $event): void
    {
        $request = $event->getRequest();
        $response = new AccessDeniedResponse();
        $response->setRequest($request);
        $response->init();
        $event->setResponse($response);
    }
}
