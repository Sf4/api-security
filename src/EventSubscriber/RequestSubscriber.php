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
use Sf4\ApiSecurity\Response\AccessDeniedResponse;
use Sf4\ApiUser\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sf4\Api\Event\RequestCreatedEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class RequestSubscriber implements EventSubscriberInterface
{

    use UserRightTrait;
    use RepositoryFactoryTrait;

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
        $token = $tokenStorage->getToken();
        if ($token) {
            $user = $token->getUser();
            if ($user) {
                $this->user = $user;
            }
        }
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

            $userRightCodes = $this->getUserRightCodes($this->user);
            return $this->rightCodeIsInRightCodes($route, $userRightCodes);
        }

        return false;
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
