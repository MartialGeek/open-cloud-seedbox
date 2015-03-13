<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Form\TrackerSearch;
use Martial\Warez\Front\Controller\TrackerController;

class TrackerControllerTest extends ControllerTestCase
{
    /**
     * @var TrackerController
     */
    public $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $client;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $userService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $profileService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $profile;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $trackerToken;

    public function testSearchActionWithAuthenticatedUser()
    {
        $this->search([
            'is_user_authenticated' => true,
            'has_search' => true
        ]);
    }

    public function testSearchActionWithNonAuthenticatedUser()
    {
        $this->search([
            'is_user_authenticated' => false,
            'has_search' => true
        ]);
    }

    protected function search(array $options = [])
    {
        $userId = 12;
        $trackerUsername = 'Toto';
        $trackerPassword = sha1($trackerUsername);
        $sessionGet = [];

        if (!$options['is_user_authenticated']) {
            $sessionGet['user_id'] = $userId;
        }

        if ($options['has_search']) {
            $sessionGet['api_token'] = $this->trackerToken;
        }

        $this->sessionGet($sessionGet);
        $this->checkTrackerAuthentication($userId, $trackerUsername, $trackerPassword, $options['is_user_authenticated']);
        $this->hasQueryParameter('tracker_search', $options['has_search']);

        $categories = ['cat1', 'cat2'];
        $trackerResult = $options['has_search'] ? ['result1', 'result2'] : [];

        $this
            ->client
            ->expects($this->once())
            ->method('getCategories')
            ->with($this->equalTo($this->trackerToken))
            ->will($this->returnValue($categories));

        $this->createForm(new TrackerSearch(), null, ['categories' => $categories]);
        $this->createFormView();

        if ($options['has_search']) {
            $offset = 0;
            $limit = 20;

            $apiSearch = [
                'terms' => 'avatar',
                'category_id' => 631,
                'offset' => $offset,
                'limit' => $limit
            ];

            $getParameters = [
                'terms' => $apiSearch['terms'],
                'category_id' => $apiSearch['category_id']
            ];

            $this
                ->queryParameterBag
                ->expects($this->exactly(3))
                ->method('get')
                ->withConsecutive(
                    [$this->equalTo('tracker_search')],
                    [$this->equalTo('offset')],
                    [$this->equalTo('limit')]
                )
                ->willReturnOnConsecutiveCalls($getParameters, $offset, $limit);

            $this
                ->form
                ->expects($this->once())
                ->method('setData')
                ->with($this->equalTo([
                    'terms' => $apiSearch['terms'],
                    'category_id' => $apiSearch['category_id']
                ]));

            $this
                ->client
                ->expects($this->once())
                ->method('search')
                ->with(
                    $this->equalTo($this->trackerToken),
                    $this->equalTo($apiSearch)
                )
                ->willReturn($trackerResult);
        }

        $this->render('@tracker/search.html.twig', [
            'result' => $trackerResult,
            'searchForm' => $this->formView
        ]);

        $this->controller->search($this->request);
    }

    protected function checkTrackerAuthentication($userId, $trackerUsername, $trackerPassword, $isAuthenticated = false)
    {
        $this->sessionHas(['api_token' => $isAuthenticated]);

        if (!$isAuthenticated) {
            $this
                ->userService
                ->expects($this->once())
                ->method('find')
                ->with($this->equalTo($userId))
                ->will($this->returnValue($this->user));

            $this
                ->user
                ->expects($this->once())
                ->method('getProfile')
                ->will($this->returnValue($this->profile));

            $this
                ->profileService
                ->expects($this->once())
                ->method('decodeTrackerPassword')
                ->with($this->equalTo($this->profile));

            $this
                ->profile
                ->expects($this->once())
                ->method('getTrackerUsername')
                ->will($this->returnValue($trackerUsername));

            $this
                ->profile
                ->expects($this->once())
                ->method('getTrackerPassword')
                ->will($this->returnValue($trackerPassword));

            $this
                ->client
                ->expects($this->once())
                ->method('authenticate')
                ->with($this->equalTo($trackerUsername), $this->equalTo($trackerPassword))
                ->will($this->returnValue($this->trackerToken));

            $this->sessionSet(['api_token' => $this->trackerToken]);
        }
    }

    protected function defineDependencies()
    {
        $this->client = $this->getMock('\Martial\Warez\T411\Api\ClientInterface');
        $this->userService = $this->getMock('\Martial\Warez\User\UserServiceInterface');
        $this->profileService = $this->getMock('\Martial\Warez\User\ProfileServiceInterface');

        $dependencies = parent::defineDependencies();
        $dependencies[] = $this->client;
        $dependencies[] = $this->userService;
        $dependencies[] = $this->profileService;

        return $dependencies;
    }

    /**
     * Returns the full qualified class name of the controller you want to test.
     *
     * @return string
     */
    protected function getControllerClassName()
    {
        return '\Martial\Warez\Front\Controller\TrackerController';
    }

    protected function setUp()
    {
        parent::setUp();

        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->profile = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\Profile')
            ->disableOriginalConstructor()
            ->getMock();

        $this->trackerToken = $this->getMock('\Martial\Warez\T411\Api\Authentication\TokenInterface');
    }
}