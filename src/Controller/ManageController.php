<?php

namespace App\Controller;

use Symfony\Component\Mime\Address;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use App\Entity\Auth\Client;
use App\Entity\Auth\Roles;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Swift_Mailer;
use Swift_SendmailTransport;
use Swift_SmtpTransport;
use Swift_Transport;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\Bridge\Google\Transport\GmailSmtpTransport;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\RawMessage;

class ManageController extends AbstractController
{

    private $em;
    private $mailer;
    private $client;
    private $passwordEncoder;
    private $jwt;
    private $jwtRefresh;
    private $validator;

    public function __construct(
        EntityManagerInterface $em,
        MailerInterface $mailer,
        HttpClientInterface $client,
        UserPasswordEncoderInterface $passwordEncoder,
        UserPasswordHasherInterface $passwordHasher,
        JWTTokenManagerInterface $jwt,
        RefreshTokenManagerInterface $jwtRefresh,
        ValidatorInterface $validator
    ) {
        $this->em = $em;

        $this->client = $client;
        $this->passwordEncoder = $passwordEncoder;
        $this->passwordEncoder = $passwordEncoder;
        $this->jwt = $jwt;
        $this->jwtRefresh = $jwtRefresh;
        $this->validator = $validator;
        $this->mailer = $mailer;
    }


