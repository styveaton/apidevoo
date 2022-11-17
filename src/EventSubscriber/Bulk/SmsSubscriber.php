<?php

namespace App\EventSubscriber\Bulk;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\Sms;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Licence\Licence;
use App\Entity\Licence\ListSMSAchette;

use App\Entity\Route\Operateur;
use App\Entity\Route\SenderApi;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

use Doctrine\Persistence\ManagerRegistry;

final class SmsSubscriber extends AbstractController implements EventSubscriberInterface
{
    private $em;
    private $client;
    public $doctrine;

    public function __construct(EntityManagerInterface $em,  HttpClientInterface $client, ManagerRegistry $doctrine)
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
        // /**
        //  * @var Sms represente l'sms encours d'envoie
        //  */
        // $Sms = $event->getControllerResult();
        // $method = $event->getRequest()->getMethod();

        // $bulkManager = $this->doctrine->getManager('Bulk');
        // $licenceManager = $this->doctrine->getManager('Licence');

        // if ($Sms instanceof Sms && Request::METHOD_POST === $method) {

        //     $nsms = $Sms->setMessage("MOuafo takoumbo");
        //     $bulkManager->persist($nsms);
        //     $bulkManager->flush();
        // }
        //     /**
        //      * @var sms represente le message texte a envoyer
        //      */
        //     $sms = $Sms->getMessage();
        //     /**
        //      * @var senderId represente le senderId du message
        //      */
        //     $senderId = $Sms->getSenderId()->getSenderId();
        //     /**
        //      * @var sms represente l'id de celui qui envoi le smsr
        //      */

        //     $clientId = $Sms->getClientId();
        //     /**
        //      * @var lotSms represente le lot concernant message texte a envoyer
        //      */
        //     $lotSms = $bulkManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $Sms->getId(), 'status' => false]);

        //     if (!$lotSms) {
        //         return;
        //     }

        //     $route = $bulkManager->getRepository(RouteListSmsLotsEnvoye::class)->findOneBy(['listSmsLotsEnvoye' => $lotSms->getId(), 'status' => false]);

        //     if (!$route) {
        //         return;
        //     }
        //     $licence = $licenceManager->getRepository(Licence::class)->findBy(['clientId' => $clientId, 'status' => false]);


        //     if (!$licence) {
        //         return;
        //     }

        //     $phone = [];
        //     $qt = 0;
        //     $routeId = $route->getRouteId();
        //     foreach ($licence as $lc) {
        //         $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['licence' => $lc->getId(), 'routeId' => $routeId]);

        //         if (!$lsa) {
        //             continue;
        //         } else {
        //             $qt += $lsa->getQuantite();
        //         }
        //     }

        //     if (empty($lotSms->getGroupeContact())) {
        //         $lotContact = $bulkManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lotSms->getId(), 'status' => false]);
        //         if (!$lotContact) {
        //             return;
        //         }
        //         foreach ($lotContact as $lt) {
        //             $phone[] = $lt->getContact()->getPhone();
        //         }

        //         if ($qt < count($phone)) {
        //             return;
        //         }

        //         // envoyer le message

        //     } else {
        //         $lotGroupe = $bulkManager->getRepository(ListGroupeContact::class)->findBy(['groupeContact' => $lotSms->getGroupeContact()->getId()]);
        //         if (!$lotGroupe) {
        //             return;
        //         }
        //         foreach ($lotGroupe as $lg) {
        //             $phone[] = $lg->getContact()->getPhone();
        //         }

        //         if ($qt < count($phone)) {
        //             return;
        //         }

        //         // envoyer les messages

        //     }

        //     // if (!empty($ListSmsContact->getContact()) && !empty($ListSmsContact->getListSmsLotsEnvoye())) {
        //     //     //  1er methode

        //     //     $phone = $ListSmsContact->getContact()->getPhone();
        //     //     $sms = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getMessage();
        //     //     $senderId = $ListSmsContact->getListSmsLotsEnvoye()->getSms()->getSenderId();


        //     //     // 2iem methode

        //     //     // $this->sendSmsRecursif();


        //     // $nsms = $Sms->setMessage("MOuafo takoumbo");
        //     // $bulkManager->persist($nsms);
        //     // $bulkManager->flush();

        //     if ($routeId == 1) {
        //         $data = [
        //             'senderId' =>
        //             $senderId,
        //             'message' => $sms,
        //             'destinataire' => $phone
        //         ];

