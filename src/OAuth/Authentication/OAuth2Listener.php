<?php


namespace Apitude\User\OAuth\Authentication;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class OAuth2Listener extends AbstractOAuth2Listener
{
    /**
     * This interface must be implemented by firewall listeners.
     *
     * @param GetResponseEvent $event
     */
    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getMethod() === Request::METHOD_OPTIONS) {
            $this->getTokenStorage()->setToken(new AnonymousToken('', 'anonymous', []));
            return;
        }

        if (
            !$request->headers->has('Authorization') ||
            !preg_match('/Bearer (.*)/', $request->headers->get('Authorization'))
        ) {
            $event->setResponse($this->getInvalidRequestResponse());
            return;
        }

        $this->doHandle($event);
    }
}
