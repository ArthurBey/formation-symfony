<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookingRepository;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookingRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity=Ad::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * @ORM\Column(type="datetime")
     * Assert\Date - A RESOUDRE -
     * @Assert\GreaterThan("today", message="Date d'arrivée doit être ultérieure à la date d'auj")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * Assert\Date - A RESOUDRE -
     * @Assert\GreaterThan(propertyPath="startDate", message="La date de départ forcément après celle d'arrivée")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Callback appelé à chaque fois qu'on créer une réservation
     * @ORM\PrePersist
     * 
     * @return void
     */
    public function prePersist() { // On a pas besoin que le BookingController détermine ça lui même à chaque fois
        if(empty($this->createdAt)){
            $this->createdAt = new \DateTime();
        }

        if(empty($this->amount)){
            $this->amount = $this->ad->getPrice() * $this->getDuration();
        }
    }

    public function getDuration() { // Pour déduire le nb de jours entre un startDate et endDate pour calculer le prix d'un séjour
        $diff = $this->endDate->diff($this->startDate); // l'objet retourné est de type "date interval"
        return $diff->days; // une propriété des objets types date intervale...
    }

    public function isBookableDates() {
        // 1) Il faut connaître les dates impossibles pour l'annonce
        $notAvailableDays = $this->ad->getNotavailableDays();

        // 2) Il faut comparer les dates choisies avec les dates impossibles
        $bookingDays = $this->getDays();

        $formatDay = function($day){
            return $day->format('Y-m-d');
        };

        $days         = array_map($formatDay, $bookingDays); // Tableau contenant des chaines de caractères des journées 
        $notAvailable = array_map($formatDay, $notAvailableDays); // On fait la même chose sauf pour les jours non dispo

        foreach($days as $day){ // array_search : si un day se trouve dans tableau notAvailable
            if(array_search($day, $notAvailable) !== false) return false;
        }

        return true;
    }

    /**
     * Permet de récup un tableau des journées correspondant à ma résa
     *
     * @return array un tableau d'objets DateTime représentant les jours de la résa
     */
    public function getDays() {
        $daysTimestamp = range( /* range() => $result = range(10, 20, 5) ==> $result = [10, 15, 20] */
            $this->startDate->getTimestamp(),
            $this->endDate->getTimestamp(),
            24 * 60 * 60 // nb de secondes dans une journée
        ); // Tableau de toutes les journées MAIS chacun stockés en timestamp

        $days = array_map(function($dayTimestamp){
            return new \DateTime(date('Y-m-d', $dayTimestamp));
        }, $daysTimestamp);

        return $days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
