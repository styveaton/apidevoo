<?php

namespace App\Entity\Auth;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Controller\ClientObjectAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use App\Repository\Auth\ClientObjectRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ClientObjectRepository::class)
 * @ApiResource(
 *     iri="http://schema.org/ClientObject",
 *     normalizationContext={
 *         "groups"={"read:clientObject"}
 *     },
 *     collectionOperations={  
 *         "post"={  
 * "controller"=ClientObjectAction::class,
 *  "deserialize"=false,
 * "denormalization_context"={
 *                  "groups"={
 *                      "create:clientObject"
 *                  }
 *              },
 *               "input_formats"={
 *                  "multipart"={ "multipart/form-data" }
 *              },
 *            
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *         },
 *     },
 *     itemOperations={
 *         "get"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          },
 *         "delete"={
 *              "security"="is_granted('IS_AUTHENTICATED_ANONYMOUSLY')"
 *          }
 *     }
 * )
 * @Vich\Uploadable
 */
class ClientObject
{

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read:clientObject",  "read:client"})
     */
    private ?int $id = null;

    /**
     * @ApiProperty(iri="http://schema.org/contentUrl")
     * @Groups({"read:clientObject","read:client" })
     */
    public ?string $contentUrl = null;

    /**
     * @Assert\NotNull(groups={"create:clientObject"})
     * @Groups({"create:clientObject","read:clientObject"})
     * @Vich\UploadableField(mapping="client_object", fileNameProperty="filePath")
     */
    public ?File $file = null;

    /**
     * @ORM\Column(nullable=true)
     * @Groups({"read:clientObject","read:client"})
     */
    public ?string $filePath = null;

    /**
     * @ORM\OneToMany(targetEntity=Client::class, mappedBy="profile")
     */
    private $clients;

    public function __construct()
    {
        $this->clients = new ArrayCollection();
    }







    public function getId(): ?int
    {
        return $this->id;
    }
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clients;
    }

    public function addClient(Client $client): self
    {
        if (!$this->clients->contains($client)) {
            $this->clients[] = $client;
            $client->setProfile($this);
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->clients->removeElement($client)) {
            // set the owning side to null (unless already changed)
            if ($client->getProfile() === $this) {
                $client->setProfile(null);
            }
        }

        return $this;
    }
}
