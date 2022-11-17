<?php

// src/Command/Notificationcron.php
namespace App\Command;

use App\Entity\Account\Compte;
use App\Entity\Account\TypeCompte;
use App\Entity\Auth\Client;
use App\Entity\Bulk\Calendrier;
use App\Entity\Bulk\Contact;
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListNotificationContact as BulkListNotificationContact;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\Notification;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\SenderId;
use App\Entity\Bulk\Sms;
use App\Entity\Bulk\TypeNotification;
use App\Entity\Licence\Facture;
use App\Entity\Licence\Licence;
use App\Entity\Licence\ListSMSAchette;
use App\Entity\Route\Operateur;
use App\Entity\Route\Pays;
use App\Entity\Route\Route;
use App\Entity\Route\SenderApi;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\Persistence\ManagerRegistry;
use Exception;
use Proxies\__CG__\App\Entity\Bulk\ListNotificationContact;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class NotificationCron extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:notificationcron';
    private $clientWeb;
    public    $doctrine;
    public function __construct(HttpClientInterface $clientWeb,  ManagerRegistry $doctrine)
    {
        $this->clientWeb = $clientWeb;
        $this->doctrine = $doctrine;
        parent::__construct();
    }


    protected function configure(): void
    {
        $this
            ->setDescription('Manage sending sms')
            ->setHelp('This command allows you to manage how the serveur do to send sms');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $this->makeNotificationSms($io);

        $io->success(sprintf('Operation finished.'));
        return Command::SUCCESS;
    }
    /**
     * ce cron devra etre lance chaque 24heure a et demarre a  7h pour la toute premiere fois
     */
    public function makeNotificationSms(SymfonyStyle $io)
    {
        $tabeSmsAndDest = array();

        $dest = array();
        $contacts = [];
        $listsmscontact = [];
        $listClientId = [];
        $listSmsLotsEnvoye = [];
        $bulkManager = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');
        $licenceManager = $this->doctrine->getManager('Licence');
        $findNotification = $bulkManager->getRepository(Notification::class);
        $findTypeNotification = $bulkManager->getRepository(TypeNotification::class);
        $findNotification = $bulkManager->getRepository(Notification::class);
        $anniversaire = $findTypeNotification->findOneBy(['id' => 1]);
        $frequence = $findTypeNotification->findOneBy(['id' => 2]);
        $findContact = $bulkManager->getRepository(Contact::class);
        $ListNotifications
            =  $findNotification->findAll();;
        setlocale(LC_TIME, "fr_FR");
        $dateStartWeek = strftime("%d-%m", strtotime("this week"));
        $dateEndWeek = strftime("%d-%m", strtotime("this week + 5days"));
        $dateEndMonth = date("t-m", strtotime("this month"));
        $date = new \DateTime();
        $date->modify('+5 minutes');

        foreach ($ListNotifications as $key => $notification) {
            $contacts = $findContact->findBy(['clientId' => $notification->getclientId()]);
            var_dump(date_format(new \DateTime(), 'H:i'));
            if ($notification->isStatus()) {
                foreach ($contacts as $key => $contact) {
                    /**
                     * notification pour les jour d'anniversaitre   [typeNotifications ==1]
                     */
                    if ($notification->getTypeNotification()->getId() ==  $anniversaire->getId()) {
                        if ($contact->getBirdDay()) {
                            // var_dump('fddffd', date_format($contact->getBirdDay(), 'm-d'), date_format(new \DateTime(), 'm-d'));
                            if (date_format($contact->getBirdDay(), 'm-d')   == date_format(new \DateTime(), 'm-d')) {

                                if (date_format(new \DateTime(), 'H:i') == '12:0') {

                                    $pays =
                                        $routeManager->getRepository(Pays::class)->findOneBy([
                                            'codePhone' =>                        $contact->getPhoneCode()
                                        ]);
                                    $route =
                                        $routeManager->getRepository(Route::class)->findOneBy(['pays' => $pays])->getId();

                                    $datas = [
                                        'idClient'
                                        =>
                                        $notification->getclientId(),
                                        'route' => $route,
                                        'contactId' =>
                                        $contact->getId(),

                                        'message' =>  str_replace(
                                            '#nom',
                                            $contact->getNom(),
                                            str_replace(
                                                '#prenom',
                                                $contact->getPrenom(),
                                                $notification->getMessage()
                                            )
                                        ),
                                        'senderId' =>

                                        $notification->getSenderId(),
                                        'calendrier' =>

                                        date_format($date, 'Y-m-d H:i')


                                    ];
                                    var_dump($datas);
                                    $litNotifCont = $this->createListNotfContact($notification, $contact);
                                    if ($litNotifCont) {
                                        $this->sendSmsApi($datas,  $notification->getId());
                                    }
                                }
                            }
                        }
                    }
                    /**
                     * notification par frequence   [typeNotifications ==2]
                     * 
                     * frequence = 1 journaliere
                     * frequence = 2 hebdomadaire
                     * frequence = 3 weeekend
                     * frequence = 4 mensuel
                     * 
                     * 
                     * 
                     * 
                     */
                    else if ($notification->getTypeNotification()->getId() ==  $frequence->getId()) {


                        /**
                         * Notifications journaliere
                         */
                        if ($notification->getFrequence() == 1) {



                            $pays =
                                $routeManager->getRepository(Pays::class)->findOneBy([
                                    'codePhone' =>                        $contact->getPhoneCode()
                                ]);

                            if ($pays) {
                                $route =
                                    $routeManager->getRepository(Route::class)->findOneBy(['pays' => $pays])->getId();
                                if (date_format(new \DateTime(), 'H:i') == '08:00') {
                                    $datas = [
                                        'idClient'
                                        =>
                                        $notification->getclientId(),
                                        'route' => $route,
                                        'contactId' =>
                                        $contact->getId(),

                                        'message' =>
                                        str_replace(
                                            '#nom',
                                            $contact->getNom(),
                                            str_replace(
                                                '#prenom',
                                                $contact->getPrenom(),
                                                $notification->getMessage()
                                            )
                                        ),
                                        'senderId' =>

                                        $notification->getSenderId(),
                                        'calendrier' =>

                                        date_format($date, 'Y-m-d H:i')


                                    ];
                                    // var_dump($datas);
                                    $litNotifCont = $this->createListNotfContact($notification, $contact);
                                    if ($litNotifCont) {
                                        $this->sendSmsApi($datas,  $notification->getId());
                                    }
                                }
                            }
                        }
                        /**
                         * Notifications a chaque debut de semaine
                         */
                        else if ($notification->getFrequence() == 2) {

                            if ($dateStartWeek == date_format(new \DateTime(), 'd-m')) {
                                if (date_format(new \DateTime(), 'H:i') == '08:00') {

                                    $pays =
                                        $routeManager->getRepository(Pays::class)->findOneBy([
                                            'codePhone' =>                        $contact->getPhoneCode()
                                        ]);
                                    $route =
                                        $routeManager->getRepository(Route::class)->findOneBy(['pays' => $pays])->getId();

                                    $datas = [
                                        'idClient'
                                        =>
                                        $notification->getclientId(),
                                        'route' => $route,
                                        'contactId' =>
                                        $contact->getId(),

                                        'message' =>  str_replace(
                                            '#nom',
                                            $contact->getNom(),
                                            str_replace(
                                                '#prenom',
                                                $contact->getPrenom(),
                                                $notification->getMessage()
                                            )
                                        ),
                                        'senderId' =>

                                        $notification->getSenderId(),
                                        'calendrier' =>

                                        date_format($date, 'Y-m-d H:i')


                                    ];
                                    // var_dump($datas);

                                    $litNotifCont = $this->createListNotfContact($notification, $contact);
                                    if ($litNotifCont) {
                                        $this->sendSmsApi($datas,  $notification->getId());
                                    }
                                }
                            }
                        }
                        /**
                         * Notifications a chaque fin de semaine [samedi]
                         */
                        else if ($notification->getFrequence() == 3) {

                            if (
                                $dateEndWeek == date_format(new \DateTime(), 'd-m')
                            ) {
                                if (date_format(new \DateTime(), 'H:i') == '08:00') {

                                    $pays =
                                        $routeManager->getRepository(Pays::class)->findOneBy([
                                            'codePhone' =>                        $contact->getPhoneCode()
                                        ]);
                                    $route =
                                        $routeManager->getRepository(Route::class)->findOneBy(['pays' => $pays])->getId();

                                    $datas = [
                                        'idClient'
                                        =>
                                        $notification->getclientId(),
                                        'route' => $route,
                                        'contactId' =>
                                        $contact->getId(),

                                        'message' =>  str_replace(
                                            '#nom',
                                            $contact->getNom(),
                                            str_replace(
                                                '#prenom',
                                                $contact->getPrenom(),
                                                $notification->getMessage()
                                            )
                                        ),
                                        'senderId' =>

                                        $notification->getSenderId(),
                                        'calendrier' =>

                                        date_format($date, 'Y-m-d H:i')


                                    ];

                                    $litNotifCont = $this->createListNotfContact($notification, $contact);
                                    if ($litNotifCont) {
                                        $this->sendSmsApi($datas,  $notification->getId());
                                    }
                                }
                            }
                        }
                        /**
                         * Notifications a chaque fin de mois [dernier jours]
                         */
                        else if ($notification->getFrequence() == 4) {

                            if (
                                $dateEndMonth == date_format(new \DateTime(), 'd-m')
                            ) {

                                $pays =
                                    $routeManager->getRepository(Pays::class)->findOneBy([
                                        'codePhone' =>                        $contact->getPhoneCode()
                                    ]);
                                $route =
                                    $routeManager->getRepository(Route::class)->findOneBy(['pays' => $pays])->getId();

                                $datas = [
                                    'idClient'
                                    =>
                                    $notification->getclientId(),
                                    'route' => $route,
                                    'contactId' =>
                                    $contact->getId(),

                                    'message' =>  str_replace(
                                        '#nom',
                                        $contact->getNom(),
                                        str_replace(
                                            '#prenom',
                                            $contact->getPrenom(),
                                            $notification->getMessage()
                                        )
                                    ),
                                    'senderId' =>

                                    $notification->getSenderId(),
                                    'calendrier' =>

                                    date_format($date, 'Y-m-d H:i')


                                ];
                                // var_dump($datas);
                                $litNotifCont = $this->createListNotfContact($notification, $contact);
                                if ($litNotifCont) {
                                    $this->sendSmsApi($datas,  $notification->getId());
                                }
                            }
                        }
                    } else {
                    }
                }
            }
        }
    }



    public function sendSmsApi($data, $idNotif)
    {


        $client = $data['idClient'];


        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');



        // $operator = $BulkManager->getRepository(Operateur::class)->findOneBy(['id' => 2]);
        // $apiLink = $BulkManager->getRepository(SenderApi::class)->findOneBy(['operateur' => $operator]);




        $dataSenderId = [
            'client'
            => $client,
            'senderId' =>
            $data['senderId']
        ];


        $newLot = $this->createLot($data, $client);

        if ($newLot) {


            $newSenderid = $this->findSenderId($dataSenderId);

            if ($newSenderid) {
                $dataSms = [
                    'client'
                    => $client,
                    'senderId' =>
                    $newSenderid,
                    'message' => $data['message']
                ];

                $newSms = $this->createSms($dataSms);
                if ($newSms) {
                    $dataLSLE = [
                        'sms'
                        => $newSms,
                        'lot' =>
                        $newLot
                    ];
                    $newLSE = $this->createListSmsLotEnvoye($dataLSLE);
                    if ($newLSE) {

                        $dataRLSLE = [
                            'route'
                            =>
                            $data['route'],
                            'lse' =>
                            $newLSE
                        ];
                        $newRouteLSLE = $this->createRouteLSLE($dataRLSLE);

                        if ($newRouteLSLE) {
                            $dataLSC = [
                                'contact'
                                =>
                                $data['contactId'],
                                'lse' =>
                                $newLSE
                            ];
                            $final =
                                $this->createListSmsContact($dataLSC);


                            if (
                                $final
                            ) {
                                // $bulkManager = $this->doctrine->getManager('Bulk');
                                // $notif
                                //     = $bulkManager->getRepository(Notification::class)->findOneBy([
                                //         'id' =>                    $idNotif
                                //     ]);
                                // $notif->setStatus(true);
                                // $bulkManager->persist($notif);
                                // $bulkManager->flush();
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


        return $newLot->getId();
    }

    /**
     * doc createListNotfContact
     *
     * @param [] $request doit contenir la notif,le contact
     * @return void
     */
    public function createListNotfContact($notif, $contact)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');

        $dateCreated = new \DateTime();
        $newListNotifContact = new BulkListNotificationContact();
        $newListNotifContact->setContact($contact);
        $newListNotifContact->setNotification($notif);
        $newListNotifContact->setDateCreated($dateCreated);
        $bulkManager->persist($newListNotifContact);
        $bulkManager->flush();
        return true;
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
}

/**
 *idClient ,
 route,
 calendrier,
 senderId,
 message,
 contactId
 * 
 * 
 * 


 */
