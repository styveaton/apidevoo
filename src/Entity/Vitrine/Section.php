<?php


namespace App\Entity\Vitrine;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Vitrine\Menu;
use App\Entity\Vitrine\Page;
use App\Repository\Vitrine\SectionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Serializer\Annotation\Groups;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

/**
 * @ORM\Entity(repositoryClass=SectionRepository::class)
 * @ApiResource(
 * collectionOperations = { 
 * "get" = {
 * "normalization_context"={
 *                  "groups"={
 *                      "read:section"
 *                  }
 *              },
 * 
 * },
 * "post"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "create:section"
 *                  }
 *              },
 *            
 *           
 *          }},
 * 
 * itemOperations={
 *          "get"={},
 * "delete"={},
 *          "patch"={
 *              "denormalization_context"={
 *                  "groups"={
 *                      "patch:section"
 *              },
 *             },
 * }
 * })
 * 
 * @ApiFilter(
 *    SearchFilter::class, 
 *    properties={ 
 *      "id": "exact",
 *      "section": "exact",
 *      "section.section": "exact",
 *      "page.vitrine": "exact"
 * })
 */
class Section
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"read:section"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true) 
     * @Groups({"read:section"})
     */
    private $description;


    /**
     * @ORM\ManyToOne(targetEntity=Page::class, inversedBy="sections")
     * @Groups({"read:section"})
     */
    private $page;

    /**
     * @ORM\OneToMany(targetEntity=Menu::class, mappedBy="section")
     * @Groups({"read:section"})
     */
    private $menus;

    /**
     * @ORM\ManyToOne(targetEntity=Section::class, inversedBy="sections")
     *
     */
    private $section;

    /**
     * @ORM\OneToMany(targetEntity=Section::class, mappedBy="section")
     * 
     */
    private $sections;


    /**
     * @ORM\ManyToOne(targetEntity=Vitrine::class, inversedBy="sections")
     */
    private $vitrine;

    /**
     * @ORM\ManyToOne(targetEntity=TypeSection::class, inversedBy="sections")
     */
    private $typeSection;

    /**
     * @ORM\OneToMany(targetEntity=Contenu::class, mappedBy="section")
     */
    private $contenus;

    /**
     * @ORM\Column(type="boolean")
     */
    private $status;

    public function __construct()
    {
        $this->menus = new ArrayCollection();
        $this->sections = new ArrayCollection();
        $this->contenus = new ArrayCollection();
        $this->status
            = false;
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



    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenus(): Collection
    {
        return $this->menus;
    }

    public function addMenu(Menu $menu): self
    {
        if (!$this->menus->contains($menu)) {
            $this->menus[] = $menu;
            $menu->setSection($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): self
    {
        if ($this->menus->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getSection() === $this) {
                $menu->setSection(null);
            }
        }

        return $this;
    }

    public function getSection(): ?self
    {
        return $this->section;
    }

    public function setSection(?self $section): self
    {
        $this->section = $section;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(self $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections[] = $section;
            $section->setSection($this);
        }

        return $this;
    }

    public function removeSection(self $section): self
    {
        if ($this->sections->removeElement($section)) {
            // set the owning side to null (unless already changed)
            if ($section->getSection() === $this) {
                $section->setSection(null);
            }
        }

        return $this;
    }


    public function getVitrine(): ?Vitrine
    {
        return $this->vitrine;
    }

    public function setVitrine(?Vitrine $vitrine): self
    {
        $this->vitrine = $vitrine;

        return $this;
    }

    public function getTypeSection(): ?TypeSection
    {
        return $this->typeSection;
    }

    public function setTypeSection(?TypeSection $typeSection): self
    {
        $this->typeSection = $typeSection;

        return $this;
    }

    /**
     * @return Collection<int, Contenu>
     */
    public function getContenus(): Collection
    {
        return $this->contenus;
    }

    public function addContenu(Contenu $contenu): self
    {
        if (!$this->contenus->contains($contenu)) {
            $this->contenus[] = $contenu;
            $contenu->setSection($this);
        }

        return $this;
    }

    public function removeContenu(Contenu $contenu): self
    {
        if ($this->contenus->removeElement($contenu)) {
            // set the owning side to null (unless already changed)
            if ($contenu->getSection() === $this) {
                $contenu->setSection(null);
            }
        }

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}

/**
 *   
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
                return
                    new JsonResponse([
                        'proprietaire'
                        =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getProprietaire()])->getNom(),
                        'createur'
                        =>   $this->em->getRepository(Client::class)->findOneBy(['id' =>  $vitrine->getClientId()])->getNom(),
                        'title' => $vitrine->getNom(),
                        'description' => $vitrine->getDescription(),
                        'date' =>    $vitrine->getDateCreated()->format('d/m/Y'),



                    ], 201);
            } else if (
                $vitrine
                ->getTypeVitrine()->getId() == 1
            ) {
                $header = [];
                $statusH = true;
                $idH = 0;
                $aboutUs = [];
                $statusA = true;
                $idH = 0;
                $service = [];
                $statusS = true;
                $idS = 0;
                $galerie = [];
                $statusG = true;
                $idG = 0;
                $temoignage = [];
                $statusT = true;
                $idT = 0;
                $footer = [];
                $statusF = true;
                $idF = 0;
                $lsetions =
                    $VitrineEntityManager->getRepository(Section::class)->findBy(['vitrine' => $vitrine, 'status' => 1]);

                foreach ($lsetions as $section) {
                    $contenus =
                        $VitrineEntityManager->getRepository(Contenu::class)->findBy(['section' => $section]);

                    if ($contenus) {
                        if ($section->getTypeSection()->getId() == 1) {
                            $statusH = $section->isStatus();
                            $idH = $section->getId();
                            foreach ($contenus as $contenu) {
                                array_push($header, [$contenu->getId(), $contenu->getDescription()]);
                            }
                        }
                        if ($section->getTypeSection()->getId() == 2) {
                            $statusA = $section->isStatus();
                            $idA = $section->getId();

                            foreach ($contenus as $contenu) {
                                array_push($aboutUs, [$contenu->getId(), $contenu->getDescription()]);
                            }
                        }
                        if ($section->getTypeSection()->getId() == 3) {
                            $statusS = $section->isStatus();
                            $idS = $section->getId();

                            foreach ($contenus as $contenu) {
                                array_push($service, [$contenu->getId(), $contenu->getDescription()]);
                            }
                        }
                        if ($section->getTypeSection()->getId() == 4) {
                            $statusG = $section->isStatus();
                            $idG = $section->getId();

                            foreach ($contenus as $contenu) {
                                array_push($galerie, [$contenu->getId(), $contenu->getDescription()]);
                            }
                        }
                        if ($section->getTypeSection()->getId() == 5) {
                            $statusT = $section->isStatus();
                            $idT = $section->getId();

                            foreach ($contenus as $contenu) {
                                array_push($temoignage, [$contenu->getId(), $contenu->getDescription()]);
                            }
                        }
                        if ($section->getTypeSection()->getId() == 6) {
                            $statusF = $section->isStatus();
                            $idF = $section->getId();

                            foreach ($contenus as $contenu) {
                                array_push($footer, [$contenu->getId(), $contenu->getDescription()]);
                            }
                        }
                    }
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
                        'header' => [
                            'id' => $idH,
                            'status' => $statusH,
                            'data' => $header
                        ],
                        'aboutUs' => [

                            'id' => $idA,

                            'status' => $statusA,
                            'data' => $aboutUs
                        ],
                        'service' => [
                            'id' => $idS,

                            'status' => $statusS,
                            'data' => $service
                        ],
                        'galerie' => [
                            'id' => $idG,

                            'status' => $statusG,
                            'data' => $galerie
                        ],
                        'temoignage' => [
                            'id' => $idT,

                            'status' => $statusT,
                            'data' => $temoignage
                        ],
                        'footer' => [
                            'id' => $idF,

                            'status' => $statusF,
                            'data' => $footer
                        ]


                    ], 201);
            }
        } else {
            return new JsonResponse([
                'message' => 'Action impossible '
            ], 400);
        }
    }
 */
