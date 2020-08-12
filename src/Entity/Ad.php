<?php

namespace App\Entity;

use App\Entity\User;
use Cocur\Slugify\Slugify;
use App\Repository\AdRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity; // Rappel: UniqueEntity a son propre namespace

/**
 * @ORM\Entity(repositoryClass=AdRepository::class)
 * @ORM\HasLifecycleCallbacks 
 * Note : HasLifecycleCallbacks -> ici utilisé pour prePersist/preUpdate pour calculer le slug
 * 
 * @UniqueEntity(
 *   fields={"title"},
 *   message="Une annonce possède déjà ce titre"
 *  )
 */
class Ad
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min=10, max=255, minMessage="Minimum 10 chars !", maxMessage="Maximum 255 chars !")
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="float")
     */
    private $price;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=10, max=255, minMessage="Minimum 10 chars !", maxMessage="Maximum 255 chars !")
     */
    private $introduction;

    /**
     * @ORM\Column(type="text")
     * @Assert\Length(min=20, max=2000, minMessage="Minimum 20 chars !", maxMessage="Maximum 2000 chars !")
     */
    private $content;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Url(message="format d'url incorrect !")
     */ 
    private $coverImage;

    /**
     * @ORM\Column(type="integer")@Assert\
     */
    private $rooms;

    /**
     * @ORM\OneToMany(targetEntity=Image::class, mappedBy="Ad", orphanRemoval=true)
     * @Assert\Valid()
     */
    private $images;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="ads")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="ad")
     */
    private $bookings;

    /**
     * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="Ad", orphanRemoval=true)
     */
    private $comments;

    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->bookings = new ArrayCollection();
        $this->comments = new ArrayCollection();
    }

    /**
     * Permet d'initialiser le slug
     * 
     * @ORM\PrePersist
     * @ORM\PreUpdate
     * @return void
     */
    public function initializeSlug() 
    {
        if(empty($this->slug)) {
            $slugify = new Slugify();
            $this->slug = $slugify->slugify($this->title);
        }
    }

    /**
     * Permet d'obtenir un tableau des joursn on dispo pour cette annonce
     *
     * @return array Un tableau d'objets DateTime représentant les jours d'occupation
     */
    public function getNotAvailableDays() {
        $notAvailableDays = [];

        foreach($this->bookings as $booking) {
            // Calculer les jours se trouvant entre la date d'arrivée et de départ
            $result = range( /* range() => $result = range(10, 20, 5) ==> $result = [10, 15, 20] */
                $booking->getStartDate()->getTimestamp(),
                $booking->getEndDate()->getTimeStamp(),
                24 * 60 * 60 // nb de secondes dans une journée
            ); // Tableau de toutes les journées MAIS chacun stockés en timestamp

            $days = array_map(function($dayTimestamp){ // array_map() execute la fonction indiquée pour chaque élément d'un tableau en 2e arg et output un deuxieme tableau avec tout les résultats
                return new \DateTime(date('Y-m-d', $dayTimestamp));
            }, $result); // Dans days on aura un tableau avec l'ensemble des jours entre date d'arrivée et départ

            $notAvailableDays = array_merge($notAvailableDays, $days);
        }

        return $notAvailableDays;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCoverImage(): ?string
    {
        return $this->coverImage;
    }

    public function setCoverImage(string $coverImage): self
    {
        $this->coverImage = $coverImage;

        return $this;
    }

    public function getRooms(): ?int
    {
        return $this->rooms;
    }

    public function setRooms(int $rooms): self
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * @return Collection|Image[]
     */
    public function getImages(): Collection
    {
        return $this->images;
    }

    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setAd($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getAd() === $this) {
                $image->setAd(null);
            }
        }
 
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection|Booking[]
     */
    public function getBookings(): Collection
    {
        return $this->bookings;
    }

    public function addBooking(Booking $booking): self
    {
        if (!$this->bookings->contains($booking)) {
            $this->bookings[] = $booking;
            $booking->setAd($this);
        }

        return $this;
    }

    public function removeBooking(Booking $booking): self
    {
        if ($this->bookings->contains($booking)) {
            $this->bookings->removeElement($booking);
            // set the owning side to null (unless already changed)
            if ($booking->getAd() === $this) {
                $booking->setAd(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Comment[]
     */
    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addComment(Comment $comment): self
    {
        if (!$this->comments->contains($comment)) {
            $this->comments[] = $comment;
            $comment->setAd($this);
        }

        return $this;
    }

    public function removeComment(Comment $comment): self
    {
        if ($this->comments->contains($comment)) {
            $this->comments->removeElement($comment);
            // set the owning side to null (unless already changed)
            if ($comment->getAd() === $this) {
                $comment->setAd(null);
            }
        }

        return $this;
    }

    /**
     * Obtenir la moyenne des notes d'une annonce
     *
     * @return float
     */
    public function getAvgRating() {
        // array_reduce() applique itérativement une fonction callback aux éléments du tableau array de manière a réduire le tableau à une simple valeur
        $sum = array_reduce($this->comments->toArray(), function($total, $comment){ // sauf que comments est une ArrayCollection et on attend un array, on utilise méthode des AC toArray
            return $total + $comment->getRating();
        }, 0); // on passe 0 pour $total, pour l'initialiser)

        if(count($this->comments) > 0) { // PREVENTION DIVISION PAR 0 !!
            return $sum / count($this->comments); 
        }
        return 0;
        
        /* Manière classique
        $sum = 0;
        foreach($this->comments as $comment){
            $sum += $comment->getRating();
        }
        return $sum / count($this->comments); */
    }

    /**
     * Permet de récuperer le commentaire de l'utilisateur connecté dans une annonce (si existe)
     *
     * @param User $author
     * @return Comment|null
     */
    public function getCommentFromAuthor(User $author) {
        foreach($this->comments as $comment) {
            if($comment->getAuthor() === $author) return $comment;
        }
        return null;
    }
}
