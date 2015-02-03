<?php

namespace Martial\Warez\T411\Api\Torrent;


use Martial\Warez\T411\Api\Category\CategoryInterface;
use Martial\Warez\T411\Api\User\UserInterface;

interface TorrentInterface
{
    /**
     * Sets the torrent ID.
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * Retrieves the torrent ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Sets the name of the torrent.
     *
     * @param string $name
     */
    public function setName($name);

    /**
     * Retrieve the name of the torrent.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the category of the torrent.
     *
     * @param CategoryInterface $category
     */
    public function setCategory(CategoryInterface $category);

    /**
     * Retrieves the category of the torrent.
     *
     * @return CategoryInterface
     */
    public function getCategory();

    /**
     * Sets the number of seeders.
     *
     * @param int $seeders
     */
    public function setNumberOfSeeders($seeders);

    /**
     * Retrieves the number of seeders.
     *
     * @return int
     */
    public function getNumberOfSeeders();

    /**
     * Sets the number of leechers.
     *
     * @param int $leechers
     */
    public function setNumberOfLeechers($leechers);

    /**
     * Retrieves the number of leechers.
     *
     * @return int
     */
    public function getNumberOfLeechers();

    /**
     * Sets the number of comments.
     *
     * @param int $comments
     */
    public function setNumberOfComments($comments);

    /**
     * Retrieves the number of comments.
     *
     * @return int
     */
    public function getNumberOfComments();

    /**
     * Sets the "is verified" flag of the torrent.
     *
     * @param bool $isVerified
     */
    public function setIsVerified($isVerified);

    /**
     * Tells if the torrent is verified.
     *
     * @return bool
     */
    public function isVerified();

    /**
     * Sets the addition date of the torrent.
     *
     * @param \DateTime $date
     */
    public function setAdditionDate(\DateTime $date);

    /**
     * Retrieves the addition date of the torrent.
     *
     * @return \DateTime
     */
    public function getAdditionDate();

    /**
     * Sets the size of the torrent (in octets).
     *
     * @param int $size
     */
    public function setSize($size);

    /**
     * Retrieves the size of the torrent (in octets).
     *
     * @return int
     */
    public function getSize();

    /**
     * Sets the number of times the torrent is completed.
     *
     * @param int $timesCompleted
     */
    public function setTimesCompleted($timesCompleted);

    /**
     * Retrieves the number of times the torrent is completed.
     *
     * @return int
     */
    public function getTimesCompleted();

    /**
     * Sets the owner of the torrent.
     *
     * @param UserInterface $owner
     */
    public function setOwner(UserInterface $owner);

    /**
     * Retrieve the owner of the torrent.
     *
     * @return UserInterface
     */
    public function getOwner();

    /**
     * Sets the privacy.
     *
     * @param string $privacy
     */
    public function setPrivacy($privacy);

    /**
     * Retrieves the privacy.
     *
     * @return string
     */
    public function getPrivacy();
}
