// namespace App\Controller\Route;

// use Doctrine\ORM\EntityManagerInterface;
// use Symfony\Contracts\HttpClient\HttpClientInterface;
// use App\Entity\Route\SenderApi;

// class SenderApiController
// {
// private $clientWeb;

// private $em;

// public function __invoke(SenderApi $data, HttpClientInterface $clientWeb, EntityManagerInterface $em)
// {
// $toExternal = new InternalToExternalApi($clientWeb, $em);


// $data1 = [
// 'keySecret' => '$2y$10$G20/uvYCIg4ta0Qg0lOTx.dxkNukwj8eYLROooww1SLC6whNeVSIm',
// 'senderId' => 'DEVOO',

// 'message' => 'message',
// 'destinataire' => [690863838, 690863838]
// ];
// $data->setApiLink('1234');
// return $data;
// }
// }