<?php

namespace App\Controller\Account;

use App\Entity\Account\Commission;
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
use App\Entity\Licence\TrancheSms;
use App\Entity\Route\Route as RouteRoute;
use Proxies\__CG__\App\Entity\Route\Route as EntityRouteRoute;

class CommissionCRUDController extends AbstractController
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
     * @Route("/commission/read", name="commissionRead", methods={"POST"})
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
    public function commissionRead(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $LicenceEntityManager = $this->doctrine->getManager('Licence');
        $RouteEntityManager = $this->doctrine->getManager('Route');
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


            // $json = $serializer->serialize($clientUser, 'json');

            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '29') {
                    $possible = true;
                }
            }
            if ($possible) {



                $commission =
                    $AccountEntityManager->getRepository(Commission::class)->findOneBy(['id' => 1]);

                $COM = [
                    'id' => $commission->getId(),
                    'libelle' => $commission->getLibelle(),
                    'description' => $commission->getDescription(),
                    'pourcentagePartage' => $commission->getPourcentagePartage(),
                    'pourcentageParrain' => $commission->getPourcentageParrain(),
                    'pourcentageParrain2' => $commission->getPourcentageParrain2(),
                    'date' => $commission->getDateCreated()->format('d/m/Y H:i'),


                ];


                $FinalC = $serializer->serialize($COM, 'json');

                $trnachesAll = [];
                $tranches =
                    $LicenceEntityManager->getRepository(TrancheSms::class)->findAll();

                foreach ($tranches   as $tranche) {

                    array_push($trnachesAll, [
                        'id' => $tranche->getId(),
                        'min' => $tranche->getMin(),
                        'max' => $tranche->getMax(),
                        'pourcentage' => $tranche->getPourcentage(),

                    ]);
                }




                $FinalT = $serializer->serialize($tranches, 'json');

                $routeAll = [];
                $routes =    $RouteEntityManager->getRepository(RouteRoute::class)->findAll();

                foreach ($routes   as $r) {

                    array_push($routeAll, [
                        'id' => $r->getId(),
                        'nom' => $r->getNom(),
                        'description' => $r->getDescription(),
                        'pays' => $r->getPays()->getNom(),
                        'codePhone' => $r->getPays()->getCodePhone(),
                        'prix' => $r->getPrix(),
                        'limite_envois' => $r->getLimiteEnvois(),

                    ]);
                }

                $FinalR = $serializer->serialize($routeAll, 'json');
                return
                    new JsonResponse([
                        'sms'
                        =>
                        JSON_DECODE($FinalC), 'tranche'
                        =>
                        JSON_DECODE($FinalT), 'route'
                        =>
                        JSON_DECODE($FinalR),
                    ], 200);
            } else {
                return new JsonResponse([
                    'data'
                    => [],
                    'message' => 'Action impossible'
                ], 200);
            }


            return new JsonResponse([
                'data'
                => [],
                'message' => 'Action impossible'
            ], 200);
        }
    }
    /**
     * @Route("/commission/sms/update", name="commissionSmsUpdate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client,idVitrine et les valeur a changer 
     * 
     */
    public function commissionSmsUpdate(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $LicenceEntityManager = $this->doctrine->getManager('Licence');
        $RouteEntityManager = $this->doctrine->getManager('Route');

        $data = $request->toArray();



        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete '
            ], 400);
        }

        $serializer = $this->get('serializer');




        $commission = $AccountEntityManager->getRepository(Commission::class)->findOneBy(['id' => 1]);


        if ($commission) {
            $clientUser =
                $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '29') {
                    $possible = true;
                }
            }
            if ($possible) {



                if (!empty($data['pourcentagePartage'])) {
                    $commission->setPourcentagePartage($data['pourcentagePartage']);
                }
                if (!empty($data['pourcentageParrain'])) {
                    $commission->setPourcentageParrain($data['pourcentageParrain']);
                }
                if (!empty($data['pourcentageParrain2'])) {
                    $commission->setPourcentageParrain2($data['pourcentageParrain2']);
                }

                $AccountEntityManager->persist($commission);
                $AccountEntityManager->flush();



                $COM = [
                    'id' => $commission->getId(),
                    'libelle' => $commission->getLibelle(),
                    'description' => $commission->getDescription(),
                    'pourcentagePartage' => $commission->getPourcentagePartage(),
                    'pourcentageParrain' => $commission->getPourcentageParrain(),
                    'pourcentageParrain2' => $commission->getPourcentageParrain2(),
                    'date' => $commission->getDateCreated()->format('d/m/Y H:i'),


                ];


                $FinalC = $serializer->serialize($COM, 'json');

                $trnachesAll = [];
                $tranches = $LicenceEntityManager->getRepository(TrancheSms::class)->findAll(['id' => 1]);

                foreach ($tranches   as $tranche) {

                    array_push($trnachesAll, [
                        'id' => $tranche->getId(),
                        'min' => $tranche->getMin(),
                        'max' => $tranche->getMax(),
                        'pourcentage' => $tranche->getPourcentage(),

                    ]);
                }




                $FinalT = $serializer->serialize($tranches, 'json');
                $routeAll = [];
                $routes =    $RouteEntityManager->getRepository(RouteRoute::class)->findAll();

                foreach ($routes   as $r) {

                    array_push($routeAll, [
                        'id' => $r->getId(),
                        'nom' => $r->getNom(),
                        'description' => $r->getDescription(),
                        'pays' => $r->getPays()->getNom(),
                        'codePhone' => $r->getPays()->getCodePhone(),
                        'prix' => $r->getPrix(),
                        'limite_envois' => $r->getLimiteEnvois(),

                    ]);
                }






                $FinalR = $serializer->serialize($routeAll, 'json');
                return
                    new JsonResponse([
                        'sms'
                        =>
                        JSON_DECODE($FinalC), 'tranche'
                        =>
                        JSON_DECODE($FinalT), 'route'
                        =>
                        JSON_DECODE($FinalR),
                    ], 200);
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
     * @Route("/commission/tranche/update", name="commissionTrancheUpdate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client,idVitrine et les valeur a changer 
     * 
     */
    public function commissionTrancheUpdate(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $LicenceEntityManager = $this->doctrine->getManager('Licence');
        $RouteEntityManager = $this->doctrine->getManager('Route');

        $data = $request->toArray();

        $possible = false;

        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete '
            ], 400);
        }

        $serializer = $this->get('serializer');




        $tranche = $LicenceEntityManager->getRepository(TrancheSms::class)->findOneBy(['id' => $data['id']]);


        if ($tranche) {
            $clientUser =
                $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '29') {
                    $possible = true;
                }
            }
            if ($possible) {



                if (!empty($data['min'])) {
                    $tranche->setMin($data['min']);
                }
                if (!empty($data['max'])) {
                    $tranche->setMax($data['max']);
                }
                if (!empty($data['pourcentage'])) {
                    $tranche->setPourcentage($data['pourcentage']);
                }

                $LicenceEntityManager->persist($tranche);
                $LicenceEntityManager->flush();

                $commission =
                    $AccountEntityManager->getRepository(Commission::class)->findOneBy(['id' => 1]);

                $COM = [
                    'id' => $commission->getId(),
                    'libelle' => $commission->getLibelle(),
                    'description' => $commission->getDescription(),
                    'pourcentagePartage' => $commission->getPourcentagePartage(),
                    'pourcentageParrain' => $commission->getPourcentageParrain(),
                    'pourcentageParrain2' => $commission->getPourcentageParrain2(),
                    'date' => $commission->getDateCreated()->format('d/m/Y H:i'),


                ];


                $FinalC = $serializer->serialize($COM, 'json');

                $trnachesAll = [];
                $tranches =
                    $LicenceEntityManager->getRepository(TrancheSms::class)->findAll(['id' => 1]);

                foreach ($tranches   as $tranche) {

                    array_push($trnachesAll, [
                        'id' => $tranche->getId(),
                        'min' => $tranche->getMin(),
                        'max' => $tranche->getMax(),
                        'pourcentage' => $tranche->getPourcentage(),

                    ]);
                }




                $FinalT = $serializer->serialize($tranches, 'json');
                $routeAll = [];
                $routes =    $RouteEntityManager->getRepository(RouteRoute::class)->findAll();

                foreach ($routes   as $r) {

                    array_push($routeAll, [
                        'id' => $r->getId(),
                        'nom' => $r->getNom(),
                        'description' => $r->getDescription(),
                        'pays' => $r->getPays()->getNom(),
                        'codePhone' => $r->getPays()->getCodePhone(),
                        'prix' => $r->getPrix(),
                        'limite_envois' => $r->getLimiteEnvois(),

                    ]);
                }






                $FinalR = $serializer->serialize($routeAll, 'json');
                return
                    new JsonResponse([
                        'sms'
                        =>
                        JSON_DECODE($FinalC), 'tranche'
                        =>
                        JSON_DECODE($FinalT), 'route'
                        =>
                        JSON_DECODE($FinalR),
                    ], 200);
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
     * @Route("/commission/route/update", name="commissionRouteUpdate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client,idVitrine et les valeur a changer 
     * 
     */
    public function commissionRouteUpdate(Request $request)
    {
        $AccountEntityManager = $this->doctrine->getManager('Account');
        $LicenceEntityManager = $this->doctrine->getManager('Licence');
        $RouteEntityManager = $this->doctrine->getManager('Route');

        $data = $request->toArray();

        $possible = false;

        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete '
            ], 400);
        }

        $serializer = $this->get('serializer');




        $route = $RouteEntityManager->getRepository(EntityRouteRoute::class)->findOneBy(['id' => $data['id']]);


        if ($route) {
            $clientUser =
                $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '29') {
                    $possible = true;
                }
            }
            if ($possible) {



                if (!empty($data['prix'])) {
                    $route->setPrix($data['prix']);
                }
                if (!empty($data['limiteEnvoi'])) {
                    $route->setLimiteEnvois($data['limiteEnvoi']);
                }
                $RouteEntityManager->persist($route);
                $RouteEntityManager->flush();

                $commission =
                    $AccountEntityManager->getRepository(Commission::class)->findOneBy(['id' => 1]);

                $COM = [
                    'id' => $commission->getId(),
                    'libelle' => $commission->getLibelle(),
                    'description' => $commission->getDescription(),
                    'pourcentagePartage' => $commission->getPourcentagePartage(),
                    'pourcentageParrain' => $commission->getPourcentageParrain(),
                    'pourcentageParrain2' => $commission->getPourcentageParrain2(),
                    'date' => $commission->getDateCreated()->format('d/m/Y H:i'),


                ];


                $FinalC = $serializer->serialize($COM, 'json');

                $trnachesAll = [];
                $tranches =
                    $LicenceEntityManager->getRepository(TrancheSms::class)->findAll(['id' => 1]);

                foreach ($tranches   as $tranche) {

                    array_push($trnachesAll, [
                        'id' => $tranche->getId(),
                        'min' => $tranche->getMin(),
                        'max' => $tranche->getMax(),
                        'pourcentage' => $tranche->getPourcentage(),

                    ]);
                }




                $FinalT = $serializer->serialize($tranches, 'json');
                $routeAll = [];
                $routes =
                    $RouteEntityManager->getRepository(RouteRoute::class)->findAll();

                foreach ($routes   as $r) {

                    array_push($routeAll, [
                        'id' => $r->getId(),
                        'nom' => $r->getNom(),
                        'description' => $r->getDescription(),
                        'pays' => $r->getPays()->getNom(),
                        'codePhone' => $r->getPays()->getCodePhone(),
                        'prix' => $r->getPrix(),
                        'limite_envois' => $r->getLimiteEnvois(),

                    ]);
                }




                $FinalR = $serializer->serialize($routeAll, 'json');
                return
                    new JsonResponse([
                        'sms'
                        =>
                        JSON_DECODE($FinalC), 'tranche'
                        =>
                        JSON_DECODE($FinalT), 'route'
                        =>
                        JSON_DECODE($FinalR),
                    ], 200);
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


    // /**
    //  * @Route("/commission/tranchesms/read", name="trancheSmsRead", methods={"POST"})
    //  * @param Request $request
    //  * @return JsonResponse
    //  * @throws ClientExceptionInterface
    //  * @throws DecodingExceptionInterface
    //  * @throws RedirectionExceptionInterface
    //  * @throws ServerExceptionInterface
    //  * @throws TransportExceptionInterface
    //  * @throws \Exception
    //  * 
    //  * 
    //  * @param array $data doit contenir la cle secrete du client
    //  * 
    //  * 
    //  */
    // public function trancheSmsRead(Request $request)
    // {
    //     $AccountEntityManager = $this->doctrine->getManager('Account');
    //     $data = $request->toArray();
    //     $possible = false;
    //     if (empty($data['keySecret'])) {

    //         return new JsonResponse([
    //             'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
    //         ], 400);
    //     }
    //     $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
    //     if ($clientUser) {
    //         $serializer = $this->get('serializer');


    //         // $json = $serializer->serialize($clientUser, 'json');


    //         /**
    //          * si il a le role admin
    //          */
    //         if ($clientUser->getRole()->getId() == 1) {


    //             $tranche =
    //                 $AccountEntityManager->getRepository(TrancheSms::class)->findOneBy(['id' => 1]);




    //             $COM = [
    //                 'id' => $tranche->getId(),
    //                 'min' => $tranche->getMin(),
    //                 'max' => $tranche->getMax(),
    //                 'pourcentage' => $tranche->getPourcentage(),




    //             ];

    //             $Final = $serializer->serialize($COM, 'json');
    //             return
    //                 new JsonResponse([
    //                     'data'
    //                     =>
    //                     JSON_DECODE($Final),
    //                 ], 200);
    //         } else {
    //             return new JsonResponse([
    //                 'data'
    //                 => [],
    //                 'message' => 'Action impossible'
    //             ], 200);
    //         }


    //         return new JsonResponse([
    //             'data'
    //             => [],
    //             'message' => 'Action impossible'
    //         ], 200);
    //     }
    // }
}
