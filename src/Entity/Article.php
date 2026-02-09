<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[UniqueEntity(fields: ['code'], message: 'Artykuł o tym kodzie już istnieje.')]
#[UniqueEntity(fields: ['name'], message: 'Artykuł o tej nazwie już istnieje.')]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank(message: 'Nazwa artykułu nie może być pusta.')]
    #[Assert\Length(min: 3, minMessage: 'Nazwa musi mieć co najmniej 3 znaki.')]
    private ?string $name = null;

    #[ORM\Column(length: 50, unique: true)]
    #[Assert\NotBlank(message: 'Kod artykułu jest wymagany.')]
    #[Assert\Regex(
        pattern: '/^[A-Z0-9_-]+$/i',
        message: 'Kod może zawierać tylko litery, cyfry, myślniki i podkreślenia.'
    )]
    private ?string $code = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(message: 'Podaj jednostkę (np. szt, kg).')]
    private ?string $unit = null;

    #[ORM\OneToMany(targetEntity: Transaction::class, mappedBy: 'article')]
    private Collection $transactions;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    public function getName(): ?string
    {
        return $this->name;
    }
    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }
    public function getCode(): ?string
    {
        return $this->code;
    }
    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }
    public function getUnit(): ?string
    {
        return $this->unit;
    }
    public function setUnit(string $unit): static
    {
        $this->unit = $unit;
        return $this;
    }

    /** @return Collection<int, Transaction> */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    public function getStockInWarehouse(Warehouse $warehouse): float
    {
        $stock = 0.0;
        foreach ($this->transactions as $transaction) {
            if ($transaction->getWarehouse() === $warehouse) {
                if ($transaction->getType()->value === 'in') {
                    $stock += (float) $transaction->getQuantity();
                } elseif ($transaction->getType()->value === 'out') {
                    $stock -= (float) $transaction->getQuantity();
                }
            }
        }
        return $stock;
    }
}
