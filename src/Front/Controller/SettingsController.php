<?php

namespace Martial\OpenCloudSeedbox\Front\Controller;

use Martial\OpenCloudSeedbox\Form\FreeboxSettings as FreeboxSettingsType;
use Martial\OpenCloudSeedbox\Form\TrackerSettings as TrackerSettingsType;
use Martial\OpenCloudSeedbox\Settings\Entity\FreeboxSettingsEntity;
use Martial\OpenCloudSeedbox\Settings\Entity\TrackerSettingsEntity;
use Martial\OpenCloudSeedbox\Settings\FreeboxSettings;
use Martial\OpenCloudSeedbox\Settings\TrackerSettings;
use Martial\OpenCloudSeedbox\User\UserServiceInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SettingsController extends AbstractController
{
    /**
     * @var FreeboxSettings
     */
    private $freeboxSettings;

    /**
     * @var TrackerSettings
     */
    private $trackerSettings;

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
        parent::__construct($twig, $formFactory, $session, $urlGenerator, $userService);
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
        return $this->formFactory->create(FreeboxSettingsType::class, $settings);
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
        return $this->formFactory->create(TrackerSettingsType::class, $settings);
    }

    /**
     * @return TrackerSettingsEntity
     */
    private function getTrackerSettings()
    {
        return $this->trackerSettings->getSettings($this->getUser());
    }
}
