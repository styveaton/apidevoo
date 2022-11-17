<?php

namespace App\Controller\User;

use App\Entity\Account\Compte;
use App\Entity\Account\TypeCompte;
use App\Entity\Auth\Client;
use App\Entity\Auth\ListRoleFonctions;
use App\Entity\Licence\ListSMSAchette;
use App\Entity\Route\Route as RouteRoute;
use App\Entity\User\ListProjetClient;
use App\Entity\User\Projet;
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
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\SenderId;
use App\Entity\Bulk\Sms;
use App\Entity\Route\Operateur;
use App\Entity\Route\Pays;
use App\Entity\Route\SenderApi;

class UserProjetController extends AbstractController
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

    public function getUniqueSecretKey()
    {
        $UserEntityManager = $this->doctrine->getManager('User');


        $getAll = $UserEntityManager->getRepository(Projet::class)->findAll();
        //  dd(count($getAll));
        $chaine = 'DevooProjet';
        $listeCar = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = mb_strlen($listeCar, '8bit') - 1;
        for ($i = 0; $i < 7; ++$i) {
            $chaine .= $listeCar[random_int(0, $max)];
        }

        $existKey = $UserEntityManager->getRepository(Projet::class)->findOneBy(['apiKey' => $chaine . count($getAll)]);
        if ($existKey) {
            return
                $this->getUniqueSecretKey();
        } else {
            return $chaine;
        }
    }


    /**
     * @Route("/send", name="sen", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur l'apiKey du projet
     * 
     */
    public function send()
    {
        $token = '5596207604:AAEPngtaa9_uet7yrqnxgxhA8S8Q48ZC79g';
        var_dump("voici les data de send.........");
        $data = [
            'chat_id' => '@Gessiiabot',
            'text' => 'sdddddddddd',
        ];

        $response = $this->client->request(
            'GET',
            "https://api.telegram.org/bot" . $token . "/sendMessage?" . http_build_query($data),
            [
                'body'
                => $data
            ]
        );
    }

    /**
     * @Route("/projet/refresh", name="refershprojet", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur l'apiKey du projet
     * 
     */
    public function refreshProjet(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $data = $request->toArray();

        if (empty($data['apiKey'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete'
            ], 400);
        } else {
            $projet = $UserEntityManager->getRepository(Projet::class)->findOneBy(['apiKey' => $data['apiKey']]);

            $projet->setApiKey($this->getUniqueSecretKey());

            $UserEntityManager->persist($projet);
            $UserEntityManager->flush();
            return
                new JsonResponse([
                    'message'
                    =>   'Projet Mis a jour avec succes',
                    'apiKey'
                    =>
                    $projet->getApiKey(),

                ], 201);
        }
    }
    /**
     * @Route("/projet/new", name="newprojet", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur clientId, quantiteSms,nomProjet, idListSmsAchette pour une route a utiliser
     * 
     */
    public function newProjet(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $licenceManager = $this->doctrine->getManager('Licence');
        $routeEntityManager = $this->doctrine->getManager('Route');
        $UserEntityManager = $this->doctrine->getManager('User');
        $data = $request->toArray();


        if (empty($data['clientId']) || empty($data['quantiteSms']) || empty($data['idListSmsAchette']) || ($data['quantiteSms']  < 20) || empty($data['nomProjet'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete'
            ], 400);
        } else {
            $clientId
                = $data['clientId'];
            $client =
                $this->em->getRepository(Client::class)->findOneBy(['id' => $clientId]);
            if ($client) {


                foreach ($client->getRole()->getListRoleFonctions()  as $lr) {
                    if ($lr->isStatus() && $lr->getFonction()->getId() == '12') {
                        $possible = true;
                    }
                }
                if ($possible) {

                    $apiLink = '';
                    $description = [];

                    $quantiteSms =
                        $data['quantiteSms'];
                    $idListSmsAchette =
                        $data['idListSmsAchette'];
                    $nomProjet = $data['nomProjet'];
                    $typeCompte = $AccountEntityManager->getRepository(
                        TypeCompte::class
                    )->findOneBy(['id' => 3]);

                    $compte =
                        $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $clientId, 'typeCompte' => $typeCompte]);

                    $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['id' => $idListSmsAchette]);

                    if ($compte->getSolde() >= $quantiteSms && $lsa->getQuantite() >= $quantiteSms) {
                        $routeId = $routeEntityManager->getRepository(RouteRoute::class)->findOneBy(['id' => $lsa->getRouteId()])->getId();

                        if ($routeId == 1) {

                            $apiLink = $_SERVER['SERVER_NAME'] . "/send/projet/smscameroon";
                            $description =    $_SERVER['SERVER_NAME'] . '/send/projet/smscameroon?apiKey=XXXXXXXXXXX&message=Bonjour&destinataires=[6XXXXXXXX,6YYYYYYYY]&senderId=Bulk';
                        } else 
     if ($routeId == 2) {
                            $apiLink = $_SERVER['SERVER_NAME'] . "/send/projet/smsguinee";
                            $description =    $_SERVER['SERVER_NAME'] . '/send/projet/smsguinee?apiKey=XXXXXXXXXXX&message=Bonjour&destinataires=[6XXXXXXXX,6YYYYYYYY]&senderId=Bulk';
                        }
                        if ($routeId == 3) {
                            $apiLink = $_SERVER['SERVER_NAME'] . "/send/projet/smscameroon";
                        }

                        $projet = new Projet();
                        $projet->setApiKey($this->getUniqueSecretKey());
                        $projet->setNomProjet($nomProjet);
                        $projet->setSoldeSms($quantiteSms);
                        $projet->setLicenceId($idListSmsAchette);
                        $projet->setApiLink($apiLink);
                        $projet->setDescriptionApiLink($description);

                        $lprojetClient = new ListProjetClient();
                        $lprojetClient->setClientId($clientId);
                        $lprojetClient->setProjet($projet);

                        $UserEntityManager->persist($projet);
                        $UserEntityManager->persist($lprojetClient);
                        $UserEntityManager->flush();
                        $response = [
                            'apiKey' =>
                            $projet->getApiKey(),
                            "apiLink"
                            => $projet->getApiLink(),
                            "description_api"
                            => $projet->getDescriptionApiLink(),
                            'solde_sms' => $projet->getSoldeSms()

                        ];
                        return
                            new JsonResponse([
                                'message'
                                =>   'Projet Cree avec succes',
                                'data'
                                =>
                                $response,

                            ], 201);
                    } else {
                        return
                            new JsonResponse([
                                'message'
                                =>   'Solde sms insuffisant pour creer ce projet',
                                'data'
                                =>
                                [],

                            ], 203);
                    }
                } else {
                    return
                        new JsonResponse([
                            'message'
                            =>   'Une erreur est survenue',
                            'data'
                            =>
                            [],

                        ], 400);
                }
            } else {
                return
                    new JsonResponse([
                        'message'
                        =>   'Une erreur est survenue',
                        'data'
                        =>
                        [],

                    ], 400);
            }
        }
    }


    /**
     * @Route("/projet/read", name="projetRead", methods={"POST"})
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
    public function projetRead(Request $request)
    {

        $projetUser = [];

        $userManager = $this->doctrine->getManager('User');

        $data = $request->toArray();
        $possible = false;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {
            $serializer = $this->get('serializer');

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '13') {
                    $possible = true;
                }
            }


            if ($possible) {
                $listprojetC =
                    $userManager->getRepository(ListProjetClient::class)->findBy(['clientId' => $clientUser->getId()]);

                if ($listprojetC) {
                    foreach ($listprojetC  as $lps) {

                        if ($lps) {

                            $lse = [
                                'id' => $lps->getProjet()->getId(),
                                'nomProjet' => $lps->getProjet()->getNomProjet(),
                                'soldeSms' =>  $lps->getProjet()->getSoldeSms(),

                                'date' => $lps->getDate()->format('d/m/Y'),
                            ];
                            array_push($projetUser,  $lse);
                        }
                    }

                    $projetUserFinal = $serializer->serialize(array_reverse($projetUser), 'json');


                    return
                        new JsonResponse([
                            'data'
                            =>
                            JSON_DECODE($projetUserFinal),

                        ], 200);
                } else {
                    return new JsonResponse([
                        'data'
                        =>
                        [],
                        'message' => 'Action impossible'
                    ], 200);
                }
            } else {
                return new JsonResponse([
                    'data'
                    => [],
                    'message' => 'Action impossible'
                ], 200);
            }
        } else {
            return new JsonResponse([
                'message' => 'Client introuvable'
            ], 400);
        }
    }

    /**
     * @Route("/projet/solde", name="soldeProjet", methods={"POST"})
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
     * 
     */
    public function soldeProjet(Request $request)
    {

        $data = $request->toArray();


        $UserEntityManager = $this->doctrine->getManager('User');
        $licenceManager = $this->doctrine->getManager('Licence');
        $routeManager = $this->doctrine->getManager('Route');
        if (empty($data['apiKey'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete, veuillez preciser votre cle d\'api projet'
            ], 400);
        }
        $projet = $UserEntityManager->getRepository(Projet::class)->findOneBy(['apiKey' => $data['apiKey']]);
        $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['id' => $projet->getLicenceId()]);
        if (!$lsa) {

            return new JsonResponse([
                'message' => 'Une Erreur est survenue, veuillez contacter l\'administrateur'
            ], 500);
        }

        $route  =
            $routeManager->getRepository(RouteRoute::class)->findOneBy(['id' => $lsa->getRouteId()]);
        if (!$route) {

            return new JsonResponse([
                'message'
                => 'Une Erreur est survenue, veuillez contacter l\'administrateur'
            ], 500);
        }
        if ($projet) {
            return new JsonResponse([
                'solde' => 'Le solde sms de votre projet est de : ' .
                    $projet->getSoldeSms() . ' sms',
                'licence' => 'Licence sms pour le ' . $route->getPays()->getNom()
            ], 200);
        } else {
            return new JsonResponse([
                'message' => 'Invalid apiKey'
            ], 400);
        }
    }

    /**
     *@Route("/send/projet/smscameroon", name="sendProjetSmsCameroun", methods={"GET"})
     * @param Request $data continet les donnees suivantes: message , apiKey du projet,la liste des destinataire,le senderId ,
     * 
     * 
     * /// on peut aussi ajouter un calendrier, qui represente la date a la quelle le message est sense etre envoye
     * @param
     * @param Array $data['destinataires'] est un tableau de numeros de destinataire
     * @param  HttpClientInterface $clientWeb l'element utilise pour effectuer les requettes
     * @throws Exception
     * 
     */
    public function sendProjetSmsCamerounApi(Request $request)
    {
        $userManager = $this->doctrine->getManager('User');
        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request->query->all();
        $destinataire = json_decode($data['destinataires']);

        if (empty($data['apiKey']) || empty($data['message']) || empty($destinataire) || empty($data['senderId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  apiKey, message ,destinataires, senderId sont requis'
            ], 400);
        }
        $destinataires =  $destinataire;
        $projet = $userManager->getRepository(Projet::class)->findOneBy(['apiKey' => $data['apiKey']]);
        if ($projet) {
            $projetId =    $projet->getId();
        } else {
            return new JsonResponse([
                'message' => 'Invalid apiKey'
            ], 400);
        }
        $numberByOperator = array();

        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');

        $phoneCode =
            $routeManager->getRepository(RouteRoute::class)->findOneBy(['id' => 1])->getPays()->getCodePhone();

        if (!empty($destinataires)) {

            $dataFindContact = [
                'projetId'
                => $projetId,
                'contacts' =>
                $destinataires
            ];
        } else {
            return new JsonResponse([
                'message' => 'liste des destinataires vide'
            ], 400);
        }



        // dd($dataFindContact);

        $dataSenderId = [
            'projetId'
            => $projetId,
            'senderId' =>
            $data['senderId']
        ];
        $lms = count($destinataires) * ceil(count(str_split($data['message'])) /   70);

        if ($lms < $projet->getSoldeSms()) {
            $newLot = $this->createLot($data);

            if ($newLot != null) {


                $newSenderid = $this->findSenderId($dataSenderId);

                if ($newSenderid != null) {
                    $dataSms = [
                        'projetId'
                        => $projetId,
                        'senderId' =>
                        $newSenderid,
                        'message' => $data['message']
                    ];

                    $newSms = $this->createSms($dataSms);
                    if (


                        $newSms
                        != null
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

                                'lse' =>
                                $newLSE
                            ];
                            $newRouteLSLE = $this->createRouteLSLE($dataRLSLE);

                            if ($newRouteLSLE != null) {
                                $final = $this->findContact($dataFindContact, $newLSE);
                                $projet->setSoldeSms($projet->getSoldeSms() - $lms);
                                $userManager->persist($projet);
                                $userManager->flush();
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
        } else {
            return new JsonResponse([
                'message' => 'Solde Insuffisant'
            ], 400);
        }
    }



    /**
     *@Route("/send/projet/smsguinee", name="sendProjetSmsGuinee", methods={"GET"})
     * @param Request $data continet les donnees suivantes: message , apiKey du projet,la liste des destinataire,le senderId ,
     * 
     * 
     * /// on peut aussi ajouter un calendrier, qui represente la date a la quelle le message est sense etre envoye
     * @param
     * @param Array $data['destinataires'] est un tableau de numeros de destinataire
     * @param  HttpClientInterface $clientWeb l'element utilise pour effectuer les requettes
     * @throws Exception
     * 
     */
    public function sendProjetSmsGuineeApi(Request $request)
    {
        $userManager = $this->doctrine->getManager('User');
        $destinatireFinal
            = array();
        $destinatireError
            = array();
        $dataSucess = array();
        $data = $request->query->all();
        $destinataire = json_decode($data['destinataires']);

        if (empty($data['apiKey']) || empty($data['message']) || empty($destinataire) || empty($data['senderId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  apiKey, message ,destinataires, senderId sont requis'
            ], 400);
        }
        $destinataires =  $destinataire;
        $projet = $userManager->getRepository(Projet::class)->findOneBy(['apiKey' => $data['apiKey']]);
        if ($projet) {
            $projetId =    $projet->getId();
        } else {
            return new JsonResponse([
                'message' => 'Invalid apiKey'
            ], 400);
        }
        $numberByOperator = array();

        $BulkManager
            = $this->doctrine->getManager('Bulk');
        $routeManager =
            $this->doctrine->getManager('Route');

        $phoneCode =
            $routeManager->getRepository(RouteRoute::class)->findOneBy(['id' => 2])->getPays()->getCodePhone();

        if (!empty($destinataires)) {

            $dataFindContact = [
                'projetId'
                => $projetId,
                'contacts' =>
                $destinataires
            ];
        } else {
            return new JsonResponse([
                'message' => 'liste des destinataires vide'
            ], 400);
        }



        // dd($dataFindContact);

        $dataSenderId = [
            'projetId'
            => $projetId,
            'senderId' =>
            $data['senderId']
        ];

        $lms = count($destinataires) * ceil(count(str_split($data['message'])) /   70);
        if ($lms < $projet->getSoldeSms()) {
            $newLot = $this->createLot($data);

            if ($newLot != null) {


                $newSenderid = $this->findSenderId($dataSenderId);

                if ($newSenderid != null) {
                    $dataSms = [
                        'projetId'
                        => $projetId,
                        'senderId' =>
                        $newSenderid,
                        'message' => $data['message']
                    ];

                    $newSms = $this->createSms($dataSms);
                    if (


                        $newSms
                        != null
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

                                'lse' =>
                                $newLSE
                            ];
                            $newRouteLSLE = $this->createRouteLSLE($dataRLSLE);

                            if ($newRouteLSLE != null) {

                                $projet->setSoldeSms($projet->getSoldeSms() - $lms);
                                $userManager->persist($projet);
                                $userManager->flush();
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
        } else {
            return new JsonResponse([
                'message' => 'Solde Insuffisant'
            ], 400);
        }
    }

    /**
     * doc createLot
     *
     * @param [] $request doit contenir idCient,la liste de  contact
     * @return void
     */
    public function createLot($data)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');


        $newLot = new Lot();

        $bulkManager->persist($newLot);
        $bulkManager->flush();

        // if (empty($data['calendrier'])) {
        // } else {


        //     $newCalendar = new Calendrier();
        //     $newCalendar->setDateExecution(new DateTime($data['calendrier']));
        //     $newCalendar->setClientId($client);

        //     $bulkManager->persist($newCalendar);
        //     $bulkManager->flush();


        //     $newlistLotCalendar = new ListLotCalendrier();
        //     $newlistLotCalendar->setCalendrier($newCalendar);
        //     $newlistLotCalendar->setLot($newLot);
        //     $bulkManager->persist($newlistLotCalendar);
        //     $bulkManager->flush();
        // }

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


        if (empty($data['projetId']) || empty($data['senderId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  senderId, CientId'
            ], 400);
        }

        $finalSenderId = null;
        $SenderId = $data['senderId'];


        $ifSenderId = $bulkManager->getRepository(SenderId::class)->findOneBy(['senderId' => $SenderId, 'projetId' => $data['projetId']]);
        if ($ifSenderId) {
            $finalSenderId = $ifSenderId->getId();
        } else {

            $newSenderId = new SenderId();
            $newSenderId->setSenderId($SenderId);
            $newSenderId->setDescription('');
            $newSenderId->setProjetId($data['projetId']);
            $bulkManager->persist($newSenderId);
            $bulkManager->flush();
            $finalSenderId = $newSenderId->getId();
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
        if (empty($data['contacts']) || empty($data['projetId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete  contact, Cient Id'
            ], 400);
        } else {
            $idContact = 0;
            $bulkManager = $this->doctrine->getManager('Bulk');

            $contacts = $data['contacts'];

            for ($i = 0; $i <   count($contacts); $i++) {
                $contactI = $contacts[$i];

                $ifContact = $bulkManager->getRepository(Contact::class)->findOneBy(['phone' => $contactI, 'projetId' => $data['projetId']]);
                if ($ifContact) {
                    $idContact = $ifContact->getId();
                } else {

                    $newContact = new Contact();
                    $newContact->setNom('Nom Contact');
                    $newContact->setPrenom('Prenom Contact');
                    $newContact->setPhone($contactI);
                    $newContact->setPhoneCode('237');
                    $newContact->setProjetId($data['projetId']);
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
        if (empty($data['message']) || empty($data['projetId']) || empty($data['senderId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete message, senderId, Projet Id'
            ], 400);
        }

        $bulkManager = $this->doctrine->getManager('Bulk');

        $senderId = $bulkManager->getRepository(SenderId::class)->findOneBy(['id' => $data['senderId']]);

        $newSms = new Sms();
        $newSms->setMessage($data['message']);
        $newSms->setSenderId($senderId);
        $newSms->setProjetId($data['projetId']);
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
        $newLSLE->setRouteId(1);
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
     * @Route("/projet/crediter", name="crediterProjet", methods={"POST"})
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
     * 
     */
    public function crediterProjet(Request $request)
    {

        $data = $request->toArray();


        $UserEntityManager = $this->doctrine->getManager('User');
        $licenceManager = $this->doctrine->getManager('Licence');

        if (empty($data['projetId']) || empty($data['quantite'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete projetId,quantite sont requis'
            ], 400);
        }

        if ($data['quantite'] < 20) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete preciser une quantite superieur a 20 sms'
            ], 400);
        }
        $projetId =   $data['projetId'];
        $quantite =   $data['quantite'];

        $projet = $UserEntityManager->getRepository(Projet::class)->findOneBy(['id' => $projetId]);

        if (
            $projet
        ) {
            $lsa = $licenceManager->getRepository(ListSMSAchette::class)->findOneBy(['id' => $projet->getLicenceId()]);
            if ($lsa->getQuantite() > $quantite) {
                $projet->setSoldeSms($projet->getSoldeSms() +  $quantite);
                $UserEntityManager->persist($projet);
                $statusCode = 201;
                $message =
                    'Creditation du solde sms du projet effectue avec success';
                $UserEntityManager->flush();
            } else {
                $statusCode = 203;
                $message =
                    'Solde sms de votre licence est insuffisant pour effectuer la transaction';
            }
        } else {
            $statusCode = 203;
            $message =
                'Solde sms insuffisant pour effectuer la transaction';
        }




        return new JsonResponse([
            'message' => $message
        ], $statusCode);
    }
}
