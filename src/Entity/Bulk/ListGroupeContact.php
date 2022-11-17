<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\ListGroupeContactRepository;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Core\Annotation\ApiResource;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ListGroupeContactRepository::class) 
 * @ApiResource(
 * normalizationContext={
 *          "groups"={
 *              "read:listgroupecontact"
 *          }
 *     },
 *  collectionOperations={
 *          "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_FULLY')"
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:listgroupecontact"
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
 * 
 * 
 ),
 * 
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *      "contact": "exact",
 *      "groupeContact": "exact"
 *     }
 * )
 */
class ListGroupeContact
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Contact::class, inversedBy="listGroupeContacts")
     * @Groups({"read:listgroupecontact","create:listgroupecontact"})
     */
    private $contact;

    /**
     * @ORM\ManyToOne(targetEntity=GroupeContact::class, inversedBy="listGroupeContacts")
     * @Groups({"read:listgroupecontact","read:contact","create:listgroupecontact"})
     */
    private $groupeContact;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function getGroupeContact(): ?GroupeContact
    {
        return $this->groupeContact;
    }

    public function setGroupeContact(?GroupeContact $groupeContact): self
    {
        $this->groupeContact = $groupeContact;

        return $this;
    }
}
