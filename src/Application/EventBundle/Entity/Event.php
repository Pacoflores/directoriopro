<?php

namespace Application\EventBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application\EventBundle\Entity\Event
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="Application\EventBundle\Entity\EventRepository")
 */
class Event
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer $user_id
     *
     * @ORM\Column(name="user_id", type="integer")
     */
    private $user_id;

    /**
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;

    /**
     * @var text $body
     *
     * @ORM\Column(name="body", type="text")
     */
    private $body;

    /**
     * @var text $address
     *
     * @ORM\Column(name="address", type="string", length=255)
     */
    private $address;

    /**
     * @var datetime $date_start
     *
     * @ORM\Column(name="date_start", type="datetime")
     */
    private $date_start;

    /**
     * @var datetime $date_end
     *
     * @ORM\Column(name="date_end", type="datetime")
     */
    private $date_end;

    /**
     * @var integer $featured
     *
     * @ORM\Column(name="featured", type="integer")
     */
    private $featured;

    /**
     * @var string $url
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=true)
     */
    private $url;

    /**
     * @var string $location
     *
     * @ORM\Column(name="location", type="string", length=255)
     */
    private $location;

    /**
     * @var integer $city_id
     *
     * @ORM\Column(name="city_id", type="integer", nullable=true)
     */
    private $city_id;

    /**
     * @var integer $country_id
     *
     * @ORM\Column(name="country_id", type="integer", nullable=true)
     */
    private $country_id;

    /**
     * @var integer $visits
     *
     * @ORM\Column(name="visits", type="integer", nullable=true)
     */
    private $visits = 0;


    /**
     * @var integer $users
     *
     * @ORM\Column(name="users", type="integer", nullable=true)
     */
    private $users = 0;

    /**
     * @var integer $hashtag
     *
     * @ORM\Column(name="hashtag", type="string", nullable=true, length=30)
     */
    private $hashtag;


    /**
     * @var string $slug
     *
     * @ORM\Column(name="slug", type="string", nullable=true, length=255)
     */
    private $slug;
    
    /**
     * @var integer $type
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type = 0;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set user_id
     *
     * @param integer $userId
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
    }

    /**
     * Get user_id
     *
     * @return integer
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body
     *
     * @param text $body
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Get body
     *
     * @return text
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set address
     *
     * @param text $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * Get address
     *
     * @return text
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @var datetime $date
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * Set date
     *
     * @param datetime $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * Get date
     *
     * @return datetime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date_start
     *
     * @param datetime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->date_start = $dateStart;
    }

    /**
     * Get date_start
     *
     * @return datetime
     */
    public function getDateStart()
    {
        return $this->date_start;
    }

    /**
     * Set date_end
     *
     * @param datetime $dateEnd
     */
    public function setDateEnd($dateEnd)
    {
        $this->date_end = $dateEnd;
    }

    /**
     * Get date_end
     *
     * @return datetime
     */
    public function getDateEnd()
    {
        return $this->date_end;
    }

    /**
     * Set featured
     *
     * @param integer $featured
     */
    public function setFeatured($featured)
    {
        $this->featured = $featured;
    }

    /**
     * Get featured
     *
     * @return integer
     */
    public function getFeatured()
    {
        return $this->featured;
    }

    /**
     * Set url
     *
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Get url
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set location
     *
     * @param string $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set city_id
     *
     * @param integer $cityId
     */
    public function setCityId($cityId)
    {
        $this->city_id = $cityId;
    }

    /**
     * Get city_id
     *
     * @return integer
     */
    public function getCityId()
    {
        return $this->city_id;
    }

    /**
     * Set country_id
     *
     * @param integer $countryId
     */
    public function setCountryId($countryId)
    {
        $this->country_id = $countryId;
    }

    /**
     * Get country_id
     *
     * @return integer
     */
    public function getCountryId()
    {
        return $this->country_id;
    }

    /**
     * Set visits
     *
     * @param integer $visits
     */
    public function setVisits($visits)
    {
        $this->visits = $visits;
    }

    /**
     * Get visits
     *
     * @return integer
     */
    public function getVisits()
    {
        return $this->visits;
    }

    /**
     * Set users
     *
     * @param integer $users
     */
    public function setUsers($users)
    {
        $this->users = $users;
    }

    /**
     * Get users
     *
     * @return integer
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Set hashtag
     *
     * @param string $hashtag
     */
    public function setHashtag($hashtag)
    {
        $this->hashtag = $hashtag;
    }

    /**
     * Get hashtag
     *
     * @return string
     */
    public function getHashtag()
    {
        return $this->hashtag;
    }

    /**
     * Set slug
     *
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * Get slug
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
    
    /**
     * Set type
     *
     * @param integer $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Get pretty date
     *
     * @return string
     */
    public function getPrettyDate( $format = '%A %e %B %Y' )
    {
        return strftime( $format, strtotime( $this->getDateStart()->format('Y-m-d H:i:s') ) );
    }

    /**
     * Get google calendar date start
     *
     * @return string
     */
    public function getGDateStart()
    {
        return date( 'Ymd\THis\Z', ( strtotime( $this->getDateStart()->format('Y-m-d H:i:s') ) - 7200 ) );
    }

    /**
     * Get google calendar date end
     *
     * @return string
     */
    public function getGDateEnd()
    {
        return date( 'Ymd\THis\Z', ( strtotime( $this->getDateEnd()->format('Y-m-d H:i:s') ) - 7200 ) );
    }

}
