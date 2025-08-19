<?php

namespace App\Entity;

use App\Repository\MeasureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MeasureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Measure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Please enter a section value')]
    #[Assert\Positive(message: 'Section must be a positive number')]
    private float $section;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Please enter a crimping height value')]
    #[Assert\Positive(message: 'Crimping height must be a positive number')]
    private float $crimpingHeight;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Please enter an insulation height value')]
    #[Assert\Positive(message: 'Insulation height must be a positive number')]
    private float $insulationHeight;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Please enter a crimping width value')]
    #[Assert\Positive(message: 'Crimping width must be a positive number')]
    private float $crimpingWidth;

    #[ORM\Column(type: 'float')]
    #[Assert\NotNull(message: 'Please enter an insulation width value')]
    #[Assert\Positive(message: 'Insulation width must be a positive number')]
    private float $insulationWidth;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSection(): float
    {
        return $this->section;
    }

    public function setSection(float $section): static
    {
        $this->section = $section;

        return $this;
    }

    public function getCrimpingHeight(): float
    {
        return $this->crimpingHeight;
    }

    public function setCrimpingHeight(float $crimpingHeight): static
    {
        $this->crimpingHeight = $crimpingHeight;

        return $this;
    }

    public function getInsulationHeight(): float
    {
        return $this->insulationHeight;
    }

    public function setInsulationHeight(float $insulationHeight): static
    {
        $this->insulationHeight = $insulationHeight;

        return $this;
    }

    public function getCrimpingWidth(): float
    {
        return $this->crimpingWidth;
    }

    public function setCrimpingWidth(float $crimpingWidth): static
    {
        $this->crimpingWidth = $crimpingWidth;

        return $this;
    }

    public function getInsulationWidth(): float
    {
        return $this->insulationWidth;
    }

    public function setInsulationWidth(float $insulationWidth): static
    {
        $this->insulationWidth = $insulationWidth;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTime();
    }
} 