<?php

namespace Martial\Warez\Tests\Security;

use Martial\Warez\Security\Firewall;

class FirewallTest extends \PHPUnit_Framework_TestCase
{
    const RESTRICTED_AREA = 'restricted_area';
    const NON_CONNECTED_USER = 'non_connected_user';

    /**
     * @var Firewall
     */
    public $firewall;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $getResponseEvent;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $request;

    public function testRestrictedAreaShouldReturnA401ResponseWithNonConnectedUser()
    {
        $this->onKernelRequest([self::RESTRICTED_AREA, self::NON_CONNECTED_USER]);
    }

    public function testRestrictedAreaWithConnectedUser()
    {
        $this->onKernelRequest([self::RESTRICTED_AREA]);
    }

    public function testPublicArea()
    {
        $this->onKernelRequest();
    }

    protected function onKernelRequest(array $options = [])
    {
        $requestUri = in_array(self::RESTRICTED_AREA, $options) ? '/restricted/area' : '/';

        $this
            ->getResponseEvent
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this
            ->request
            ->expects($this->once())
            ->method('getRequestUri')
            ->will($this->returnValue($requestUri));

        if (in_array(self::RESTRICTED_AREA, $options)) {
            $connected = !in_array(self::NON_CONNECTED_USER, $options);

            $this
                ->session
                ->expects($this->once())
                ->method('get')
                ->with($this->equalTo('connected'), $this->equalTo(false))
                ->will($this->returnValue($connected));

            if (in_array(self::NON_CONNECTED_USER, $options)) {
                $this
                    ->getResponseEvent
                    ->expects($this->once())
                    ->method('setResponse')
                    ->with($this->isInstanceOf('\Symfony\Component\HttpFoundation\Response'));
            }
        }

        $this->firewall->onKernelRequest($this->getResponseEvent);
    }

    protected function setUp()
    {
        $this->session = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->getResponseEvent = $this
            ->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->request = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->firewall = new Firewall($this->session);
    }
}