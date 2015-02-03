<?php

namespace Martial\Warez\T411\Api\Torrent;

use Martial\Warez\T411\Api\Category\CategoryInterface;
use Martial\Warez\T411\Api\User\UserInterface;

class Torrent implements TorrentInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var CategoryInterface
     */
    private $category;

    /**
     * @var int
     */
    private $numberOfSeeders;

    /**
     * @var int
     */
    private $numberOfLeechers;

    /**
     * @var int
     */
    private $numberOfComments;

    /**
     * @var bool
     */
    private $isVerified;

    /**
     * @var \DateTime
     */
    private $additionDate;

    /**
     * @var int
     */
    private $size;

    /**
     * @var int
     */
    private $timesCompleted;

    /**
     * @var UserInterface
     */
    private $owner;

    /**
     * @var string
     */
    private $privacy;

    /**
     * Sets the torrent ID.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Retrieves the torrent ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the name of the torrent.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Retrieve the name of the torrent.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the category.
     *
     * @param CategoryInterface $category
     */
    public function setCategory(CategoryInterface $category)
    {
        $this->category = $category;
    }

    /**
     * Retrieves the category.
     *
     * @return CategoryInterface
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Sets the number of seeders.
     *
     * @param int $seeders
     */
    public function setNumberOfSeeders($seeders)
    {
        $this->numberOfSeeders = $seeders;
    }

    /**
     * Retrieves the number of seeders.
     *
     * @return int
     */
    public function getNumberOfSeeders()
    {
        return $this->numberOfSeeders;
    }

    /**
     * Sets the number of leechers.
     *
     * @param int $leechers
     */
    public function setNumberOfLeechers($leechers)
    {
        $this->numberOfLeechers = $leechers;
    }

    /**
     * Retrieves the number of leechers.
     *
     * @return int
     */
    public function getNumberOfLeechers()
    {
        return $this->numberOfLeechers;
    }

    /**
     * Sets the number of comments.
     *
     * @param int $comments
     */
    public function setNumberOfComments($comments)
    {
        $this->numberOfComments = $comments;
    }

    /**
     * Retrieves the number of comments.
     *
     * @return int
     */
    public function getNumberOfComments()
    {
        return $this->numberOfComments;
    }

    /**
     * Sets the "is verified" flag of the torrent.
     *
     * @param bool $isVerified
     */
    public function setIsVerified($isVerified)
    {
        $this->isVerified = $isVerified;
    }

    /**
     * Tells if the torrent is verified.
     *
     * @return bool
     */
    public function isVerified()
    {
        return $this->isVerified;
    }

    /**
     * Sets the addition date of the torrent.
     *
     * @param \DateTime $date
     */
    public function setAdditionDate(\DateTime $date)
    {
        $this->additionDate = $date;
    }

    /**
     * Retrieves the addition date of the torrent.
     *
     * @return \DateTime
     */
    public function getAdditionDate()
    {
        return $this->additionDate;
    }

    /**
     * Sets the size of the torrent (in octets).
     *
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * Retrieves the size of the torrent (in octets).
     *
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Sets the number of times the torrent is completed.
     *
     * @param int $timesCompleted
     */
    public function setTimesCompleted($timesCompleted)
    {
        $this->timesCompleted = $timesCompleted;
    }

    /**
     * Retrieves the number of times the torrent is completed.
     *
     * @return int
     */
    public function getTimesCompleted()
    {
        return $this->timesCompleted;
    }

    /**
     * Sets the owner of the torrent.
     *
     * @param UserInterface $owner
     */
    public function setOwner(UserInterface $owner)
    {
        $this->owner = $owner;
    }

    /**
     * Retrieve the owner of the torrent.
     *
     * @return UserInterface
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Sets the privacy.
     *
     * @param string $privacy
     */
    public function setPrivacy($privacy)
    {
        $this->privacy = $privacy;
    }

    /**
     * Retrieves the privacy.
     *
     * @return string
     */
    public function getPrivacy()
    {
        return $this->privacy;
    }
}
