<?php

namespace App\Entity\Bulk;

use App\Repository\Bulk\RouteListSmsLotsEnvoyeRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ApiResource(
 *     normalizationContext={
 *          "groups"={
 *              "read:routelse"
 *          }
 *     },
 *     collectionOperations={
 *          "get"={
 *            
 *          },
 *          "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:routelse"
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
 * 
 * @ApiFilter(
 *     SearchFilter::class,
 *     properties={
 *      "routeId": "exact",
 *      "listSmsLotsEnvoye.sms.clientId": "exact",
 * 
 *  
 *     }
 * )
 * @ORM\Entity(repositoryClass=RouteListSmsLotsEnvoyeRepository::class)
 */
class RouteListSmsLotsEnvoye
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=ListSmsLotsEnvoye::class, inversedBy="routeListSmsLotsEnvoyes")
     * 
     * @Groups({"create:sms","read:routelse","create:routelse"})
     */
    private $listSmsLotsEnvoye;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"create:sms","read:routelse","create:routelse"})
     */
    private $routeId;

    /**
     * @ORM\Column(type="boolean")
     *  @Groups({"create:sms"})
     */
    private $status = false;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListSmsLotsEnvoye(): ?ListSmsLotsEnvoye
    {
        return $this->listSmsLotsEnvoye;
    }

    public function setListSmsLotsEnvoye(?ListSmsLotsEnvoye $listSmsLotsEnvoye): self
    {
        $this->listSmsLotsEnvoye = $listSmsLotsEnvoye;

        return $this;
    }

    public function getRouteId(): ?int
    {
        return $this->routeId;
    }

    public function setRouteId(?int $routeId): self
    {
        $this->routeId = $routeId;

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
}
