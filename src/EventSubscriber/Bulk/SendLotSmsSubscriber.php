<?php

namespace App\EventSubscriber\Bulk;

use ApiPlatform\Core\EventListener\EventPriorities;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\Sms;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Entity\Bulk\Contact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;

// final class SendLotSmsSubscriber extends AbstractController implements EventSubscriberInterface
// {
//     private $em;
//     private $clientWeb;
//     public    $doctrine;
//     public function __construct(EntityManagerInterface $em, HttpClientInterface $clientWeb,  ManagerRegistry $doctrine)
//     {
//         $this->em = $em;
//         $this->clientWeb = $clientWeb;
//         $this->doctrine = $doctrine;
//     }

//     public static function getSubscribedEvents()
//     {
//         return [
//             KernelEvents::VIEW => ['operationAfterCreateALot', EventPriorities::POST_WRITE]
//         ];
//     }

//     public function operationAfterCreateALot(ViewEvent $event): void
//     {
//         $listSmsLotsEnvoye = $event->getControllerResult();
//         $method = $event->getRequest()->getMethod();

//         if ($listSmsLotsEnvoye instanceof ListSmsLotsEnvoye && Request::METHOD_POST === $method) {
//             $this->SendSmsBuyLot();
//         }
//     }
//     public function SendSmsBuyLot()
//     {
//         $tabeSmsAndDest = array();

//         $dest = array();
//         $contacts = [];
//         $listsmscontact = [];
//         $listSmsLotsEnvoye = [];
//         $customerEntityManager = $this->doctrine->getManager('Bulk');

//         $findlistSmsLotsEnvoye = $customerEntityManager->getRepository(ListSmsLotsEnvoye::class);
//         $findlistSmsContact = $customerEntityManager->getRepository(ListSmsContact::class);
//         $findContact = $customerEntityManager->getRepository(Contact::class);
//         $findLot = $customerEntityManager->getRepository(Lot::class);
//         $findSms = $customerEntityManager->getRepository(Sms::class);
//         $i = 0;

//         $listSmsLotsEnvoye = $findlistSmsLotsEnvoye->findAll();

//         foreach ($listSmsLotsEnvoye as $listSmsLot) {
//             if ($listSmsLot->getStatus() !== true) {
//                 $listsmscontact = $findlistSmsContact->findBy(['listSmsLotsEnvoye' => $listSmsLot->getId()]);;

//                 $listcontact = [];
//                 foreach ($listsmscontact as $smscontact) {

//                     array_push($listcontact,  $smscontact->getContact()->getId());

//                     $i++;
//                     $contacts = $findContact->findBy(['id' => $smscontact->getId()]);
//                 }
//                 array_push($tabeSmsAndDest, [$listSmsLot->getSms()->getClientId(),  $listSmsLot->getSms()->getId(), $listcontact, $listSmsLot->getId()]);
//             }
//         }
//         if (!empty($tabeSmsAndDest)) {
//             //  var_dump($tabeSmsAndDest);
//             $total = 0;
//             $numBClient = 0;
//             $moyenneSend = 0;
//             $baeId = null;

//             for ($i = 0; $i < count($tabeSmsAndDest); $i++) {
//                 $total += count($tabeSmsAndDest[$i][2]);
//                 // var_dump($tabeSmsAndDest[$i][0]);
//                 if ($tabeSmsAndDest[$i][0] !== $baeId) {
//                     $numBClient++;
//                     // var_dump($numBClient);
//                 }
//                 $baeId = $tabeSmsAndDest[$i][0];
//             }

//             $moyenneSend =  $total / $numBClient;
//             $listDestinataire = array();
//             // var_dump($total);
//             // var_dump($numBClient);
//             // var_dump($moyenneSend);
//             for ($i = 0; $i < count($tabeSmsAndDest); $i++) {

//                 // var_dump(" lot d'id " . $tabeSmsAndDest[$i][1]);
//                 if (!empty($tabeSmsAndDest[$i][2])) {
//                     $message =  $findSms->findOneBy(['id' => $tabeSmsAndDest[$i][1]])->getMessage();
//                     $senderId =     $findSms->findOneBy(['id' => $tabeSmsAndDest[$i][1]])->getSenderId()->getSenderId();
//                     if (count($tabeSmsAndDest[$i][2]) <= $moyenneSend) {

//                         for ($j = 0; $j < count($tabeSmsAndDest[$i][2]); $j++) {
//                             array_push($listDestinataire, $findContact->findOneBy(['id' => $tabeSmsAndDest[$i][2][$j]])->getPhone());
//                             // var_dump("envoi du sms " . $message . " a " . $tabeSmsAndDest[$i][2][$j]);
//                         }
//                         // var_dump("changement du status du lot d'id" . $tabeSmsAndDest[$i][3]);
//                     } else {
//                         for ($j = 0; $j < $moyenneSend; $j++) {
//                             array_push($listDestinataire, $findContact->findOneBy(['id' => $tabeSmsAndDest[$i][2][$j]])->getPhone());
//                             var_dump("le nombre envoyable est " . $moyenneSend . " a " . $tabeSmsAndDest[$i][2][$j]);
//                         }
//                     }

//                     var_dump(
//                         "le message  " . $message
//                     );
//                     var_dump(
//                         "le senderId  " . $senderId
//                     );
//                     // $this->send([$senderId, $message,  $listDestinataire]);
//                     var_dump($listDestinataire);
//                     $listDestinataire = array();

//                     $listlotsmsconcerne =   $findlistSmsLotsEnvoye->findOneBy(['id' => $tabeSmsAndDest[$i][3]])->setStatus(true);

//                     $customerEntityManager->persist($listlotsmsconcerne);
//                     $customerEntityManager->flush();
//                 } else {
//                     var_dump("vide...........");
//                 }
//                 // var_dump("changement du status du lot d'id" . $tabeSmsAndDest[$i][3]);
//             }
//             $this->SendSmsBuyLot();
//         } else {
//             var_dump("tout a deja ete send");
//         }
//     }
//     /**
//      * Undocumented function
//      * @param [array] $data doit contenir le senderId, le message , la liste des destinataire
//      * @return void
//      */
//     public function send($data)
//     {
//         var_dump("voici les data de send.........");
//         var_dump($data);

//         $response = $this->clientWeb->request(
//             'POST',
//             "http://127.0.0.1:8000/sendToCamerounApi",
//             [
//                 'body' => [
//                     "senderId" => $data['senderId'],
//                     "message"  => $data['message'],
//                     "destinataire"
//                     => $data['destinataire'],
//                 ]
//             ]
//         );
//     }
// }
