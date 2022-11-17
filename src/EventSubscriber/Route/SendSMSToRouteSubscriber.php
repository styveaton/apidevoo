<?php

namespace App\EventSubscriber\Route;


use App\Entity\Bulk\Sms;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;


final class SendSMSToRouteSubscriber extends AbstractController implements EventSubscriberInterface
{

    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['afterCreateASMS', EventPriorities::POST_WRITE],
        ];
    }

    public function afterCreateASMS(ViewEvent $event): void
    {
      /*   $SMS = $event->getControllerResult();

        $method = $event->getRequest()->getMethod();

        if ($SMS instanceof Sms && Request::METHOD_POST === $method) {



            $SMS->setMessage("MOuafo");

            $this->em->persist($SMS);
            $this->em->flush();
        } */
    }
}