    /**
     * @Route("/update/profil/client", name="updateProfilClient", methods={"PATCH"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateProfilClient(Request $request)
    {
        $data = $request->toArray();
        if (empty($data['clientId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le clientId est requis'
            ], 400);
        }
        $user = $this->em->getRepository(Client::class)->find((int)$data['clientId']);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }

        if (!empty($data['nom'])) {
            $user->setNom($data['nom']);
        }

        if (!empty($data['prenom'])) {
            $user->setPrenom($data['prenom']);
        }
        if (!empty($data['email'])) {
            $user->setEmail($data['email']);
        }
        if (!empty($data['nomEntreprise'])) {
            $user->setNomEntreprise($data['nomEntreprise']);
        }
        if (!empty($data['posteSocial'])) {
            $user->setPosteSocial($data['posteSocial']);
        }

        if (!empty($data['phone'])) {
            $user->setPhone($data['phone']);
        }


        $this->em->persist($user);
        $this->em->flush();

        $infoUser = $this->createNewJWT($user);
        $tokenAndRefresh = json_decode($infoUser->getContent());

        return new JsonResponse([
            'token' => $tokenAndRefresh->token,
            'refreshToken' => $tokenAndRefresh->refreshToken,
        ], 200);
    }

    /**
     * @Route("/update/role/client", name="updateRoleClient", methods={"PATCH"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updateRoleClient(Request $request)
    {
        $data = $request->toArray();
        if (empty($data['clientId']) || empty($data['roleId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le clientId et roleId sont requis'
            ], 400);
        }
        $user = $this->em->getRepository(Client::class)->find((int)$data['clientId']);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }
        $role = $this->em->getRepository(Roles::class)->find((int)$data['roleId']);


        $user->setRole($role);
        $this->em->persist($user);
        $this->em->flush();

        $infoUser = $this->createNewJWT($user);
        $tokenAndRefresh = json_decode($infoUser->getContent());

        return new JsonResponse([
            'token' => $tokenAndRefresh->token,
            'refreshToken' => $tokenAndRefresh->refreshToken,
        ], 200);
    }

    public function getNewPssw(/* $id */)
    {

        $chaine = '';
        $listeCar = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $max = mb_strlen($listeCar, '8bit') - 1;
        for ($i = 0; $i < 7; ++$i) {
            $chaine .= $listeCar[random_int(0, $max)];
        }
        // $user = $this->em->getRepository(Client::class)->findOneBy(['id' => $id]);
        // $password = $this->passwordHasher->hashPassword(
        //     $user,
        //     $chaine
        // );
        // $user->setPassword($password);

        return $chaine;
    }
    /**
     * @Route("/update/password/client", name="updatePasswordClient", methods={"PATCH"})
     * @param Request $request
     * @return JsonResponse
     */
    public function updatePasswordClient(Request $request)
    {
        $data = $request->toArray();
        if (empty($data['clientId'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete le clientId et password sont requis'
            ], 400);
        }
        $user = $this->em->getRepository(Client::class)->find((int)$data['clientId']);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }
        $npass = $this->getNewPssw();
        $user->setPassword($npass);
        $password = $this->passwordEncoder->encodePassword(
            $user,
            $user->getPassword()
        );
        $user->setPassword($password);
        $this->em->persist($user);
        $this->em->flush();

        $infoUser = $this->createNewJWT($user);
        $tokenAndRefresh = json_decode($infoUser->getContent());

        return new JsonResponse([
            'password' => $npass,
            'token' => $tokenAndRefresh->token,
            'refreshToken' => $tokenAndRefresh->refreshToken,
        ], 200);
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

    /**
     * @Route("/desactivate/client", name="desactivateClient", methods={"PATCH"})
     * @param Request $request
     * @return JsonResponse
     */
    public function desactivateProfilClient(Request $request)
    {
        $data = $request->toArray();
        // if (empty($data['clientId']) || empty($data['status'])) {
        //     return new JsonResponse([
        //         'message' => 'Mauvais parametre de requete le clientId et status sont requis'
        //     ], 400);
        // }
        $user = $this->em->getRepository(Client::class)->find((int)$data['clientId']);

        if (!$user) {
            return new JsonResponse([
                'message' => 'Desolez l\'utilisateur en question n\'existe pas dans la base de donnée'
            ], 400);
        }


        $user->setStatus($data['status']);
        $this->em->persist($user);
        $this->em->flush();

        $infoUser = $this->createNewJWT($user);
        $tokenAndRefresh = json_decode($infoUser->getContent());

        return new JsonResponse([
            'token' => $tokenAndRefresh->token,
            'refreshToken' => $tokenAndRefresh->refreshToken,
        ], 200);
    }




    /**
     * @Route("/forgot/password", name="forgotPassword", methods={"POST"})
     * @param Request $request action = 1 => verifier exist numero ou email, action = 2 => send code phone, action = 3 => send code email , action = 4 => verify code
     * @return JsonResponse
     * 
     */
    public function forgotPassword(Request $request)
    {
        $data = $request->toArray();
        if (empty($data['action'])) {
            return new JsonResponse([
                'message' => 'Mauvais parametre de requete action est requis'
            ], 400);
        }

        $action = $data['action'];
        if ($action == 1) {
            if (!empty($data['phone']) || !empty($data['email'])) {
                $user =
                    !empty($data['phone']) ? $this->em->getRepository(Client::class)->findOneBy([
                        'phone' => $data['phone']
                    ]) : $this->em->getRepository(Client::class)->findOneBy([
                        'email' => $data['email']
                    ]);
                if ($user) {
                    return new JsonResponse([
                        'message' => 'Utilisateur correct, veuillez poursuivre',
                        'user' => $user->getKeySecret(),
                        'status' => true
                    ], 200);
                } else {
                    return new JsonResponse([
                        'message' => 'Utilisateur inexistant',

                        'status' => false
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Renseigner un numero ou une adresse email',

                    'status' => false
                ], 400);
            }
        }
        if ($action == 2) {
            if (!empty($data['user'])) {
                $user =
                    $this->em->getRepository(Client::class)->findOneBy([
                        'keySecret' => $data['user']
                    ]);
                if ($user) {
                    $code = $this->createCode();
                    $user->setCodeRecup($code);
                    $this->em->persist($user);
                    $this->em->flush();
                    $sendSms =   $this->sendCode($user->getPhone(), null,   $code);
                    if ($sendSms) {
                        return new JsonResponse([
                            'message' => 'Le code a ete transmis veuillez consulter votre appareil',

                            'status' => true
                        ], 200);
                    } else {
                        return new JsonResponse([
                            'message' => 'Une erreur est survenue',

                            'status' => false
                        ], 400);
                    }
                } else {
                    return new JsonResponse([
                        'message' => 'Utilisateur inexistant',

                        'status' => false
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Renseigner un numero ou une adresse email',

                    'status' => false
                ], 400);
            }
        }
        if ($action == 3) {
            if (!empty($data['user'])) {
                $user =
                    $this->em->getRepository(Client::class)->findOneBy([
                        'keySecret' => $data['user']
                    ]);
                if ($user) {
                    $code = $this->createCode();
                    $user->setCodeRecup($code);
                    $this->em->persist($user);
                    $this->em->flush();
                    $sendSms =
                        $this->sendCode(null, $user->getEmail(),   $code);
                    if ($sendSms) {
                        return new JsonResponse([
                            'message' => 'Le code a ete transmis veuillez consulter votre appareil',

                            'status' => true
                        ], 200);
                    } else {
                        return new JsonResponse([
                            'message' => 'Une erreur est survenue',

                            'status' => false
                        ], 400);
                    }
                } else {
                    return new JsonResponse([
                        'message' => 'Utilisateur inexistant',

                        'status' => false
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Renseigner un numero ou une adresse email',

                    'status' => false
                ], 400);
            }
        }
        if ($action == 4) {
            if (!empty($data['user']) && !empty($data['code'])) {
                $user =
                    $this->em->getRepository(Client::class)->findOneBy([
                        'keySecret' => $data['user']
                    ]);
                if ($user) {


                    if (
                        $data['code']
                        == $user->getCodeRecup()
                    ) {
                        return new JsonResponse([
                            'message' => 'Le code transmis est correct',
                            'user' => $user->getKeySecret(),
                            'status' => true
                        ], 200);
                    } else {
                        return new JsonResponse([
                            'message' => 'Une erreur est survenue',

                            'status' => false
                        ], 400);
                    }
                } else {
                    return new JsonResponse([
                        'message' => 'Utilisateur inexistant',

                        'status' => false
                    ], 400);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Renseigner un numero ou une adresse email',

                    'status' => false
                ], 400);
            }
        }
        if ($action == 5) {

            if (empty($data['user']) || empty($data['password'])) {
                return new JsonResponse([
                    'message' => 'Mauvais parametre de requete le user et password sont requis'
                ], 400);
            }
            $user =
                $this->em->getRepository(Client::class)->findOneBy([
                    'keySecret' => $data['user']
                ]);
            if (!$user) {
                return new JsonResponse([
                    'message' => 'Desolez l\'utilisateur en question n\'existe pas dans notre base de donnée'
                ], 400);
            }
            // $npass = $this->getNewPssw();
            $user->setPassword($data['password']);
            $password = $this->passwordEncoder->encodePassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($password);
            $this->em->persist($user);
            $this->em->flush();

            $infoUser = $this->createNewJWT($user);
            $tokenAndRefresh = json_decode($infoUser->getContent());

            return new JsonResponse([
                'status' => true,
                'message' => 'Mot de passe mis a jour avec succes',
                'token' => $tokenAndRefresh->token,
                'refreshToken' => $tokenAndRefresh->refreshToken,
            ], 200);
        }
    }

    public function sendCode($phone, $email,   $code)
    {

        $message = 'Votre code de reinitilisation pubX est : ' . $code . ' veuillez le garder precieusement';
        if ($phone != null) {
            try {


                $query = [
                    // 'login' =>  679170000,   //$client->getPhone(),
                    // 'password' => "Oi7i469x", //$client->getPassword(),


                    'login' =>  690863838,   //$client->getPhone(),
                    'password' => "12345678", //$client->getPassword(),
                    'sender_id' => 'pubX',
                    'destinataire' => $phone,
                    'message' => $message
                ];
                $response = $this->client->request(
                    'GET',
                    'http://sms.gessiia.com/ss/api.php',
                    [
                        'query' => $query
                    ]
                );
                $statusCode =
                    $response->getStatusCode();
                $responseApi =  $response->getContent();

                if ($statusCode == 200 && !empty($responseApi) && trim($responseApi) != 'Solde insuffisant' /* && $this->verifCorrectRes($responseApi) */) {
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                var_dump($e);
                return false;
            }
        } else if ($email != null) {

            $emailU = (new Email())
                ->from('hello@example.com')
                ->to($email)

                ->subject('User code')
                ->text($message);
            // ->html('<p>See Twig integration for better HTML integration!</p>');

            $this->mailer->send($emailU);
            return true;
        } else {
            return false;
        }
    }
    /**
     * @Route("/email0", name="sendEmail", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function sendEmail(/* MailerInterface $mailer */)
    {
        //smtp.pubx.cm
        // $transport =    //new   GmailSmtpTransport('hari.randoll@gmail.com', 'Gyslaine01');
        //     //  Transport::fromDsn('gmail://hari.randoll@gmail.com:Gyslaine01@localhost');
        //     Transport::fromDsn('smtp://528f7b4852db74:3c6784d2838f73@smtp.mailtrap.io:2525');
        // $mailerA = new Mailer($transport);

        $transport =
            new GmailSmtpTransport('hari.randoll@gmail.com', 'Gyslaine01');
        $mailer = new Mailer($transport);
        $email = (new Email())
            ->from(new Address('hari.randoll@gmail.com'))
            ->to(new Address('hari.randoll@gmail.com'))
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Time for Symfony Mailer!')
            ->text('Sending emails is fun again!')
            ->html('ssssssssssss');

        try {
            $mailer->send($email);
            return new JsonResponse([
                'message' =>
                $email->getSubject(),       'message0' =>
                $mailer,

                'status' => true
            ], 200);
        } catch (TransportExceptionInterface $e) {
            var_dump($e);
            return new JsonResponse([
                'message' => 'Une erreur est survenue',

                'status' => false
            ], 200);
        }

        // ...
    }
    /**
     * @Route("/email", name="testEmail", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function sendEmail2(Swift_Mailer $mailerA): JsonResponse
    {
        $transport = new Swift_SmtpTransport($_SERVER['MAILER_URL']);


        // $mailerA = new Swift_Mailer();
        $message = (new \Swift_Message('New'))
            ->setFrom('hari.randoll@gmail.com')
            ->setTo('hari.randoll5.0@gmail.com')
            ->setBody(
                'ffdfddf'
            );
        $mailerA->send($message);
        if (0 === $mailerA->send($message)) {
            throw new \RuntimeException('Unable to send email');
        }
        return new JsonResponse([
            "message" => 'Well',
            's' => $mailerA
        ]);
    }

    /**
     * @Route("/code", name="email", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     */
    public function createCode()
    {

        $code = '';
        $listeCar = '0123456789';

        for ($i = 0; $i < 4; ++$i) {
            $code .= $listeCar[random_int(0, 9)];
        }
        $ExistTransaction = $this->em->getRepository(Client::class)->findOneBy(['codeRecup' => $code]);
        if ($ExistTransaction) {
            return
                $this->createCode();
        } else {
            return      $code;
        }
    }
}
