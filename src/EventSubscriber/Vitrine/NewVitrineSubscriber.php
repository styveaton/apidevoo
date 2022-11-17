<?php

namespace App\EventSubscriber\Vitrine;

use ApiPlatform\Core\EventListener\EventPriorities;

use App\Entity\Bulk\ListSmsLotsEnvoye;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Licence\ListSmsManipule;
use App\Entity\Vitrine\VitrineObject;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;

final class NewVitrineSubscriber extends AbstractController implements EventSubscriberInterface
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
        $vitrineObject = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();



        if ($vitrineObject instanceof VitrineObject && Request::METHOD_POST === $method) {
            $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
            $lObject =
                $VitrineEntityManager->getRepository(VitrineObject::class)->findBy(['contenu' => $vitrineObject->getContenu()]);

            foreach ($lObject  as $l) {
                $data =     $l->setContenu(null);


                $VitrineEntityManager->persist($data);
            }
            for ($i = 0; $i < count($lObject) - 1; $i++) {
                $data =     $lObject[$i]->setContenu(null);


                $VitrineEntityManager->persist($data);
            }




            $VitrineEntityManager->flush();
        }
    }
}
