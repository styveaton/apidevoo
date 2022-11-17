<?php

namespace App\Entity\Bulk;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Bulk\SmsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:sms"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:sms"
 *                  }
 *              },
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          },
 *          "patch"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          },
 *          "delete"={
 *              "security"="is_granted('ROLE_SMS_DELETE')"
 *          },
 *     }
 * ),
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *       
 *      "clientId": "exact"
 *     }
 * )
 * @ORM\Entity(repositoryClass=SmsRepository::class)
 *
 */
class Sms
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:listsmslotsenvoye","read:sms"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"read:sms", "create:sms","read:listsmslotsenvoye"})
     */
    private $message;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:sms", "create:sms"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"read:sms", "create:sms"})
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read:sms", "create:sms","read:routelse"})
     */
    private $clientId;

    /**
     * @ORM\ManyToOne(targetEntity=SenderId::class, inversedBy="sms")
     * @Groups({"read:sms", "create:sms","read:listsmslotsenvoye"})
     */
    private $senderId;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsLotsEnvoye::class, mappedBy="sms", cascade={"persist"})
     * @Groups({"create:sms","read:sms"})
     */
    private $listSmsLotsEnvoyes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $projetId;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsExcepte::class, mappedBy="sms")
     */
    private $listSmsExceptes;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read:sms", "create:sms"})
     */
    private $statusSpecial =true;

    public function __construct()
    {
        $this->dateCreated = new \DateTime();
        $this->listSmsLotsEnvoyes = new ArrayCollection();
        $this->listSmsExceptes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

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

    public function getClientId(): ?int
    {
        return $this->clientId;
    }

    public function setClientId(?int $clientId): self
    {
        $this->clientId = $clientId;

        return $this;
    }

    public function getSenderId(): ?SenderId
    {
        return $this->senderId;
    }

    public function setSenderId(?SenderId $senderId): self
    {
        $this->senderId = $senderId;

        return $this;
    }

    /**
     * @return Collection<int, ListSmsLotsEnvoye>
     */
    public function getListSmsLotsEnvoyes(): Collection
    {
        return $this->listSmsLotsEnvoyes;
    }

    public function addListSmsLotsEnvoye(ListSmsLotsEnvoye $listSmsLotsEnvoye): self
    {
        if (!$this->listSmsLotsEnvoyes->contains($listSmsLotsEnvoye)) {
            $this->listSmsLotsEnvoyes[] = $listSmsLotsEnvoye;
            $listSmsLotsEnvoye->setSms($this);
        }

        return $this;
    }

    public function removeListSmsLotsEnvoye(ListSmsLotsEnvoye $listSmsLotsEnvoye): self
    {
        if ($this->listSmsLotsEnvoyes->removeElement($listSmsLotsEnvoye)) {
            // set the owning side to null (unless already changed)
            if ($listSmsLotsEnvoye->getSms() === $this) {
                $listSmsLotsEnvoye->setSms(null);
            }
        }

        return $this;
    }

    public function getProjetId(): ?int
    {
        return $this->projetId;
    }

    public function setProjetId(int $projetId): self
    {
        $this->projetId = $projetId;

        return $this;
    }

    /**
     * @return Collection<int, ListSmsExcepte>
     */
    public function getListSmsExceptes(): Collection
    {
        return $this->listSmsExceptes;
    }

    public function addListSmsExcepte(ListSmsExcepte $listSmsExcepte): self
    {
        if (!$this->listSmsExceptes->contains($listSmsExcepte)) {
            $this->listSmsExceptes[] = $listSmsExcepte;
            $listSmsExcepte->setSms($this);
        }

        return $this;
    }

    public function removeListSmsExcepte(ListSmsExcepte $listSmsExcepte): self
    {
        if ($this->listSmsExceptes->removeElement($listSmsExcepte)) {
            // set the owning side to null (unless already changed)
            if ($listSmsExcepte->getSms() === $this) {
                $listSmsExcepte->setSms(null);
            }
        }

        return $this;
    }

    public function isStatusSpecial(): ?bool
    {
        return $this->statusSpecial;
    }

    public function setStatusSpecial(?bool $statusSpecial): self
    {
        $this->statusSpecial = $statusSpecial;

        return $this;
    }
}
