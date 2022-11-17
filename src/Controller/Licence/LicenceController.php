<?php

namespace App\Controller\Licence;

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
use App\Entity\Bulk\ListGroupeContact;
use App\Entity\Bulk\ListLotCalendrier;
use App\Entity\Bulk\ListSmsContact;
use App\Entity\Bulk\ListSmsLotsEnvoye;
use App\Entity\Bulk\Lot;
use App\Entity\Bulk\RouteListSmsLotsEnvoye;
use App\Entity\Bulk\SenderId;
use App\Entity\Bulk\Sms;
use App\Entity\Licence\Facture;
use App\Entity\Licence\ListSMSAchette;
use App\Entity\Route\Operateur;
use App\Entity\Route\Pays;
use App\Entity\Route\Route as RouteRoute;
use App\Entity\Route\SenderApi;


class LicenceController extends AbstractController
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
     * @Route("/facture/read", name="factureRead", methods={"POST"})
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
    public function factureRead(Request $request)
    {
        $lFacture = [];

        $LicenceEntityManager = $this->doctrine->getManager('Licence');
        $RouteEntityManager = $this->doctrine->getManager('Route');
        $data = $request->toArray();
        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {

            $serializer = $this->get('serializer');
            $possible = false;
            // foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
            //     if ($lr->isStatus() && $lr->getFonction()->getId() == '14') {
            //         $possible = true;
            //     }
            // }


            // if ($possible) {

            $FactureUser =
                $LicenceEntityManager->getRepository(Facture::class)->findBy(['clientId' => $clientUser->getId()]);

            foreach ($FactureUser as $facture) {
                $montant = $facture->getMontant();
                $date = $facture->getDate()->format('Y-m-d H:i');
                $lsa =
                    $LicenceEntityManager->getRepository(ListSMSAchette::class)->findOneBy(['facture' => $facture]);
                if ($lsa) {


                    $route =
                        $RouteEntityManager->getRepository(RouteRoute::class)->findOneBy(['id' => $lsa->getRouteId()]);
                    // if ($route) {
                    //     $pays = $route->getPays()->getNom();
                    //     $factureA = [
                    //         'id' => $facture->getId(),
                    //         'date' => $date,
                    //         'montant' => $montant,
                    //         // 'pays' => $pays,
                    //         'motif' => 'Achat de sms'
                    //     ];
                    //     array_push($lFacture, $factureA);
                    // }
                    if ($facture->getLicence() == 3) {
                        $pays = $route->getPays()->getNom();
                        $factureA = [
                            'id' => $facture->getId(),
                            'date' => $date,
                            'montant' => $montant,
                            // 'pays' => $pays,
                            'motif' => 'Achat de sms'
                        ];
                        array_push($lFacture, $factureA);
                    }
                } else {
                }
            }
            $state = [];
            $lFactureFinal = $serializer->serialize(array_reverse($lFacture), 'json');



            return new JsonResponse(
                [
                    'data' => JSON_DECODE(
                        $lFactureFinal
                    )
                ],
                200
            );
        } else {

            return new JsonResponse([
                'message' => 'Client Introuvable '
            ], 400);
        }
    }
}
