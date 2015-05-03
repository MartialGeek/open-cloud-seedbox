<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Form\FreeboxSettings as FreeboxSettingsType;
use Martial\Warez\Form\TrackerSettings as TrackerSettingsType;
use Martial\Warez\Settings\Entity\FreeboxSettingsEntity;
use Martial\Warez\Settings\Entity\TrackerSettingsEntity;
use Martial\Warez\Settings\FreeboxSettings;
use Martial\Warez\Settings\TrackerSettings;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     * @var TrackerSettings
     */
    private $trackerSettings;

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
     * @param TrackerSettings $trackerSettings
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        UserServiceInterface $userService,
        FreeboxSettings $freeboxSettings,
        TrackerSettings $trackerSettings
    ) {
        parent::__construct($twig, $formFactory, $session, $urlGenerator);
        $this->userService = $userService;
        $this->freeboxSettings = $freeboxSettings;
        $this->trackerSettings = $trackerSettings;
    }

    public function index()
    {
        return $this->displayFreeboxSettings();
    }

    public function displayFreeboxSettings()
    {
        $form = $this->getFreeboxForm($this->getFreeboxSettings());

        return $this->twig->render('@settings/freebox.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function updateFreeboxSettings(Request $request)
    {
        $settings = new FreeboxSettingsEntity();
        $form = $this->getFreeboxForm($settings);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->freeboxSettings->updateSettings($settings, $this->getUser());
            $this->session->getFlashBag()->add('success', 'Your Freebox settings was successfully updated.');

            return new RedirectResponse($this->urlGenerator->generate('settings_freebox'));
        }

        $this->session->getFlashBag()->add('error', 'An error occurred during the Freebox settings update.');

        return $this->twig->render('@settings/freebox.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function displayTrackerSettings()
    {
        $form = $this->getTrackerForm($this->getTrackerSettings());

        return $this->twig->render('@settings/tracker.html.twig', [
            'form' => $form->createView()
        ]);
    }

    public function updateTrackerSettings(Request $request)
    {
        $settings = new TrackerSettingsEntity();
        $form = $this->getTrackerForm($settings);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->trackerSettings->updateSettings($settings, $this->getUser());
            $this->session->getFlashBag()->add('success', 'Your tracker settings was successfully updated.');

            return new RedirectResponse($this->urlGenerator->generate('settings_tracker'));
        }

        $this->session->getFlashBag()->add('error', 'An error occurred during the tracker settings update.');

        return $this->twig->render('@settings/tracker.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @param FreeboxSettingsEntity $settings
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function getFreeboxForm(FreeboxSettingsEntity $settings)
    {
        return $this->formFactory->create(new FreeboxSettingsType(), $settings);
    }

    /**
     * @return FreeboxSettingsEntity
     */
    private function getFreeboxSettings()
    {
        return $this->freeboxSettings->getSettings($this->getUser());
    }

    /**
     * @param TrackerSettingsEntity $settings
     * @return \Symfony\Component\Form\Form|\Symfony\Component\Form\FormInterface
     */
    private function getTrackerForm(TrackerSettingsEntity $settings)
    {
        return $this->formFactory->create(new TrackerSettingsType(), $settings);
    }

    /**
     * @return TrackerSettingsEntity
     */
    private function getTrackerSettings()
    {
        return $this->trackerSettings->getSettings($this->getUser());
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
