<?php

namespace App\Controller\Bulk;

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
use App\Entity\Bulk\GroupeContact;
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

class ContactController extends AbstractController
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
     * @Route("/contact/new", name="newContact", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret, nom,prenom,phone,phoneCode,attribute
     */
    public function newContact(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $licenceManager = $this->doctrine->getManager('Licence');
        $routeEntityManager = $this->doctrine->getManager('Route');
        $UserEntityManager = $this->doctrine->getManager('User');
        $bulkManager = $this->doctrine->getManager('Bulk');
        $data = $request->toArray();


        if (empty($data['keySecret']) || empty($data['nom']) || empty($data['prenom']) || empty($data['phone']) || empty($data['phoneCode']) || empty($data['birdDay'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete'
            ], 400);
        } else {
            $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


            if ($clientUser) {
                $possible = false;
                foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                    if ($lr->isStatus() && $lr->getFonction()->getId() == '17') {
                        $possible = true;
                    }
                }


                if ($possible) {
                    $client =    $clientUser->getId();


                    $newContact = new Contact();
                    $newContact->setNom($data['nom']);
                    $newContact->setPrenom($data['prenom']);
                    $newContact->setPhone($data['phone']);
                    $newContact->setPhoneCode($data['phoneCode']);
                    $newContact->setAttribute($data['attribute']);
                    $newContact->setClientId($client);
                    $newContact->setBirdDay(new \DateTime($data['birdDay']));
                    $bulkManager->persist($newContact);
                    $bulkManager->flush();
                    if (!empty($data['idGroupe'])) {
                        $groupe =  $bulkManager->getRepository(GroupeContact::class)->findOneBy(['id' => $data['idGroupe']]);

                        if (
                            $newContact
                            ->getClientId() ==
                            $groupe
                            ->getClientId()
                        ) {
                            $newCToGroupe = new ListGroupeContact();
                            $newCToGroupe->setContact($newContact);
                            $newCToGroupe->setGroupeContact($groupe);

                            $bulkManager->persist($newCToGroupe);
                            $bulkManager->flush();

                            return new JsonResponse([
                                'message' => 'Contact Ajouter avec success'
                            ], 200);
                        } else {

                            return new JsonResponse([
                                'message' => 'error'
                            ], 400);
                        }
                    }
                    return new JsonResponse([
                        'message' => 'Contact Creer avec success'
                    ], 200);
                } else {

                    return new JsonResponse([
                        'message' => 'error'
                    ], 400);
                }
            } else {

                return new JsonResponse([
                    'message' => 'error'
                ], 400);
            }
        }
    }


    /**
     * @Route("/contact/read", name="contactRead", methods={"POST"})
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
    public function contactRead(Request $request)
    {


        $userManager = $this->doctrine->getManager('User');
        $bulkManager = $this->doctrine->getManager('Bulk');

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
            $contactUser = [];
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '19') {
                    $possible = true;
                }
            }
            if ($possible) {
                $listContact =
                    $bulkManager->getRepository(Contact::class)->findBy(['clientId' => $clientUser->getId()]);

                if ($listContact) {
                    foreach ($listContact  as $contact) {

                        if ($contact) {

                            $con = [
                                'id' => $contact->getId(),
                                'nom' => $contact->getNom(),
                                'prenom' =>  $contact->getPrenom(),
                                'phone' => $contact->getPhone(),
                                'phoneCode' => $contact->getPhoneCode()
                            ];
                            array_push($contactUser,  $con);
                        }
                    }



                    $listContactF = $serializer->serialize(array_reverse($contactUser), 'json');


                    return
                        new JsonResponse([
                            'data'
                            =>
                            JSON_DECODE($listContactF),

                        ], 200);
                } else {
                    return new JsonResponse([
                        'message' => 'Action impossible'
                    ], 400);
                }
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

    /**
     * @Route("/groupe/new", name="newGroupe", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret, nom,description, 
     */
    public function newGroupe(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $licenceManager = $this->doctrine->getManager('Licence');
        $routeEntityManager = $this->doctrine->getManager('Route');
        $UserEntityManager = $this->doctrine->getManager('User');
        $bulkManager = $this->doctrine->getManager('Bulk');
        $data = $request->toArray();
        $possible = false;

        if (empty($data['keySecret']) || empty($data['nom']) || empty($data['description'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete'
            ], 400);
        } else {
            $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
            if ($clientUser) {
                foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                    if ($lr->isStatus() && $lr->getFonction()->getId() == '18') {
                        $possible = true;
                    }
                }
                if ($possible) {
                    $client =    $clientUser->getId();


                    $newGroupe = new GroupeContact();
                    $newGroupe->setNom($data['nom']);
                    $newGroupe->setDescription($data['description']);
                    $newGroupe->setClientId($client);
                    $bulkManager->persist($newGroupe);
                    $bulkManager->flush();



                    return new JsonResponse([
                        'message' => 'Groupe Creer avec success'
                    ], 200);
                } else {

                    return new JsonResponse([
                        'message' => 'error'
                    ], 400);
                }
            } else {

                return new JsonResponse([
                    'message' => 'error'
                ], 400);
            }
        }
    }

    /**
     * @Route("/groupe/read", name="groupeRead", methods={"POST"})
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
    public function groupeRead(Request $request)
    {


        $userManager = $this->doctrine->getManager('User');
        $bulkManager = $this->doctrine->getManager('Bulk');

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
            $groupeUser = [];
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '18') {
                    $possible = true;
                }
            }
            if ($possible) {
                $listGroupe =
                    $bulkManager->getRepository(GroupeContact::class)->findBy(['clientId' => $clientUser->getId(), 'status' => true]);

                if ($listGroupe) {
                    foreach ($listGroupe  as $groupe) {

                        if ($groupe) {

                            $group = [
                                'id' => $groupe->getId(),
                                'nom' => $groupe->getNom(),
                                'description' =>  $groupe->getDescription(),
                                'status' => $groupe->getStatus(),
                                'dateCreated' => $groupe->getDateCreated()
                            ];
                            array_push($groupeUser,  $group);
                        }
                    }



                    $listGroupF = $serializer->serialize(array_reverse($groupeUser), 'json');


                    return
                        new JsonResponse([
                            'data'
                            =>
                            JSON_DECODE($listGroupF),

                        ], 200);
                } else {
                    return new JsonResponse([
                        'dta' => []
                    ], 200);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Action impossible000'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'message' => 'Client introuvable'
            ], 400);
        }
    }



    /**
     * @Route("/groupe/addContact", name="addContactToGroupe", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret, idContact,idGroupe
     */
    public function addContactToGroupe(Request $request)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');
        $data = $request->toArray();


        if (empty($data['keySecret']) || empty($data['idContact']) || empty($data['idGroupe'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete'
            ], 400);
        } else {
            $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
            if ($clientUser) {
                $possible = false;
                foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                    if ($lr->isStatus() && $lr->getFonction()->getId() == '18') {
                        $possible = true;
                    }
                }
                if ($possible) {
                    $contact =  $bulkManager->getRepository(Contact::class)->findOneBy(['id' => $data['idContact']]);
                    $groupe =  $bulkManager->getRepository(GroupeContact::class)->findOneBy(['id' => $data['idGroupe']]);
                    $exist =  $bulkManager->getRepository(ListGroupeContact::class)->findOneBy(['contact' => $contact, 'groupeContact' => $groupe]);
                    if ($exist) {
                        return new JsonResponse([
                            'message' => 'Existe deja'
                        ], 200);
                    } else {


                        if (
                            $contact
                            ->getClientId() ==
                            $groupe
                            ->getClientId()
                        ) {
                            $newCToGroupe = new ListGroupeContact();
                            $newCToGroupe->setContact($contact);
                            $newCToGroupe->setGroupeContact($groupe);

                            $bulkManager->persist($newCToGroupe);
                            $bulkManager->flush();

                            return new JsonResponse([
                                'message' => 'Contact Ajouter avec success'
                            ], 200);
                        } else {

                            return new JsonResponse([
                                'message' => 'error'
                            ], 400);
                        }
                    }
                } else {

                    return new JsonResponse([
                        'message' => 'error'
                    ], 400);
                }
            } else {

                return new JsonResponse([
                    'message' => 'error'
                ], 400);
            }
        }
    }



    /**
     * @Route("/contact/modify", name="contactModify", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function contactModify(Request $request)
    {

        $bulkManager = $this->doctrine->getManager('Bulk');

        $data = $request->toArray();
        if (empty($data['keySecret']) || empty($data['idContact'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le keySecret ,idContact est requis'
            ], 400);
        }

        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

        if (!$clientUser) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }
        $possible = false;
        foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            if ($lr->isStatus() && $lr->getFonction()->getId() == '20') {
                $possible = true;
            }
        }
        if ($possible) {
            $contact = $bulkManager->getRepository(Contact::class)->findOneBy(['id' => $data['idContact']]);

            if ($data['nom']) {
                $contact->setNom($data['nom']);
            }

            if ($data['prenom']) {
                $contact->setPrenom($data['prenom']);
            }

            if ($data['phone']) {
                $contact->setPhone($data['phone']);
            }
            if ($data['phoneCode']) {
                $contact->setphoneCode($data['phoneCode']);
            }


            $bulkManager->persist($contact);
            $bulkManager->flush();




            return new JsonResponse([
                'message' => 'Mise A jour Reussi',

            ], 200);
        } else {

            return new JsonResponse([
                'message' => 'error'
            ], 400);
        }
    }
}
