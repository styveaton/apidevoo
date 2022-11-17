<?php


namespace App\Entity\Auth;

use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken;
use App\Repository\Auth\JwtRefreshTokenRepository;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * This class extends Gesdinet\JWTRefreshTokenBundle\Entity\RefreshToken to have another table name.
 *
 * @ORM\Table("jwt_refresh_token")
 * @ORM\Entity(repositoryClass=JwtRefreshTokenRepository::class)
 */
class JwtRefreshToken implements RefreshTokenInterface
{

    /**
     * @var int|string|null
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=128)
     */
    protected $refreshToken;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=255)
     */
    protected $username;

    /**
     * @var DateTimeInterface|null
     * @ORM\Column(type="datetime", length=180, unique=true)
     */
    protected $valid;

    /**
     * Creates a new model instance based on the provided details.
     */
    public static function createForUserWithTtl(string $refreshToken, UserInterface $user, int $ttl): RefreshTokenInterface
    {
        // TODO: Implement createForUserWithTtl() method.
        $valid = new \DateTime();
        $valid->modify('+'.$ttl.' seconds');

        $model = new static();
        $model->setRefreshToken($refreshToken);
        $model->setUsername(method_exists($user, 'getUserIdentifier') ? $user->getUserIdentifier() : $user->getUsername());
        $model->setValid($valid);

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        // TODO: Implement getId() method.
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setRefreshToken($refreshToken = null)
    {
        // TODO: Implement setRefreshToken() method.
        if (null === $refreshToken || '' === $refreshToken) {
            trigger_deprecation('gesdinet/jwt-refresh-token-bundle', '1.0', 'Passing an empty token to %s() to automatically generate a token is deprecated.', __METHOD__);

            $refreshToken = bin2hex(random_bytes(64));
        }

        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRefreshToken()
    {
        // TODO: Implement getRefreshToken() method.
        return $this->refreshToken;
    }

    /**
     * {@inheritdoc}
     */
    public function setValid($valid)
    {
        // TODO: Implement setValid() method.
        $this->valid = $valid;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getValid()
    {
        // TODO: Implement getValid() method.
        return $this->valid;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsername($username)
    {
        // TODO: Implement setUsername() method.
        $this->username = $username;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        // TODO: Implement getUsername() method.
        return $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid()
    {
        // TODO: Implement isValid() method.
        return null !== $this->valid && $this->valid >= new \DateTime();
    }

    /**
     * @return string Refresh Token
     */
    public function __toString()
    {
        // TODO: Implement __toString() method.
        return $this->getRefreshToken() ?: '';
    }
}