<?php

namespace App\EventSubscriber\Bulk;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\Sms;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\Auth\Client;
use App\Entity\Bulk\Contact;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;

final class ListSmsContactSubscriber extends AbstractController implements EventSubscriberInterface
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
            KernelEvents::VIEW => ['operationAfterCreateASms', EventPriorities::POST_WRITE]
        ];
    }

    public function operationAfterCreateASms(ViewEvent $event): void
    {
        $ListSmsContact = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        $customerEntityManager = $this->doctrine->getManager('Bulk');

        if ($ListSmsContact instanceof ListSmsContact && Request::METHOD_POST === $method) {


//             $idLot = $
//
//             $phone = $ListSmsContact->getContact()->getPhone();
//             $sms = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getMessage();
//             $senderId = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getSenderId();
//             $route = $ListSmsContact->getListSmsLotsEnvoye()->getRouteListSmsLotsEnvoyes()[0]->getRouteId();



            // if (!empty($ListSmsContact->getContact()) && !empty($ListSmsContact->getListSmsLotsEnvoye())) {
            //     //  1er methode

            //     $phone = $ListSmsContact->getContact()->getPhone();
            //     $sms = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getMessage();
            //     $senderId = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getSenderId();
            //     $route = $ListSmsContact->getListSmsLotsEnvoye()->getRouteListSmsLotsEnvoyes()[0]->getRouteId();

            //     // 2iem methode

            //     // $this->sendSmsRecursif();

            //     $nsms = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->setMessage("MOuafo takoumbo");
            //     $customerEntityManager->persist($nsms);
            //     $customerEntityManager->flush();
            //     $data = [
            //         'senderId' =>
            //         $senderId,
            //         'message' => $sms,
            //         'destinataire' => $phone
            //     ];
            //     $this->send($data);
            // }

            $findrouteListSmsLotEnvoye = $customerEntityManager->getRepository(RouteListSmsLotsEnvoye::class);
        }
    }

    public function sendSmsRecursif()
    {
        $bulkManager = $this->getDoctrine()->getManager('Bulk');

        $AllLot = $bulkManager->getRepository(Lot::class)->findBy(['status' => false]);

        foreach ($AllLot as $lot) {
            $lotSms = $bulkManager->getRepository(ListSmsLotsEnvoye::class)->findBy(['lot' => $lot->getId()]);
        }
    }
    /**
     * Undocumented function
     * @param [array] $data doit contenir le senderId, le message , la liste des destinataire
     * @return void
     */
    public function send($data): void
    {
        var_dump("voici les data de send.........");
        var_dump($data);


        $response = $this->client->request(
            'POST',
            "http://127.0.0.1:8000/sendToCamerounApi",
            [
                'body' => [
                    "senderId" => $data['senderId'],
                    "message"  => $data['message'],
                    "destinataire"
                    => $data['destinataire'],
                ]
            ]
        );
    }
}
