<?php

namespace App\Controller\Vitrine;

use Symfony\Component\Process\Exception\ProcessFailedException;
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
use Camoo\Hosting\Modules\Domains;
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
use App\Entity\Vitrine\Contenu;
use App\Entity\Vitrine\Section;
use App\Entity\Vitrine\TypeContenu;
use App\Entity\Vitrine\TypeSection;
use App\Entity\Vitrine\TypeVitrine;
use App\Entity\Vitrine\Vitrine;
use App\Entity\Vitrine\Theme;
use App\Entity\Vitrine\VitrineObject;
use App\FunctionU\MyFunction;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\String\Slugger\SluggerInterface;

class VitrineController extends AbstractController
{
    private $em;
    private $myFunction;
    private $client;
    private $apiKey;
    private $shame;
    public function __construct(EntityManagerInterface $em, HttpClientInterface $client,  ManagerRegistry $doctrine, MyFunction  $myFunction)
    {
        $this->em = $em;
        $this->doctrine = $doctrine;
        $this->client = $client;
        $this->myFunction = $myFunction;
        $this->shame =  'https://';
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
     * @Route("/vitrine/state", name="stateVitrine", methods={"POST", "GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function stateVitrine(Request $request)
    {
        $zoneId = 4785;
        $data = $request->toArray();
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['id' => $data['idVitrine']]);
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {

            $serializer = $this->get('serializer');
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {

                // var_dump($lr->getFonction()->getId(), $lr->isStatus());
                if ($lr->isStatus() && $lr->getFonction()->getId() == '27') {
                    $possible = true;
                }
            }
            // var_dump($possible);

            if ($possible) {
                if (
                    $vitrine

                ) {
                    $vitrine->setStatus(!$vitrine->getStatus());
                    $VitrineEntityManager->persist($vitrine);
                    $VitrineEntityManager->flush();
                    return
                        new JsonResponse([

                            'message' => 'Succes',
                        ], 200);
                } else {
                    return
                        new JsonResponse([

                            'message' => 'Erreur',
                        ], 400);
                }
            } else {
                return
                    new JsonResponse([

                        'message' => 'Action impossible',
                    ], 400);
            }
        } else {
            return
                new JsonResponse([

                    'message' => 'Action impossible',
                ], 400);
        }
    }

    /**
     * @Route("/vitrine/delette", name="deletteVitrineA", methods={"POST", "GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deletteVitrineA(Request $request)
    {
        $zoneId = 4785;
        $data = $request->toArray();
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['id' => $data['idVitrine']]);
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {

            $serializer = $this->get('serializer');
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {

                // var_dump($lr->getFonction()->getId(), $lr->isStatus());
                if ($lr->isStatus() && $lr->getFonction()->getId() == '28') {
                    $possible = true;
                }
            }
            // var_dump($possible);

            if ($possible) {
                if (
                    $vitrine

                ) {


                    $responseL = $this->client->request(
                        'POST',
                        'https://api.camoo.hosting/v1/auth',
                        [
                            "json" => [
                                "email" => "gihaireslontsi@gmail.com",
                                "password" => "Gessiia@2022"

                            ],
                            'headers' => ['Content-Type' => 'application/json']
                        ]
                    ); //code... 
                    if ($responseL->toArray()['result']['access_token'] != null) {
                        $response = $this->client->request(
                            'POST',
                            'https://api.camoo.hosting/v1/dns/delete-record',
                            [
                                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $responseL->toArray()['result']['access_token']],
                                "json" => [
                                    "zone_id" =>
                                    $zoneId,
                                    "record_id" =>  $vitrine->getRecordId1()

                                ]
                            ]
                        );
                        $response0 = $this->client->request(
                            'POST',
                            'https://api.camoo.hosting/v1/dns/delete-record',
                            [
                                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $responseL->toArray()['result']['access_token']],
                                "json" => [
                                    "zone_id" =>
                                    $zoneId,
                                    "record_id" =>  $vitrine->getRecordId2()

                                ]
                            ]
                        ); //code... 
                        if ($response->toArray()['status'] == 'OK') {


                            return
                                new JsonResponse([
                                    'status' => $response->getStatusCode(),
                                    'message' => $response->toArray(),
                                ], 201);
                        } else {
                            return
                                new JsonResponse([
                                    'status' => $response->getStatusCode(),
                                    'message' => 'Erreur',
                                ], 400);
                        }
                    } else {
                        return
                            new JsonResponse([

                                'message' => 'Erreur',
                            ], 400);
                    }
                } else {
                    return
                        new JsonResponse([

                            'message' => 'Erreur',
                        ], 400);;
                }
            } else {
                return
                    new JsonResponse([

                        'message' => 'Action impossible',
                    ], 400);
            }
        } else {
            return
                new JsonResponse([

                    'message' => 'Action impossible',
                ], 400);
        }
    }
    /**
     * @Route("/vitrine/delettedomain", name="testdeletteVitrine", methods={"POST", "GET"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function testdeletteVitrine(Request $request)
    {
        $zoneId = 4785;
        $data = $request->toArray();
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['nom' => $data['nom']]);
        $record =
            $data['recordId'] == 1 ? $vitrine->getRecordId1() : $vitrine->getRecordId2();
        if (
            $vitrine

        ) {


            $responseL = $this->client->request(
                'POST',
                'https://api.camoo.hosting/v1/auth',
                [
                    "json" => [
                        "email" => "gihaireslontsi@gmail.com",
                        "password" => "Gessiia@2022"

                    ],
                    'headers' => ['Content-Type' => 'application/json']
                ]
            ); //code... 
            if ($responseL->toArray()['result']['access_token'] != null) {
                $response = $this->client->request(
                    'POST',
                    'https://api.camoo.hosting/v1/dns/delete-record',
                    [
                        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $responseL->toArray()['result']['access_token']],
                        "json" => [
                            "zone_id" =>
                            $zoneId,
                            "record_id" => $record

                        ]
                    ]
                ); //code... 
                if ($response->toArray()['status'] == 'OK') {


                    return
                        new JsonResponse([
                            'status' => $response->getStatusCode(),
                            'message' => $response->toArray(),
                        ], 201);
                } else {
                    return
                        new JsonResponse([
                            'status' => $response->getStatusCode(),
                            'message' => 'Erreur',
                        ], 400);
                }
            } else {
                return
                    new JsonResponse([

                        'message' => 'Erreur',
                    ], 400);
            }
        } else {
            return
                new JsonResponse([

                    'message' => 'Erreur',
                ], 400);;
        }
    }
    /**
     *  
     * @param array $data doit nom  de la vitrine   
    
     */
    public function createVitrine($nom)
    {
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');


        $zoneId = 4785;

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['nom' => $nom]);

        if (
            $vitrine
        ) {


            $responseL = $this->client->request(
                'POST',
                'https://api.camoo.hosting/v1/auth',
                [
                    "json" => [
                        "email" => "gihaireslontsi@gmail.com",
                        "password" => "Gessiia@2022"

                    ],
                    'headers' => ['Content-Type' => 'application/json']
                ]
            ); //code... 
            if ($responseL->toArray()['result']['access_token'] != null) {
                $response = $this->client->request(
                    'POST',
                    'https://api.camoo.hosting/v1/dns/add-record',
                    [
                        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $responseL->toArray()['result']['access_token']],
                        "json" => [
                            "zone_id" =>
                            $zoneId,
                            "host" =>  $nom,
                            "type" => "A",
                            "value" => "62.171.160.98"
                        ]
                    ]
                ); //code...   
                $response0 = $this->client->request(
                    'POST',
                    'https://api.camoo.hosting/v1/dns/add-record',
                    [
                        'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $responseL->toArray()['result']['access_token']],
                        "json" => [
                            "zone_id" =>
                            $zoneId,
                            "host" => "www." . $nom,
                            "type" => "A",
                            "value" => "62.171.160.98"
                        ]
                    ]
                ); //code... 


                if ($response->toArray()['status'] == 'OK' && $response0->toArray()['status'] == 'OK') {
                    $vitrine->setRecordId1($response->toArray()['result']['record_id']);
                    $vitrine->setRecordId2($response0->toArray()['result']['record_id']);

                    $this->createConfig($nom, $vitrine->getTypeVitrine()->getId());
                    $this->SSL(($vitrine->getTypeVitrine()->getId() == 1) ?  $nom . '.pubx.cm' :  $nom . 'sms' . '.pubx.cm');
                    $VitrineEntityManager->persist($vitrine);
                    $VitrineEntityManager->flush();
                    return true;
                } else {
                    return false;
                }
            } else {
                return
                    false;
            }
        } else {
            return
                false;
        }
    }

    public function createConfig($nom, $type)
    {
        $pub = ($type == 1) ?  $nom . '.pubx.cm' :  $nom . 'sms' . '.pubx.cm';

        $dest = ($type == 1) ? '/var/www/DevooVitrineStandard/infinity' : '/var/www/vitrine.bulk.gessiia.com/dist/spa/';
        $conf
            =
            "
server {

   listen 80;
   server_name $pub www.$pub;
   index index.php index.html index.htm;
   root  $dest;
   access_log /var/log/nginx/digest/admin.bulk.gessiia.com.access.log;
   error_log /var/log/nginx/digest/admin.bulk.gessiia.com.error.log;
#  return 301 https://\$server_name\$request_uri;

    location / {
            try_files \$uri \$uri/ /index.html;
            #    proxy_pass https://localhost:8081/;
            #    proxy_http_version 1.1;
            #    proxy_set_header Upgrade \$http_upgrade;
            #    proxy_set_header Connection 'upgrade';

            #rewrite ^/api/?(.*)$ /webservice/dispatcher.php?url=$1 last;
            #rewrite ^/([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$1$2.jpg last;
            #rewrite ^/([0-9])([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$2/$1$2$3.jpg last;
            #rewrite ^/([0-9])([0-9])([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$2/$3/$1$2$3$4.jpg last;
            #rewrite ^/([0-9])([0-9])([0-9])([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$2/$3/$4/$1$2$3$4$5.jpg last;
            #rewrite ^/([0-9])([0-9])([0-9])([0-9])([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$2/$3/$4/$5/$1$2$3$4$5$6.jpg last;
            #rewrite ^/([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$2/$3/$4/$5/$6/$1$2$3$4$5$6$7.jpg last;
            #rewrite ^/([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$2/$3/$4/$5/$6/$7/$1$2$3$4$5$6$7$8.jpg last;
            #rewrite ^/([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])([0-9])(-[_a-zA-Z0-9-]*)?(-[0-9]+)?/.+\.jpg$ /img/p/$1/$2/$3/$4/$5/$6/$7/$8/$1$2$3$4$5$6$7$8$9.jpg last;
            #rewrite ^/c/([0-9]+)(-[_a-zA-Z0-9-]*)(-[0-9]+)?/.+\.jpg$ /img/c/$1$2.jpg last;
            #rewrite ^/c/([a-zA-Z-]+)(-[0-9]+)?/.+\.jpg$ /img/c/$1.jpg last;
            #rewrite ^/([0-9]+)(-[_a-zA-Z0-9-]*)(-[0-9]+)?/.+\.jpg$ /img/c/$1$2.jpg last;
            #try_files \$uri \$uri/ /index.php?\$args;
            #try_files \$uri /index.php\$is_args\$args;
            
        #       try_files \$uri \$uri/ =404;
        }
    location ~ \.php$ {
        try_files \$uri =404;
        fastcgi_intercept_errors on;
        fastcgi_index  index.php;
        include        fastcgi_params;
        fastcgi_param  SCRIPT_FILENAME  \$document_root\$fastcgi_script_name;
        fastcgi_pass   php-fpm;
    }
    location ~ \.php$ {
    try_files \$uri =404;
    fastcgi_split_path_info ^(.+\.php)(/.+)$;
    fastcgi_pass unix:/run/php-fpm/www.sock;
    fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    include /etc/nginx/fastcgi_params;
    }
    gzip on;
    gzip_comp_level 1;
    gzip_buffers 16 8k;
    gzip_types application/json text/css application/javascript;

 }";

        $Handle = fopen('/etc/nginx/conf.d/' . $pub . '.conf', 'w+');
        $bodytag =
            file_get_contents('/etc/nginx/conf.d/' . $pub . '.conf', true);
        $bodytag = file_put_contents(
            '/etc/nginx/conf.d/' . $pub . '.conf',
            $conf
        );

        fclose($Handle);
    }



    public function SSL($pub)
    {

        $output1 = exec("sudo /usr/bin/certbot --nginx -d " . $pub . " -d www." . $pub . ' 2>&1', $outArr, $rc);
        echo  $output1;
    }
    public function ds($datas)
    {
        return ((intval($datas) != 0) ? true : false);
    }


    /**
     * @Route("/rs", name="rrads", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array 
     * */
    public function rrads(Request $request)
    {

        $output1 = exec("sudo /usr/bin/certbot --nginx -d Babi.pubx.cm -d www.Babi.pubx.cm" . ' 2>&1', $outArr, $rc);

        return
            new JsonResponse([

                'status' => $output1

            ], 201);
    }

