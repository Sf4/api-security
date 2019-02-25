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
use Sf4\ApiSecurity\Repository\UserRepository;
use Sf4\ApiSecurity\Response\AccessDeniedResponse;
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
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function isGranted(RequestInterface $request): bool
    {
        $route = $request->getRoute();
        $user = $this->getCurrentUser($request);

        if (!$user) {
            $user = $this->getAnonymousUser();
        }

        if ($route && $user) {
            $roles = $user->getRoles();
            if (in_array(UserRoleInterface::ROLE_SUPER_ADMIN, $roles)) {
                return true;
            }

            $userRightCodes = $this->getUserRightCodes($user);
            return $this->rightCodeIsInRightCodes($route, $userRightCodes);
        }

        return false;
    }

    /**
     * @return UserInterface|null
     */
    protected function getAnonymousUser(): ?UserInterface
    {
        /** @var UserRepository $userRepository */
        $userRepository = $this->getRepositoryFactory()->create(
            UserRepository::TABLE_NAME
        );

        return $userRepository->getAnonymousUser();
    }

    /**
     * @param RequestInterface $request
     * @return UserInterface|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function getCurrentUser(RequestInterface $request): ?UserInterface
    {
        $token = $request->getRequest()->attributes->get(static::REQUEST_ATTRIBUTE);
        if (!$token) {
            return null;
        }

        $cacheAdapter = $request->getRequestHandler()->getCacheAdapter();
        $cacheItem = $cacheAdapter->getItem('user_by_token_' . $token);
        if ($cacheItem->isHit()) {
            $user = $cacheItem->get();
        } else {
            /** @var UserRepository $userRepository */
            $userRepository = $this->getRepositoryFactory()->create(
                UserRepository::TABLE_NAME
            );

            $user = $userRepository->getUserByToken($token);

            $cacheItem->set($user);
            $cacheItem->expiresAfter(static::CURRENT_USER_CACHE_TIME);

            $cacheAdapter->save($cacheItem);
        }

        return $user;
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
