<?php

namespace Martial\Warez\Tests\T411\Api\Torrent;

use Martial\Warez\T411\Api\Category\Category;
use Martial\Warez\T411\Api\Torrent\Torrent;
use Martial\Warez\T411\Api\User\User;

class TorrentTest extends \PHPUnit_Framework_TestCase
{
    public function testInstance()
    {
        $id = 123;
        $name = 'Avatar';
        $category = new Category();
        $numberOfSeeders = 123456;
        $numberOfLeechers = 1234554;
        $numberOfComments = 234;
        $isVerified = true;
        $additionDate = new \DateTime('-2 months');
        $size = 585959;
        $timesCompleted = 75757;
        $owner = new User();
        $privacy = 'normal';

        $torrent = new Torrent();
        $torrent->setId($id);
        $torrent->setName($name);
        $torrent->setCategory($category);
        $torrent->setNumberOfSeeders($numberOfSeeders);
        $torrent->setNumberOfLeechers($numberOfLeechers);
        $torrent->setNumberOfComments($numberOfComments);
        $torrent->setIsVerified($isVerified);
        $torrent->setAdditionDate($additionDate);
        $torrent->setSize($size);
        $torrent->setTimesCompleted($timesCompleted);
        $torrent->setOwner($owner);
        $torrent->setPrivacy($privacy);

        $this->assertSame($id, $torrent->getId());
        $this->assertSame($name, $torrent->getName());
        $this->assertSame($category, $torrent->getCategory());
        $this->assertSame($numberOfSeeders, $torrent->getNumberOfSeeders());
        $this->assertSame($numberOfLeechers, $torrent->getNumberOfLeechers());
        $this->assertSame($numberOfComments, $torrent->getNumberOfComments());
        $this->assertSame($isVerified, $torrent->isVerified());
        $this->assertSame($additionDate, $torrent->getAdditionDate());
        $this->assertSame($size, $torrent->getSize());
        $this->assertSame($timesCompleted, $torrent->getTimesCompleted());
        $this->assertSame($owner, $torrent->getOwner());
        $this->assertSame($privacy, $torrent->getPrivacy());
    }
}
