<?php

namespace Martial\Warez\T411\Api\User;


interface UserInterface
{
    /**
     * Sets the ID.
     *
     * @param int $id
     */
    public function setId($id);

    /**
     * Retrieves the ID.
     *
     * @return int
     */
    public function getId();

    /**
     * Sets the username.
     *
     * @param string $username
     */
    public function setUsername($username);

    /**
     * Retrieves the username.
     *
     * @return string
     */
    public function getUsername();

    /**
     * Sets the gender.
     *
     * @param string $gender
     */
    public function setGender($gender);

    /**
     * Retrieves the gender.
     *
     * @return string
     */
    public function getGender();

    /**
     * Sets the age.
     *
     * @param int $age
     */
    public function setAge($age);

    /**
     * Retrieve the age.
     *
     * @return int
     */
    public function getAge();

    /**
     * Sets the avatar URI.
     *
     * @param string $avatarUri
     */
    public function setAvatarUri($avatarUri);

    /**
     * Retrieves the avatar URI.
     *
     * @return string
     */
    public function getAvatarUri();

    /**
     * Sets the amount of downloaded data (in octets).
     *
     * @param int $size
     */
    public function setDownloadedData($size);

    /**
     * Retrieve the amount of downloaded data (in octets).
     *
     * @return int
     */
    public function getDownloadedData();

    /**
     * Sets the amount of uploaded data (in octets).
     *
     * @param int $size
     */
    public function setUploadedData($size);

    /**
     * Retrieve the amount of uploaded data (in octets).
     *
     * @return int
     */
    public function getUploadedData();
}
