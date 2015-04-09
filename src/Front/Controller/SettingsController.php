<?php

namespace Martial\Warez\Front\Controller;

use Martial\Warez\Settings\SettingsContainerInterface;
use Martial\Warez\Settings\SettingsUpdatingException;
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
     * @var SettingsContainerInterface
     */
    private $settingsContainer;

    /**
     * @var UserServiceInterface
     */
    private $userService;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param Session $session
     * @param UrlGeneratorInterface $urlGenerator
     * @param SettingsContainerInterface $settingsContainer
     * @param UserServiceInterface $userService
     */
    public function __construct(
        \Twig_Environment $twig,
        FormFactoryInterface $formFactory,
        Session $session,
        UrlGeneratorInterface $urlGenerator,
        SettingsContainerInterface $settingsContainer,
        UserServiceInterface $userService
    ) {
        $this->settingsContainer = $settingsContainer;
        $this->userService = $userService;
        parent::__construct($twig, $formFactory, $session, $urlGenerator);
    }

    public function displaySections()
    {
        $templates = [];
        $user = $this->getUser();

        foreach ($this->settingsContainer->getAll() as $setting) {
            $templates[] = $setting->getView($user);
        }

        return $this->renderSections($templates);
    }

    public function updateSettings(Request $request, $key)
    {
        $settings = $this->settingsContainer->get($key);
        $user = $this->getUser();

        try {
            $settings->updateSettings($user, $request);
        } catch (SettingsUpdatingException $e) {
            $this->session->getFlashBag()->add('error', 'An error occurred.');
            $templates = [];

            foreach ($this->settingsContainer->getAll() as $settingsKey => $service) {
                $form = $settingsKey == $key ? $e->getForm() : null;
                $templates[] = $service->getView($user, $form);
            }

            return $this->renderSections($templates);
        }

        $this->session->getFlashBag()->add('success', 'Settings successfully update.');

        return new RedirectResponse($this->urlGenerator->generate('settings'));
    }

    /**
     * @param array $templates
     * @return string
     */
    protected function renderSections(array $templates)
    {
        return $this->twig->render('@settings/display-sections.html.twig', [
            'templates' => $templates
        ]);
    }

    /**
     * @return User
     */
    protected function getUser()
    {
        return $this->userService->find($this->session->get('user_id'));
    }
}
