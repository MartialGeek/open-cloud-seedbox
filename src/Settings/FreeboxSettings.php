<?php

namespace Martial\Warez\Settings;

use Doctrine\ORM\EntityManagerInterface;
use Martial\Warez\Form\FreeboxSettings as FreeboxSettingsForm;
use Martial\Warez\Settings\Entity\FreeboxSettings as FreeboxSettingsEntity;
use Martial\Warez\User\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;

class FreeboxSettings implements SettingsInterface
{
    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @param \Twig_Environment $twig
     * @param FormFactoryInterface $formFactory
     * @param EntityManagerInterface $em
     */
    public function __construct(\Twig_Environment $twig, FormFactoryInterface $formFactory, EntityManagerInterface $em)
    {
        $this->twig = $twig;
        $this->formFactory = $formFactory;
        $this->em = $em;
    }

    /**
     * Updates the settings.
     *
     * @param User $user
     * @param mixed $settings
     * @throws SettingsUpdatingException
     */
    public function updateSettings(User $user, $settings)
    {
        $currentSettings = $this->getSettings($user);

        if (is_null($currentSettings)) {
            $currentSettings = new FreeboxSettingsEntity();
            $currentSettings->setUserId($user->getId());
        }

        $form = $this->formFactory->create(new FreeboxSettingsForm(), $currentSettings);
        $form->handleRequest($settings);

        if (!$form->isValid()) {
            $e = new SettingsUpdatingException();
            $e->setForm($form);
            throw $e;
        }

        $this->em->persist($currentSettings);
        $this->em->flush();
    }

    /**
     * Retrieves the settings.
     *
     * @param User $user
     * @return mixed
     */
    public function getSettings(User $user)
    {
        return $this
            ->em
            ->getRepository('\Martial\Warez\Settings\Entity\FreeboxSettings')
            ->findOneBy(['userId' => $user->getId()]);
    }

    /**
     * Renders the view for managing the settings.
     *
     * @param User $user
     * @param FormInterface $form
     * @return string
     */
    public function getView(User $user, FormInterface $form = null)
    {
        if (is_null($form)) {
            $settings = $this->getSettings($user);
            $form = $this->formFactory->create(new FreeboxSettingsForm(), $settings);
        }

        return $this->twig->render('@settings/freebox.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
