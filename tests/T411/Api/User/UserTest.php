<?php

namespace Martial\Warez\Tests\T411\Api\User;


use Martial\Warez\T411\Api\User\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $id = 123;
        $username = 'WarezMan';
        $gender = 'male';
        $age = 42;
        $avatarUri = '/images/avatar.jpg';
        $downloadedData = 12345;
        $uploadedData = 12345;

        $user = new User();
        $user->setId($id);
        $user->setUsername($username);
        $user->setGender($gender);
        $user->setAge($age);
        $user->setAvatarUri($avatarUri);
        $user->setDownloadedData($downloadedData);
        $user->setUploadedData($uploadedData);

        $this->assertSame($id, $user->getId());
        $this->assertSame($username, $user->getUsername());
        $this->assertSame($gender, $user->getGender());
        $this->assertSame($age, $user->getAge());
        $this->assertSame($avatarUri, $user->getAvatarUri());
        $this->assertSame($downloadedData, $user->getDownloadedData());
        $this->assertSame($uploadedData, $user->getUploadedData());
    }
}
