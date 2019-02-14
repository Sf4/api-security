<?php
/**
 * Created by PhpStorm.
 * User: siim
 * Date: 14.02.19
 * Time: 8:56
 */

namespace Sf4\ApiSecurity\EventSubscriber;

use Sf4\ApiSecurity\Response\AccessDeniedResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Sf4\Api\Event\RequestCreatedEvent;

class RequestSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            RequestCreatedEvent::NAME => 'handleRequestCreated'
        ];
    }

    protected function handleRequestCreated(RequestCreatedEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->getRoute();

//        if ($route) {
            $response = new AccessDeniedResponse();
            $event->setResponse($response);
//        }
    }
}
