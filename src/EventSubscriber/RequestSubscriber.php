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
use Sf4\ApiSecurity\Entity\UserRoleInterface;
use Sf4\ApiSecurity\Repository\UserRightRepository;
use Sf4\ApiSecurity\Response\AccessDeniedResponse;
use Sf4\ApiUser\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sf4\Api\Event\RequestCreatedEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{

    /** @var User $user */
    protected $user;

    /** @var RepositoryFactory $repositoryFactory */
    protected $repositoryFactory;

    /**
     * RequestSubscriber constructor.
     * @param TokenStorageInterface $tokenStorage
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(TokenStorageInterface $tokenStorage, RepositoryFactory $repositoryFactory)
    {
        $user = $tokenStorage->getToken()->getUser();
        if ($user) {
            $this->user = $user;
        }
        $this->repositoryFactory = $repositoryFactory;
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
     */
    protected function isGranted(RequestInterface $request): bool
    {
        $route = $request->getRoute();

        if ($route && $this->user) {
            $roles = $this->user->getRoles();
            if (in_array(UserRoleInterface::ROLE_SUPER_ADMIN, $roles)) {
                return true;
            }

            $userRightCodes = $this->getUserRightCodes();
            return $this->hasRight($route, $userRightCodes);
        }

        return false;
    }

    /**
     * @param string $rightCode
     * @param array $rightCodes
     * @return bool
     */
    protected function hasRight(string $rightCode, array $rightCodes): bool
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

    /**
     * @return array
     */
    protected function getUserRightCodes(): array
    {
        $repository = $this->repositoryFactory->create(
            UserRightRepository::TABLE_NAME
        );
        if ($repository instanceof UserRightRepository) {
            return $repository->getUserRights($this->user);
        }

        return [];
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
