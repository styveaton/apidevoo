<?php

namespace App\EventSubscriber\Licence;

use ApiPlatform\Core\EventListener\EventPriorities;

use App\Entity\Bulk\ListSmsLotsEnvoye;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Licence\ListSmsManipule;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;

final class ListSmsManipuleSubscriber extends AbstractController implements EventSubscriberInterface
{
    private $em;
    private $client;
    public    $doctrine;
    public function __construct(EntityManagerInterface $em, HttpClientInterface $client,  ManagerRegistry $doctrine)
    {
        $this->em = $em;
        $this->client = $client;
        $this->doctrine = $doctrine;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['operationAfterCreateListSmsManipule', EventPriorities::POST_WRITE]
        ];
    }

    public function operationAfterCreateListSmsManipule(ViewEvent $event): void
    {
        $ListSmsManipule = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        $customerEntityManager = $this->doctrine->getManager('Licence');

        if ($ListSmsManipule instanceof ListSmsManipule && Request::METHOD_POST === $method) {
            // $typeTransactionId = $ListSmsManipule->getTypemanipulation()->getId();
            // if ($typeTransactionId == 1) {
                if ($ListSmsManipule->getEmetteur()->getQuantite() - $ListSmsManipule->getQuantite() >= 0) {
                    $emetteur =   $ListSmsManipule->getEmetteur()->setQuantite($ListSmsManipule->getEmetteur()->getQuantite() - $ListSmsManipule->getQuantite());
                    $recepteur =   $ListSmsManipule->getRecepteur()->setQuantite($ListSmsManipule->getRecepteur()->getQuantite() + $ListSmsManipule->getQuantite());

                    $customerEntityManager->persist($emetteur);
                    $customerEntityManager->persist($recepteur);
                    $customerEntityManager->persist($ListSmsManipule->setStatus(true));
                }
            // } else {
            //     if ($ListSmsManipule->getRecepteur()->getQuantite() - $ListSmsManipule->getQuantite() >= 0) {
            //         $emetteur =   $ListSmsManipule->getEmetteur()->setQuantite($ListSmsManipule->getEmetteur()->getQuantite() + $ListSmsManipule->getQuantite());
            //         $recepteur =   $ListSmsManipule->getRecepteur()->setQuantite($ListSmsManipule->getRecepteur()->getQuantite() - $ListSmsManipule->getQuantite());

            //         $customerEntityManager->persist($emetteur);
            //         $customerEntityManager->persist($recepteur);
            //         $customerEntityManager->persist($ListSmsManipule->setStatus(true));
            //     }
            // }
            $customerEntityManager->flush();
        }
    }
}
