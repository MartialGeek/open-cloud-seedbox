<?php

namespace Martial\Warez\T411\Api\User;


class User implements UserInterface
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $username;

    /**
     * @var string
     */
    private $gender;

    /**
     * @var int
     */
    private $age;

    /**
     * @var string
     */
    private $avatarUri;

    /**
     * @var int
     */
    private $downloadedData;

    /**
     * @var int
     */
    private $uploadedData;

    /**
     * Sets the ID.
     *
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Retrieves the ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the username.
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Retrieves the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the gender.
     *
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * Retrieves the gender.
     *
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * Sets the age.
     *
     * @param int $age
     */
    public function setAge($age)
    {
        $this->age = $age;
    }

    /**
     * Retrieve the age.
     *
     * @return int
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Sets the avatar URI.
     *
     * @param string $avatarUri
     */
    public function setAvatarUri($avatarUri)
    {
        $this->avatarUri = $avatarUri;
    }

    /**
     * Retrieves the avatar URI.
     *
     * @return string
     */
    public function getAvatarUri()
    {
        return $this->avatarUri;
    }

    /**
     * Sets the amount of downloaded data (in octets).
     *
     * @param int $size
     */
    public function setDownloadedData($size)
    {
        $this->downloadedData = $size;
    }

    /**
     * Retrieve the amount of downloaded data (in octets).
     *
     * @return int
     */
    public function getDownloadedData()
    {
        return $this->downloadedData;
    }

    /**
     * Sets the amount of uploaded data (in octets).
     *
     * @param int $size
     */
    public function setUploadedData($size)
    {
        $this->uploadedData = $size;
    }

    /**
     * Retrieve the amount of uploaded data (in octets).
     *
     * @return int
     */
    public function getUploadedData()
    {
        return $this->uploadedData;
    }
}
