<?php

namespace App\EventSubscriber\Bulk;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;

final class ListSmsLotsEnvoyeSubscriber extends AbstractController implements EventSubscriberInterface
{
    private $em;
    private $client;
    public    $doctrine;
    public function __construct(EntityManagerInterface $em, HttpClientInterface $client, ManagerRegistry $doctrine)
    {
        $this->em = $em;
        $this->client = $client;
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['operationAfterCreateASms', EventPriorities::POST_WRITE]
        ];
    }

    public function operationAfterCreateASms(ViewEvent $event): void
    {
        $ListSmsContact = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();
        $customerEntityManager = $this->doctrine->getManager('Bulk');
        if ($ListSmsContact instanceof ListSmsContact && Request::METHOD_POST === $method) {

            // if (!empty($ListSmsContact->getContact()) && !empty($ListSmsContact->getListSmsLotsEnvoye())) {
            //     $phone = $ListSmsContact->getContact()->getPhone();
            //     $sms = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getMessage();
            //     $senderId = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getSenderId();
            //     $route = $ListSmsContact->getListSmsLotsEnvoye()->getRouteListSmsLotsEnvoyes()[0]->getRouteId();
            // }
            /*$listSmsLotsEnvoye->setStatus(true);
            $customerEntityManager->persist($listSmsLotsEnvoye);
            $customerEntityManager->flush();*/
        }
        // dd($listSmsLotsEnvoye);
    }
}
