<?php

namespace App\Entity\Bulk;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\Bulk\GroupeContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Symfony\Component\Serializer\Annotation\Groups;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\BooleanFilter;

/**
 * @ORM\Entity(repositoryClass=GroupeContactRepository::class)
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:groupecontact"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:groupecontact"
 *                  }
 *              },
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          }
 *     },
 *     itemOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "patch"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "delete"={
 *              "security"="is_granted('ROLE_SMS_DELETE')"
 *          },
 *     }
 * ) 
 */
class GroupeContact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:groupecontact","create:groupecontact","read:contact"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:groupecontact","create:groupecontact","read:contact"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:groupecontact","create:groupecontact","read:contact"})
     * 
     */
    private $description;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"read:groupecontact","create:groupecontact","read:contact"})
     */
    private $status = true;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateCreated;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *  @Groups({"read:groupecontact","create:groupecontact","read:contact"})
     */
    private $clientId;

    /**
     * @ORM\OneToMany(targetEntity=ListGroupeContact::class, mappedBy="groupeContact") 
     */
    private $listGroupeContacts;

    /**
     * @ORM\OneToMany(targetEntity=ListSmsLotsEnvoye::class, mappedBy="groupeContact") 
     */
    private $listSmsLotsEnvoyes;

    public function __construct()
    {

        $this->dateCreated = new \DateTime();
        $this->contacts = new ArrayCollection();
        $this->listGroupeContacts = new ArrayCollection();
        $this->listSmsLotsEnvoyes = new ArrayCollection();
    }
    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    /**
     * @return Collection<int, ListGroupeContact>
     */
    public function getListGroupeContacts(): Collection
    {
        return $this->listGroupeContacts;
    }

    public function addListGroupeContact(ListGroupeContact $listGroupeContact): self
    {
        if (!$this->listGroupeContacts->contains($listGroupeContact)) {
            $this->listGroupeContacts[] = $listGroupeContact;
            $listGroupeContact->setGroupeContact($this);
        }

        return $this;
    }

    public function removeListGroupeContact(ListGroupeContact $listGroupeContact): self
    {
        if ($this->listGroupeContacts->removeElement($listGroupeContact)) {
            // set the owning side to null (unless already changed)
            if ($listGroupeContact->getGroupeContact() === $this) {
                $listGroupeContact->setGroupeContact(null);
            }
        }

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
            $listSmsLotsEnvoye->setGroupeContact($this);
        }

        return $this;
    }

    public function removeListSmsLotsEnvoye(ListSmsLotsEnvoye $listSmsLotsEnvoye): self
    {
        if ($this->listSmsLotsEnvoyes->removeElement($listSmsLotsEnvoye)) {
            // set the owning side to null (unless already changed)
            if ($listSmsLotsEnvoye->getGroupeContact() === $this) {
                $listSmsLotsEnvoye->setGroupeContact(null);
            }
        }

        return $this;
    }
}
