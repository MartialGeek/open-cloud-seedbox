<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Download\TorrentClientException;
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
    public $settings;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $entity;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $trackerToken;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $torrentClient;

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

    public function testDownloadWithoutErrors()
    {
        $this->download();
    }

    public function testDownloadWithErrors()
    {
        $this->download(true);
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
        $this->sessionHas(['api_token' => $options['is_user_authenticated']]);
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

    protected function download($withErrors = false)
    {
        $userId = 12;
        $torrentId = 45612;
        $trackerUsername = 'Toto';
        $trackerPassword = sha1($trackerUsername);
        $isAuthenticated = true;
        $transmissionSessionId = uniqid();

        $torrent = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\File\File')
            ->setConstructorArgs(['/path/to/file', false])
            ->getMock();

        $this->sessionGet([
            'api_token' => $this->trackerToken,
            'transmission_session_id' => $transmissionSessionId
        ]);

        $this->sessionHas([
            'api_token' => $isAuthenticated,
            'transmission_session_id' => true
        ]);

        $this->checkTrackerAuthentication($userId, $trackerUsername, $trackerPassword, $isAuthenticated);

        $this
            ->client
            ->expects($this->once())
            ->method('download')
            ->with($this->equalTo($this->trackerToken), $this->equalTo($torrentId))
            ->willReturn($torrent);

        $torrentClientResult = $withErrors ?
            $this->throwException(new TorrentClientException()) :
            $this->returnValue(null);

        $this
            ->torrentClient
            ->expects($this->once())
            ->method('addToQueue')
            ->with($this->equalTo($transmissionSessionId), $this->equalTo($torrent))
            ->will($torrentClientResult);

        $response = $this->controller->download($torrentId);

        if ($withErrors) {
            $this->assertSame(500, $response->getStatusCode());
        } else {
            $this->assertSame(200, $response->getStatusCode());
        }
    }

    protected function checkTrackerAuthentication($userId, $trackerUsername, $trackerPassword, $isAuthenticated = false)
    {
        if (!$isAuthenticated) {
            $this->getUser($userId, $this->user);

            $this
                ->settings
                ->expects($this->once())
                ->method('getSettings')
                ->with($this->user)
                ->willReturn($this->entity);

            $this
                ->entity
                ->expects($this->once())
                ->method('getUsername')
                ->willReturn($trackerUsername);

            $this
                ->entity
                ->expects($this->once())
                ->method('getPassword')
                ->willReturn($trackerPassword);

            $this
                ->client
                ->expects($this->once())
                ->method('authenticate')
                ->with($this->equalTo($trackerUsername), $this->equalTo($trackerPassword))
                ->willReturn($this->trackerToken);

            $this->sessionSet(['api_token' => $this->trackerToken]);
        }
    }

    protected function defineDependencies()
    {
        $this->client = $this->getMock('\Martial\T411\Api\ClientInterface');
        $this->userService = $this->getMock('\Martial\Warez\User\UserServiceInterface');
        $this->settings = $this
            ->getMockBuilder('\Martial\Warez\Settings\TrackerSettings')
            ->disableOriginalConstructor()
            ->getMock();
        $this->torrentClient = $this->getMock('\Martial\Warez\Download\TorrentClientInterface');

        $dependencies = parent::defineDependencies();
        $dependencies[] = $this->client;
        $dependencies[] = $this->settings;
        $dependencies[] = $this->torrentClient;

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

        $this->entity = $this
            ->getMockBuilder('\Martial\Warez\Settings\Entity\TrackerSettingsEntity')
            ->disableOriginalConstructor()
            ->getMock();

        $this->trackerToken = $this->getMock('\Martial\T411\Api\Authentication\TokenInterface');
    }
}
