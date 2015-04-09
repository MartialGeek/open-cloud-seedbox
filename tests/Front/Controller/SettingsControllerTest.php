<?php

namespace Martial\Warez\Tests\Front\Controller;

use Martial\Warez\Front\Controller\SettingsController;
use Martial\Warez\Settings\SettingsUpdatingException;

class SettingsControllerTest extends ControllerTestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $settingsContainer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $userService;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject[]
     */
    public $settings;

    /**
     * @var string[]
     */
    public $views;

    /**
     * @var SettingsController
     */
    public $controller;

    public function testDisplaySections()
    {
        $this->getUser();
        $this->getAllSettings();

        $key = 0;

        foreach ($this->settings as $settings) {
            $settings
                ->expects($this->once())
                ->method('getView')
                ->with($this->equalTo($this->user))
                ->willReturn($this->views[$key]);

            $key++;
        }

        $this->render('@settings/display-sections.html.twig', [
            'templates' => $this->views
        ]);

        $this->controller->displaySections();
    }

    public function testUpdateSettingsWithoutErrors()
    {
        $this->updateSettings();
    }

    protected function defineDependencies()
    {
        $dependencies = parent::defineDependencies();
        $this->settingsContainer = $this->getMock('\Martial\Warez\Settings\SettingsContainerInterface');
        $this->userService = $this->getMock('\Martial\Warez\User\UserServiceInterface');

        $dependencies[] = $this->settingsContainer;
        $dependencies[] = $this->userService;

        return $dependencies;
    }

    protected function setUp()
    {
        parent::setUp();

        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->settings = [
            'freebox' => $this->getMock('\Martial\Warez\Settings\SettingsInterface'),
            't411' => $this->getMock('\Martial\Warez\Settings\SettingsInterface')
        ];

        $this->views = [
            'freebox view',
            't411 view'
        ];
    }

    /**
     * Returns the full qualified class name of the controller you want to test.
     *
     * @return string
     */
    protected function getControllerClassName()
    {
        return '\Martial\Warez\Front\Controller\SettingsController';
    }

    protected function getUser()
    {
        $userId = 123;
        $this->sessionGet(['user_id' => $userId]);

        $this
            ->userService
            ->expects($this->once())
            ->method('find')
            ->with($this->equalTo($userId))
            ->willReturn($this->user);
    }

    protected function getAllSettings()
    {
        $this
            ->settingsContainer
            ->expects($this->once())
            ->method('getAll')
            ->willReturn($this->settings);
    }

    protected function updateSettings($validForm = true)
    {
        $settingsKey = 'freebox';
        $freeboxSettings = $this->settings[$settingsKey];

        $this
            ->settingsContainer
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo($settingsKey))
            ->willReturn($freeboxSettings);

        $this->getUser();

        $updateResult = $validForm ? null : new SettingsUpdatingException();

        if (!$validForm) {
            $updateResult->setForm($this->form);
        }

        $freeboxSettings
            ->expects($this->once())
            ->method('updateSettings')
            ->with($this->equalTo($this->user), $this->request)
            ->willReturn($updateResult);

        if ($validForm) {
            $this->addFlash('success', 'Settings successfully update.');
            $this->generateUrl('settings', '/settings');
        } else {
            $this->addFlash('error', 'An error occurred.');

            foreach ($this->settings as $key => $settings) {
                $form = $key == $settingsKey ? $updateResult->getForm() : null;
                $settings
                    ->expects($this->once())
                    ->method('getView')
                    ->with($this->equalTo($this->user), $this->equalTo($form))
                    ->willReturn($this->views[$key]);
            }

            $this->render('@settings/display-sections.html.twig', [
                'templates' => $this->views
            ]);
        }

        $response = $this->controller->updateSettings($this->request, $settingsKey);

        if ($validForm) {
            $this->assertSame(302, $response->getStatusCode());
            $this->assertSame('/settings', $response->getTargetUrl());
        }
    }
}
