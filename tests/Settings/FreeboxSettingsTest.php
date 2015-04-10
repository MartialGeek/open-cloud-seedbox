<?php

namespace Martial\Warez\Tests\Settings;

use Martial\Warez\Settings\FreeboxSettings;
use Martial\Warez\Settings\SettingsUpdatingException;

class FreeboxSettingsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $twig;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $formFactory;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $user;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    public $repository;

    /**
     * @var FreeboxSettings
     */
    public $settings;

    public function testUpdateSettingsWithValidForm()
    {
        $this->updateSettings();
    }

    public function testUpdateSettingsShouldThrowAnExceptionWithAnInvalidForm()
    {
        $this->updateSettings(false);
    }

    public function testUpdateNonExistentSettings()
    {
        $this->updateSettings(true, false);
    }

    public function testGetViewWithoutExistingForm()
    {
        $this->getView();
    }

    public function testGetViewWithAnExistingForm()
    {
        $this->getView(true);
    }

    protected function updateSettings($isValidForm = true, $settingsAlreadyExists = true)
    {
        $currentSettings = $this->getFreeboxSettings();

        $request = $this
            ->getMockBuilder('\Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();

        $this->getSettings($currentSettings, $settingsAlreadyExists);
        $form = $this->getMock('\Symfony\Component\Form\FormInterface');
        $this->createForm($currentSettings, $form, $settingsAlreadyExists);

        $form
            ->expects($this->once())
            ->method('handleRequest')
            ->with($this->equalTo($request))
            ->willReturn($form);

        $form
            ->expects($this->once())
            ->method('isValid')
            ->willReturn($isValidForm);

        if ($isValidForm) {
            $entity = $settingsAlreadyExists ?
                $this->equalTo($currentSettings) :
                $this->isInstanceOf('\Martial\Warez\Settings\Entity\FreeboxSettings');

            $this
                ->em
                ->expects($this->once())
                ->method('persist')
                ->with($entity);

            $this
                ->em
                ->expects($this->once())
                ->method('flush');
        }

        try {
            $this->settings->updateSettings($this->user, $request);
        } catch (SettingsUpdatingException $e) {
            if ($isValidForm) {
                $this->fail('An exception should not be thrown with a valid form.');
            }

            $this->assertSame($form, $e->getForm());
        }
    }

    protected function getView($withForm = false)
    {
        $form = $this->getMock('\Symfony\Component\Form\FormInterface');

        if (!$withForm) {
            $currentSettings = $this->getFreeboxSettings();
            $this->getSettings($currentSettings);
            $this->createForm($currentSettings, $form);
        }

        $formView = $this
            ->getMockBuilder('\Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();

        $form
            ->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this
            ->twig
            ->expects($this->once())
            ->method('render')
            ->with(
                $this->equalTo('@settings/freebox.html.twig'),
                $this->equalTo(['form' => $formView])
            );

        if (!$withForm) {
            $this->settings->getView($this->user);
        } else {
            $this->settings->getView($this->user, $form);
        }
    }

    protected function setUp()
    {
        $this->twig = $this
            ->getMockBuilder('\Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formFactory = $this->getMock('\Symfony\Component\Form\FormFactoryInterface');
        $this->em = $this->getMock('\Doctrine\ORM\EntityManagerInterface');
        $this->user = $this
            ->getMockBuilder('\Martial\Warez\User\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->repository = $this
            ->getMockBuilder('\Doctrine\Common\Persistence\ObjectRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $this->settings = new FreeboxSettings($this->twig, $this->formFactory, $this->em);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFreeboxSettings()
    {
        return $this
            ->getMockBuilder('\Martial\Warez\Settings\Entity\FreeboxSettings')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getSettings(\PHPUnit_Framework_MockObject_MockObject $currentSettings, $isFound = true)
    {
        $userId = 123;
        $result = $isFound ? $currentSettings : null;
        $callsGetId = $isFound ? $this->once() : $this->exactly(2);

        $this
            ->em
            ->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('\Martial\Warez\Settings\Entity\FreeboxSettings'))
            ->willReturn($this->repository);

        $this
            ->user
            ->expects($callsGetId)
            ->method('getId')
            ->willReturn($userId);

        $this
            ->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with($this->equalTo(['userId' => $userId]))
            ->willReturn($result);
    }

    protected function createForm(
        \PHPUnit_Framework_MockObject_MockObject $currentSettings,
        \PHPUnit_Framework_MockObject_MockObject $form,
        $settingsAlreadyExists = true
    ) {
        $settings =$settingsAlreadyExists ?
            $this->equalTo($currentSettings) :
            $this->isInstanceOf('\Martial\Warez\Settings\Entity\FreeboxSettings');

        $this
            ->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(
                $this->isInstanceOf('\Martial\Warez\Form\FreeboxSettings'),
                $settings
            )
            ->willReturn($form);
    }
}
