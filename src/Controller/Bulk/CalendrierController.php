<?php

namespace App\Controller\Bulk;

use App\Entity\Account\TransactionCompte;
use App\Entity\Auth\Client;
use App\Entity\Auth\Fonctions;
use App\Entity\Auth\ListRoleFonctions;
use App\Entity\Auth\Roles;
use App\Entity\Bulk\Calendrier;
use DateTime;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use FFI\Exception;
use Proxies\__CG__\App\Entity\Auth\Module;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Entity\Bulk\Contact;
use App\Entity\Bulk\GroupeContact;
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\Notification;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\SenderId;
use App\Entity\Bulk\Sms;
use App\Entity\Route\Operateur;
use App\Entity\Route\Pays;
use App\Entity\Route\Route as RouteRoute;
use App\Entity\Route\SenderApi;
use Proxies\__CG__\App\Entity\Bulk\Sms as BulkSms;
use Proxies\__CG__\App\Entity\Pub\Publication;
use Symfony\Component\Validator\Constraints\Date;

class CalendrierController  extends AbstractController
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
     * @Route("/calendrier/new", name="calendrierNew", methods={"POST"})
     * 
     * @param Request $data continet les donnees suivantes: message , keySecret du client,la liste des destinataire[Ou l'id du groupe de contact],la route,le senderId ,  un calendrier, qui represente la date a la quelle le message est sense etre envoye
     * @param Client $client represente l'utiliasteur voulant emettre
     * @param Array $data['destinataire'] est un tableau de numeros de destinataire
     * @param  HttpClientInterface $clientWeb l'element utilise pour effectuer les requettes
     * @throws Exception
     * 
     *
     * data doit contenir le type de calendrier[typeCalendar] : 0=>single user, 1=>group, 2=> all User
     */
    public function calendrierNew(Request $request)
    {

        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request->toArray();
        // dd($data);
        if (empty($data['keySecret'])  || empty($data['calendrier'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  calendrier, typeCalendar ,keySecret sont requis'
            ], 400);
        }


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
        if ($data['typeCalendar'] == 0) {


            $dataFindContact = [
                'client'
                => $client,
                'contacts' =>
                $data['destinataire'],
                'codePhone' =>        $phoneCode
            ];
        } else  if ($data['typeCalendar'] == 1) {
            /**
             * si l'utilisateur precise un groude de contact existant 
             */
            $destinataires = [];

            $groupe = $BulkManager->getRepository(GroupeContact::class)->findOneBy(['id' => $data['groupeContact']]);

            $lgc = $BulkManager->getRepository(ListGroupeContact::class)->findBy(['groupeContact' => $groupe]);

            if ($groupe) {
                foreach ($lgc as $lg) {

                    if ($lg->getContact()) {
                        $contact = $BulkManager->getRepository(Contact::class)->findOneBy(['id' => $lg->getContact()->getId()]);
                        if ($contact) {
                            array_push($destinataires, $contact->getPhone());
                        } # code...
                    } # code...
                    // var_dump($lg->getContact()->getPhone());
                }
                if (!empty($destinataires)) {

                    $dataFindContact = [
                        'client'
                        => $client,
                        'contacts' =>
                        $destinataires,  'codePhone' =>        $phoneCode
                    ];
                } else {
                    return new JsonResponse([
                        'message' => 'groupe vide'
                    ], 203);
                }
            } else {
                return new JsonResponse([
                    'message' => 'groupe innexistant'
                ], 400);
            }
        } else if ($data['typeCalendar'] == 2) {
            $destinataires = [];
            $all = $BulkManager->getRepository(Contact::class)->findBy(['clientId' => $client]);
            if ($all) {
                foreach ($all as $cont) {
                    if ($cont) {
                        if ($cont->getPhone() !== null) {
                            array_push($destinataires, $cont->getPhone());
                        } # code...
                    } # code...
                    // var_dump($groupe->getContact()->getPhone());
                }
                if (!empty($destinataires)) {

                    $dataFindContact = [
                        'client'
                        => $client,
                        'contacts' =>
                        $destinataires,  'codePhone' =>        $phoneCode
                    ];
                } else {
                    return new JsonResponse([
                        'message' => 'list phone Contact vide'
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Aucun Contact '
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
                    'message' => $data['message']
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
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  calendrier manquant'
            ], 400);
        } else {


            $newCalendar = new Calendrier();
            $newCalendar->setDateProgramme(new DateTime($data['calendrier']));
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
            var_dump('initttttttttttt');
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
     * @Route("/calendrier/read", name="calendrierRead", methods={"POST"})
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
     * @param array $data doit contenir la cle secrete du client
     * 
     * 
     */
    public function calendrierRead(Request $request)
    {
        $data = $request->toArray();
        $possible = false;
        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete   '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);



        $serializer = $this->get('serializer');

        $lcalendarSms  = [];

        $listlotc = $BulkManager->getRepository(ListLotCalendrier::class)->findAll();
        $all = $BulkManager->getRepository(Calendrier::class)->findBy(['clientId' => $clientUser->getId()]);

        if (
            $listlotc
        ) {
            foreach ($listlotc  as $llc) {

                if ($llc->getCalendrier()->getClientId() == $clientUser->getId()) {

                    $lot = $llc->getLot();
                    if ($lot) {
                        $listlote = $BulkManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['lot' => $lot]);
                        if ($listlote) {
                            if ($listlote->getSms()) {
                                $calen = [
                                    'expediteur'
                                    => $listlote->getSms()->getSenderId()->getSenderId(),  'message'
                                    => $listlote->getSms()->getMessage(),
                                    'dateCreate' =>
                                    $listlote->getDateCreated()->format('d/m/Y'),
                                    'dateProgramme' =>   $llc->getCalendrier()->getDateProgramme()->format('m/d/Y'),
                                    'status' => $llc->getCalendrier()->getStatus() ? "Active" : "Desactive",
                                    'idCalendrier' => $llc->getCalendrier()->getId()
                                ];
                                array_push($lcalendarSms, $calen);
                            }
                        }
                    }
                }
            }
            $lcalendarSmsF = $serializer->serialize(array_reverse($lcalendarSms), 'json');

            return
                new JsonResponse(
                    [
                        'data'
                        =>
                        JSON_DECODE($lcalendarSmsF)
                    ],
                    201
                );
        } else {
            return new JsonResponse([
                'message' => 'Aucun calendriere ',
                'data'
                => [],
            ], 203);
        }
    }
    /**
     * @Route("/notifications/read", name="notificationsRead", methods={"POST"})
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
     * @param array $data doit contenir la cle secrete du client
     * 
     * 
     */
    public function notificationsRead(Request $request)
    {
        $data = $request->toArray();
        $possible = false;
        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete   '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);



        $serializer = $this->get('serializer');

        $lnotif  = [];

        $notifications = $BulkManager->getRepository(Notification::class)->findBy(['clientId' =>  $clientUser->getId()]);

        if (
            $notifications
        ) {
            foreach ($notifications  as $ntc) {




                $calen = [
                    'message'
                    => $ntc->getMessage(),
                    'date' =>
                    $ntc->getDateCreated()->format('d/m/Y'),
                    'senderId' =>   $ntc->getSenderId(),
                    'status' => $ntc->isStatus() ? "Active" : "Desactive",
                    's1' => $ntc->isStatus(),
                    'typeNotification' => $ntc->getTypeNotification()->getLibelle(),
                    'frequence' => $ntc->getFrequence(),
                    'id' => $ntc->getId()
                ];
                array_push($lnotif, $calen);
            }
            $lnotifF = $serializer->serialize(array_reverse($lnotif), 'json');

            return
                new JsonResponse(
                    [
                        'data'
                        =>
                        JSON_DECODE($lnotifF)
                    ],
                    201
                );
        } else {
            return new JsonResponse([
                'message' => 'Aucun calendriere ',
                'data'
                => [],
            ], 203);
        }
    }

    /**
     * @Route("/calendrier/update", name="calendrierUpdate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client,idcalendrier et les valeur a changer 
     * 
     */
    public function calendrierUpdate(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $bulkEntityManager = $this->doctrine->getManager('Bulk');
        $data = $request->toArray();

        $possible = true;

        if (empty($data['id'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre calendrier '
            ], 400);
        }

        $serializer = $this->get('serializer');


        $idcalendrier = $data['id'];

        $calendrier = $bulkEntityManager->getRepository(Calendrier::class)->findOneBy(['id' => $idcalendrier]);


        if ($calendrier) {
            // $clientUser =
            //     $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

            // foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            //     if ($lr->isStatus() && $lr->getFonction()->getId() == '27') {
            //         $possible = true;
            //     }
            // }


            if ($possible) {
                if (!empty($data['message'])) {
                    $lot
                        = $bulkEntityManager->getRepository(ListLotCalendrier::class)->findOneBy(['calendrier' => $calendrier])->getLot();

                    $sms =
                        $bulkEntityManager->getRepository(ListSmsLotsEnvoye::class)->findOneBy(['lot' => $calendrier])->getSms();

                    $mess =  $bulkEntityManager->getRepository(Sms::class)->findOneBy(['id' =>  $sms->getId()]);
                    $mess->setMessage($data['message']);
                }
                // if (!empty($data['senderId'])) {
                //     $mess =    $calendrier->getListLotCalendriers()->getLot()->getListSmsLotsEnvoyes()->getSms()->setMessage($data['senderId']);
                // }
                if (!empty($data['dateProgramme'])) {
                    $calendrier->setDateProgramme(new DateTime($data['dateProgramme']));
                }
                /*  if (!empty($data['status'])) {
                    $calendrier->setStatus($data['status']);
                }
 */

                $bulkEntityManager->persist($mess);
                $bulkEntityManager->persist($calendrier);
                $bulkEntityManager->flush();
                return
                    new JsonResponse([
                        'message'
                        =>      'success',

                    ], 201);
            } else {
                return new JsonResponse([
                    'message' => 'Action impossible '
                ], 203);
            }
        } else {
            return new JsonResponse([
                'message' => 'Action impossible '
            ], 203);
        }
    }
    /**
     * @Route("/notification/update", name="notificationUpdate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client,idnotification et les valeur a changer 
     * 
     */
    public function notificationUpdate(Request $request)
    {

        $bulkEntityManager = $this->doctrine->getManager('Bulk');
        $data = $request->toArray();

        $possible = true;

        if (empty($data['id'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre calendrier '
            ], 400);
        }

        $serializer = $this->get('serializer');


        $idnotification = $data['id'];

        $notif = $bulkEntityManager->getRepository(Notification::class)->findOneBy(['id' => $idnotification]);


        if ($notif) {

            if ($possible) {

                $notif->setMessage($data['message']);

                $notif->setSenderId($data['senderId']);

                $bulkEntityManager->persist($notif);
                $bulkEntityManager->flush();
                return
                    new JsonResponse([
                        'message'
                        =>      'success',

                    ], 201);
            } else {
                return new JsonResponse([
                    'message' => 'Action impossible '
                ], 203);
            }
        } else {
            return new JsonResponse([
                'message' => 'Action impossible '
            ], 203);
        }
    }
    /**
     * @Route("/notification/status", name="notificationStatus", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client,idcalendrier et les valeur a changer 
     * 
     */
    public function notificationStatus(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $bulkEntityManager = $this->doctrine->getManager('Bulk');
        $data = $request->toArray();

        $possible = true;

        if (empty($data['id'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre notification '
            ], 400);
        }

        $serializer = $this->get('serializer');


        $idnotification = $data['id'];

        $notification = $bulkEntityManager->getRepository(Notification::class)->findOneBy(['id' => $idnotification]);


        if ($notification) {
            // $clientUser =
            //     $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

            // foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            //     if ($lr->isStatus() && $lr->getFonction()->getId() == '27') {
            //         $possible = true;
            //     }
            // }


            if ($possible) {



                $notification->setStatus(!$notification->isStatus());


                $bulkEntityManager->persist($notification);
                $bulkEntityManager->flush();
                return
                    new JsonResponse([
                        'message'
                        =>      'success',

                    ], 201);
            } else {
                return new JsonResponse([
                    'message' => 'Action impossible '
                ], 203);
            }
        } else {
            return new JsonResponse([
                'message' => 'Action impossible '
            ], 203);
        }
    }
}
