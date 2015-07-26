<?php

namespace Martial\OpenCloudSeedbox\Tests\Security;

use Martial\OpenCloudSeedbox\Security\CookieTokenizer;

class CookieTokenizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CookieTokenizer
     */
    public $tokenizer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    protected function setUp()
    {
        $this->user = $this
            ->getMockBuilder('\Martial\OpenCloudSeedbox\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $this->tokenizer = new CookieTokenizer($this->em);
    }

    public function testGenerateAndStoreToken()
    {
        $this
            ->user
            ->expects($this->once())
            ->method('setCookieTokenId')
            ->with($this->isType('string'))
            ->willReturnSelf();

        $this
            ->user
            ->expects($this->once())
            ->method('setCookieTokenHash')
            ->with($this->isType('string'))
            ->willReturnSelf();

        $this
            ->em
            ->expects($this->once())
            ->method('flush');

        $token = $this->tokenizer->generateAndStoreToken($this->user);
        $this->assertInstanceOf('\Martial\OpenCloudSeedbox\Security\CookieTokenInterface', $token);
        $this->assertInternalType('string', $token->getTokenId());
        $this->assertInternalType('string', $token->getTokenHash());
    }

    public function testFindExistingToken()
    {
        $this->findToken();
    }

    /**
     * @expectedException \Martial\OpenCloudSeedbox\Security\CookieTokenNotFoundException
     */
    public function testFindNonExistingToken()
    {
        $this->findToken(false);
    }

    private function findToken($isExist = true)
    {
        $id = uniqid();
        $hash = sha1($id);
        $repository = $this->getMock('\Doctrine\Common\Persistence\ObjectRepository');
        $findResult = $isExist ? $this->user : null;

        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->willReturn($repository);

        $repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['cookieTokenId' => $id])
            ->willReturn($findResult);

        if ($isExist) {
            $this
                ->user
                ->expects($this->once())
                ->method('getCookieTokenId')
                ->willReturn($id);

            $this
                ->user
                ->expects($this->once())
                ->method('getCookieTokenHash')
                ->willReturn($hash);
        }

        $result = $this->tokenizer->findToken($id);

        if ($isExist) {
            $this->assertInstanceOf('\Martial\OpenCloudSeedbox\Security\CookieToken', $result);
            $this->assertSame($id, $result->getTokenId());
            $this->assertSame($hash, $result->getTokenHash());
        }
    }
}
