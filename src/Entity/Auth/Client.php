<?php

namespace App\Entity\Auth;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Auth\Roles;
use App\Entity\Auth\TypeClient;
use App\Repository\Auth\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Controller\Api\Auth\MeController;
use App\Controller\Api\Auth\ClientCreateController;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:client"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *           
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:client"
 *                  }
 *              },
 *              "controller"=ClientCreateController::class,
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          }
 *     },
 *     itemOperations={
 *       
 *      "patch"={
 * "denormalization_context"={
 *                  "groups"={
 *                      "update:client"
 *                  }
 *              },
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "get"={
 *         
 *          }
 *     }
 * ),
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "phone": "exact",
 *    
 * }
 *)
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 */
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:client"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     * @Groups({"read:client", "create:client", "update:client"})
     */
    private $phone;

    /**
     * @ORM\Column(type="json", nullable=true)
     * @Groups({"read:client", "create:client", "update:client"})
     */
    private $roles = ["ROLE_CLIENT"];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups({"create:client"})
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     *  @Groups({"read:client"})
     */
    private $keySecret = "";

    /** 
     * @Groups({"create:client","read:client", "update:client"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomEntreprise;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nomFacturation;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $numTva;

    /**
     * @Groups({"create:client","read:client", "update:client"})
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @Groups({"create:client","read:client", "update:client"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @Groups({"create:client","read:client", "update:client"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @Groups({"create:client","read:client", "update:client"})
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codeParrain;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bureauPhone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $fax;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:client", "create:client", "update:client"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read:client"})
     */
    private $dateCreated;

    /**
     * @ORM\ManyToOne(targetEntity=TypeClient::class, inversedBy="clients")
     */
    private $typeCLient;

    /**
     * @Groups({"read:client","create:client", "update:client"})
     * 
     * @ORM\ManyToOne(targetEntity=Roles::class, inversedBy="clients")
     */
    private $role;

    /**
     * @Groups({"read:client", "create:client", "update:client"})
     * 
     * @ORM\Column(type="date", nullable=true)
     */
    private $birthday;


    /**
     * @Groups({"read:client", "create:client", "update:client"})
     * 
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $posteSocial;

    /**
     * @Groups({"read:client", "create:client", "update:client"})
     * 
     * @ORM\ManyToOne(targetEntity=ClientObject::class, inversedBy="clients")
     */
    private $profile;

    /**
     * @Groups({"read:client", "create:client", "update:client"})
     * 
     * @ORM\ManyToOne(targetEntity=ClientObject::class, inversedBy="clients")
     */
    private $couverture;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rPhone1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rPhone2;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rEmail1;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rEmail2;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $codeRecup;




    public function __construct()
    {
        $this->dateCreated = new \DateTime();
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getCodeParrain(): ?string
    {
        return $this->codeParrain;
    }

    public function setCodeParrain(string $codeParrain): self
    {
        $this->codeParrain = $codeParrain;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->phone;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->phone;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_CLIENT';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getKeySecret(): ?string
    {
        return $this->keySecret;
    }

    public function setKeySecret(string $keySecret): self
    {
        $this->keySecret = $keySecret;

        return $this;
    }

    public function getNomEntreprise(): ?string
    {
        return $this->nomEntreprise;
    }

    public function setNomEntreprise(?string $nomEntreprise): self
    {
        $this->nomEntreprise = $nomEntreprise;

        return $this;
    }

    public function getNomFacturation(): ?string
    {
        return $this->nomFacturation;
    }

    public function setNomFacturation(?string $nomFacturation): self
    {
        $this->nomFacturation = $nomFacturation;

        return $this;
    }

    public function getNumTva(): ?float
    {
        return $this->numTva;
    }

    public function setNumTva(?float $numTva): self
    {
        $this->numTva = $numTva;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getBureauPhone(): ?string
    {
        return $this->bureauPhone;
    }

    public function setBureauPhone(?string $bureauPhone): self
    {
        $this->bureauPhone = $bureauPhone;

        return $this;
    }

    public function getFax(): ?string
    {
        return $this->fax;
    }

    public function setFax(?string $fax): self
    {
        $this->fax = $fax;

        return $this;
    }

    public function getStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateCreated(): ?\DateTimeInterface
    {
        return $this->dateCreated;
    }

    public function setDateCreated(\DateTimeInterface $dateCreated): self
    {
        $this->dateCreated = $dateCreated;

        return $this;
    }

    public function getTypeCLient(): ?TypeClient
    {
        return $this->typeCLient;
    }

    public function setTypeCLient(?TypeClient $typeCLient): self
    {
        $this->typeCLient = $typeCLient;

        return $this;
    }

    public function getRole(): ?Roles
    {
        return $this->role;
    }

    public function setRole(?Roles $role): self
    {
        $this->role = $role;

        return $this;
    }

    public function getBirthday(): ?\DateTimeInterface
    {
        return $this->birthday;
    }

    public function setBirthday(?\DateTimeInterface $birthday): self
    {
        $this->birthday = $birthday;

        return $this;
    }

    public function getPosteSocial(): ?string
    {
        return $this->posteSocial;
    }

    public function setPosteSocial(?string $posteSocial): self
    {
        $this->posteSocial = $posteSocial;

        return $this;
    }

    public function getProfile(): ?ClientObject
    {
        return $this->profile;
    }

    public function setProfile(?ClientObject $profile): self
    {
        $this->profile = $profile;

        return $this;
    }

    public function getCouverture(): ?ClientObject
    {
        return $this->couverture;
    }

    public function setCouverture(?ClientObject $couverture): self
    {
        $this->couverture = $couverture;

        return $this;
    }

    public function getRPhone1(): ?string
    {
        return $this->rPhone1;
    }

    public function setRPhone1(?string $rPhone1): self
    {
        $this->rPhone1 = $rPhone1;

        return $this;
    }

    public function getRPhone2(): ?string
    {
        return $this->rPhone2;
    }

    public function setRPhone2(string $rPhone2): self
    {
        $this->rPhone2 = $rPhone2;

        return $this;
    }

    public function getREmail1(): ?string
    {
        return $this->rEmail1;
    }

    public function setREmail(?string $rEmail1): self
    {
        $this->rEmail1 = $rEmail1;

        return $this;
    }

    public function getREmail2(): ?string
    {
        return $this->rEmail2;
    }

    public function setREmail2(string $rEmail2): self
    {
        $this->rEmail2 = $rEmail2;

        return $this;
    }

    public function getCodeRecup(): ?int
    {
        return $this->codeRecup;
    }

    public function setCodeRecup(?int $codeRecup): self
    {
        $this->codeRecup = $codeRecup;

        return $this;
    }
}
