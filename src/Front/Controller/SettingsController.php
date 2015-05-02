<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Form\FreeboxSettings as FreeboxSettingsType;
use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\FreeboxSettings;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SettingsController extends AbstractController
{
    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @var FreeboxSettings
     */
    private $freeboxSettings;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param UserServiceInterface $userService
     * @param FreeboxSettings $freeboxSettings
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService,
        FreeboxSettings $freeboxSettings
    ) {
        parent::__construct($twig, $formFactory, $session, $urlGenerator);
        $this->userService = $userService;
        $this->freeboxSettings = $freeboxSettings;
    }

    public function index()
    {
        return $this->displayFreeboxSettings();
    }

    public function displayFreeboxSettings()
    {
        $settings = $this->getFreeboxSettings();
        $form = $this->getFreeboxForm($settings);

        return $this->twig->render('@settings/freebox.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function updateFreeboxSettings(Request $request)
    {
        $settings = $this->getFreeboxSettings();
        $form = $this->getFreeboxForm($settings);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->freeboxSettings->updateSettings($settings, $this->getUser());
            $this->session->getFlashBag()->add('success', 'Your Freebox settings was successfully updated.');
        }

        $this->session->getFlashBag()->add('error', 'An error occurred during the Freebox settings update.');

        return $this->twig->render('@settings/freebox.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param FreeboxSettingsEntity $settings
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function getFreeboxForm(FreeboxSettingsEntity $settings)
    {
        return $form = $this->formFactory->create(new FreeboxSettingsType(), $settings);
    }

    /**
     * @return FreeboxSettingsEntity
     */
    private function getFreeboxSettings()
    {
        return $this->freeboxSettings->getSettings($this->getUser());
    }

    /**
     * @return User
     */
    private function getUser()
    {
        if (is_null($this->currentUser)) {
            $this->currentUser = $this->userService->find($this->session->get('user_id'));
        }

        return $this->currentUser;
    }
}
