<?php


namespace App\EventListener\Auth;

use App\Entity\Auth\Client;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTCreatedEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTAuthenticatedEvent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;

class JWTListener extends AbstractController
{

    private $security;

    /**
     * JWTCreatedListener constructor.
     */
    public function __construct()
    {
    }

    public function onJWTCreated(JWTCreatedEvent $event)
    {
        $listFonction = [];


        /** @var Client $user */
        $user = $event->getUser();
        $payload = $event->getData();
        $payload['id'] = $user->getId();
        $payload['nom'] = $user->getNom();
        $payload['prenom'] = $user->getPrenom();
        $payload['keySecret'] = $user->getKeySecret();
        $payload['profile'] =
        $user->getProfile()  ? '/images/client/' . $user->getProfile()->getFilePath() : "null";
        $payload['idRole'] = $user->getRole()->getId();
        if ($user->getRole()) {
            foreach ($user->getRole()->getListRoleFonctions()  as $lr) {
                array_push($listFonction, $lr->getFonction()->getId());
            }
        }
        $payload['fonctions'] =
            $listFonction;

        // $payload['id'] = $user->getId();
        $event->setData($payload);
    }

    public function onJWTAuthenticated(JWTAuthenticatedEvent $event)
    {
        $token = $event->getToken();
        $payload = $event->getPayload();
        $token->setAttribute('id', $payload['id']);
        $token->setAttribute('nom', $payload['nom']);
        $token->setAttribute('prenom', $payload['prenom']);
        $token->setAttribute('email', $payload['email']);
        $token->setAttribute('keySecret', $payload['keySecret']);
        $token->setAttribute('idRole', $payload['idRole']);
        $token->setAttribute('profile', $payload['profile']);
        $token->setAttribute('fonctions', $payload['fonctions']);
    }

    public function onAuthenticationSuccessResponse(AuthenticationSuccessEvent $event)
    {
        $data = $event->getData();
        $user = $event->getUser();

        if (!$user instanceof UserInterface) {
            return;
        }

        $event->setData($data);
    }
}
