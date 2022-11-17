<?php

namespace App\Controller\Roles;

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
use App\Entity\Account\TypeCompte;
use App\Entity\Account\Compte;

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
use App\Entity\Route\Route as RouteRoute;
use App\Entity\Route\SenderApi;


class ClientCRUDController extends AbstractController
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
     * @Route("/client/read", name="clientRead", methods={"POST"})
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
    public function clientRead(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $typeCompte = $AccountEntityManager->getRepository(TypeCompte::class)->findOneBy(['id' => 1]);
        $data = $request->toArray();
        $possible = false;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser) {
            $serializer = $this->get('serializer');

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '2') {
                    $possible = true;
                }
            }

            $json = $serializer->serialize($clientUser, 'json');
            if ($possible) {
                $lclient = $this->em->getRepository(Client::class)->findAll();
                /**
                 * si il a le role admin
                 */
                if ($clientUser->getRole()->getId() == 1) {

                    $lf = [];
                    foreach ($lclient  as $cl) {
                        if (
                            $cl->getId() !=
                            $clientUser->getId()
                        ) {

                            $compteSMS =
                                $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $cl->getId(), 'typeCompte' => $typeCompte]);

                            // var_dump($cl->getPhone());
                            $client =  [
                                'id' => $cl->getId(),
                                'phone' => $cl->getPhone(),
                                'prenom' => $cl->getPrenom(),
                                'nom' => $cl->getNom(),
                                'solde' => ($compteSMS) ? $compteSMS->getSolde() : 0,
                                'nomEntreprise' => $cl->getNomEntreprise(),
                                'email' => $cl->getEmail(),
                                'codeParrain' => $cl->getCodeParrain(),
                                'role' => $cl->getRole(),
                                'keySecret' => $cl->getKeySecret(),
                                'posteSocial' => $cl->getPosteSocial() ?? '',
                                'status' => $cl->getStatus(),
                                'birthday' => $cl->getBirthday() ?? new DateTime(),
                                'dateCreated' => $cl->getDateCreated() ?? new DateTime(),
                                'profile' => ($cl->getProfile()) ? $cl->getProfile()->getFilePath() : null,
                                'couverture' => ($cl->getCouverture()) ? $cl->getCouverture()->getFilePath() : null,

                            ];
                            array_push($lf, $client);
                        }
                    }
                    $clients = $serializer->serialize(array_reverse($lf), 'json');
                } else {
                    $lf = [];
                    foreach ($lclient  as $cl) {
                        $compteSMS =
                            $AccountEntityManager->getRepository(Compte::class)->findOneBy(['clientId' => $cl->getId(), 'typeCompte' => $typeCompte]);

                        if (
                            explode('@',  $cl->getCodeParrain())[0] ==
                            $clientUser->getId() && $cl->getId() !=
                            $clientUser->getId()
                        ) {

                            $client =  [
                                'id' => $cl->getId(),
                                'phone' => $cl->getPhone(),
                                'prenom' => $cl->getPrenom(),
                                'nom' => $cl->getNom(),
                                'solde' => ($compteSMS) ?
                                    $compteSMS->getSolde() : 0,

                                'nomEntreprise' => $cl->getNomEntreprise(),
                                'email' => $cl->getEmail(),
                                'codeParrain' => $cl->getCodeParrain(),
                                'role' => $cl->getRole(),
                                'keySecret' => $cl->getKeySecret(),
                                'posteSocial' => $cl->getPosteSocial() ?? '',

                                'status' => $cl->getStatus(),
                                'birthday' => $cl->getBirthday() ?? new DateTime(),

                                'dateCreated' => $cl->getDateCreated() ?? new DateTime(),
                                'profile' => ($cl->getProfile()) ? $cl->getProfile()->getFilePath() : null,
                                'couverture' => ($cl->getCouverture()) ? $cl->getCouverture()->getFilePath() : null,


                            ];
                            array_push($lf, $client);
                        }
                    }

                    $clients = $serializer->serialize(array_reverse($lf), 'json');
                }

                return
                    new JsonResponse([
                        'data'
                        =>
                        JSON_DECODE($clients),

                    ], 200);
            } else {
                return new JsonResponse([
                    'data'
                    => [],
                    'message' => 'Action impossible'
                ], 200);
            }
        } else {
            return new JsonResponse([
                'data'
                => [],
                'message' => 'Client introuvable'
            ], 400);
        }
    }



    /**
     * @Route("/client/create", name="clientCreate", methods={"POST"})
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
    public function clientCreate(Request $request)
    {
        // $AuthEntityManager = $this->doctrine->getManager('Auth');
        $data = $request->toArray();
        $possible = false;
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret  '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser) {
            $serializer = $this->get('serializer');

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '1') {
                    $possible = true;
                }
                var_dump($lr->getFonction()->getNom());
            }

            $json = $serializer->serialize($clientUser, 'json');
            if ($possible) {

                $client = new Client();
                $clients = $serializer->serialize($client, 'json');


                return
                    new JsonResponse([
                        'data'
                        =>
                        JSON_DECODE($clients),

                    ], 200);
            } else {
                return new JsonResponse([
                    'message' => 'Action impossible'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'message' => 'Client introuvable'
            ], 400);
        }
    }
}