    public function sitemapUpdate()
    {

        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $vitrines = $VitrineEntityManager->getRepository(Vitrine::class)->findAll();
        $listV = [];
        if (
            $vitrines
        ) {
            foreach ($vitrines  as $vitrine) {

                array_push(
                    $listV,
                    $this->shame . $vitrine->getNom()
                        . ($vitrine
                            ->getTypeVitrine()->getId() == 2 ? '' : ".") . $vitrine
                        ->getTypeVitrine()->getLink(),
                );
            }
        }

        $myfile = fopen("sitemap_vitrine.xml", 'w+') or die("Unable to open file!");

        $finalLIstString = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>

<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";

        foreach ($listV as $vi) {
            $finalLIstString = $finalLIstString . "\n<url><loc>" . $vi . "</loc></url>";
        }
        $finalLIstString
            =  $finalLIstString . "\n</urlset>";
        fwrite(
            $myfile,
            $finalLIstString
        );

        // fclose($Handle);
        // fwrite($myfile, $txt);
        fclose($myfile);
        // $output1 = exec("sudo /usr/bin/certbot --nginx -d guyditsms.pubx.cm -d www.guyditsms.pubx.cm" . ' 2>&1', $outArr, $rc);

        // return
        //     new JsonResponse([

        //         'status' => true

        //     ], 201);
    }
    /**
     * @Route("/vitrine/verify/exist", name="vitrineExist", methods={"POST"})
     * @param Request $request    concerne le modification type Text de contenu
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir  le nom de la vitrine
    
     */
    public function vitrineExist(Request $request)
    {
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();


        if (empty($data['vitrine'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre vitrine '
            ], 400);
        }

        $serializer = $this->get('serializer');


        $nom = $data['vitrine'];

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['nom' => $nom]);

