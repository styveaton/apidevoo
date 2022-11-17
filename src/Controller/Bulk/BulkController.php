<?php

namespace App\Controller\Bulk;

use App\Entity\Account\TransactionCompte;
use App\Entity\Auth\Client;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FFI\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use App\Entity\Bulk\Calendrier;
use App\Entity\Bulk\Contact;
use App\Entity\Bulk\Exception as BulkException;
use App\Entity\Bulk\GroupeContact;
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListSenderIdExcepte;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\SenderId;
use App\Entity\Bulk\Sms;
use App\Entity\Route\Operateur;
use App\Entity\Route\Pays;
use App\Entity\Route\Route as RouteRoute;
use App\Entity\Route\SenderApi;
use Proxies\__CG__\App\Entity\Bulk\Sms as BulkSms;
use Proxies\__CG__\App\Entity\Pub\Publication;
use Proxies\__CG__\App\Entity\Route\Route as EntityRouteRoute;

class BulkController extends AbstractController
{
    private $em;
    private $client;
    private $apiKey;
    public function __construct(EntityManagerInterface $em, HttpClientInterface $client,  ManagerRegistry $doctrine)
    {
        $this->em = $em;
        $this->doctrine = $doctrine;
        $this->client = $client;
    }


    /**
     * @Route("/state/sendingsms", name="sendingsms", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * 
     * 
     * @param array $data doit contenur clientId
     * 
     * 
     */
    public function getStateSendingSms(Request $request)
    {
        $BulkEntityManager = $this->doctrine->getManager('Bulk');
        $data = $request->toArray();


        if (empty($data['clientId'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser l\'id du client'
            ], 400);
        }
        $listTransaction = [];
        $dataT = null;
        $clientId = $data['clientId'];
        $LSms =
            $BulkEntityManager->getRepository(Sms::class)->findBy(
                ['clientId' => $clientId]
            );
        $message = '';

        $senderId = '';
        $lState = [];
        foreach ($LSms as  $sms) {

            if ($sms) {

                $message = $sms->getmessage();
                if ($sms->getSenderId()) {
                    $senderId = $sms->getSenderId()->getSenderId();
                    $LSmsLE =

                        $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);

                    $lContactT = [];
                    $lContactE = [];
                    $LSmsContact =
                        $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $LSmsLE]);
                    foreach ($LSmsContact as  $smsContact) {
                        array_push($lContactT, $smsContact);
                        if ($smsContact->getStatus()) {
                            array_push($lContactE, $smsContact);
                        }
                    }
                    if (count($lContactE) < count($lContactT)) {
                        $state = [
                            'senderId' => $senderId,
                            'message' => $message,
                            'nombreEnvoye' => count($lContactE),
                            'nombreContact' => count($lContactT),



                        ];
                        array_push($lState,         $state);
                    }
                }
            }
        }

        // foreach ($LTransaction as  $Transaction) {


        //     $clientUser = $this->em->getRepository(Client::class)->findOneBy(['id' => $Transaction->getClientId()]);

        //     if ($clientUser) {

        //         if ($clientUser->getCodeParrain() !== null) {


        //             $parrain = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $clientUser->getCodeParrain())[0]]);
        //             if ($parrain) {


        //                 $parrain1 = $this->em->getRepository(Client::class)->findOneBy(['id' => explode('@', $parrain->getCodeParrain())[0]]);



        //                 if ($parrain->getId() == $clientId) {

        //                     $dataT = [
        //                         'nom' => $clientUser->getNom(),
        //                         'phone' => $clientUser->getPhone(),
        //                         'entreprise' => $clientUser->getNomEntreprise(),
        //                         'commissionT' => $Transaction->getMontantPartage(),
        //                         'commission' => $Transaction->getMontantParrain(),
        //                         'date' =>    $Transaction->getDateCreate()->format('d/m/Y'),
        //                     ];

        //                     array_push($listTransaction, $dataT);
        //                 } else if ($parrain1->getId() == $clientId) {



        //                     $dataT = [
        //                         'nom' => $clientUser->getNom(),
        //                         'phone' => $clientUser->getPhone(),
        //                         'entreprise' => $clientUser->getNomEntreprise(),
        //                         'commissionT' => $Transaction->getMontantPartage(),
        //                         'commission' => $Transaction->getMontantParrain2(),
        //                         'date' =>  $Transaction->getDateCreate()->format('d/m/Y'),

        //                     ];

        //                     array_push($listTransaction, $dataT);
        //                 } else {
        //                 }
        //             }
        //         } else {
        //         }
        //     } else {
        //     }
        // }
        return
            new JsonResponse([
                'data'
                =>   $lState,

            ], 200);
    }

    /**
     *@Route("/sendSmsApi", name="sendSmsApi", methods={"POST"})
     * @param Request $data continet les donnees suivantes: message , keySecret du client,la liste des destinataire[Ou l'id du groupe de contact],la route,le senderId , on peut aussi ajouter un calendrier, qui represente la date a la quelle le message est sense etre envoye
     * @param Client $client represente l'utiliasteur voulant emettre
     * @param Array $data['destinataire'] est un tableau de numeros de destinataire
     * @param  HttpClientInterface $clientWeb l'element utilise pour effectuer les requettes
     * @throws Exception
     * 
     */
    public function sendSmsApi(Request $request)
    {

        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request->toArray();
        // dd($data);
        // if (empty($data['id']) || empty($data['message']) || empty($data['destinataire']) || empty($data['route'])) {
        //     return new JsonResponse([
        //         'message' => 'Mauvais parametre de requete  id, message ,destinataire, route sont requis'
        //     ], 400);
        // }


        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {

            $client =    $clientUser->getId();
        } else {
            return new JsonResponse([
                'message' => 'Invalid keySecret'
            ], 400);
        }
        $numberByOperator = array();

        $possible = false;
        foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            if ($lr->isStatus() && $lr->getFonction()->getId() == '23') {
                $possible = true;
            }
        }
        if ($possible == false) {
            return new JsonResponse([
                'message' => 'Action impossible'
            ], 400);
        }
        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');

        $phoneCode =
            $routeManager->getRepository(RouteRoute::class)->findOneBy(['id' => $data['route']])->getPays()->getCodePhone();

        // $operator = $BulkManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
        // $apiLink = $BulkManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
        if (empty($data['groupeContact'])) {


            $dataFindContact = [
                'client'
                => $client,
                'contacts' =>
                $data['destinataire'],
                'codePhone' =>        $phoneCode
            ];
        } else {
            /**
             * si l'utilisateur precise un groude de contact existant 
             */
            $destinataires = [];

            $getGroupe = $BulkManager->getRepository(ListGroupeContact::class)->findBy(['groupeContact' => $data['groupeContact']]);
            if ($getGroupe) {
                foreach ($getGroupe as $groupe) {
                    if ($groupe->getContact()) {
                        $contact = $BulkManager->getRepository(Contact::class)->findOneBy(['id' => $groupe->getContact()->getId()]);
                        if ($contact) {
                            array_push($destinataires, $contact->getPhone());
                        } # code...
                    }

                    // array_push($destinataires, $groupe->getContact()->getPhone());
                    # code...
                    // var_dump($groupe->getContact()->getPhone());
                }
                // dd($destinataires);
                if (!empty($destinataires)) {

                    $dataFindContact = [
                        'client'
                        => $client,
                        'groupeContact' =>
                        $data['groupeContact'],  'codePhone' =>        $phoneCode
                    ];
                } else {
                    return new JsonResponse([
                        'message' => 'groupe vide'
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'groupe innexistant'
                ], 400);
            }
        }





        $dataSenderId = [
            'client'
            => $client,
            'senderId' =>
            $data['senderId']
        ];


        $newLot = $this->createLot($data, $client);

        if ($newLot != null) {


            $newSenderid = $this->findSenderId($dataSenderId);

            if ($newSenderid != null) {
                $dataSms = [

                    'client'
                    => $client,
                    'senderId' =>
                    $newSenderid,
                    'message' => $data['message'],
                    'statusSpecial' =>  $data['statusSpecial'] ?? true
                ];



                $newSms = $this->createSms($dataSms);
                if (


                    $newSms != null
                ) {
                    $dataLSLE = [
                        'sms'
                        => $newSms,
                        'lot' =>
                        $newLot,
                        'groupeContact' =>
                        $data['groupeContact'] ?? 0,
                    ];

                    $newLSE = $this->createListSmsLotEnvoye($dataLSLE);
                    if ($newLSE != null) {
                        $dataRLSLE = [
                            'route'
                            =>
                            $data['route'],
                            'lse' =>
                            $newLSE
                        ];
                        $newRouteLSLE = $this->createRouteLSLE($dataRLSLE);

                        if ($newRouteLSLE != null) {
                            if (empty($data['groupeContact'])) {

                                $final = $this->findContact($dataFindContact, $newLSE);
                                if (
                                    $final
                                    != null
                                ) {
                                    return  new JsonResponse([

                                        'reponse' => 'Traitement effectue',
                                        'id' =>   $newSms

                                    ], 200);
                                } else {
                                    return new JsonResponse([
                                        'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                                    ], 400);
                                }
                            } else {
                                return  new JsonResponse([

                                    'reponse' => 'Traitement effectue',
                                    'id' =>   $newSms

                                ], 200);
                            }
                        } else {
                            return new JsonResponse([
                                'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                            ], 400);
                        }
                    } else {
                        return new JsonResponse([
                            'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                        ], 400);
                    }
                } else {
                    return new JsonResponse([
                        'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'message' => 'Une erreur est survenue durant l\'execution de votre requette'
            ], 400);
        }
    }


    /**
     * doc createLot
     *
     * @param [] $request doit contenir idCient,la liste de  contact
     * @return void
     */
    public function createLot($data, $client)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');


        $newLot = new Lot();

        $bulkManager->persist($newLot);
        $bulkManager->flush();

        if (empty($data['calendrier'])) {
        } else {


            $newCalendar = new Calendrier();
            $newCalendar->setDateExecution(new DateTime($data['calendrier']));
            $newCalendar->setClientId($client);

            $bulkManager->persist($newCalendar);
            $bulkManager->flush();


            $newlistLotCalendar = new ListLotCalendrier();
            $newlistLotCalendar->setCalendrier($newCalendar);
            $newlistLotCalendar->setLot($newLot);
            $bulkManager->persist($newlistLotCalendar);
            $bulkManager->flush();
        }

        return $newLot->getId();
    }

    /**
     * doc findSenderId
     *
     * @param [] $request doit contenir idCient,le  SenderId soit en int ou en string
     * @return void
     */
    public function findSenderId($request)
    {



        $bulkManager = $this->doctrine->getManager('Bulk');
        $data = $request;


        if (empty($data['client']) || empty($data['senderId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  senderId, CientId'
            ], 400);
        }

        $finalSenderId = 0;
        $SenderId = $data['senderId'];

        if ((string)(int)($data['senderId']) == ($data['senderId'])) {
            $finalSenderId = $SenderId;
        } else {
            $ifSenderId = $bulkManager->getRepository(SenderId::class)->findOneBy(['senderId' => $SenderId, 'clientId' => $data['client']]);
            if ($ifSenderId) {

                $finalSenderId = $ifSenderId->getId();
            } else {


                $newSenderId = new SenderId();
                $newSenderId->setSenderId($SenderId);
                $newSenderId->setDescription('');
                $newSenderId->setClientId($data['client']);
                $bulkManager->persist($newSenderId);
                $bulkManager->flush();
                $finalSenderId = $newSenderId->getId();
            }
        }
        return $finalSenderId;
    }


    /**
     * doc findContact
     *
     * @param [] $request doit contenir id Cient,la liste de  contact,l'id du list sms lot envoye[lse]
     * @return void
     */
    public function findContact($request, $lse)
    {
        $data = $request;
        if (empty($data['contacts']) || empty($data['client'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  contact, Cient Id'
            ], 400);
        } else {
            $idContact = 0;
            $bulkManager = $this->doctrine->getManager('Bulk');

            $contacts = $data['contacts'];

            for ($i = 0; $i <   count($contacts); $i++) {
                $contactI = $contacts[$i];

                $ifContact = $bulkManager->getRepository(Contact::class)->findOneBy(['phone' => $contactI, 'clientId' => $data['client']]);
                if ($ifContact) {
                    $idContact = $ifContact->getId();
                } else {
                    if (
                        strlen($contactI) != 0 || $contactI != ''
                    ) {
                        $newContact = new Contact();
                        $newContact->setNom('Nom Contact');
                        $newContact->setPrenom('Prenom Contact');
                        $newContact->setPhone($contactI);
                        $newContact->setPhoneCode($data['codePhone']);
                        $newContact->setClientId($data['client']);
                        $bulkManager->persist($newContact);
                        $bulkManager->flush();
                        $idContact = $newContact->getId();
                    }

                    $dataLSC  = ['contact' => $idContact, 'lse' => $lse];

                    $this->createListSmsContact($dataLSC);
                }
            }
            return true;
        }
    }


    /**
     * doc createSms
     *
     * @param [] $data doit contenir idCient,le senderId,le messageText Choisit
     * @return void
     */
    public function createSms($data)
    {
        if (empty($data['message']) || empty($data['client']) || empty($data['senderId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete message, senderId, Cient Id'
            ], 400);
        }

        $bulkManager = $this->doctrine->getManager('Bulk');

        $senderId = $bulkManager->getRepository(SenderId::class)->findOneBy(['id' => $data['senderId']]);

        $newSms = new Sms();
        $newSms->setMessage($data['message']);
        $newSms->setSenderId($senderId);
        $newSms->setClientId($data['client']);
        $newSms->setStatusSpecial($data['statusSpecial']);
        $bulkManager->persist($newSms);
        $bulkManager->flush();


        return $newSms->getId();
    }


    /**
     * doc createListSmsLotEnvoye
     *
     * @param [] $data doit contenir id de SMS,l'id du lot 
     * @return void
     */
    public function createListSmsLotEnvoye($datas)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');
        $lot = $bulkManager->getRepository(lot::class)->findOneBy(['id' => $datas['lot']]);
        $sms = $bulkManager->getRepository(sms::class)->findOneBy(['id' => $datas['sms']]);


        $newLSLE = new ListSmsLotsEnvoye();
        $newLSLE->setLot($lot);
        $newLSLE->setSms($sms);
        // dd($datas['groupeContact']);
        if (!empty($datas['groupeContact'])) {
            $GroupeContact = $bulkManager->getRepository(GroupeContact::class)->findOneBy(['id' => $datas['groupeContact'],]);
            // dd($datas);
            $newLSLE->setGroupeContact($GroupeContact);
        }


        $bulkManager->persist($newLSLE);
        $bulkManager->flush();

        return $newLSLE->getId();
    }
    /**
     * doc createRouteLSLE
     *
     * @param [] $request doit contenir id de la route[route],id listsmslotenvoye [lse]
     * @return void
     */
    public function createRouteLSLE($data)
    {

        // dd($data);
        $bulkManager = $this->doctrine->getManager('Bulk');
        $lse = $bulkManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['id' => $data['lse']]);


        $newLSLE = new RouteListSmsLotsEnvoye();
        $newLSLE->setRouteId($data['route']);
        $newLSLE->setListSmsLotsEnvoye($lse);
        $bulkManager->persist($newLSLE);
        $bulkManager->flush();

        return $newLSLE->getId();
    }
    /**
     * doc createListSmsContact
     *
     * @param [] $request doit contenir l'id du contact[contact],id listsmslotenvoye [lse]
     * @return void
     */
    public function createListSmsContact($data)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');
        $lse =
            $bulkManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['id' => $data['lse']]);
        $contact = $bulkManager->getRepository(Contact::class)->findOneBy(['id' => $data['contact']]);
        $newLSC = new ListSmsContact();
        // dd($data);
        $newLSC->setContact($contact);
        $newLSC->setListSmsLotsEnvoye($lse);
        $bulkManager->persist($newLSC);
        $bulkManager->flush();

        return $newLSC->getId();
    }


    /**
     *@Route("/eventSmsApi", name="eventSmsApi", methods={"POST"})
     * @param Request $data continet les donnees suivantes: message , keySecret du client,la liste des destinataire[Ou l'id du groupe de contact],la route,le senderId , on peut aussi ajouter un calendrier, qui represente la date a la quelle le message est sense etre envoye
     * @param Client $client represente l'utiliasteur voulant emettre
     * @param Array $data['destinataire'] est un tableau de numeros de destinataire
     * @param  HttpClientInterface $clientWeb l'element utilise pour effectuer les requettes
     * @throws Exception
     * 
     */
    public function eventSmsApi(Request $request)
    {
        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request->toArray();
        // dd($data);
        // if (empty($data['id']) || empty($data['message']) || empty($data['destinataire']) || empty($data['route'])) {
        //     return new JsonResponse([
        //         'message' => 'Mauvais parametre de requete  id, message ,destinataire, route sont requis'
        //     ], 400);
        // }


        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser) {
            $client =    $clientUser->getId();
        } else {
            return new JsonResponse([
                'message' => 'Invalid keySecret'
            ], 400);
        }
        $numberByOperator = array();



        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');
        $destinatairesRoute = [];
        $route = 0;
        $dataDestinataireSave = [];
        $dataRouteSave = 0;
        if (count($data['destinataire']) == 1) {
            $codePhone =
                $BulkManager->getRepository(Contact::class)->findOneBy(['phone' => $data['destinataire'][0]])->getPhoneCode();
            $pays =
                $routeManager->getRepository(Pays::class)->findOneBy(['codePhone' => $codePhone]);
            $route =
                $routeManager->getRepository(RouteRoute::class)->findOneBy(['pays' => $pays])->getId();

            $destinatairesRoute = [['destinataires' => $data['destinataire'], 'route' => $route]];
            dd($destinatairesRoute);
        } else if (count($data['destinataire']) > 1) {
            for ($i = 0; $i < count($data['destinataire']); $i++) {
                $codePhone =
                    $BulkManager->getRepository(Contact::class)->findOneBy(['phone' => $data['destinataire'][$i]])->getPhoneCode();
                $pays =
                    $routeManager->getRepository(Pays::class)->findOneBy(['codePhone' => $codePhone]);
                $route =
                    $routeManager->getRepository(RouteRoute::class)->findOneBy(['pays' => $pays])->getId();


                if ($route != $dataRouteSave && $dataRouteSave != 0) {

                    var_dump($data['destinataire'][$i]);

                    if (!empty($destinatairesRoute)) {

                        for ($j = 0; $j < count($destinatairesRoute); $j++) {
                            var_dump($destinatairesRoute[$j]['route'] == $route);
                            array_push($dataDestinataireSave, $data['destinataire'][$i]);
                            if ($destinatairesRoute[$j]['route'] == $route) {

                                array_push($destinatairesRoute[$j]['destinataires'], $data['destinataire'][$i]);
                            } else {
                                dd('pppppppppp00');
                                array_push(
                                    $destinatairesRoute,
                                    ['destinataires' =>
                                    $dataDestinataireSave, 'route' => $route]
                                );
                            }
                        }
                    } else {
                        array_push(
                            $destinatairesRoute,
                            ['destinataires' =>
                            $dataDestinataireSave, 'route' => $dataRouteSave]
                        );
                    }

                    $dataRouteSave =   $route;
                } else {
                    array_push($dataDestinataireSave, $data['destinataire'][$i]);
                    $dataRouteSave =   $route;
                }
            }
            dd($destinatairesRoute);
        }






        // $operator = $BulkManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
        // $apiLink = $BulkManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);
        if (empty($data['groupeContact'])) {


            $dataFindContact = [
                'client'
                => $client,
                'contacts' =>
                $data['destinataire'],

            ];
        } else {
            /**
             * si l'utilisateur precise un groude de contact existant 
             */
            $destinataires = [];

            $getGroupe = $BulkManager->getRepository(ListGroupeContact::class)->findBy(['groupeContact' => $data['groupeContact']]);
            if ($getGroupe != null) {
                foreach ($getGroupe as $groupe) {
                    array_push($destinataires, $groupe->getContact()->getPhone());
                    # code...
                    // var_dump($groupe->getContact()->getPhone());
                }
                if (!empty($destinataires)) {

                    $dataFindContact = [
                        'client'
                        => $client,
                        'contacts' =>
                        $destinataires
                    ];
                } else {
                    return new JsonResponse([
                        'message' => 'groupe vide'
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'groupe innexistant'
                ], 400);
            }
        }

        $dataSenderId = [
            'client'
            => $client,
            'senderId' =>
            $data['senderId']
        ];


        $newLot = $this->createLot($data, $client);

        if ($newLot != null) {
            $newSenderid = $this->findSenderId($dataSenderId);

            if ($newSenderid) {
                $dataSms = [
                    'client'
                    => $client,
                    'senderId' =>
                    $newSenderid,
                    'message' => $data['message'],
                    'statusSpecial' => $data['statusSpecial'] ?? true
                ];

                $newSms = $this->createSms($dataSms);
                if (


                    $newSms != null
                ) {
                    $dataLSLE = [
                        'sms'
                        => $newSms,
                        'lot' =>
                        $newLot
                    ];
                    $newLSE = $this->createListSmsLotEnvoye($dataLSLE);
                    if ($newLSE != null) {
                        $dataRLSLE = [
                            'route'
                            =>
                            $data['route'],
                            'lse' =>
                            $newLSE
                        ];
                        $newRouteLSLE = $this->createRouteLSLE($dataRLSLE);

                        if ($newRouteLSLE != null) {
                            $final = $this->findContact($dataFindContact, $newLSE);


                            if (
                                $final
                            ) {
                                return  new JsonResponse([

                                    'reponse' => 'Traitement effectue',


                                ], 200);
                            } else {
                                return new JsonResponse([
                                    'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                                ], 400);
                            }
                        } else {
                            return new JsonResponse([
                                'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                            ], 400);
                        }
                    } else {
                        return new JsonResponse([
                            'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                        ], 400);
                    }
                } else {
                    return new JsonResponse([
                        'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Une erreur est survenue durant l\'execution de votre requette'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'message' => 'Une erreur est survenue durant l\'execution de votre requette'
            ], 400);
        }
    }


    /**
     *@Route("/sms/examiner", name="ExaminerSms", methods={"POST"})
     * @param Request $data continet les donnees suivantes: idSms  
     * @param  HttpClientInterface $clientWeb l'element utilise pour effectuer les requettes
     * @throws Exception
     * 
     */
    public function ExaminerSms(Request $request)
    {

        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request->toArray();

        $BulkEntityManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');
        if (empty($data['idSms'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  idSms est requis '
            ], 400);
        }
        $idSms
            = $data['idSms'];

        $sms
            =  $BulkEntityManager->getRepository(Sms::class)->findOneBy(['id' => $idSms]);
        if ($sms) {

            $message = $sms->getMessage();
            if ($sms->getSenderId()) {
                $senderId = $sms->getSenderId()->getSenderId();
                $LSmsLE =

                    $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
                if ($LSmsLE) {
                    $routeU = $BulkEntityManager->getRepository(RouteListSmsLotsEnvoye::class)->findOneBy(['listSmsLotsEnvoye' =>  $LSmsLE]);

                    if ($routeU) {


                        $route = $routeManager->getRepository(EntityRouteRoute::class)->findOneBy(['id' =>  $routeU->getRouteId()])->getNom();


                        $lExcep = [];
                        $lContHisto = [];
                        $lContExcep = [];
                        $lContactR = [];
                        $lContactA = [];
                        $lContactEchec = [];
                        $LSmsContact =
                            $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $LSmsLE]);
                        $lse =    $BulkEntityManager->getRepository(ListSenderIdExcepte::class)->findBy(['senderId' => $senderId]);
                        if ($lse) {

                            foreach ($lse as $l) {

                                array_push($lContExcep, $l->getException()->getCodePhone() . $l->getException()->getContact());
                            }


                            foreach ($LSmsContact as  $smsContact) {



                                if (
                                    strlen($smsContact->getContact()->getPhone()) != 0 || $smsContact->getContact()->getPhone() != ''
                                ) {
                                    /**
                                     * status == 0 attente , 1 reussi, 2 echec
                                     */
                                    if (in_array($smsContact->getContact()->getPhoneCode() . $smsContact->getContact()->getPhone(), $lContExcep)) {
                                        array_push($lExcep, $smsContact->getContact()->getPhone());
                                    } else {
                                        if ($smsContact->getStatus() && ($smsContact->getResponseApi() != null)) {
                                            array_push($lContactR, $smsContact->getContact()->getPhone());
                                            array_push($lContHisto, ['phone' => $smsContact->getContact()->getPhone(), 'date' =>   date_format($smsContact->getListSmsLotsEnvoye()->getDateCreated(), 'Y-m-d H:i'), 'status' => 1]);
                                        }
                                        if (($smsContact->getStatus() == false) && ($smsContact->getResponseApi() == null)) {
                                            array_push($lContactA, $smsContact->getContact()->getPhone());
                                            array_push($lContHisto, ['phone' => $smsContact->getContact()->getPhone(), 'date' =>   date_format($smsContact->getListSmsLotsEnvoye()->getDateCreated(), 'Y-m-d H:i'), 'status' => 0]);
                                        }
                                        if (($smsContact->getStatus() == false) && ($smsContact->getResponseApi() != null)) {
                                            array_push($lContactEchec, $smsContact->getContact()->getPhone());
                                            array_push($lContHisto, ['phone' => $smsContact->getContact()->getPhone(), 'date' =>   date_format($smsContact->getListSmsLotsEnvoye()->getDateCreated(), 'Y-m-d H:i'), 'status' => 2]);
                                        }
                                    }
                                }
                            }

                            return
                                new JsonResponse([
                                    'data' => [
                                        'senderId' => $senderId,
                                        'message' => $message,
                                        'route' => strtoupper($route),
                                        'nombreContact' => count($lContactR) + count($lContactA) + count($lContactEchec),
                                        'reussis' => $lContactR,
                                        'attente' => $lContactA,
                                        'echec' => $lContactEchec,
                                        'excepte' => $lExcep,
                                        'historique' => $lContHisto
                                    ]


                                ], 201);
                        } else {
                            foreach ($LSmsContact as  $smsContact) {






                                if ($smsContact->getStatus() && ($smsContact->getResponseApi() != null)) {
                                    array_push($lContactR, $smsContact->getContact()->getPhone());
                                    array_push($lContHisto, ['phone' => $smsContact->getContact()->getPhone(), 'date' =>  date_format($smsContact->getListSmsLotsEnvoye()->getDateCreated(), 'Y-m-d H:i'), 'status' => 1]);
                                }
                                if (($smsContact->getStatus() == false) && ($smsContact->getResponseApi() == null)) {
                                    array_push($lContactA, $smsContact->getContact()->getPhone());
                                    array_push($lContHisto, ['phone' => $smsContact->getContact()->getPhone(), 'date' =>  date_format($smsContact->getListSmsLotsEnvoye()->getDateCreated(), 'Y-m-d H:i'), 'status' => 0]);
                                }
                                if (($smsContact->getStatus() == false) && ($smsContact->getResponseApi() != null)) {
                                    array_push($lContactEchec, $smsContact->getContact()->getPhone());
                                    array_push($lContHisto, ['phone' => $smsContact->getContact()->getPhone(), 'date' =>  date_format($smsContact->getListSmsLotsEnvoye()->getDateCreated(), 'Y-m-d H:i'), 'status' => 2]);
                                }
                            }

                            return
                                new JsonResponse([
                                    'data' => [
                                        'senderId' => $senderId,
                                        'message' => $message,
                                        'route' => strtoupper($route),
                                        'nombreContact' => count($lContactR) + count($lContactA) + count($lContactEchec),
                                        'reussis' => $lContactR,
                                        'attente' => $lContactA,
                                        'echec' => $lContactEchec,
                                        'excepte' => $lExcep,
                                        'historique' => $lContHisto

                                    ]


                                ], 201);
                        }
                    } else {
                        return
                            new JsonResponse([
                                'message' => 'UNe Erreur est survenue'


                            ], 400);
                    }
                } else {
                    return
                        new JsonResponse([
                            'message' => 'UNe Erreur est survenue'


                        ], 400);
                }
            } else {
                return
                    new JsonResponse([
                        'message' => 'UNe Erreur est survenue'


                    ], 400);
            }
        } else {
            return
                new JsonResponse([
                    'message' => 'UNe Erreur est survenue'


                ], 400);
        }
    }






    /**
     *@Route("/sms/datas/examiner", name="ExaminerDatasSms", methods={"POST"})
     * @param Request $data continet les donnees suivantes: senderId, destinataires,idRoute, message,  
     * @param  HttpClientInterface $clientWeb l'element utilise pour effectuer les requettes
     * @throws Exception
     * 
     */
    public function ExaminerDatasSms(Request $request)
    {

        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request->toArray();

        $senderId = $data['senderId'];
        $statusSpecial = $data['statusSpecial'];


        $destinataire
            = array_unique($data['destinataire']);
        $coutSms = count($destinataire) * ceil(count(str_split($data['message'])) / (($statusSpecial ? 70  :  159)));
        $message = $data['message'];
        $idRoute = $data['idRoute'];
        $correct = true;
        $exceptionText = false;
        $exceptionSenderId = false;
        $exceptionMessage = 'Vous avez utilise un text interdit dans votre message veuillez corriger cela';
        $exceptionNumber = 'Votre senderId a ete excepte par un de vos contact veuillez corriger cela';
        $exceptionAll = 'Vous avez utilise un text interdit dans votre message et un de vos numero a excepte ce senderId veuillez corriger cela';
        $mediatise = (!is_bool($data['mediatise'])) ? 'Aucune' : ($data['mediatise'] == true ? 'Aucune' : "Les SMS mediatisés ont un plus grands succès lors des campagnes. En plus de vous permettres de garantir un suivit réel de la campagne, elle fournit des statistiques reels sur l'impact de la campagne au près de la cible suivant toute la periode.");
        $doublons =  array_diff_assoc($data['destinataire'], array_unique($destinataire));
        $BulkEntityManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');
        if (empty($data['senderId']) || empty($data['destinataire']) || empty($data['message']) || empty($data['idRoute'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete'
            ], 400);
        }


        $route = $routeManager->getRepository(EntityRouteRoute::class)->findOneBy(['id' => $idRoute]);
        $lExcep = [];
        $lContExcep = [];
        $lContact = [];
        $lExceptionText = [];
        $lMessageFinal = [];
        $lMessage = explode(' ', $message);
        $lExceptioGn = $BulkEntityManager->getRepository(BulkException::class)->findAll();
        foreach ($lExceptioGn as $eText) {
            if ($eText->getText() && $eText->isStatus()) {
                array_push($lExceptionText, strtoupper($eText->getText()));
            }
        }




        foreach ($lMessage  as  $me) {


            if (in_array(strtoupper($me), $lExceptionText)) {
                $correct = false;
                $exceptionText = true;

                array_push($lMessageFinal, ['text' => $me, 'status' => false]);
            } else {
                array_push($lMessageFinal, ['text' => $me, 'status' => true]);
            }

            // array_push($lContact, ['phone' => $contact, 'status' => true]);
        }


        $lse =    $BulkEntityManager->getRepository(ListSenderIdExcepte::class)->findBy(['senderId' => $senderId]);
        if ($lse) {

            foreach ($lse as $l) {

                array_push($lContExcep, $l->getException()->getCodePhone() . $l->getException()->getContact());
            }


            foreach ($destinataire  as  $contact) {

                if (
                    strlen($contact) != 0 || $contact != ''
                ) {
                    /**
                     * status == 0 attente , 1 reussi, 2 echec
                     */
                    if (in_array($route->getPays()->getCodePhone() . $contact, $lContExcep)) {
                        $correct = false;
                        $exceptionSenderId = true;
                        array_push(
                            $lContact,
                            ['phone' => $contact, 'status' => false]
                        );
                        array_push($lExcep,  $contact);
                    } else {
                        array_push($lContact, ['phone' => $contact, 'status' => true]);
                    }
                }
            }

            $rq = "Votre Campagne SMS est prete\n Cette campagne vous coutera  $coutSms\n Unites  " .

                (!empty($doublons) ?
                    "Nous avons remarqués que les contacts suivants " . implode(" ", $doublons) . " se repettes et les avons supprimees les doublons automatiquement." : "");
            return
                new JsonResponse([
                    'data' => [
                        'senderId' => $senderId,
                        'message' => $lMessageFinal,
                        'messageStart' => $message,
                        'destinataires' =>  $lContact,
                        'route' => strtoupper($route->getPays()->getNom()),
                        'nombreContact' => count($lContact),
                        'nombreExcepte' => count($lExcep),
                        'remarques' => ($exceptionText && $exceptionSenderId) ? $exceptionAll : (($exceptionText) ? $exceptionMessage : (($exceptionSenderId) ? $exceptionNumber  :  $rq)),
                        'suggestions' =>
                        $mediatise,
                        'correct' =>     $correct

                    ]


                ], 201);
        } else {
            foreach ($destinataire  as  $contact) {
                if (
                    strlen($contact) != 0 || $contact != ''
                ) {
                    array_push($lContact, ['phone' => $contact, 'status' => true]);
                }
            }



            $aa
                =   implode(",", $doublons);
            $rq = "Votre Campagne SMS est prete\n Cette campagne vous coutera  $coutSms\n  Unites " .

                (!empty($doublons) ?
                    "Nous avons remarqués que les contacts suivants " . $aa . " se repettes et les avons supprimees les doublons automatiquement." : "");

            return
                new JsonResponse([
                    'data' => [
                        'senderId' => $senderId,
                        'destinataires' =>
                        $lContact,
                        'message' => $lMessageFinal,
                        'messageStart' => $message,

                        'route' => strtoupper($route->getPays()->getNom()),
                        'nombreContact' => count($lContact),
                        'nombreExcepte' => count($lExcep),
                        'remarques' => ($exceptionText && $exceptionSenderId) ?  $exceptionAll : (($exceptionText) ? $exceptionMessage : (($exceptionSenderId) ? $exceptionNumber :  $rq)),
                        'suggestions' =>
                        $mediatise,
                        'correct' =>     $correct


                    ]


                ], 201);


            // } else {
            //     return
            //         new JsonResponse([
            //             'message' => 'UNe Erreur est survenue'


            //         ], 400);
        }
    }



    /**
     * @Route("/smssender/read", name="smsSenderIdRead", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur la keySecret du client
     * 
     */
    public function smsSenderIdRead(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $PubEntityManager = $this->doctrine->getManager('Pub');
        $BulkEntityManager = $this->doctrine->getManager('Bulk');
        // $AuthEntityManager = $this->doctrine->getManager('Auth');
        $data = $request->toArray();
        $listCatFinal = [];
        $listCatThisDay =  [];
        $listCatThisWeek =  [];
        $listCatThisMonth = [];
        $listCatThisYear = [];
        $saveDay = [];

        $saveMonth = [];
        $saveCat = [];
        $saveYear = [];
        $possible = false;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }


        $serializer = $this->get('serializer');
        // foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
        //     if ($lr->isStatus() && $lr->getFonction()->getId() == '32') {
        //         $possible = true;
        //     }
        // }
        // if ($possible) {
        //Block Toutes les categories
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

        $saveListSmsFinalAll = [];
        $saveListSmsFinal = [];
        $Final = [];
        $lsmsU = $BulkEntityManager->getRepository(Sms::class)->findBy(['clientId' => $clientUser->getId()]);





        /**
         * Logique v*****
         * 
         */

        $lsenderU = $BulkEntityManager->getRepository(SenderId::class)->findBy(['clientId' => $clientUser->getId()]);
        foreach ($lsenderU  as $sender) {

            $lsmsUSender = $BulkEntityManager->getRepository(Sms::class)->findBy(['senderId' => $sender]);
            foreach ($lsmsUSender  as $sms) {
                $lse = $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
                $lscontact = $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lse]);
                if (!empty($lscontact)) {
                    array_push($saveListSmsFinalAll, [
                        'idSms' => $sms->getId(),
                        'senderId' => $sender->getSenderId(),
                        'message' =>   $sms->getMessage(),
                        'date' =>   $sms->getDateCreated(),
                        'nombreContact' => count($lscontact)


                    ]);
                }
            }
        }

        foreach ($lsenderU  as $sender) {
            $saveListSms = [];
            $lsmsUSender = $BulkEntityManager->getRepository(Sms::class)->findBy(['senderId' => $sender]);
            foreach ($lsmsUSender  as $sms) {
                $lse = $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
                $lscontact = $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lse]);
                if (!empty($lscontact)) {

                    array_push($saveListSms, [
                        'idSms' => $sms->getId(),
                        'senderId' => $sender->getSenderId(),
                        'message' =>   $sms->getMessage(),
                        'date' =>   $sms->getDateCreated(),
                        'nombreContact' => count($lscontact)


                    ]);
                }
            }
            if (!empty($saveListSms)) {
                array_push($saveListSmsFinal, [

                    'id' => $sender->getId(),
                    'senderId' => $sender->getSenderId(),

                    'sms' => $saveListSms


                ]);
            }
        }









        //Block   le senderId la plus recente de la semaine

        $dateStartWeek = strftime("%d/%m/%Y", strtotime("this week"));
        $dateEndWeek = strftime("%d/%m/%Y", strtotime("this week + 6days"));

        foreach ($lsenderU  as $sender) {
            $saveListSms = [];
            $lsmsUSender = $BulkEntityManager->getRepository(Sms::class)->findBy(['senderId' => $sender]);
            foreach ($lsmsUSender  as $sms) {
                $lse = $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
                $lscontact = $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lse]);
                if (
                    $dateStartWeek <=
                    $sms->getDateCreated()->format('d/m/Y')  && $sms->getDateCreated()->format('d/m/Y')
                    <=      $dateEndWeek
                ) {
                    if (!empty($lscontact)) {
                        array_push($saveListSms, [
                            'idSms' => $sms->getId(),
                            'senderId' => $sender->getSenderId(),
                            'message' =>   $sms->getMessage(),
                            'date' =>   $sms->getDateCreated(),
                            'nombreContact' => count($lscontact)


                        ]);
                    }
                }
            }
            if (!empty($saveListSms)) {
                array_push($listCatThisWeek, [

                    'id' => $sender->getId(),
                    'senderId' => $sender->getSenderId(),

                    'sms' => $saveListSms


                ]);
            }
        }



        if (!empty($listCatThisWeek)) {
            $saveCat = $listCatThisWeek[0];

            for (
                $j = 0;
                $j < count($listCatThisWeek);
                $j++
            ) {

                for ($i = 0; $i < count($listCatThisWeek[$j]['sms']); $i++) {


                    if (
                        $listCatThisWeek[$j]['sms'][$i]['date']
                        >=

                        $saveCat['sms'][$i]['date']
                    ) {
                        $saveCat = $listCatThisWeek[$j];
                    }
                }
            }
        }


        //Block   la senderId la plus recente de ce mois

        $dateStartMonth = strftime("%d/%m/%Y", strtotime(date('Y-m-1')));
        $dateEndMonth =
            date("t/m/y", strtotime("this month"));

        foreach ($lsenderU  as $sender) {
            $saveListSms = [];
            $lsmsUSender = $BulkEntityManager->getRepository(Sms::class)->findBy(['senderId' => $sender]);
            foreach ($lsmsUSender  as $sms) {
                $lse = $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
                $lscontact = $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lse]);
                if (
                    $dateStartMonth <=
                    $sms->getDateCreated()->format('d/m/Y')  && $sms->getDateCreated()->format('d/m/Y')
                    <=      $dateEndMonth
                ) {
                    if (!empty($lscontact)) {
                        array_push($saveListSms, [
                            'idSms' => $sms->getId(),
                            'senderId' => $sender->getSenderId(),
                            'message' =>   $sms->getMessage(),
                            'date' =>   $sms->getDateCreated(),
                            'nombreContact' => count($lscontact)


                        ]);
                    }
                }
            }
            if (!empty($saveListSms)) {
                array_push($listCatThisMonth, [

                    'id' => $sender->getId(),
                    'senderId' => $sender->getSenderId(),

                    'sms' => $saveListSms


                ]);
            }
        }





        if (!empty($listCatThisMonth)) {
            $saveMonth = $listCatThisMonth[0];
            for (
                $j = 0;
                $j < count($listCatThisMonth);
                $j++
            ) {

                for ($i = 0; $i < count($listCatThisMonth[$j]['sms']); $i++) {

                    if (
                        !empty($listCatThisMonth[$j]['sms']) &&

                        !empty($saveMonth)
                    ) {
                        if (
                            $listCatThisMonth[$j]['sms'][$i]['date']
                            >=

                            $saveMonth['sms'][$i]['date']
                        ) {
                            $saveMonth = $listCatThisMonth[$j];
                        }
                    }
                }
            }


            //

        }


        // //Block   la senderId la plus recente de la journee

        $datethisDay = strftime("%d/%m/%Y", strtotime(date('Y-m-d')));

        foreach ($lsenderU  as $sender) {
            $saveListSms = [];
            $lsmsUSender = $BulkEntityManager->getRepository(Sms::class)->findBy(['senderId' => $sender]);
            foreach ($lsmsUSender  as $sms) {
                $lse = $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
                $lscontact = $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lse]);
                if (
                    $datethisDay ==
                    $sms->getDateCreated()->format('d/m/Y')
                ) {
                    if (!empty($lscontact)) {
                        array_push($saveListSms, [
                            'idSms' => $sms->getId(),
                            'senderId' => $sender->getSenderId(),
                            'message' =>   $sms->getMessage(),
                            'date' =>   $sms->getDateCreated(),
                            'nombreContact' => count($lscontact)


                        ]);
                    }
                }
            }
            if (!empty($saveListSms)) {
                array_push($listCatThisDay, [

                    'id' => $sender->getId(),
                    'senderId' => $sender->getSenderId(),

                    'sms' => $saveListSms


                ]);
            }
        }





        if (!empty($listCatThisDay)) {


            $saveDay = $listCatThisDay[0];
            for (
                $j = 0;
                $j < count($listCatThisDay);
                $j++
            ) {

                for ($i = 0; $i < count($listCatThisDay[$j]['sms']); $i++) {


                    if (
                        $listCatThisDay[$j]['sms'][$i]['date']
                        >=

                        $saveDay['sms'][$i]['date']
                    ) {
                        $saveDay = $listCatThisDay[$j];
                    }
                }
            }


            //

        }




        //Block   la senderId la plus recente de l'annee'

        $datethisYearStart = strftime(
            "%d/%m/%Y",
            strtotime(date('Y-01-01'))
        );
        $datethisYearEnd = strftime("%d/%m/%Y", strtotime(date('Y-12-31')));
        foreach ($lsenderU  as $sender) {
            $saveListSms = [];
            $lsmsUSender = $BulkEntityManager->getRepository(Sms::class)->findBy(['senderId' => $sender]);
            foreach ($lsmsUSender  as $sms) {
                $lse = $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
                $lscontact = $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lse]);
                if (
                    $datethisYearStart <=
                    $sms->getDateCreated()->format('d/m/Y')  && $sms->getDateCreated()->format('d/m/Y')
                    <=      $datethisYearEnd
                ) {
                    if (!empty($lscontact)) {
                        array_push($saveListSms, [
                            'idSms' => $sms->getId(),
                            'senderId' => $sender->getSenderId(),
                            'message' =>   $sms->getMessage(),
                            'date' =>   $sms->getDateCreated(),
                            'nombreContact' => count($lscontact)


                        ]);
                    }
                }
            }
            if (!empty($saveListSms)) {
                array_push($listCatThisYear, [

                    'id' => $sender->getId(),
                    'senderId' => $sender->getSenderId(),

                    'sms' => $saveListSms


                ]);
            }
        }



        if (!empty($listCatThisYear)) {
            $saveYear = $listCatThisYear[0];
            for (
                $j = 0;
                $j < count($listCatThisYear);
                $j++
            ) {

                for (
                    $i = 0;
                    $i < count($listCatThisYear[$j]['sms']);
                    $i++
                ) {


                    if (
                        $listCatThisYear[$j]['sms'][$i]['date']
                        >=

                        $saveYear['sms'][$i]['date']
                    ) {
                        $saveYear = $listCatThisYear[$j];
                    }
                }
            }


            //

        }





        $Final = $serializer->serialize(array_reverse($listCatFinal,/*  true */), 'json');
        $FinaDay = $serializer->serialize([array_reverse($saveDay,/*  true */)], 'json');
        $FinalWeek = $serializer->serialize([array_reverse($saveCat,/*  true */)], 'json');
        $FinalMonth = $serializer->serialize([array_reverse($saveMonth,/*  true */)], 'json');
        $FinalYear = $serializer->serialize([array_reverse($saveYear,/*  true */)], 'json');

        $FinalAll = $serializer->serialize($saveListSmsFinalAll, 'json');
        $Final = $serializer->serialize($saveListSmsFinal, 'json');
        return
            new JsonResponse([
                'all'
                =>
                JSON_DECODE($FinalAll), 'data'
                =>
                JSON_DECODE($Final),
                'thisDay'
                =>  JSON_DECODE($FinaDay),
                'thisWeek'
                =>  JSON_DECODE($FinalWeek),
                'thisMonth'
                =>  JSON_DECODE($FinalMonth),
                'thisYear'
                =>  JSON_DECODE($FinalYear),

            ], 201);
        // return
        //     new JsonResponse([
        //         'data'
        //         =>
        //         JSON_DECODE($Final),
        //         'thisDay'
        //         =>  JSON_DECODE($FinaDay),
        //         'thisWeek'
        //         =>  JSON_DECODE($FinalWeek),
        //         'thisMonth'
        //         =>  JSON_DECODE($FinalMonth),
        //         'thisYear'
        //         =>  JSON_DECODE($FinalYear),

        //     ], 201);
        // } else {
        //     return new JsonResponse([
        //         'message' => 'Action impossible000'
        //     ], 400);
        // }
    }




    /**
     * @Route("/sms/read", name="smsRead", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur la keySecret du client
     * 
     */
    public function smsRead(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $PubEntityManager = $this->doctrine->getManager('Pub');
        $BulkEntityManager = $this->doctrine->getManager('Bulk');
        // $AuthEntityManager = $this->doctrine->getManager('Auth');
        $data = $request->toArray();
        $listCatFinal = [];
        $listCatThisDay =  [];
        $listCatThisWeek =  [];
        $listCatThisMonth = [];
        $listCatThisYear = [];
        $saveDay = [];

        $saveMonth = [];
        $saveCat = [];
        $saveYear = [];
        $possible = false;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        $saveListSmsFinal = [];
        $serializer = $this->get('serializer');
        foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            if ($lr->isStatus() && $lr->getFonction()->getId() == '24') {
                $possible = true;
            }
        }
        if ($possible) {



            $Final = [];
            $lsmsU = $BulkEntityManager->getRepository(Sms::class)->findBy(['clientId' => $clientUser->getId()]);
            $saveListSmsFinal = $this->getSmsUser($clientUser->getId(),  $saveListSmsFinal, 1);
            $lclient = $this->em->getRepository(Client::class)->findAll();
            foreach ($lclient  as $cl) {

                if (
                    explode('@',  $cl->getCodeParrain())[0] ==
                    $clientUser->getId() && $cl->getId() !=
                    $clientUser->getId()
                ) {
                    $saveListSmsFinal = $this->getSmsUser($cl->getId(),  $saveListSmsFinal, 2);
                }
            }
        }

        $Final = $serializer->serialize(array_reverse($saveListSmsFinal), 'json');
        return
            new JsonResponse([

                'data'
                => JSON_DECODE($Final),

            ], 201);
    }

    function getSmsUser($idUser,  $data, $type)
    {
        $BulkEntityManager = $this->doctrine->getManager('Bulk');
        // $dataF = [];
        $lsmsU = $BulkEntityManager->getRepository(Sms::class)->findBy(['clientId' => $idUser]);
        $nomPro =
            $type == 1 ? 'Moi même' :    $this->em->getRepository(Client::class)->findOneBy(['id' => $idUser])->getNom();
        // $lsenderU = $BulkEntityManager->getRepository(SenderId::class)->findBy(['clientId' => $clientUser->getId()]);
        foreach ($lsmsU  as $sms) {
            $status = 0;
            $lse = $BulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['sms' => $sms]);
            $lscontact = $BulkEntityManager->getRepository(ListSmsContact::class)->findBy(['listSmsLotsEnvoye' => $lse]);
            if ($sms->getSenderId()) {
                if (!empty($lscontact)) {
                    foreach ($lscontact  as $lc) {
                        if ($sms) {
                            if ($lc->getStatus() == true && ($lc->getResponseApi() != null)) {
                                $status = 1;
                            }
                            if (($lc->getStatus() == false) && ($lc->getResponseApi() == null)) {
                                $status = 0;
                            }
                            if (($lc->getStatus() == false) && ($lc->getResponseApi() != null)) {
                                $status = 2;
                            }
                            array_push($data, [
                                'idSms' => $sms->getId(),
                                'senderId' => $sms->getSenderId()->getSenderId(),
                                'message' =>   $sms->getMessage(),
                                'date' =>   $sms->getDateCreated()->format('d/m/Y H:i'),
                                'status' =>    $status == 0 ? 'Attente' : ($status == 1 ? "Envoye" : "Echec"),
                                'contact' => $lc->getContact()->getPhone(),
                                'proprietaire' => $nomPro,


                            ]);
                        }
                    }
                }
            }
        }
        return $data;
    }
}