        //         $this->sendToCamerounApi($data);
        //     }
        // }
    }



    // public function sendToCamerounApi($request)
    // {
    //     $destinatireFinal
    //         = array();
    //     $destinatireError
    //         = array();
    //     $dataSucess = array();
    //     $data = $request;
    //     // var_dump($data);
    //     $numberByOperator = array();
    //     $customerEntityManager = $this->doctrine->getManager('Route');
    //     foreach ($data['destinataire'] as $dest) {

    //         $NumAndApi = array();
    //         if (strlen($dest) == 9) {
    //             if (str_split($dest)[0] . str_split($dest)[1] === "65") {

    //                 if (str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "651") {

    //                     $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
    //                     $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                     $NumAndApi
    //                         = [$dest, $apiLink->getId()];
    //                     array_push($numberByOperator,  $NumAndApi);
    //                 }
    //                 if (str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "652") {

    //                     $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
    //                     $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                     $NumAndApi
    //                         = [$dest, $apiLink->getId()];
    //                     array_push($numberByOperator,  $NumAndApi);
    //                 }
    //                 if (str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "653") {

    //                     $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
    //                     $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                     $NumAndApi
    //                         = [$dest, $apiLink->getId()];
    //                     array_push($numberByOperator,  $NumAndApi);
    //                 }
    //                 if (str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "654") {

    //                     $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
    //                     $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                     $NumAndApi
    //                         = [$dest, $apiLink->getId()];
    //                     array_push($numberByOperator,  $NumAndApi);
    //                 }
    //                 if (str_split($dest)[0] . str_split($dest)[1] . str_split($dest)[2] === "654") {

    //                     $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
    //                     $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                     $NumAndApi
    //                         = [$dest, $apiLink->getId()];
    //                     array_push($numberByOperator,  $NumAndApi);
    //                 } else {
    //                     $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
    //                     $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                     $NumAndApi
    //                         = [$dest, $apiLink->getId()];
    //                     array_push($numberByOperator,  $NumAndApi);
    //                 }
    //             } else if (
    //                 str_split($dest)[0] . str_split($dest)[1] === "69"
    //             ) {
    //                 $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
    //                 $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);

    //                 $NumAndApi
    //                     = [$dest, $apiLink->getId()];
    //                 array_push($numberByOperator,  $NumAndApi);
    //             } else if (str_split($dest)[0] . str_split($dest)[1] === "67") {
    //                 $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
    //                 $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                 $NumAndApi
    //                     = [$dest, $apiLink->getId()];
    //                 array_push($numberByOperator,  $NumAndApi);
    //             } else if (str_split($dest)[0] . str_split($dest)[1] === "68") {
    //                 $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
    //                 $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                 $NumAndApi
    //                     = [$dest, $apiLink->getId()];
    //                 array_push($numberByOperator,  $NumAndApi);
    //             } else if (str_split($dest)[0] . str_split($dest)[1] === "66") {
    //                 $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 1]);
    //                 $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                 $NumAndApi
    //                     = [$dest, $apiLink->getId()];
    //                 array_push($numberByOperator, $NumAndApi);
    //             } else {
    //                 $operator = $customerEntityManager->getRepository(Operateur::class)->findOneBy(['id' => 6]);
    //                 $apiLink = $customerEntityManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
    //                 if ($apiLink !== null) {
    //                     $NumAndApi
    //                         = [$dest, $apiLink->getId()];
    //                     array_push(
    //                         $numberByOperator,
    //                         $NumAndApi
    //                     );
    //                 }
    //             }

    //             array_push(
    //                 $destinatireFinal,
    //                 $NumAndApi
    //             );
    //         } else {
    //             array_push($destinatireError,  ['incorrectNUmber', $dest]);
    //         }
    //     }
    //     // dd($destinatireFinal);

    //     foreach ($destinatireFinal as $desta => $desti) {

    //         if (preg_match("/^[a-zA-Z0-9. ,?Â'ùçàéëèà;@!:_-]*$/", $data['message'])) {
    //             // dd(" is a valid message");

    //             try {

    //                 $query = [
    //                     'login' =>  679170000,   //$client->getPhone(),
    //                     'password' => "Oi7i469x", //$client->getPassword(),
    //                     'sender_id' => $data['senderId'],
    //                     'destinataire' => $desti[0],
    //                     'message' => $data['message']
    //                 ];


    //                 $response = $this->clientWeb->request(
    //                     'GET',
    //                     'http://sms.gessiia.com/ss/api.php',
    //                     [
    //                         'query' => $query
    //                     ]
    //                 );

    //                 $statusCode = $response->getStatusCode();
    //                 // dd( $statusCode);
    //                 array_push($dataSucess, [($statusCode == 200) ?  'success' : 'error', $desti[0]]);
    //             } catch (Exception $e) {

    //                 return   new JsonResponse([
    //                     'success' => false,
    //                     'message' => $e,
    //                 ], 400);
    //             }
    //         } else {
    //             return   new JsonResponse([
    //                 'success' => false,
    //                 'reponse' => "invalid text message",
    //             ], 400);
    //         }

    //         # code...
    //     }

    //     return  new JsonResponse([

    //         'reponse' => 'Traitement effectue',
    //         'messageSend' => $data['message'],
    //         'success'
    //         => $dataSucess,
    //         'error'
    //         => $destinatireError,

    //     ], 200);
    // }


    // public function sendSmsRecursif()
    // {
    //     $bulkManager = $this->getDoctrine()->getManager('Bulk');

    //     $AllLot = $bulkManager->getRepository(Lot::class)->findBy(['status' => false]);

    //     foreach ($AllLot as $lot) {
    //         $lotSms = $bulkManager->getRepository(ListSmsLotsEnvoye::class)->findBy(['lot' => $lot->getId()]);
    //     }
    // }
}
