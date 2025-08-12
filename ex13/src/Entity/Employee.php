<?php

namespace App\Entity;

use DateTime;
use App\Enum\HoursEnum;
use App\Enum\PositionEnum;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ORM\Table(name: "ex13_employees")]
#[Assert\Callback('validateDates')]
class Employee
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $firstname = null;

    #[ORM\Column(length: 60)]
    private ?string $lastname = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[ORM\Column(length: 100, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private ?\DateTime $birthdate = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column]
    private ?\DateTime $employed_since = null;

    #[ORM\Column]
    private ?\DateTime $employed_until = null;

    #[ORM\Column(enumType: HoursEnum::class)]
    private ?HoursEnum $hours = null;

    #[ORM\Column]
    private ?int $salary = null;

    #[ORM\Column(enumType: PositionEnum::class)]
    private ?PositionEnum $position = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'employees')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?self $manager = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: 'manager')]
    private Collection $employees;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getBirthdate(): ?\DateTime
    {
        return $this->birthdate;
    }

    public function setBirthdate(\DateTime $birthdate): static
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getEmployedSince(): ?\DateTime
    {
        return $this->employed_since;
    }

    public function setEmployedSince(\DateTime $employed_since): static
    {
        $this->employed_since = $employed_since;

        return $this;
    }

    public function getEmployedUntil(): ?\DateTime
    {
        return $this->employed_until;
    }

    public function setEmployedUntil(\DateTime $employed_until): static
    {
        $this->employed_until = $employed_until;

        return $this;
    }

    public function getHours(): ?HoursEnum
    {
        return $this->hours;
    }

    public function setHours(HoursEnum $hours): static
    {
        $this->hours = $hours;

        return $this;
    }

    public function getSalary(): ?int
    {
        return $this->salary;
    }

    public function setSalary(int $salary): static
    {
        $this->salary = $salary;

        return $this;
    }

    public function getPosition(): ?PositionEnum
    {
        return $this->position;
    }

    public function setPosition(PositionEnum $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getManager(): ?self
    {
        return $this->manager;
    }

    public function setManager(?self $manager): static
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(self $employee): static
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->setManager($this);
        }

        return $this;
    }

    public function removeEmployee(self $employee): static
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getManager() === $this) {
                $employee->setManager(null);
            }
        }

        return $this;
    }

    public static function validateDates(self $employee, ExecutionContextInterface $context): void
    {
        $birthdate      = $employee->getBirthdate();
        $employedSince  = $employee->getEmployedSince();
            $employedUntil  = $employee->getEmployedUntil();

    // Garde-fou : si un champ date n'est pas un DateTime valide (transfo échouée, etc.)
        foreach (['birthdate' => $birthdate, 'employed_since' => $employedSince, 'employed_until' => $employedUntil] as $field => $value) {
            if ($value !== null && !($value instanceof \DateTimeInterface)) {
                $context->buildViolation('Invalid date.')
                    ->atPath($field)
                    ->addViolation();
                // on continue pour signaler les autres problèmes éventuels
            }
        }

        // Si pas de dates minimales, on s’arrête là (le reste des règles attend au moins naissance + début)
        if (!$birthdate || !$employedSince)
            return;

        $today   = new DateTime('today');
        $minDate = new DateTime('1945-01-01');
        $maxDate = new DateTime('2045-12-31');

        foreach (['birthdate' => $birthdate, 'employed_since' => $employedSince, 'employed_until' => $employedUntil] as $field => $date)
        {
            if ($date !== null && ($date < $minDate || $date > $maxDate))
            {
                $context->buildViolation("Error. $field must be between 1945 and 2045.")
                    ->atPath($field)
                    ->addViolation();
            }
        }

        // 0) Naissance dans la plage 1945 ... aujourd’hui
        if ($birthdate < $minDate || $birthdate > $today)
        {
            $context->buildViolation('Birthdate must be between January 1, 1945 and today.')
                ->atPath('birthdate')
                ->addViolation();
        }

        // 1) L’employé doit être né avant son embauche
        if ($employedSince < $birthdate)
        {
            $context->buildViolation('Error. Hire date cannot be before birth date.')
                ->atPath('employed_since')
                ->addViolation();
        }

        // 2) L’employé doit avoir au moins 18 ans à l’embauche
        $minHireDate = (new DateTime($birthdate->format('Y-m-d H:i:s')))->modify('+18 years');
        if ($employedSince < $minHireDate)
        {
            $context->buildViolation('Error. Employee must be at least 18 years old to be hired.')
                ->atPath('employed_since')
                ->addViolation();
        }

        // 3) Embauche dans la plage 1945 ... 2045
        if ($employedSince < $minDate || $employedSince > $maxDate)
        {
            $context->buildViolation('Employment start date must be between January 1, 1945 and December 31, 2045.')
                ->atPath('employed_since')
                ->addViolation();
        }

        // 4) Fin de contrat : au moins +24h et dans la plage 1945 ... 2045
        if ($employedUntil)
        {
            if ($employedUntil < (new DateTime($employedSince->format('Y-m-d H:i:s')))->modify('+1 day'))
            {
                $context->buildViolation('Error. Contract end date must be at least 24 hours after hire date.')
                    ->atPath('employed_until')
                    ->addViolation();
            }
            if ($employedUntil < $minDate || $employedUntil > $maxDate)
            {
                $context->buildViolation('Employment end date must be between January 1, 1945 and December 31, 2045.')
                    ->atPath('employed_until')
                    ->addViolation();
            }
        }
    }



}
