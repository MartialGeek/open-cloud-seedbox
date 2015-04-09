<?php

namespace Martial\Warez\Settings;

use Symfony\Component\Form\FormInterface;

class SettingsUpdatingException extends \Exception
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @param FormInterface $form
     */
    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }
}
