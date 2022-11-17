<?php

namespace App\Entity\Licence;

use App\Repository\Licence\ListSmsManipuleRepository;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use DateTime;

/**
 * @ORM\Entity(repositoryClass=ListSmsManipuleRepository::class)
   @ApiResource(
 *   itemOperations={"get", "patch", "delete"},
 *   normalizationContext={"groups"={"lsm:read"}},
 *   denormalizationContext={"groups"={"lsm:write"}}
 * ),
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *    "clientId": "exact",
 * "emetteur.facture.clientId": "exact",
 *   "recepteur.facture.clientId": "exact",

 *     }
 * )
 * 
 * */
class ListSmsManipule
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     *  @Groups({"lsm:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"lsm:read","lsm:write"})
     * 
     */
    private $clientId;

    /**
     * @ORM\ManyToOne(targetEntity=ListSMSAchette::class, inversedBy="listSmsManipules")
     * @Groups({"lsm:read","lsm:write"})
     * 
     */
    private $emetteur;

    /**
     * @ORM\ManyToOne(targetEntity=ListSMSAchette::class, inversedBy="listSmsManipules")
     * @Groups({"lsm:read","lsm:write"})
     * 
     */
    private $recepteur;

    /**
     * @ORM\Column(type="datetime")
     *  @Groups({"lsm:read"})
     */
    private $dateCreated;

    /**
     * @Groups({"lsm:read","lsm:write"})
     * @ORM\Column(type="integer")
     */
    private $quantite;

    /**
     * @ORM\Column(type="boolean")
     *  @Groups({"lsm:read"})
     */
    private $status;

    /**
     * @ORM\ManyToOne(targetEntity=TypeManipulation::class, inversedBy="listSmsManipules")
     * @Groups({"lsm:read","lsm:write"})
     */
    private $typemanipulation;

    public function __construct()
    {

        $this->dateCreated = new \DateTime();
        $this->status = false;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getEmetteur(): ?ListSMSAchette
    {
        return $this->emetteur;
    }

    public function setEmetteur(?ListSMSAchette $emetteur): self
    {
        $this->emetteur = $emetteur;

        return $this;
    }

    public function getRecepteur(): ?ListSMSAchette
    {
        return $this->recepteur;
    }

    public function setRecepteur(?ListSMSAchette $recepteur): self
    {
        $this->recepteur = $recepteur;

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

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(int $quantite): self
    {
        $this->quantite = $quantite;

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

    public function getTypemanipulation(): ?TypeManipulation
    {
        return $this->typemanipulation;
    }

    public function setTypemanipulation(?TypeManipulation $typemanipulation): self
    {
        $this->typemanipulation = $typemanipulation;

        return $this;
    }
}
