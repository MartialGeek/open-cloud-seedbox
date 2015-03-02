<?php

namespace Martial\Warez\Tests\User\Repository;

use Martial\Warez\User\Repository\UserRepository;

class UserRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UserRepository
     */
    public $repo;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $classMetadata;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $queryBuilder;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $query;

    public function testFindUserByEmailAndPassword()
    {
        $this->findUserByEmailAndPassword();
    }

    protected function findUserByEmailAndPassword()
    {
        $email = 'toto@gmail.com';
        $password = sha1($email);

        $this
            ->em
            ->expects($this->once())
            ->method('createQueryBuilder')
            ->will($this->returnValue($this->queryBuilder));

        $this
            ->queryBuilder
            ->expects($this->once())
            ->method('select')
            ->with($this->equalTo('u'))
            ->will($this->returnValue($this->queryBuilder));

        $this
            ->queryBuilder
            ->expects($this->once())
            ->method('from')
            ->with($this->equalTo(null), $this->equalTo('u'))
            ->will($this->returnValue($this->queryBuilder));

        $this
            ->queryBuilder
            ->expects($this->once())
            ->method('where')
            ->with($this->equalTo('u.email = :email'))
            ->will($this->returnValue($this->queryBuilder));

        $this
            ->queryBuilder
            ->expects($this->once())
            ->method('andWhere')
            ->with($this->equalTo('u.password = :password'))
            ->will($this->returnValue($this->queryBuilder));

        $this
            ->queryBuilder
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($this->query));

        $this
            ->query
            ->expects($this->once())
            ->method('setParameters')
            ->with($this->equalTo([
                'email' => $email,
                'password' => $password
            ]))
            ->will($this->returnValue($this->query));

        $this
            ->query
            ->expects($this->once())
            ->method('getSingleResult');

        $this->repo->findUserByEmailAndPassword($email, $password);
    }

    protected function setUp()
    {
        $this->em = $this
            ->getMockBuilder('\Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->classMetadata = $this
            ->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->queryBuilder = $this
            ->getMockBuilder('\Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->query = $this
            ->getMockBuilder('\Martial\Warez\Tests\Resources\StubDoctrineQuery')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repo = new UserRepository($this->em, $this->classMetadata);
    }
}
