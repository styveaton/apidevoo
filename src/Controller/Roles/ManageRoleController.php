<?php

namespace App\Controller\Roles;

use App\Entity\Account\TransactionCompte;
use App\Entity\Auth\Client;
use App\Entity\Auth\Fonctions;
use App\Entity\Auth\ListRoleFonctions;
use App\Entity\Auth\Roles;
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
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class ManageRoleController extends AbstractController
{
    private $em;
    private $client;
    private $apiKey;
    public function __construct(
        EntityManagerInterface $em,
        HttpClientInterface $client,
        ManagerRegistry $doctrine,
        JWTTokenManagerInterface $jwt,
        RefreshTokenManagerInterface $jwtRefresh,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->doctrine = $doctrine;
        $this->client = $client;
        $this->jwt = $jwt;
        $this->jwtRefresh = $jwtRefresh;
        $this->validator = $validator;
    }


    /**
     * @Route("/fonction/all", name="fonctionAll", methods={"POST"})
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
     * @param array $data doit contenir le role du client
     * 
     * 
     */
    public function fonctionAll(Request $request)
    {
        $data = $request->toArray();
        $possible = false;
        $listRoleFonction = [];
        $listRoleFonctionModule = [];
        if (empty($data['role'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete   '
            ], 400);
        }
        $role = $this->em->getRepository(Roles::class)->findOneBy(['id' => $data['role']]);
        $lfonction = $this->em->getRepository(Fonctions::class)->findAll();
        $lmodule = $this->em->getRepository(Module::class)->findAll();

        foreach ($lfonction as  $fonction) {
            $lrf = $this->em->getRepository(ListRoleFonctions::class)->findOneBy(['role' => $role, 'fonction' => $fonction, 'status' => true]);

            if (
                $lrf
            ) {

                $fonctioni = [
                    'id' => $fonction->getId(),
                    'module' => $fonction->getModule()->getId(),

                    'name' => $fonction->getNom(),
                    'description' => $fonction->getDescription(),
                    'check' => true
                ];
                array_push($listRoleFonction,   $fonctioni);
            } else {
                $fonctioni = [
                    'id' => $fonction->getId(),
                    'module' => $fonction->getModule()->getId(),

                    'name' => $fonction->getNom(),
                    'description' => $fonction->getDescription(),
                    'check' => false
                ];
                array_push($listRoleFonction,   $fonctioni);
            }
        }

        foreach ($lmodule as  $module) {
            $l = [];
            $nameModule =
                $module->getNom();
            $idModule =
                'module' . strval($module->getId());


            foreach ($listRoleFonction as  $fc) {

                if ($fc['module'] ==  $module->getId()) {
                    array_push($l,   $fc);
                }
            }

            if (!empty(count($l))) {
                array_push($listRoleFonctionModule,  [

                    'nomModule'
                    =>  $nameModule, 'idModule'
                    =>  $idModule,
                    'data' => $l

                ]);
            }
        }


        return new JsonResponse(
            [
                'data' => $listRoleFonctionModule
            ],
            201
        );
    }


    /**
     * @Route("/fonction/remove", name="fonctionRemove", methods={"POST"})
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
    public function fonctionRemove(Request $request)
    {
        $data = $request->toArray();
        $possible = false;
        if (empty($data['role']) || empty($data['fonction']) || empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete   '
            ], 400);
        }
        $role = $this->em->getRepository(Roles::class)->findOneBy(['id' => $data['role']]);
        $fonction = $this->em->getRepository(Fonctions::class)->findOneBy(['id' => $data['fonction']]);
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser->getRole()->getId()  == 1) {

            if ($role && $fonction) {
                $lrf = $this->em->getRepository(ListRoleFonctions::class)->findOneBy(['role' => $role, 'fonction' => $fonction]);
                $lrf->setStatus(false);

                $this->em->persist($lrf);
                $this->em->flush();
                $infoUser = $this->createNewJWT($clientUser);
                $tokenAndRefresh = json_decode($infoUser->getContent());

                return new JsonResponse(
                    [
                        'message' => ' effectue avec success',
                        'token' => $tokenAndRefresh->token,
                        'refreshToken' => $tokenAndRefresh->refreshToken,
                    ],
                    201
                );
            } else {
                return new JsonResponse([
                    'message' => ' introuvable'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'data'
                => [],
                'message' => 'Pas autorise'
            ], 400);
        }
    }

    /**
     * @Route("/fonction/add", name="fonctionAdd", methods={"POST"})
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
    public function fonctionAdd(Request $request)
    {
        $data = $request->toArray();
        $possible = false;
        if (empty($data['role']) || empty($data['fonction']) || empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete   '
            ], 400);
        }
        $role = $this->em->getRepository(Roles::class)->findOneBy(['id' => $data['role']]);
        $fonction = $this->em->getRepository(Fonctions::class)->findOneBy(['id' => $data['fonction']]);
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);
        if ($clientUser->getRole()->getId()  == 1) {
            if ($role && $fonction) {
                $lrf = $this->em->getRepository(ListRoleFonctions::class)->findOneBy(['role' => $role, 'fonction' => $fonction]);
                if (!$lrf) {
                    $lrf = new ListRoleFonctions();
                    $lrf->setRole($role);
                    $lrf->setFonction($fonction);

                    $this->em->persist($lrf);
                    $this->em->flush();
                    $infoUser = $this->createNewJWT($clientUser);
                    $tokenAndRefresh = json_decode($infoUser->getContent());

                    return new JsonResponse(
                        [
                            'message' => ' operation effectuee avec success',
                            'token' => $tokenAndRefresh->token,
                            'refreshToken' => $tokenAndRefresh->refreshToken,
                        ],
                        201
                    );
                } else {
                    $lrf->setStatus(!$lrf->isStatus());

                    $this->em->persist($lrf);
                    $this->em->flush();
                    $infoUser = $this->createNewJWT($clientUser);
                    $tokenAndRefresh = json_decode($infoUser->getContent());

                    return new JsonResponse(
                        [
                            'message' => ' operation effectuee avec success',
                            'token' => $tokenAndRefresh->token,
                            'refreshToken' => $tokenAndRefresh->refreshToken,
                        ],
                        201
                    );
                }
            } else {
                return new JsonResponse([
                    'message' => ' introuvable'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'data'
                => [],
                'message' => 'Pas autorise'
            ], 400);
        }
    }
    /**
     * @Route("/role/new", name="roleAdd", methods={"POST"})
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
     * @param array $data doit contenir la cle secrete du client, nom du role , description
     * 
     * 
     */
    public function roleAdd(Request $request)
    {


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
                if ($lr->isStatus() && $lr->getFonction()->getId() == '14') {
                    $possible = true;
                }
            }


            if ($possible) {

                $role = new Roles();
                $role->setNom($data['nom']);
                $role->setDescription($data['description']);
                $this->em->persist($role);
                $this->em->flush();
                return new JsonResponse(
                    [
                        'message' => ' ajoute avec success'
                    ],
                    201
                );
            } else {
                return new JsonResponse([
                    'message' => ' introuvable'
                ], 400);
            }
        } else {
            return new JsonResponse([
                'message' => ' introuvable'
            ], 400);
        }
    }


    /**
     * @Route("/role/read", name="roleRead", methods={"POST"})
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
    public function roleRead(Request $request)
    {

        $lrole = [];

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
                if ($lr->isStatus() && $lr->getFonction()->getId() == '15') {
                    $possible = true;
                }
            }


            if ($possible) {
                $listRoleC =
                    $this->em->getRepository(Roles::class)->findAll();

                if ($listRoleC) {
                    foreach ($listRoleC  as $lrs) {

                        if ($lrs) {

                            $role = [
                                'id' => $lrs->getId(),
                                'name' => $lrs->getNom(),
                                'description' =>  $lrs->getDescription(),

                                // 'date' => $lrs->getDate(),
                            ];

                            array_push($lrole,  $role);
                        }
                    }



                    $lroleFinal = $serializer->serialize(array_reverse($lrole), 'json');


                    return
                        new JsonResponse([
                            'data'
                            =>
                            JSON_DECODE($lroleFinal),

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
                    'message' => 'Action impossible'
                ], 200);
            }
        } else {
            return new JsonResponse([
                'message' => 'Client introuvable'
            ], 400);
        }
    }
    public function createNewJWT(Client $user)
    {
        $token = $this->jwt->create($user);

        $datetime = new \DateTime();
        $datetime->modify('+2592000 seconds');

        $refreshToken = $this->jwtRefresh->create();

        $refreshToken->setUsername($user->getUsername());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid($datetime);

        // Validate, that the new token is a unique refresh token
        $valid = false;
        while (false === $valid) {
            $valid = true;
            $errors = $this->validator->validate($refreshToken);
            if ($errors->count() > 0) {
                foreach ($errors as $error) {
                    if ('refreshToken' === $error->getPropertyPath()) {
                        $valid = false;
                        $refreshToken->setRefreshToken();
                    }
                }
            }
        }

        $this->jwtRefresh->save($refreshToken);

        return new JsonResponse([
            'token' => $token,
            'refreshToken' => $refreshToken->getRefreshToken()
        ], 200);
    }
}