        return
            new JsonResponse([

                'status' => ($vitrine == null) ? true : false

            ], 201);
    }



    public function vitrineExist2($nom)
    {
        $ext = false;
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findAll();
        foreach ($vitrine   as $vf) {


            if ($vf->getNom()  == $nom) {
                $ext = true;
            }
        }
        // dd($ext);
        return  $ext;
    }
    /**
     * @Route("/vitrine/new", name="vitrineNew", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client qui cree la vitrine, typeVitrine,nom, description,proprietaire
     * 
     */
    public function vitrineNew(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        if (empty($data['keySecret']) || empty($data['proprietaire']) || empty($data['nom'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret et proprietaire '
            ], 400);
        }
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);


        if ($clientUser) {

            $serializer = $this->get('serializer');
            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {

                // var_dump($lr->getFonction()->getId(), $lr->isStatus());
                if ($lr->isStatus() && $lr->getFonction()->getId() == '25') {
                    $possible = true;
                }
            }
            // var_dump($possible);

            if ($possible) {
                $typeSection = $VitrineEntityManager->getRepository(TypeSection::class)->findAll();

                $typeContenuTitle =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 1]);
                $typeContenuDescription =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 2]);
                $typeContenuLien =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 3]);
                $typeContenuFavIcones =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 4]);
                $typeContenuImage =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 5]);
                $typeContenuPhone =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 7]);
                $typeContenuEmail =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 6]);
                $typeContenuEmailSubS =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 8]);
                $typeContenuService =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 9]);
                $typeContenuGalerie =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 10]);
                $typeFindUs =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 11]);
                $typeFollowUs =
                    $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 12]);

                $type = $data['typeVitrine'];
                $nom = $data['nom'];
                $clientId =
                    $clientUser->getId();
                $description = $data['description'];
                $proprietaire = $data['proprietaire'];

                $typeVitrine = $VitrineEntityManager->getRepository(TypeVitrine::class)->findOneBy(['id' => $type]);
                $vitrineExist = $this->vitrineExist2($nom);

                // if (
                //     $vitrineExist
                // ) {
                //     return new JsonResponse(
                //         [
                //             'message' => 'Action impossible '
                //         ],
                //         203
                //     );
                // } else {
                try {
                    $vitrine = new Vitrine();
                    $vitrine->setNom($this->myFunction->removeSpace(
                        $nom
                    ));
                    $theme =
                        $VitrineEntityManager->getRepository(Theme::class)->findOneBy(['id' => 1]);

                    $vitrine->setDescription('description');
                    $vitrine->setClientId($clientId);
                    $vitrine->setProprietaire($proprietaire);
                    $vitrine->setTypeVitrine($typeVitrine);
                    $vitrine->setTheme($theme);
                    $VitrineEntityManager->persist($vitrine);
                    $VitrineEntityManager->flush();

                    $this->sitemapUpdate();

                    if (
                        $type == 2
                    ) {


                        $email = $data['email'] ?? '';
                        $adresse = $data['adresse'] ?? '';
                        $numero1 = $data['numero1'] ?? '';
                        $numero2 = $data['numero2'] ?? '';
                        $typeContactUs =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 7]);
                        $typeER =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 8]);

                        $section = new Section();
                        $section->setVitrine($vitrine);
                        $section->setTypeSection($typeContactUs);
                        $section->setNom($typeContactUs->getLibelle());
                        $section->setDescription($typeContactUs->getLibelle());
                        $VitrineEntityManager->persist($section);
                        $VitrineEntityManager->flush();
                        $contenu = new Contenu();
                        $contenu->setSection($section);
                        $contenu->setDescription('Description Contact Us');
                        $contenu->setTypeContenu($typeContenuDescription);
                        $VitrineEntityManager->persist(
                            $contenu
                        );
                        $contenu0 = new Contenu();
                        $contenu0->setSection($section);
                        $contenu0->setDescription($email);
                        $contenu0->setTypeContenu($typeContenuEmail);

                        $VitrineEntityManager->persist(
                            $contenu0
                        );
                        $contenu3 = new Contenu();
                        $contenu3->setSection($section);
                        $contenu3->setDescription($adresse);
                        $contenu3->setTypeContenu($typeFindUs);
                        $VitrineEntityManager->persist(
                            $contenu3
                        );


                        $section->setStatus(1);

                        $contenuX = new Contenu();
                        $contenuX->setSection($section);
                        $contenuX->setDescription($numero1);
                        $contenuX->setTypeContenu($typeContenuPhone);

                        $VitrineEntityManager->persist(
                            $contenuX
                        );
                        $contenuXA = new Contenu();
                        $contenuXA->setSection($section);
                        $contenuXA->setDescription($numero2);
                        $contenuXA->setTypeContenu($typeContenuPhone);

                        $VitrineEntityManager->persist(
                            $contenuXA
                        );

                        $sociaux = [
                            'Facebook', 'Twitter',  'Instagram',

                            'Snapchat', 'Telegram',
                            'WhatsAPP', 'StackExchange',
                            'TikTok'
                        ];
                        $section1 = new Section();
                        $section1->setVitrine($vitrine);
                        $section1->setTypeSection($typeER);
                        $section1->setNom($typeER->getLibelle());
                        $section1->setDescription($typeER->getLibelle());
                        $VitrineEntityManager->persist($section1);
                        $VitrineEntityManager->flush();
                        $section1->setStatus(1);

                        foreach ($sociaux as $s) {
                            $contenu = new Contenu();
                            $contenu->setSection($section1);
                            $contenu->setDescription($s);
                            $contenu->setLien('');
                            $contenu->setTypeContenu($typeContenuLien);
                            $VitrineEntityManager->persist(
                                $contenu
                            );
                            $VitrineEntityManager->flush();
                        }


                        $VitrineEntityManager->flush();
                        $start =    $this->createVitrine($nom);
                        if ($start) {
                            return
                                new JsonResponse([
                                    'message'
                                    =>   'Vitrine Cree Avec success'


                                ], 201);
                        } else {
                            return new JsonResponse([
                                'message' => 'Echec lors de la creation de cette vitrine '
                            ], 203);
                        }
                    } else if (
                        $type == 1

                    ) {



                        $email = $data['email'] ?? '';
                        $adresse = $data['adresse'] ?? '';
                        $numero1 = $data['numero1'] ?? '';
                        $numero2 = $data['numero2'] ?? '';
                        $titre = $data['titre'] ?? '';
                        foreach ($typeSection as $typeS) {
                            $section = new Section();
                            $section->setVitrine($vitrine);
                            $section->setTypeSection($typeS);
                            $section->setNom($typeS->getLibelle());
                            $section->setDescription($typeS->getLibelle());
                            $VitrineEntityManager->persist($section);
                            $VitrineEntityManager->flush();
                            if ($typeS->getId() == 1) {
                                $contenu = new Contenu();
                                $contenu->setSection($section);
                                $contenu->setDescription('image');
                                $contenu->setTypeContenu($typeContenuImage);
                                $section->setStatus(1);

                                $contenuFavIcon = new Contenu();
                                $contenuFavIcon->setSection($section);
                                $contenuFavIcon->setDescription('image');
                                $contenuFavIcon->setTypeContenu($typeContenuFavIcones);


                                $contenu0 = new Contenu();
                                $contenu0->setSection($section);
                                $contenu0->setDescription($titre);
                                $contenu0->setTypeContenu($typeContenuTitle);



                                $contenu1 = new Contenu();
                                $contenu1->setSection($section);
                                $contenu1->setDescription($description);
                                $contenu1->setTypeContenu($typeContenuDescription);
                                $VitrineEntityManager->persist(
                                    $contenuFavIcon
                                );
                                $VitrineEntityManager->persist(
                                    $contenu
                                );
                                $VitrineEntityManager->persist(
                                    $section
                                );
                                $vitrineObject0 = new VitrineObject();
                                $vitrineObject0->setContenu($contenuFavIcon);
                                $vitrineObject = new VitrineObject();
                                $vitrineObject->setContenu($contenu);
                                // $vitrineObject->setDescription('image');
                                $VitrineEntityManager->persist(
                                    $vitrineObject
                                );
                                $VitrineEntityManager->persist(
                                    $vitrineObject0
                                );


                                $VitrineEntityManager->persist(
                                    $contenu0
                                );
                                $VitrineEntityManager->persist($contenu1);
                                $VitrineEntityManager->flush();
                            } else  if ($typeS->getId() == 2) {

                                $contenu = new Contenu();
                                $contenu->setSection($section);
                                $contenu->setDescription('A propos de votre Structure');
                                $contenu->setTypeContenu($typeContenuDescription);


                                $VitrineEntityManager->persist(
                                    $contenu
                                );

                                $VitrineEntityManager->persist($contenu);
                                $VitrineEntityManager->flush();
                            } else   if ($typeS->getId() == 6) {

                                $contenu2 = new Contenu();
                                $contenu2->setSection($section);
                                $contenu2->setDescription($nom);
                                $section->setStatus(1);
                                $contenu3 = new Contenu();
                                $contenu3->setSection($section);
                                $contenu3->setDescription('GESSIIA SARL');
                                $VitrineEntityManager->persist(
                                    $contenu2
                                );
                                $VitrineEntityManager->persist(
                                    $contenu3
                                );
                                $VitrineEntityManager->persist($contenu3);
                                $VitrineEntityManager->persist($section);
                                $VitrineEntityManager->flush();
                            } else   if ($typeS->getId() == 3) {

                                $contenu6 = new Contenu();
                                $contenu6->setSection($section);
                                $contenu6->setDescription('titre de votre service');
                                $contenu6->setTypeContenu($typeContenuTitle);

                                $contenu5 = new Contenu();
                                $contenu5->setSection($section);
                                $contenu5->setDescription('Description de votre service');
                                $contenu5->setTypeContenu($typeContenuDescription);
                                $contenu7 = new Contenu();
                                $contenu7->setSection($section);
                                $contenu7->setTypeContenu($typeContenuImage);
                                $contenu7->setDescription('image de votre service');

                                $vitrineObject = new VitrineObject();
                                $vitrineObject->setContenu($contenu7);
                                $vitrineObject->setFilePath('Aucune');
                                // $vitrineObject->setDescription('image');


                                $VitrineEntityManager->persist(
                                    $contenu6
                                );
                                $VitrineEntityManager->persist(
                                    $contenu5
                                );
                                $VitrineEntityManager->persist(
                                    $contenu7
                                );
                                $VitrineEntityManager->persist(
                                    $vitrineObject
                                );

                                $VitrineEntityManager->persist($contenu5);
                                $VitrineEntityManager->flush();
                            } else   if ($typeS->getId() == 4) {

                                $contenu6 = new Contenu();
                                $contenu6->setSection($section);
                                $contenu6->setDescription('titre de votre galerie');
                                $contenu6->setTypeContenu($typeContenuTitle);

                                $contenu5 = new Contenu();
                                $contenu5->setSection($section);
                                $contenu5->setDescription('Description de votre galerie');
                                $contenu5->setTypeContenu($typeContenuDescription);

                                $VitrineEntityManager->persist(
                                    $contenu6
                                );
                                $VitrineEntityManager->persist(
                                    $contenu5
                                );
                                $VitrineEntityManager->persist($contenu5);
                                $VitrineEntityManager->flush();
                            } else   if ($typeS->getId() == 9) {

                                $VitrineEntityManager->flush();
                            } else   if ($typeS->getId() == 7) {

                                $contenu = new Contenu();
                                $contenu->setSection($section);
                                $contenu->setDescription('Description Contact Us');
                                $contenu->setTypeContenu($typeContenuDescription);
                                $VitrineEntityManager->persist(
                                    $contenu
                                );
                                $contenu0 = new Contenu();
                                $contenu0->setSection($section);
                                $contenu0->setDescription($email);
                                $contenu0->setTypeContenu($typeContenuEmail);

                                $contenu3 = new Contenu();
                                $contenu3->setSection($section);
                                $contenu3->setDescription($adresse);
                                $contenu3->setTypeContenu($typeFindUs);
                                $VitrineEntityManager->persist(
                                    $contenu3
                                );
                                $section->setStatus(1);

                                $contenuX = new Contenu();
                                $contenuX->setSection($section);
                                $contenuX->setDescription($numero1);
                                $contenuX->setTypeContenu($typeContenuPhone);

                                $VitrineEntityManager->persist(
                                    $contenuX
                                );
                                $contenuXA = new Contenu();
                                $contenuXA->setSection($section);
                                $contenuXA->setDescription($numero2);
                                $contenuXA->setTypeContenu($typeContenuPhone);

                                $VitrineEntityManager->persist(
                                    $contenuXA
                                );

                                $VitrineEntityManager->persist(
                                    $contenu0
                                );


                                $VitrineEntityManager->flush();
                            } else   if ($typeS->getId() == 8) {
                                $sociaux = [
                                    'Facebook', 'Twitter',  'Instagram',

                                    'Snapchat', 'Telegram',
                                    'WhatsAPP', 'StackExchange',
                                    'TikTok'
                                ];

                                $section->setStatus(1);

                                foreach ($sociaux as $s) {
                                    $contenu = new Contenu();
                                    $contenu->setSection($section);
                                    $contenu->setDescription($s);
                                    $contenu->setLien('');
                                    $contenu->setTypeContenu($typeContenuLien);
                                    $VitrineEntityManager->persist(
                                        $contenu
                                    );
                                    $VitrineEntityManager->flush();
                                }

                                $VitrineEntityManager->flush();
                            } else {
                                $contenu4 = new Contenu();
                                $contenu4->setSection($section);
                                $contenu4->setDescription('');
                                $VitrineEntityManager->persist(
                                    $contenu4
                                );
                                $VitrineEntityManager->flush();
                            }
                        }
                        $start =    $this->createVitrine($nom);
                        if ($start) {
                            return
                                new JsonResponse([
                                    'message'
                                    =>   'Vitrine Cree Avec success'


                                ], 201);
                        } else {
                            return new JsonResponse([
                                'message' => 'Echec lors de la creation de cette vitrine '
                            ], 203);
                        }
                    } else {
                        return new JsonResponse([
                            'message' => 'Echec lors de la creation de cette vitrine '
                        ], 203);
                    }
                } catch (Exception $e) {
                    return new JsonResponse([
                        'message' => 'Une Erreur est survenue '
                    ], 203);
                }
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
     * @Route("/vitrine/user", name="vitrineUser", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir le nom vitrine de la vitrine, 
     * 
     */
    public function vitrineUser(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        if (empty($data['vitrine'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre vitrine '
            ], 400);
        }

        $typeHeader =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 1]);
        $typeAboutUs =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 2]);
        $typeService =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 3]);
        $typeGalerie =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 4]);
        $typeTemoignage =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 5]);
        $typeFooter =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 6]);
        $typeContactUs =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 7]);
        $typeEReputation =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 8]);
        $typeConfiance =
            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 9]);

        $typeContenuTitle =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 1]);
        $typeContenuDescription =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 2]);
        $typeContenuLien =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 3]);
        $typeContenuFavIcones =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 4]);
        $typeContenuImage =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 5]);
        $typeContenuPhone =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 7]);
        $typeContenuEmail =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 6]);
        $typeContenuEmailSubS =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 13]);
        $typeContenuService =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 9]);
        $typeContenuGalerie =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 10]);
        $typeFindUs =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 11]);
        $typeFollowUs =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 12]);


        $serializer = $this->get('serializer');


        $nom = $data['vitrine'];

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['nom' => $nom]);

        if (
            $vitrine
        ) {

            if (
                $vitrine
                ->getTypeVitrine()->getId() == 2
            ) {

                $sectionContactUs =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeContactUs]);
                $contactUs =  [];

                if (
                    $sectionContactUs
                ) {
                    // $titreHeader =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuTitle]);
                    $descriptionContactUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuDescription]);
                    $emailContactUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmail]);
                    $findUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeFindUs]);

                    //  dd($sectionContactUs->getId());
                    $followUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeFollowUs]);
                    $ldataFollow = [];
                    foreach ($followUs as $Fu) {

                        $ldataFollow[]
                            = ['id' => $Fu->getId(), 'text' => $Fu->getDescription()];
                    }
                    $contactUsL =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmailSubS]);
                    $lcontactUS = [];
                    foreach ($contactUsL as $Cu) {

                        $lcontactUS[]
                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription(), 'url' => $Cu->getLien()];
                    }


                    $contactUsNum =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuPhone]);
                    $lcontactUSNum = [];
                    foreach ($contactUsNum as $Cu) {

                        $lcontactUSNum[]
                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription()];
                    }
                    $contactUs =  [
                        'id'
                        => $sectionContactUs->getId(),
                        'status'
                        => $sectionContactUs->isStatus(),

                        'email' =>   $emailContactUs  ?  ['id' =>  $emailContactUs->getId(), 'text' =>  $emailContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                        'description' => $descriptionContactUs ? ['id' => $descriptionContactUs->getId(), 'text' => $descriptionContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                        'findUs' => $findUs ? ['id' => $findUs->getId(), 'text' => $findUs->getDescription()] :  ['id' => 0, 'text' => ''],
                        'followUs' => $ldataFollow,
                        'contactUs' => $lcontactUS,
                        'contactUsPhone' => $lcontactUSNum,
                    ];
                }
                $EReputation = [];

                $sectionEReputation =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeEReputation]);

                if (
                    $sectionEReputation
                ) {
                    // $titreGalerie =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuTitle]);
                    // $descriptionService =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuDescription]);
                    $ldataER = [];
                    $LEReputation =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuLien]);

                    $sociaux = [
                        'Facebook', 'Twitter',  'Instagram',
                        'Snapchat', 'Telegram',  'WhatsAPP',
                        'StackExchange', 'TikTok'
                    ];

                    foreach ($LEReputation as $EReputationU) {
                        foreach ($sociaux as $s) {


                            if ($EReputationU->getDescription() == $s) {

                                $ldataER[] =  ['id' => $EReputationU->getId(), 'title' => $s, 'lien' => $EReputationU->getLien()];
                            }
                        }
                    }


                    $EReputation =  [
                        'id'
                        => $sectionEReputation->getId(),
                        'status'
                        => $sectionEReputation->isStatus(),
                        'data' =>  $ldataER
                    ];
                }



                return
                    new JsonResponse([
                        'proprietaire'
                        =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getProprietaire()])->getNom(),
                        'createur'
                        =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getClientId()])->getNom(),
                        'title' => $vitrine->getNom(),
                        'description' => $vitrine->getDescription(),
                        'date' =>    $vitrine->getDateCreated()->format('d/m/Y'),
                        'metaDescription' =>    $vitrine
                            ->getMetaDescription(),
                        'contactUs' =>  $contactUs, 'ereputation' => $EReputation,




                    ], 201);
            } else if (
                $vitrine
                ->getTypeVitrine()->getId() == 1
            ) {
                $header = [];
                $statusH = false;
                $idA = 0;
                $aboutUs = [];
                $statusA = false;
                $idH = 0;
                $service = [];
                $statusS = false;
                $idS = 0;
                $galerie = [];
                $statusG = false;
                $idG = 0;
                $temoignage = [];
                $statusT = false;
                $idT = 0;
                $footer = [];
                $photo = [];

                $statusF = false;
                $idF = 0;
                $contactUs = [];
                $statusCont = false;
                $idCont = 0;

                $lsetions =
                    $VitrineEntityManager->getRepository(Section::class)->findBy(['vitrine' => $vitrine]);

                foreach ($lsetions as $section) {
                    $contenus =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $section]);

                    if ($contenus) {

                        if ($section->getTypeSection()->getId() == 6) {
                            $statusF = $section->isStatus();
                            $idF = $section->getId();

                            foreach ($contenus as $contenu) {
                                array_push($footer, ['id' => $contenu->getId(), 'text' => $contenu->getDescription()]);
                            }
                        }
                    }
                }
                //Section Header(typeHeader)


                $sectionHeader =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeHeader]);

                if (
                    $sectionHeader
                ) {
                    $titreHeader =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuTitle]);
                    $descriptionHeader =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuDescription]);
                    $imageHeader =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuImage]);
                    $FaIcon =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuFavIcones]);

                    $favIcon = '';
                    $Faobject =
                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $FaIcon]);

                    if ($Faobject) {
                        $favIcon = ['id' => $FaIcon->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $Faobject->getFilePathId()];
                    }
                    $image = '';
                    $object =
                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $imageHeader]);

                    if ($object) {
                        $image = ['id' => $imageHeader->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                    }

                    $header =  [
                        'status'
                        => $sectionHeader->isStatus(),
                        'image' => $image,
                        'favIcon' => $favIcon,
                        'title' =>  $titreHeader ?  ['id' => $titreHeader->getId(), 'text' => $titreHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                        'description' => $descriptionHeader ? ['id' => $descriptionHeader->getId(), 'text' => $descriptionHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                    ];
                }
                //Section AboutUs($typeAboutUs)


                $sectionAboutUs =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeAboutUs]);

                if (
                    $sectionAboutUs
                ) {
                    // $titreHeader =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionAboutUs, 'typeContenu' => $typeContenuTitle]);
                    $descriptionAboutUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionAboutUs, 'typeContenu' => $typeContenuDescription]);
                    $aboutUs =  [
                        'id'
                        => $sectionAboutUs->getId(),
                        'status'
                        => $sectionAboutUs->isStatus(),

                        // 'title' =>  $titreHeader ?  ['id' => $titreHeader->getId(), 'text' => $titreHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                        'description' => $descriptionAboutUs ? ['id' => $descriptionAboutUs->getId(), 'text' => $descriptionAboutUs->getDescription()] :  ['id' => 0, 'text' => ''],
                    ];
                }
                //Section Service($typeService)


                $sectionService =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeService]);

                if (
                    $sectionService
                ) {
                    $imageS = null;
                    $titreService =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuTitle]);
                    $descriptionService =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuDescription]);
                    $ldataService = [];
                    $LService =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionService, 'typeContenu' => $typeContenuService]);

                    foreach ($LService as $serviceU) {

                        $object =
                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $serviceU]);
                        if ($object) {


                            $ldataService[] = ['id' => $serviceU->getId(), 'title' => $serviceU->getTitleImage(), 'description' => $serviceU->getDescriptionImage(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' .  $object->getFilePathId() ?? ''];
                        }
                    }
                    $imageService =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuImage]);

                    $object =
                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $imageService]);

                    if ($object) {
                        $imageS = ['id' => $imageService->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                    }

                    $service =  [
                        'id'
                        => $sectionService->getId(),
                        'status'
                        => $sectionService->isStatus(),
                        'image' => $imageS,

                        'title' =>  $titreService ?  ['id' => $titreService->getId(), 'text' => $titreService->getDescription()] :  ['id' => 0, 'text' => ''],
                        'description' => $descriptionService ? ['id' => $descriptionService->getId(), 'text' => $descriptionService->getDescription()] :  ['id' => 0, 'text' => ''],
                        'data' =>  $ldataService
                    ];
                }

                //Section galerie($typeGalerie)


                $sectionGalerie =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeGalerie]);

                if (
                    $sectionGalerie
                ) {
                    $titreGalerie =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuTitle]);
                    $descriptionGalerie =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuDescription]);
                    $ldataGalerie = [];
                    $LGalerie =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuGalerie]);
                    foreach ($LGalerie as $galerieU) {
                        $object =
                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $galerieU]);
                        if ($object) {


                            $ldataGalerie[] = ['id' => $galerieU->getId(), 'title' => $galerieU->getTitleImage(), 'description' => $galerieU->getDescriptionImage(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' .  $object->getFilePathId() ?? ''];
                        }
                    }

                    $bGalerie =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuImage]);

                    $object =
                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $bGalerie]);

                    if ($object) {
                        $image = ['id' => $imageHeader->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                    }


                    $galerie =  [
                        'id'
                        => $sectionGalerie->getId(),    'status'
                        => $sectionGalerie->isStatus(),

                        'title' =>  $titreGalerie ?  ['id' => $titreGalerie->getId(), 'text' => $titreGalerie->getDescription()] :  ['id' => 0, 'text' => ''],
                        'description' => $descriptionGalerie ? ['id' => $descriptionGalerie->getId(), 'text' => $descriptionGalerie->getDescription()] :  ['id' => 0, 'text' => ''],
                        'data' =>  $ldataGalerie,  'image' => $image
                    ];
                }


                //Section temoignage($typeTemoignage)

                $sectionTemoignage =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeTemoignage]);

                if (
                    $sectionTemoignage
                ) {
                    // $titreGalerie =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuTitle]);
                    // $descriptionService =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuDescription]);
                    $ldataGalerie = [];
                    $LTemoignage =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuGalerie]);
                    foreach ($LTemoignage as $temoignageU) {
                        $object =
                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $temoignageU]);
                        if ($object) {


                            $ldataGalerie[] = ['id' => $temoignageU->getId(), 'nom' => $temoignageU->getNomTemoin(), 'poste' => $temoignageU->getPosteTemoin(), 'description' => $temoignageU->getDescription(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' .  $object->getFilePathId() ?? ''];
                        }
                    }


                    $temoignage =  [
                        'id'
                        => $sectionTemoignage->getId(),
                        'status'
                        => $sectionTemoignage->isStatus(),
                        'data' =>  $ldataGalerie
                    ];
                }


                //Section EReputation($typeEReputation)

                $EReputation = [];
                $sectionEReputation =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeEReputation]);

                if (
                    $sectionEReputation
                ) {
                    // $titreGalerie =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuTitle]);
                    // $descriptionService =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuDescription]);
                    $ldataER = [];
                    $LEReputation =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuLien]);

                    $sociaux = [
                        'Facebook', 'Twitter',  'Instagram',
                        'Snapchat', 'Telegram',  'WhatsAPP',
                        'StackExchange', 'TikTok'
                    ];

                    foreach ($LEReputation as $EReputationU) {
                        foreach ($sociaux as $s) {


                            if ($EReputationU->getDescription() == $s) {
                                $ldataER[] =  ['id' => $EReputationU->getId(), 'title' => $s, 'lien' => $EReputationU->getLien()];
                            }
                        }
                    }


                    $EReputation =  [
                        'id'
                        => $sectionEReputation->getId(),
                        'status'
                        => $sectionEReputation->isStatus(),
                        'data' =>  $ldataER
                    ];
                }




                //Section ContactUs($typeContactUs)


                $sectionContactUs =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeContactUs]);
                $contactUs =  [];
                if (
                    $sectionContactUs
                ) {
                    // $titreHeader =
                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuTitle]);
                    $descriptionContactUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuDescription]);
                    $emailContactUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmail]);
                    $findUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeFindUs]);

                    //  dd($sectionContactUs->getId());
                    $followUs =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeFollowUs]);
                    $ldataFollow = [];
                    foreach ($followUs as $Fu) {

                        $ldataFollow[]
                            = ['id' => $Fu->getId(), 'text' => $Fu->getDescription()];
                    }
                    $contactUsL =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmailSubS]);
                    $lcontactUS = [];
                    foreach ($contactUsL as $Cu) {

                        $lcontactUS[]
                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription(), 'url' => $Cu->getLien()];
                    }


                    $contactUsNum =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuPhone]);
                    $lcontactUSNum = [];
                    foreach ($contactUsNum as $Cu) {

                        $lcontactUSNum[]
                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription()];
                    }
                    $contactUs =  [
                        'id'
                        => $sectionContactUs->getId(),
                        'status'
                        => $sectionContactUs->isStatus(),

                        'email' =>   $emailContactUs  ?  ['id' =>  $emailContactUs->getId(), 'text' =>  $emailContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                        'description' => $descriptionContactUs ? ['id' => $descriptionContactUs->getId(), 'text' => $descriptionContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                        'findUs' => $findUs ? ['id' => $findUs->getId(), 'text' => $findUs->getDescription()] :  ['id' => 0, 'text' => ''],
                        'followUs' => $ldataFollow,
                        'contactUs' => $lcontactUS,
                        'contactUsPhone' => $lcontactUSNum,
                    ];
                }


                //Section confiance($typeconfiance)

                $confiance = [];
                $sectionConfiance =
                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeConfiance]);

                if (
                    $sectionConfiance
                ) {
                    $ldataConfiance = [];
                    $Lconfiance =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionConfiance, 'typeContenu' => $typeContenuGalerie]);
                    foreach ($Lconfiance as $confianceU) {
                        $object =
                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $confianceU]);
                        if ($object) {


                            $ldataConfiance[] = ['id' => $confianceU->getId(), 'nom' => $confianceU->getNomTemoin(), 'poste' => $confianceU->getPosteTemoin(), 'description' => $confianceU->getDescription(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' .  $object->getFilePathId() ?? ''];
                        }
                    }


                    $confiance =  [
                        'id'
                        => $sectionConfiance->getId(),
                        'status'
                        => $sectionConfiance->isStatus(),
                        'data' =>  $ldataConfiance
                    ];
                }

                $theme = $this->themeRead($vitrine->getId());
                return
                    new JsonResponse([
                        'id' =>  $vitrine->getId(),
                        'proprietaire'
                        =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getProprietaire()])->getNom(),
                        'createur'
                        =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getClientId()])->getNom(),
                        'title' => preg_replace(
                            "/\s+/",
                            "",
                            $vitrine->getNom()
                        ),
                        'theme' => $theme,
                        'typeVitrine' =>    $vitrine
                            ->getTypeVitrine()->getId(),
                        'url' =>
                        $this->shame .  $vitrine
                            ->getNom() . '.' . $vitrine->getTypeVitrine()->getLink(),

                        'description' => $vitrine->getDescription(),
                        'date' =>    $vitrine->getDateCreated()->format('d/m/Y'),
                        'typeVitrine' =>    $vitrine
                            ->getTypeVitrine()->getId(),
                        'metaDescription' =>    $vitrine
                            ->getMetaDescription(),
                        'metaKey' =>    $vitrine
                            ->getMetaKey(),
                        'titreSite' =>    $vitrine
                            ->getTitreSite(),
                        'logo'
                        =>    $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . (($VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['Vitrine' => $vitrine])) ? $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['Vitrine' => $vitrine])->getFilePathId() : ''),
                        'header' => $header,
                        'aboutUs' => $aboutUs,
                        'service' => $service,
                        'galerie' =>  $galerie,
                        'ereputation' => $EReputation,
                        'confiance' => $confiance,
                        'temoignage' =>  $temoignage,
                        'contactUs' =>  $contactUs,
                        'footer' => ['status' =>  $statusF, 'data' => $footer]

                    ], 201);
            }
        } else {
            return new JsonResponse([
                'message' => 'Vitrine inexistante'
            ], 400);
        }
    }


    /**
     * @Route("/vitrine/read", name="vitrineRead", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client qui cree la vitrine, 
     * 
     */
    public function vitrineRead(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        if (empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre keySecret '
            ], 400);
        }
        $clientUser = $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

        $lv  = [];

        $serializer = $this->get('serializer');
        if (($clientUser->getRole()->getId() == 1)) {
            $vitrines = $VitrineEntityManager->getRepository(Vitrine::class)->findAll();

            if (
                $vitrines
            ) {
                foreach ($vitrines  as $vitrine) {

                    if (
                        $vitrine
                    ) {
                        $typeHeader =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 1]);
                        $typeAboutUs =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 2]);
                        $typeService =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 3]);
                        $typeGalerie =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 4]);
                        $typeTemoignage =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 5]);
                        $typeFooter =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 6]);
                        $typeContactUs =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 7]);
                        $typeEReputation =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 8]);
                        $typeConfiance =
                            $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 9]);

                        $typeContenuTitle =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 1]);
                        $typeContenuDescription =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 2]);
                        $typeContenuLien =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 3]);
                        $typeContenuFavIcones =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 4]);
                        $typeContenuImage =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 5]);
                        $typeContenuPhone =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 7]);
                        $typeContenuEmail =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 6]);
                        $typeContenuEmailSubS =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 13]);
                        $typeContenuService =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 9]);
                        $typeContenuGalerie =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 10]);
                        $typeFindUs =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 11]);
                        $typeFollowUs =
                            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 12]);



                        if (
                            $vitrine
                            ->getTypeVitrine()->getId() == 2
                        ) {


                            $sectionContactUs =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeContactUs]);
                            $contactUs =  [];


                            if (
                                $sectionContactUs
                            ) {
                                // $titreHeader =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuTitle]);
                                $descriptionContactUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuDescription]);
                                $emailContactUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmail]);
                                $findUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeFindUs]);
                                $followUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeFollowUs]);
                                $ldataFollow = [];
                                foreach ($followUs as $Fu) {

                                    $ldataFollow[]
                                        = ['id' => $Fu->getId(), 'text' => $Fu->getDescription()];
                                }
                                $contactUsL =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmailSubS]);
                                $lcontactUS = [];
                                foreach ($contactUsL as $Cu) {

                                    $lcontactUS[]
                                        = ['id' => $Cu->getId(), 'text' => $Cu->getDescription(), 'url' => $Cu->getLien()];
                                }


                                $contactUsNum =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuPhone]);
                                $lcontactUSNum = [];
                                foreach ($contactUsNum as $Cu) {

                                    $lcontactUSNum[]
                                        = ['id' => $Cu->getId(), 'text' => $Cu->getDescription()];
                                }
                                $contactUs =  [
                                    'id'
                                    => $sectionContactUs->getId(),
                                    'status'
                                    => $sectionContactUs->isStatus(),

                                    'email' =>   $emailContactUs  ?  ['id' =>  $emailContactUs->getId(), 'text' =>  $emailContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'description' => $descriptionContactUs ? ['id' => $descriptionContactUs->getId(), 'text' => $descriptionContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'findUs' => $findUs ? ['id' => $findUs->getId(), 'text' => $findUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'followUs' => $ldataFollow,
                                    'contactUs' => $lcontactUS,  'contactUsPhone' => $lcontactUSNum,
                                ];
                            }

                            $EReputation = [];

                            $sectionEReputation =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeEReputation]);

                            if (
                                $sectionEReputation
                            ) {
                                // $titreGalerie =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuTitle]);
                                // $descriptionService =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuDescription]);
                                $ldataER = [];
                                $LEReputation =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuLien]);

                                $sociaux = [
                                    'Facebook', 'Twitter',  'Instagram',
                                    'Snapchat', 'Telegram',  'WhatsAPP',
                                    'StackExchange', 'TikTok'
                                ];

                                foreach ($LEReputation as $EReputationU) {
                                    foreach ($sociaux as $s) {


                                        if ($EReputationU->getDescription() == $s) {

                                            $ldataER[] =  ['id' => $EReputationU->getId(), 'title' => $s, 'lien' => $EReputationU->getLien()];
                                        }
                                    }
                                }


                                $EReputation =  [
                                    'id'
                                    => $sectionEReputation->getId(),
                                    'status'
                                    => $sectionEReputation->isStatus(),
                                    'data' =>  $ldataER
                                ];
                            }

                            array_push($lv, [
                                'id' =>  $vitrine->getId(),
                                'proprietaire'
                                =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getProprietaire()])->getNom(),
                                'createur'
                                =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getClientId()])->getNom(),
                                'title' => preg_replace("/\s+/", "",                                 $vitrine->getNom()),
                                'description' => $vitrine->getDescription(),
                                'date' =>    $vitrine->getDateCreated()->format('d/m/Y'),
                                'url' =>  $this->shame . $vitrine->getNom() . $vitrine
                                    ->getTypeVitrine()->getLink(),
                                'typeVitrine' =>    $vitrine
                                    ->getTypeVitrine()->getId(),
                                'status' =>    $vitrine
                                    ->getStatus() == 0 ? "Desactive" : "Active",
                                'metaDescription' =>    $vitrine
                                    ->getMetaDescription(),
                                'contactUs' =>  $contactUs,

                                'ereputation' => $EReputation,

                            ]);
                        } else if (
                            $vitrine
                            ->getTypeVitrine()->getId() == 1
                        ) {
                            $header = [];
                            $statusH = false;
                            $idA = 0;
                            $aboutUs = [];
                            $statusA = false;
                            $idH = 0;
                            $service = [];
                            $statusS = false;
                            $idS = 0;
                            $galerie = [];
                            $statusG = false;
                            $idG = 0;
                            $temoignage = [];
                            $statusT = false;
                            $idT = 0;
                            $footer = [];
                            $photo = [];

                            $statusF = false;
                            $idF = 0;
                            $contactUs = [];
                            $statusCont = false;
                            $idCont = 0;
                            $lsetions =
                                $VitrineEntityManager->getRepository(Section::class)->findBy(['vitrine' => $vitrine]);

                            foreach ($lsetions as $section) {
                                $contenus =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $section]);

                                if ($contenus) {

                                    if ($section->getTypeSection()->getId() == 6) {
                                        $statusF = $section->isStatus();
                                        $idF = $section->getId();

                                        foreach ($contenus as $contenu) {
                                            array_push($footer, ['id' => $contenu->getId(), 'text' => $contenu->getDescription()]);
                                        }
                                    }
                                }
                            }
                            //Section Header(typeHeader)


                            $sectionHeader =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeHeader]);

                            if (
                                $sectionHeader
                            ) {
                                $titreHeader =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuTitle]);
                                $descriptionHeader =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuDescription]);
                                $imageHeader =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuImage]);
                                $FaIcon =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuFavIcones]);

                                $favIcon = '';
                                $Faobject =
                                    $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $FaIcon]);

                                if ($Faobject) {
                                    $favIcon = ['id' => $FaIcon->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $Faobject->getFilePathId()];
                                }
                                $image = '';
                                $object =
                                    $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $imageHeader]);

                                if ($object) {
                                    $image = ['id' => $imageHeader->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                                }

                                $header =  [
                                    'status'
                                    => $sectionHeader->isStatus(),
                                    'image' => $image,
                                    'favIcon' => $favIcon,
                                    'title' =>  $titreHeader ?  ['id' => $titreHeader->getId(), 'text' => $titreHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'description' => $descriptionHeader ? ['id' => $descriptionHeader->getId(), 'text' => $descriptionHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                                ];
                            }
                            //Section AboutUs($typeAboutUs)


                            $sectionAboutUs =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeAboutUs]);

                            if (
                                $sectionAboutUs
                            ) {
                                // $titreHeader =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionAboutUs, 'typeContenu' => $typeContenuTitle]);
                                $descriptionAboutUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionAboutUs, 'typeContenu' => $typeContenuDescription]);
                                $aboutUs =  [
                                    'id'
                                    => $sectionAboutUs->getId(),   'status'
                                    => $sectionAboutUs->isStatus(),

                                    // 'title' =>  $titreHeader ?  ['id' => $titreHeader->getId(), 'text' => $titreHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'description' => $descriptionAboutUs ? ['id' => $descriptionAboutUs->getId(), 'text' => $descriptionAboutUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                ];
                            }
                            //Section Service($typeService)


                            $sectionService =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeService]);

                            if (
                                $sectionService
                            ) {
                                $imageS =
                                    null;

                                $titreService =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuTitle]);
                                $descriptionService =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuDescription]);
                                $ldataService = [];
                                $LService =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionService, 'typeContenu' => $typeContenuService]);
                                foreach ($LService as $serviceU) {
                                    $object =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $serviceU]);
                                    if ($object) {


                                        $ldataService[] = ['id' => $serviceU->getId(), 'title' => $serviceU->getTitleImage(), 'description' => $serviceU->getDescriptionImage(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId() ?? ''];
                                    }
                                }
                                $imageService =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuImage]);

                                $object =
                                    $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $imageService]);

                                if ($object) {
                                    $imageS = ['id' => $imageService->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                                }
                                $service =  [
                                    'id'
                                    => $sectionService->getId(),    'status'
                                    => $sectionService->isStatus(),
                                    'image' => $imageS,

                                    'title' =>  $titreService ?  ['id' => $titreService->getId(), 'text' => $titreService->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'description' => $descriptionService ? ['id' => $descriptionService->getId(), 'text' => $descriptionService->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'data' =>  $ldataService
                                ];
                            }

                            //Section galerie($typeGalerie)


                            $sectionGalerie =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeGalerie]);

                            if (
                                $sectionGalerie
                            ) {
                                $titreGalerie =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuTitle]);
                                $descriptionGalerie =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuDescription]);
                                $ldataGalerie = [];
                                $LGalerie =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuGalerie]);
                                foreach ($LGalerie as $galerieU) {
                                    $object =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $galerieU]);
                                    if ($object) {


                                        $ldataGalerie[] = ['id' => $galerieU->getId(), 'title' => $galerieU->getTitleImage(), 'description' => $galerieU->getDescriptionImage(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId() ?? ''];
                                    }
                                }


                                $bGalerie =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuImage]);

                                $object =
                                    $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $bGalerie]);

                                if ($object) {
                                    $image = ['id' => $imageHeader->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                                }


                                $galerie =  [
                                    'id'
                                    => $sectionGalerie->getId(),  'status'
                                    => $sectionGalerie->isStatus(),

                                    'title' =>  $titreGalerie ?  ['id' => $titreGalerie->getId(), 'text' => $titreGalerie->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'description' => $descriptionGalerie ? ['id' => $descriptionGalerie->getId(), 'text' => $descriptionGalerie->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'data' =>  $ldataGalerie,
                                    'image' => $image
                                ];
                            }


                            //Section temoignage($typeTemoignage)
                            $temoignage = [];
                            $sectionTemoignage =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeTemoignage]);
                            if (
                                $sectionTemoignage
                            ) {
                                // $titreGalerie =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuTitle]);
                                // $descriptionService =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuDescription]);
                                $ldataTemoi = [];
                                $LTemoignage =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuGalerie]);
                                foreach ($LTemoignage as $temoignageU) {
                                    $object =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $temoignageU]);
                                    if ($object) {


                                        $ldataTemoi[] = ['id' => $temoignageU->getId(), 'nom' => $temoignageU->getNomTemoin(), 'poste' => $temoignageU->getPosteTemoin(), 'description' => $temoignageU->getDescription(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId() ?? ''];
                                    }
                                }


                                $temoignage =  [
                                    'id'
                                    => $sectionTemoignage->getId(),
                                    'status'
                                    => $sectionTemoignage->isStatus(),
                                    'data' =>  $ldataTemoi
                                ];
                            }

                            //Section EReputation($typeEReputation)

                            $EReputation = [];

                            $sectionEReputation =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeEReputation]);

                            if (
                                $sectionEReputation
                            ) {
                                // $titreGalerie =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuTitle]);
                                // $descriptionService =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuDescription]);
                                $ldataER = [];
                                $LEReputation =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuLien]);

                                $sociaux = [
                                    'Facebook', 'Twitter',  'Instagram',
                                    'Snapchat', 'Telegram',  'WhatsAPP',
                                    'StackExchange', 'TikTok'
                                ];

                                foreach ($LEReputation as $EReputationU) {
                                    foreach ($sociaux as $s) {


                                        if ($EReputationU->getDescription() == $s) {

                                            $ldataER[] =  ['id' => $EReputationU->getId(), 'title' => $s, 'lien' => $EReputationU->getLien()];
                                        }
                                    }
                                }


                                $EReputation =  [
                                    'id'
                                    => $sectionEReputation->getId(),
                                    'status'
                                    => $sectionEReputation->isStatus(),
                                    'data' =>  $ldataER
                                ];
                            }




                            //Section ContactUs($typeContactUs)


                            $sectionContactUs =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeContactUs]);
                            $contactUs =  [];

                            if (
                                $sectionContactUs
                            ) {
                                // $titreHeader =
                                //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuTitle]);
                                $descriptionContactUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuDescription]);
                                $emailContactUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmail]);
                                $findUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeFindUs]);
                                $followUs =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeFollowUs]);
                                $ldataFollow = [];
                                foreach ($followUs as $Fu) {

                                    $ldataFollow[]
                                        = ['id' => $Fu->getId(), 'text' => $Fu->getDescription()];
                                }
                                $contactUsL =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmailSubS]);
                                $lcontactUS = [];
                                foreach ($contactUsL as $Cu) {

                                    $lcontactUS[]
                                        = ['id' => $Cu->getId(), 'text' => $Cu->getDescription(), 'url' => $Cu->getLien()];
                                }


                                $contactUsNum =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuPhone]);
                                $lcontactUSNum = [];
                                foreach ($contactUsNum as $Cu) {

                                    $lcontactUSNum[]
                                        = ['id' => $Cu->getId(), 'text' => $Cu->getDescription()];
                                }
                                $contactUs =  [
                                    'id'
                                    => $sectionContactUs->getId(),
                                    'status'
                                    => $sectionContactUs->isStatus(),

                                    'email' =>   $emailContactUs  ?  ['id' =>  $emailContactUs->getId(), 'text' =>  $emailContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'description' => $descriptionContactUs ? ['id' => $descriptionContactUs->getId(), 'text' => $descriptionContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'findUs' => $findUs ? ['id' => $findUs->getId(), 'text' => $findUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                    'followUs' => $ldataFollow,
                                    'contactUs' => $lcontactUS,  'contactUsPhone' => $lcontactUSNum,
                                ];
                            }

                            //Section confiance($typeconfiance)

                            $confiance = [];
                            $sectionConfiance =
                                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeConfiance]);

                            if (
                                $sectionConfiance
                            ) {
                                $ldataConfiance = [];
                                $Lconfiance =
                                    $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionConfiance, 'typeContenu' => $typeContenuGalerie]);
                                foreach ($Lconfiance as $confianceU) {
                                    $object =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $confianceU]);
                                    if ($object) {


                                        $ldataConfiance[] = ['id' => $confianceU->getId(), 'nom' => $confianceU->getNomTemoin(), 'poste' => $confianceU->getPosteTemoin(), 'description' => $confianceU->getDescription(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' .  $object->getFilePathId() ?? ''];
                                    }
                                }


                                $confiance =  [
                                    'id'
                                    => $sectionConfiance->getId(),
                                    'status'
                                    => $sectionConfiance->isStatus(),
                                    'data' =>  $ldataConfiance
                                ];
                            }
                            $theme = $this->themeRead($vitrine->getId());
                            array_push($lv, [
                                'id' =>  $vitrine->getId(),

                                'proprietaire'
                                =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getProprietaire()])->getNom(),
                                'createur'
                                =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getClientId()])->getNom(),
                                'title' => preg_replace(
                                    "/\s+/",
                                    "",
                                    $vitrine->getNom()
                                ),
                                'theme' =>  $theme,
                                'url' =>  $this->shame .  $vitrine
                                    ->getNom() . '.' . $vitrine->getTypeVitrine()->getLink(),
                                'description' => $vitrine->getDescription(),
                                'date' =>    $vitrine->getDateCreated()->format('d/m/Y'),
                                'typeVitrine' =>    $vitrine
                                    ->getTypeVitrine()->getId(), 'status' =>    $vitrine
                                    ->getStatus() == 0 ? "Desactive" : "Active",
                                'metaDescription' =>    $vitrine
                                    ->getMetaDescription(),
                                'metaKey' =>    $vitrine
                                    ->getMetaKey(),
                                'titreSite' =>    $vitrine
                                    ->getTitreSite(),
                                'logo' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . (($VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['Vitrine' => $vitrine])) ? $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['Vitrine' => $vitrine])->getFilePathId() : ''),

                                'header' => $header,
                                'aboutUs' => $aboutUs,
                                'service' => $service,
                                'galerie' =>  $galerie,
                                'ereputation' => $EReputation,
                                'confiance' => $confiance,

                                'temoignage' =>  $temoignage,
                                'contactUs' =>  $contactUs,
                                'footer' => ['status' =>  $statusF, 'data' => $footer]


                            ]);
                        }
                    } else {
                        return new JsonResponse([
                            'message' => 'Action impossible '
                        ], 400);
                    }
                }

                $lvf = $serializer->serialize(array_reverse($lv), 'json');

                return
                    new JsonResponse(
                        [
                            'data'
                            =>
                            JSON_DECODE($lvf)
                        ],
                        201
                    );
            } else {
                return new JsonResponse([
                    'message' => 'Aucune vitrine ',
                    'data'
                    =>
                    []
                ], 200);
            }
        } else {

            $possible = false;
            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '26') {
                    $possible = true;
                }
            }


            if ($possible) {
                $vitrines = $VitrineEntityManager->getRepository(Vitrine::class)->findBy(['proprietaire' => $clientUser->getId()]);

                if (
                    $vitrines
                ) {
                    foreach ($vitrines  as $vitrine) {

                        if (
                            $vitrine
                        ) {
                            $typeHeader =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 1]);
                            $typeAboutUs =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 2]);
                            $typeService =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 3]);
                            $typeGalerie =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 4]);
                            $typeTemoignage =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 5]);
                            $typeFooter =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 6]);
                            $typeContactUs =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 7]);
                            $typeEReputation =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 8]);
                            $typeConfiance =
                                $VitrineEntityManager->getRepository(TypeSection::class)->findOneBy(['id' => 9]);

                            $typeContenuTitle =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 1]);
                            $typeContenuDescription =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 2]);
                            $typeContenuLien =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 3]);
                            $typeContenuFavIcones =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 4]);
                            $typeContenuImage =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 5]);
                            $typeContenuPhone =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 7]);
                            $typeContenuEmail =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 6]);
                            $typeContenuEmailSubS =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 13]);
                            $typeContenuService =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 9]);
                            $typeContenuGalerie =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 10]);
                            $typeFindUs =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 11]);
                            $typeFollowUs =
                                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 12]);



                            if (
                                $vitrine
                                ->getTypeVitrine()->getId() == 2
                            ) {


                                $sectionContactUs =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeContactUs]);
                                $contactUs =  [];


                                if (
                                    $sectionContactUs
                                ) {
                                    // $titreHeader =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuTitle]);
                                    $descriptionContactUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuDescription]);
                                    $emailContactUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmail]);
                                    $findUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeFindUs]);
                                    $followUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeFollowUs]);
                                    $ldataFollow = [];
                                    foreach ($followUs as $Fu) {

                                        $ldataFollow[]
                                            = ['id' => $Fu->getId(), 'text' => $Fu->getDescription()];
                                    }
                                    $contactUsL =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmailSubS]);
                                    $lcontactUS = [];
                                    foreach ($contactUsL as $Cu) {

                                        $lcontactUS[]
                                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription(), 'url' => $Cu->getLien()];
                                    }


                                    $contactUsNum =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuPhone]);
                                    $lcontactUSNum = [];
                                    foreach ($contactUsNum as $Cu) {

                                        $lcontactUSNum[]
                                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription()];
                                    }
                                    $contactUs =  [
                                        'id'
                                        => $sectionContactUs->getId(),
                                        'status'
                                        => $sectionContactUs->isStatus(),

                                        'email' =>   $emailContactUs  ?  ['id' =>  $emailContactUs->getId(), 'text' =>  $emailContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'description' => $descriptionContactUs ? ['id' => $descriptionContactUs->getId(), 'text' => $descriptionContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'findUs' => $findUs ? ['id' => $findUs->getId(), 'text' => $findUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'followUs' => $ldataFollow,
                                        'contactUs' => $lcontactUS,  'contactUsPhone' => $lcontactUSNum,
                                    ];
                                }

                                $EReputation = [];

                                $sectionEReputation =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeEReputation]);

                                if (
                                    $sectionEReputation
                                ) {
                                    // $titreGalerie =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuTitle]);
                                    // $descriptionService =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuDescription]);
                                    $ldataER = [];
                                    $LEReputation =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuLien]);

                                    $sociaux = [
                                        'Facebook', 'Twitter',  'Instagram',
                                        'Snapchat', 'Telegram',  'WhatsAPP',
                                        'StackExchange', 'TikTok'
                                    ];

                                    foreach ($LEReputation as $EReputationU) {
                                        foreach ($sociaux as $s) {


                                            if ($EReputationU->getDescription() == $s) {

                                                $ldataER[] =  ['id' => $EReputationU->getId(), 'title' => $s, 'lien' => $EReputationU->getLien()];
                                            }
                                        }
                                    }


                                    $EReputation =  [
                                        'id'
                                        => $sectionEReputation->getId(),
                                        'status'
                                        => $sectionEReputation->isStatus(),
                                        'data' =>  $ldataER
                                    ];
                                }

                                array_push($lv, [
                                    'id' =>  $vitrine->getId(),
                                    'proprietaire'
                                    =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getProprietaire()])->getNom(),
                                    'createur'
                                    =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getClientId()])->getNom(),
                                    'title' => preg_replace("/\s+/", "",                                 $vitrine->getNom()),
                                    'description' => $vitrine->getDescription(),
                                    'date' =>    $vitrine->getDateCreated()->format('d/m/Y'),
                                    'url' =>  $this->shame . $vitrine->getNom() . $vitrine
                                        ->getTypeVitrine()->getLink(),
                                    'typeVitrine' =>    $vitrine
                                        ->getTypeVitrine()->getId(),
                                    'status' =>    $vitrine
                                        ->getStatus() == 0 ? "Desactive" : "Active",
                                    'metaDescription' =>    $vitrine
                                        ->getMetaDescription(),
                                    'contactUs' =>  $contactUs,

                                    'ereputation' => $EReputation,

                                ]);
                            } else if (
                                $vitrine
                                ->getTypeVitrine()->getId() == 1
                            ) {
                                $header = [];
                                $statusH = false;
                                $idA = 0;
                                $aboutUs = [];
                                $statusA = false;
                                $idH = 0;
                                $service = [];
                                $statusS = false;
                                $idS = 0;
                                $galerie = [];
                                $statusG = false;
                                $idG = 0;
                                $temoignage = [];
                                $statusT = false;
                                $idT = 0;
                                $footer = [];
                                $photo = [];

                                $statusF = false;
                                $idF = 0;
                                $contactUs = [];
                                $statusCont = false;
                                $idCont = 0;
                                $lsetions =
                                    $VitrineEntityManager->getRepository(Section::class)->findBy(['vitrine' => $vitrine]);

                                foreach ($lsetions as $section) {
                                    $contenus =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $section]);

                                    if ($contenus) {

                                        if ($section->getTypeSection()->getId() == 6) {
                                            $statusF = $section->isStatus();
                                            $idF = $section->getId();

                                            foreach ($contenus as $contenu) {
                                                array_push($footer, ['id' => $contenu->getId(), 'text' => $contenu->getDescription()]);
                                            }
                                        }
                                    }
                                }
                                //Section Header(typeHeader)


                                $sectionHeader =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeHeader]);

                                if (
                                    $sectionHeader
                                ) {
                                    $titreHeader =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuTitle]);
                                    $descriptionHeader =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuDescription]);
                                    $imageHeader =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuImage]);
                                    $FaIcon =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionHeader, 'typeContenu' => $typeContenuFavIcones]);

                                    $favIcon = '';
                                    $Faobject =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $FaIcon]);

                                    if ($Faobject) {
                                        $favIcon = ['id' => $FaIcon->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $Faobject->getFilePathId()];
                                    }
                                    $image = '';
                                    $object =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $imageHeader]);

                                    if ($object) {
                                        $image = ['id' => $imageHeader->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                                    }

                                    $header =  [
                                        'status'
                                        => $sectionHeader->isStatus(),
                                        'image' => $image,
                                        'favIcon' => $favIcon,
                                        'title' =>  $titreHeader ?  ['id' => $titreHeader->getId(), 'text' => $titreHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'description' => $descriptionHeader ? ['id' => $descriptionHeader->getId(), 'text' => $descriptionHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                                    ];
                                }
                                //Section AboutUs($typeAboutUs)


                                $sectionAboutUs =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeAboutUs]);

                                if (
                                    $sectionAboutUs
                                ) {
                                    // $titreHeader =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionAboutUs, 'typeContenu' => $typeContenuTitle]);
                                    $descriptionAboutUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionAboutUs, 'typeContenu' => $typeContenuDescription]);
                                    $aboutUs =  [
                                        'id'
                                        => $sectionAboutUs->getId(),   'status'
                                        => $sectionAboutUs->isStatus(),

                                        // 'title' =>  $titreHeader ?  ['id' => $titreHeader->getId(), 'text' => $titreHeader->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'description' => $descriptionAboutUs ? ['id' => $descriptionAboutUs->getId(), 'text' => $descriptionAboutUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                    ];
                                }
                                //Section Service($typeService)


                                $sectionService =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeService]);

                                if (
                                    $sectionService
                                ) {
                                    $imageS =
                                        null;

                                    $titreService =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuTitle]);
                                    $descriptionService =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuDescription]);
                                    $ldataService = [];
                                    $LService =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionService, 'typeContenu' => $typeContenuService]);
                                    foreach ($LService as $serviceU) {
                                        $object =
                                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $serviceU]);
                                        if ($object) {


                                            $ldataService[] = ['id' => $serviceU->getId(), 'title' => $serviceU->getTitleImage(), 'description' => $serviceU->getDescriptionImage(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId() ?? ''];
                                        }
                                    }
                                    $imageService =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionService, 'typeContenu' => $typeContenuImage]);

                                    $object =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $imageService]);

                                    if ($object) {
                                        $imageS = ['id' => $imageService->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                                    }
                                    $service =  [
                                        'id'
                                        => $sectionService->getId(),    'status'
                                        => $sectionService->isStatus(),
                                        'image' => $imageS,

                                        'title' =>  $titreService ?  ['id' => $titreService->getId(), 'text' => $titreService->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'description' => $descriptionService ? ['id' => $descriptionService->getId(), 'text' => $descriptionService->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'data' =>  $ldataService
                                    ];
                                }

                                //Section galerie($typeGalerie)


                                $sectionGalerie =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeGalerie]);

                                if (
                                    $sectionGalerie
                                ) {
                                    $titreGalerie =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuTitle]);
                                    $descriptionGalerie =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuDescription]);
                                    $ldataGalerie = [];
                                    $LGalerie =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuGalerie]);
                                    foreach ($LGalerie as $galerieU) {
                                        $object =
                                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $galerieU]);
                                        if ($object) {


                                            $ldataGalerie[] = ['id' => $galerieU->getId(), 'title' => $galerieU->getTitleImage(), 'description' => $galerieU->getDescriptionImage(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId() ?? ''];
                                        }
                                    }


                                    $bGalerie =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionGalerie, 'typeContenu' => $typeContenuImage]);

                                    $object =
                                        $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $bGalerie]);

                                    if ($object) {
                                        $image = ['id' => $imageHeader->getId(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId()];
                                    }


                                    $galerie =  [
                                        'id'
                                        => $sectionGalerie->getId(),  'status'
                                        => $sectionGalerie->isStatus(),

                                        'title' =>  $titreGalerie ?  ['id' => $titreGalerie->getId(), 'text' => $titreGalerie->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'description' => $descriptionGalerie ? ['id' => $descriptionGalerie->getId(), 'text' => $descriptionGalerie->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'data' =>  $ldataGalerie,
                                        'image' => $image
                                    ];
                                }


                                //Section temoignage($typeTemoignage)
                                $temoignage = [];
                                $sectionTemoignage =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeTemoignage]);
                                if (
                                    $sectionTemoignage
                                ) {
                                    // $titreGalerie =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuTitle]);
                                    // $descriptionService =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuDescription]);
                                    $ldataTemoi = [];
                                    $LTemoignage =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionTemoignage, 'typeContenu' => $typeContenuGalerie]);
                                    foreach ($LTemoignage as $temoignageU) {
                                        $object =
                                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $temoignageU]);
                                        if ($object) {


                                            $ldataTemoi[] = ['id' => $temoignageU->getId(), 'nom' => $temoignageU->getNomTemoin(), 'poste' => $temoignageU->getPosteTemoin(), 'description' => $temoignageU->getDescription(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . $object->getFilePathId() ?? ''];
                                        }
                                    }


                                    $temoignage =  [
                                        'id'
                                        => $sectionTemoignage->getId(),
                                        'status'
                                        => $sectionTemoignage->isStatus(),
                                        'data' =>  $ldataTemoi
                                    ];
                                }

                                //Section EReputation($typeEReputation)

                                $EReputation = [];

                                $sectionEReputation =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeEReputation]);

                                if (
                                    $sectionEReputation
                                ) {
                                    // $titreGalerie =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuTitle]);
                                    // $descriptionService =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuDescription]);
                                    $ldataER = [];
                                    $LEReputation =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionEReputation, 'typeContenu' => $typeContenuLien]);

                                    $sociaux = [
                                        'Facebook', 'Twitter',  'Instagram',
                                        'Snapchat', 'Telegram',  'WhatsAPP',
                                        'StackExchange', 'TikTok'
                                    ];

                                    foreach ($LEReputation as $EReputationU) {
                                        foreach ($sociaux as $s) {


                                            if ($EReputationU->getDescription() == $s) {

                                                $ldataER[] =  ['id' => $EReputationU->getId(), 'title' => $s, 'lien' => $EReputationU->getLien()];
                                            }
                                        }
                                    }


                                    $EReputation =  [
                                        'id'
                                        => $sectionEReputation->getId(),
                                        'status'
                                        => $sectionEReputation->isStatus(),
                                        'data' =>  $ldataER
                                    ];
                                }




                                //Section ContactUs($typeContactUs)


                                $sectionContactUs =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeContactUs]);
                                $contactUs =  [];

                                if (
                                    $sectionContactUs
                                ) {
                                    // $titreHeader =
                                    //     $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuTitle]);
                                    $descriptionContactUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuDescription]);
                                    $emailContactUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmail]);
                                    $findUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['section' => $sectionContactUs, 'typeContenu' => $typeFindUs]);
                                    $followUs =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeFollowUs]);
                                    $ldataFollow = [];
                                    foreach ($followUs as $Fu) {

                                        $ldataFollow[]
                                            = ['id' => $Fu->getId(), 'text' => $Fu->getDescription()];
                                    }
                                    $contactUsL =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuEmailSubS]);
                                    $lcontactUS = [];
                                    foreach ($contactUsL as $Cu) {

                                        $lcontactUS[]
                                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription(), 'url' => $Cu->getLien()];
                                    }


                                    $contactUsNum =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionContactUs, 'typeContenu' => $typeContenuPhone]);
                                    $lcontactUSNum = [];
                                    foreach ($contactUsNum as $Cu) {

                                        $lcontactUSNum[]
                                            = ['id' => $Cu->getId(), 'text' => $Cu->getDescription()];
                                    }
                                    $contactUs =  [
                                        'id'
                                        => $sectionContactUs->getId(),
                                        'status'
                                        => $sectionContactUs->isStatus(),

                                        'email' =>   $emailContactUs  ?  ['id' =>  $emailContactUs->getId(), 'text' =>  $emailContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'description' => $descriptionContactUs ? ['id' => $descriptionContactUs->getId(), 'text' => $descriptionContactUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'findUs' => $findUs ? ['id' => $findUs->getId(), 'text' => $findUs->getDescription()] :  ['id' => 0, 'text' => ''],
                                        'followUs' => $ldataFollow,
                                        'contactUs' => $lcontactUS,  'contactUsPhone' => $lcontactUSNum,
                                    ];
                                }

                                //Section confiance($typeconfiance)

                                $confiance = [];
                                $sectionConfiance =
                                    $VitrineEntityManager->getRepository(Section::class)->findOneBy(['vitrine' => $vitrine, 'typeSection' => $typeConfiance]);

                                if (
                                    $sectionConfiance
                                ) {
                                    $ldataConfiance = [];
                                    $Lconfiance =
                                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $sectionConfiance, 'typeContenu' => $typeContenuGalerie]);
                                    foreach ($Lconfiance as $confianceU) {
                                        $object =
                                            $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['contenu' => $confianceU]);
                                        if ($object) {


                                            $ldataConfiance[] = ['id' => $confianceU->getId(), 'nom' => $confianceU->getNomTemoin(), 'poste' => $confianceU->getPosteTemoin(), 'description' => $confianceU->getDescription(), 'url' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' .  $object->getFilePathId() ?? ''];
                                        }
                                    }


                                    $confiance =  [
                                        'id'
                                        => $sectionConfiance->getId(),
                                        'status'
                                        => $sectionConfiance->isStatus(),
                                        'data' =>  $ldataConfiance
                                    ];
                                }
                                $theme = $this->themeRead($vitrine->getId());
                                array_push($lv, [
                                    'id' =>  $vitrine->getId(),

                                    'proprietaire'
                                    =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getProprietaire()])->getNom(),
                                    'createur'
                                    =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getClientId()])->getNom(),
                                    'title' => preg_replace(
                                        "/\s+/",
                                        "",
                                        $vitrine->getNom()
                                    ),
                                    'theme' => $theme,
                                    'url' =>  $this->shame .  $vitrine
                                        ->getNom() . '.' . $vitrine->getTypeVitrine()->getLink(),
                                    'description' => $vitrine->getDescription(),
                                    'date' =>    $vitrine->getDateCreated()->format('d/m/Y'),
                                    'typeVitrine' =>    $vitrine
                                        ->getTypeVitrine()->getId(),
                                    'metaDescription' =>    $vitrine
                                        ->getMetaDescription(),
                                    'metaKey' =>    $vitrine
                                        ->getMetaKey(),
                                    'titreSite' =>    $vitrine
                                        ->getTitreSite(),
                                    'logo' =>  $this->shame . $_SERVER['SERVER_NAME'] . '/images/vitrine/' . (($VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['Vitrine' => $vitrine])) ? $VitrineEntityManager->getRepository(VitrineObject::class)->findOneBy(['Vitrine' => $vitrine])->getFilePathId() : ''),

                                    'header' => $header,
                                    'aboutUs' => $aboutUs,
                                    'service' => $service,
                                    'galerie' =>  $galerie,
                                    'ereputation' => $EReputation,
                                    'confiance' => $confiance,
                                    'status' =>    $vitrine
                                        ->getStatus() == 0 ? "Desactive" : "Active",
                                    'temoignage' =>  $temoignage,
                                    'contactUs' =>  $contactUs,
                                    'footer' => ['status' =>  $statusF, 'data' => $footer]


                                ]);
                            }
                        } else {
                            return new JsonResponse([
                                'message' => 'Action impossible '
                            ], 400);
                        }
                    }

                    $lvf = $serializer->serialize(array_reverse($lv), 'json');

                    return
                        new JsonResponse(
                            [
                                'data'
                                =>
                                JSON_DECODE($lvf)
                            ],
                            201
                        );
                } else {
                    return new JsonResponse([
                        'message' => 'Aucune vitrine ',
                        'data'
                        =>
                        []
                    ], 200);
                }
            } else {
                return new JsonResponse([
                    'message' => 'Action impossible '
                ], 203);
            }
        }
    }

    /**
     * @Route("/vitrine/update", name="vitrineUpdate", methods={"POST"})
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
    public function vitrineUpdate(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        $possible = false;

        if (empty($data['idVitrine'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre vitrine '
            ], 400);
        }

        $serializer = $this->get('serializer');


        $idVitrine = $data['idVitrine'];

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['id' => $idVitrine]);


        if ($vitrine) {
            $clientUser =
                $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '27') {
                    $possible = true;
                }
            }


            if ($possible) {
                if (!empty($data['nom'])) {
                    $vitrine->setNom($this->myFunction->removeSpace($data['nom']));
                }
                if (!empty($data['description'])) {
                    $vitrine->setDescription($data['description']);
                }
                if (!empty($data['metaDescription'])) {
                    $vitrine->setMetaDescription($data['metaDescription']);
                }
                if (!empty($data['metaKey'])) {
                    $vitrine->setMetaKey($data['metaKey']);
                }

                $VitrineEntityManager->persist($vitrine);
                $VitrineEntityManager->flush();
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
     * @Route("/section/update", name="sectionUpdate", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenur keySecret du client qui cree la vitrine, 
     * 
     */
    public function setionUpdate(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        if (empty($data['idSection'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre vitrine '
            ], 400);
        }



        $serializer = $this->get('serializer');
        $possible = false;

        $section =
            $VitrineEntityManager->getRepository(Section::class)->findOneBy(['id' => $data['idSection']]);
        if ($section) {

            $clientUser =
                $this->em->getRepository(Client::class)->findOneBy(['id' =>
                $section->getVitrine()->getProprietaire()]);

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '27') {
                    $possible = true;
                }
            }


            if ($possible) {
                if ($section->getTypeSection()->getId() != 1 && $section->getTypeSection()->getId() != 6) {
                    $section->setStatus(!$section->isStatus());
                    $VitrineEntityManager->persist($section);
                    $VitrineEntityManager->flush();
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
        } else {
            return new JsonResponse([
                'message' => 'Action impossible '
            ], 400);
        }
    }


    /**
     * @Route("/section/modifyContains", name="sectionmodifyContains", methods={"POST"})
     * @param Request $request    concerne le modification type Text de contenu
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir  action = 1 =>ajout d'un element a la section,   action = 2 =>retirer un element a la section, action = 3 =>modification d'un element a la section
     * 
     keySecret du client,  il faut  ( action ==1) ?  (idSection concerne,description du contenu a ajouter) :(action ==2) ?(idSection concerne,idContenu a retirer)  : (description du contenu a changer, idContenu)
     * 
     */
    public function sectionmodifyContains(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        if (empty($data['action'])/*  || empty($data['keySecret']) */) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre action '
            ], 400);
        }
        $serializer = $this->get('serializer');
        $action = $data['action'];
        $using = false;
        $message = 'Operation failed';
        //Ajouter un nouveau contenu a la section
        if ($action == 1) {
            if (empty($data['typeContenu'])/*  || empty($data['keySecret']) */) {

                return new JsonResponse([
                    'message' => 'Mauvais parametre de requete veuillez preciser votre action '
                ], 400);
            }
            $typeContenu  =
                $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => $data['typeContenu']]);
            $section =
                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['id' => $data['idSection']]);
            $contenu = new Contenu();
            $contenu->setSection($section);
            $contenu->setDescription($data['description'] ?? '');

            $contenu->setTitleImage($data['titleImg'] ?? '');
            $contenu->setDescriptionImage($data['descriptionImg'] ??
                '');
            $contenu->setNomTemoin($data['nomTemoin'] ?? '');
            $contenu->setPosteTemoin($data['posteTemoin'] ?? '');
            $contenu->setTitleContenu($data['titleContenu'] ?? '');
            $contenu->setDescriptionContenu($data['descriptionContenu'] ?? '');
            $contenu->setLien($data['lienContenu'] ?? '');
            $contenu->setTypeContenu($typeContenu);

            $VitrineEntityManager->persist(
                $contenu
            );
            $VitrineEntityManager->flush();
            $using = true;
            $message = "Contenu ajoute avec success";
        }
        //retirer un contenu a la section
        else  if ($action == 2) {
            $contenu =
                $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['id' => $data['idContenu']]);

            $contenu->setSection(null);
            // $contenu->setDescription($data['description'] ?? '');

            $VitrineEntityManager->persist(
                $contenu
            );
            $VitrineEntityManager->flush();
            $using = true;
            $message = "Contenu retire avec success";
        }
        //modifier le contenu de la section
        else  if ($action == 3) {
            $contenu =
                $VitrineEntityManager->getRepository(Contenu::class)->findOneBy(['id' => $data['idContenu']]);
            $contenu->setDescription($data['description'] ??    ($contenu->getDescription() ?? ''));
            $contenu->setLien($data['lien'] ??    ($contenu->getLien() ?? ''));

            $contenu->setTitleImage($data['titleImg'] ?? $contenu->getTitleImage() ?? '');
            $contenu->setDescriptionImage($data['descriptionImg'] ?? $contenu->getDescriptionImage() ?? '');
            $contenu->setNomTemoin($data['nomTemoin'] ?? $contenu->getNomTemoin() ?? '');
            $contenu->setPosteTemoin($data['posteTemoin'] ?? $contenu->getPosteTemoin() ?? '');
            $contenu->setTitleContenu($data['titleContenu'] ?? $contenu->getTitleContenu() ?? '');
            $contenu->setDescriptionContenu($data['descriptionContenu'] ?? $contenu->getDescriptionContenu() ?? '');
            // $contenu->setLien($data['lienContenu'] ?? $contenu->getLien() ?? '');
            $VitrineEntityManager->persist(
                $contenu
            );
            $VitrineEntityManager->flush();
            $using = true;
            $message = "Contenu modifie avec success";
        }


        if ($using) {
            return
                new JsonResponse([
                    'message'
                    =>      $message,

                ], 201);
        } else {
            return new JsonResponse([
                'message' => 'Action impossible '
            ], 400);
        }
    }



    /**
     * @Route("/section/upload", name="uploadSectionObject", methods={"POST"})
     * @param Request $request    concerne le modification type Text de contenu
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir  action = 1 =>ajout d'un element au contenu de la section,   action = 2 =>update d'un element a la section,  
     keySecret du client,  il faut  ( action ==1) ?  (idSection concerne,et le file a ajouter a ajouter) :(idSectionObject,element a modifier) 
     * 
     */
    public function uploadSectionObject(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        if (empty($data['action'])/*  || empty($data['keySecret']) */) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre action '
            ], 400);
        }
        $serializer = $this->get('serializer');
        $action = $data['action'];
        $using = false;
        $message = 'Operation failed';
        //Ajouter un nouveau contenu a la section
        if ($action == 1) {
            $section =
                $VitrineEntityManager->getRepository(Section::class)->findOneBy(['id' => $data['idSection']]);
            $contenu = new Contenu();
            $contenu->setSection($section);
            $contenu->setDescription($data['description'] ?? '');
            $contenu->setTitleImage($data['descriptionImg'] ?? '');
            $contenu->setDescriptionImage($data['descriptionImg'] ??
                '');
            $contenu->setNomTemoin($data['nomTemoin'] ?? '');
            $contenu->setPosteTemoin($data['posteTemoin'] ?? '');

            $VitrineEntityManager->persist(
                $contenu
            );
            $VitrineEntityManager->flush();
            $using = true;
            $message = "Contenu ajoute avec success";
        }

        return
            new JsonResponse([
                'message'
                =>      $message,
                'id' => $contenu->getId()

            ], 201);
    }





    /**
     * @Route("/section/upload/add", name="addServiceGalerieTemoi", methods={"POST"})
     * @param Request $request    concerne le modification type Text de contenu
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir  idSection, type = 1 =>typeService,     2 =>typeGalerie /image/temoignage 
    
     */
    public function addServiceGalerieTemoi(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();
        $typeContenuService =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 9]);
        $typeContenuGalerie =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 10]);
        if (empty($data['type'])/*  || empty($data['keySecret']) */) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre type '
            ], 400);
        }

        $serializer = $this->get('serializer');
        $type = $data['type'];

        $message = 'Operation failed';
        //Ajouter un nouveau contenu a la section service

        $section =
            $VitrineEntityManager->getRepository(Section::class)->findOneBy(['id' => $data['idSection']]);
        $contenu = new Contenu();
        $contenu->setSection($section);
        $contenu->setDescription($data['description'] ?? '');

        $contenu->setTitleImage($data['titleImg'] ?? '');
        $contenu->setDescriptionImage($data['descriptionImg'] ??
            '');
        $contenu->setNomTemoin($data['nomTemoin'] ?? '');
        $contenu->setPosteTemoin($data['posteTemoin'] ?? '');
        $contenu->setTypeContenu($type == 1 ? $typeContenuService :  $typeContenuGalerie);

        $VitrineEntityManager->persist(
            $contenu
        );
        $VitrineEntityManager->flush();
        $using = true;
        $message = "Contenu ajoute avec success";


        return
            new JsonResponse([
                'message'
                =>      $message,
                'id' => $contenu->getId()

            ], 201);
    }



    /**
     * @Route("/section/contact/add", name="addFollowEmail", methods={"POST"})
     * @param Request $request    concerne le modification type Text de contenu
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir  idSection, type = 1 =>typeFollowUs,     2 =>typeContenuEmail 
    
     */
    public function addFollowEmail(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();
        $typeFollowUs =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 12]);
        $typeContenuEmailSubS =
            $VitrineEntityManager->getRepository(TypeContenu::class)->findOneBy(['id' => 13]);
        if (empty($data['type'])/*  || empty($data['keySecret']) */) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre type '
            ], 400);
        }

        $serializer = $this->get('serializer');
        $type = $data['type'];

        $message = 'Operation failed';
        //Ajouter un nouveau contenu a la section ContactUs

        $section =
            $VitrineEntityManager->getRepository(Section::class)->findOneBy(['id' => $data['idSection']]);
        $contenu = new Contenu();
        $contenu->setSection($section);
        $contenu->setDescription($data['description'] ?? '');

        $contenu->setLien($data['lien'] ?? '');

        $contenu->setTypeContenu($type == 1 ? $typeFollowUs : $typeContenuEmailSubS);

        $VitrineEntityManager->persist(
            $contenu
        );
        $VitrineEntityManager->flush();
        $using = true;
        $message = "Contenu ajoute avec success";


        return
            new JsonResponse([
                'message'
                =>      $message,
                'id' => $contenu->getId()

            ], 201);
    }



    /**
     * @Route("/vitrine/template/exist", name="templateExist", methods={"POST"})
     * @param Request $request    concerne le modification type Text de contenu
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir  le nom de la vitrine
    
     */
    public function templateExist(Request $request)
    {
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();


        if (empty($data['nom'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre nom '
            ], 400);
        }

        $serializer = $this->get('serializer');


        $nom = $data['nom'];

        $theme = $VitrineEntityManager->getRepository(Theme::class)->findOneBy(['libelle' => $nom]);

        return
            new JsonResponse([

                'status' => ($theme == null) ? true : false

            ], 201);
    }



    /**
     * @Route("/vitrine/template/new", name="templatenew", methods={"POST"})
     * @param Request $request    concerne le modification type Text de contenu
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     * @throws \Exception
     * @param array $data doit contenir  le nom de la vitrine
    
     */
    public function templatenew(Request $request, SluggerInterface $slugger)
    {
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        // dd($request );
        $nom =  $request->get('nom');
        $logo = $request->files->get('logo');
        $file = $request->files->get('file');


        $serializer = $this->get('serializer');


        // var_dump($nom);
        // var_dump($logo);
        // var_dump($file);

        $themeF = $VitrineEntityManager->getRepository(Theme::class)->findOneBy(['libelle' => $nom]);
        if (!$themeF) {
            $theme = new Theme();

            if (
                $logo && $file
            ) {
                $originalFilename = pathinfo($logo->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $nom . '.' . $logo->guessExtension();


                $originalFilenameData = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // this is needed to safely include the file name as part of the URL
                $safeFilenameData = $slugger->slug($originalFilenameData);
                $newFilenameData = $nom . '.' . $file->guessExtension();

                // Move the file to the directory where brochures are stored
                try {
                    $logo->move(
                        $this->getParameter('theme_object'),
                        $newFilename
                    );
                    $file->move(
                        $this->getParameter('DevooVitrineStandard'),
                        $newFilenameData
                    );
                    $myfile = fopen("/var/www/DevooVitrineStandard/shel.sh", 'w+');
                    fwrite(
                        $myfile,
                        "echo moua@ | sudo -S unzip /var/www/DevooVitrineStandard/" . $newFilenameData  . " -d /var/www/DevooVitrineStandard/ "
                    );
                    fclose($myfile);
                    $output =  shell_exec('sh /var/www/DevooVitrineStandard/shel.sh' . ' 2>&1');


                    $myfile1 = fopen("/var/www/DevooVitrineStandard/shel.sh", 'w+');
                    fwrite(
                        $myfile1,
                        "echo moua@ | sudo -S rm /var/www/DevooVitrineStandard/" . $newFilenameData
                    );
                    fclose($myfile1);
                    $output1 =  shell_exec('sh /var/www/DevooVitrineStandard/shel.sh' . ' 2>&1');


                    $myfile2 = fopen("/var/www/DevooVitrineStandard/shel.sh", 'w+');
                    fwrite(
                        $myfile2,
                        "echo moua@ | sudo -S rm /var/www/DevooVitrineStandard/" . $newFilenameData
                    );
                    fclose($myfile2);
                    $output2 =  shell_exec('echo moua@ | sudo -S rm /var/www/DevooVitrineStandard/shel.sh' . ' 2>&1');



                    $theme->setLibelle($nom);
                    $theme->setImage($newFilename);
                    $VitrineEntityManager->persist($theme);
                    $VitrineEntityManager->flush();
                    return
                        new JsonResponse(
                            [

                                'status' =>  true,
                                // 'output'
                                // =>  $output,    'output1'
                                // =>  $output1,  'output2'
                                // =>  $output2

                            ],
                            201
                        );
                } catch (Exception $e) {
                    return
                        new JsonResponse([

                            'status' =>   false,
                            'message' =>   'Une erreur est survenue' . $e

                        ], 203);
                }

                // updates the 'brochureFilename' property to store the PDF file name
                // instead of its contents

            } else {
                return
                    new JsonResponse([

                        'status' =>   false,
                        'message' =>   'Une erreur est survenue'

                    ], 203);
            }
        } else {
            return
                new JsonResponse([
                    'status' =>   false,
                    'message' =>   'Ce nom de template est deja utilise'

                ], 203);
        }
    }
    public function themeRead($idVitrine)
    {
        $listTheme = [];
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['id' => $idVitrine]);
        $allTheme = $VitrineEntityManager->getRepository(Theme::class)->findAll();



        $serializer = $this->get('serializer');

        foreach ($allTheme   as $theme) {



            array_push($listTheme, [
                'id' =>  $theme->getId(),
                'label' =>  $theme->getLibelle(),
                'image' => $this->shame  . $_SERVER['SERVER_NAME'] . '/images/theme/' . $theme->getImage(),
                'status' => ($theme->getId() == $vitrine->getTheme()->getId())
            ]);
        }

        return $listTheme;
    }


    /**
     * @Route("/vitrine/theme/update", name="vitrineUpdateTheme", methods={"POST"})
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
    public function vitrineUpdateTheme(Request $request)
    {
        $UserEntityManager = $this->doctrine->getManager('User');
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');
        $data = $request->toArray();

        $possible = false;

        if (empty($data['idVitrine']) || empty($data['theme']) || empty($data['keySecret'])) {

            return new JsonResponse([
                'message' => 'Mauvais parametre de requete veuillez preciser votre vitrine et le theme '
            ], 400);
        }

        $serializer = $this->get('serializer');


        $idVitrine = $data['idVitrine'];

        $vitrine = $VitrineEntityManager->getRepository(Vitrine::class)->findOneBy(['id' => $idVitrine]);


        if ($vitrine) {
            $clientUser =
                $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

            foreach ($clientUser->getRole()->getListRoleFonctions()  as $lr) {
                if ($lr->isStatus() && $lr->getFonction()->getId() == '27') {
                    $possible = true;
                }
            }


            if ($possible) {

                if (!empty($data['theme'])) {
                    $theme =
                        $VitrineEntityManager->getRepository(Theme::class)->findOneBy(['id' => $data['theme']]);

                    // $a =  $vitrine->getNom() . '.pubx.cm';
                    // $b = $vitrine->getTheme()->getLibelle();
                    //                     $c=    $theme->getLibelle();
                    $this->updateTheme(
                        $vitrine->getNom() . '.pubx.cm',
                        $vitrine->getTheme()->getLibelle(),
                        $theme->getLibelle()
                    );
                    $vitrine->setTheme($theme);
                }


                $VitrineEntityManager->persist($vitrine);
                $VitrineEntityManager->flush();
                $myfile = fopen("/var/www/DevooVitrineStandard/shel.sh", 'w+');
                fwrite(
                    $myfile,
                    "echo moua@ | sudo -S nginx service restart;"
                );

                fclose($myfile);





                $output2 =  shell_exec('sh /var/www/DevooVitrineStandard/shel.sh' . ' 2>&1');
                return
                    new JsonResponse([
                        'message'
                        =>      'success'


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
     * @Route("/ptest", name="ptest", methods={"GET" })
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function ptest(Request $request)
    {

        $pub = 'fdsdf.pubx.cm';
        $previousTheme = '';
        $newTheme  = '';
        // $Handle = fopen('test.txt', 'w+');
        // $bodytag =
        //     file_get_contents('test.txt', true);
        // $myfile = fopen("sitemap_vitrine.xml", 'w+') or die("Unable to open file!");
        // var_dump(str_replace('w', 'replace', $bodytag));
        // fwrite($Handle, str_replace('w', 'replace', $bodytag));



        // $prev =   '/var/www/DevooVitrineStandard/' . $previousTheme . '/dist';
        // $new =   '/var/www/DevooVitrineStandard/' . $newTheme . '/dist';
        // $file   = '/etc/nginx/conf.d/' . $pub . '.conf';
        // $prev =   '/var/www/DevooVitrineStandard/dist';
        // $new =   '/var/www/DevooVitrineStandard/distNew';
        // $file   = '/etc/nginx/conf.d/' . $pub . '.conf';
        // file_put_contents($file, str_replace($prev, $new, file_get_contents($file)));
        // $bodytag =
        //     file_get_contents('test.txt', true);

        // var_dump($bodytag);
        //     $as
        //     = str_replace('f', 'replace', $bodytag);
        //     var_dump($as);
        // $bodytag =   file_put_contents(
        //     'test.txt',
        //     str_replace('f', 'replace', $bodytag)

        // );

        // fclose($Handle);
        $myfile = fopen("/var/www/DevooVitrineStandard/shel.sh", 'w+');
        fwrite(
            $myfile,
            "echo moua@ | sudo -S unzip /var/www/DevooVitrineStandard/leadmark0d.zip" . " -d /var/www/DevooVitrineStandard/  "
        );

        fclose($myfile);

        $output1 =  shell_exec('sh /var/www/DevooVitrineStandard/shel.sh' . ' 2>&1');


        return
            new JsonResponse(
                [

                    'status' =>  true,
                    'output1'
                    =>  $output1

                ],
                201
            );
        return
            new JsonResponse([
                'h' => 'r'
            ], 201);
    }

    public function updateTheme($pub, $previousTheme, $newTheme)
    {

        $prev =   $previousTheme;
        $new =   $newTheme;
        $file   = '/etc/nginx/conf.d/' . $pub . '.conf';

        file_put_contents($file, str_replace($prev, $new, file_get_contents($file)));
    }



    /**
     * @Route("/vitrine/deletteall", name="deletteVitrineAAl", methods={"POST"})
     * @param Request $request
     * @return JsonResponse
     * @throws ClientExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws ServerExceptionInterface
     * @throws TransportExceptionInterface
     */
    public function deletteVitrineAAl(Request $request)
    {
        $zoneId = 4785;
        $data = $request->toArray();
        $VitrineEntityManager = $this->doctrine->getManager('Vitrine');

        $vitrines = $VitrineEntityManager->getRepository(Vitrine::class)->findAll();
        $clientUser =
            $this->em->getRepository(Client::class)->findOneBy(['keySecret' => $data['keySecret']]);

 
            foreach ($vitrines  as $vitrine) {


                if (
                    $vitrine
                    ->getId() != 24
                ) {

                    $responseL = $this->client->request(
                        'POST',
                        'https://api.camoo.hosting/v1/auth',
                        [
                            "json" => [
                                "email" => "gihaireslontsi@gmail.com",
                                "password" => "Gessiia@2022"

                            ],
                            'headers' => ['Content-Type' => 'application/json']
                        ]
                    ); //code... 
                    if ($responseL->toArray()['result']['access_token'] != null) {
                        $response = $this->client->request(
                            'POST',
                            'https://api.camoo.hosting/v1/dns/delete-record',
                            [
                                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $responseL->toArray()['result']['access_token']],
                                "json" => [
                                    "zone_id" =>
                                    $zoneId,
                                    "record_id" =>  $vitrine->getRecordId1()

                                ]
                            ]
                        );
                        $response0 = $this->client->request(
                            'POST',
                            'https://api.camoo.hosting/v1/dns/delete-record',
                            [
                                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer ' . $responseL->toArray()['result']['access_token']],
                                "json" => [
                                    "zone_id" =>
                                    $zoneId,
                                    "record_id" =>  $vitrine->getRecordId2()

                                ]
                            ]
                        ); //code... 
                        $newFilenameData =  '/etc/nginx/conf.d/' . ($vitrine->getTypeVitrine()->getId() == 1 ?  $vitrine->getNom() . '.' . $vitrine->getTypeVitrine()->getLink()  : $vitrine->getNom()  . $vitrine->getTypeVitrine()->getLink()) . '.conf';
                        $myfile1 = fopen("/var/www/DevooVitrineStandard/shel1.sh", 'w+');
                        fwrite(
                            $myfile1,
                            "echo moua@ | sudo -S rm " . $newFilenameData
                        );
                        fclose($myfile1);
                        $output1 =  shell_exec('sh /var/www/DevooVitrineStandard/shel1.sh' . ' 2>&1');
                        $output2 =   exec('sh /var/www/DevooVitrineStandard/shel1.sh');
                        $output3 =  shell_exec('sudo -S rm ' . $newFilenameData);
                        $output4 =   exec('sudo -S rm ' . $newFilenameData);
                    } else {
                        return
                            new JsonResponse([

                                'message' => 'Erreur',
                            ], 400);
                    }
                }
                return
                    new JsonResponse([
                        'status' => true,

                    ], 201);
            }
        }
    }
 
