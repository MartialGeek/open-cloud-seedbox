<?php

namespace Martial\Warez\Tests\Form;

use Martial\Warez\Form\Login;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginTest extends FormTestCase
{
    /**
     * @var Login
     */
    public $form;

    public function testBuildForm()
    {
        $this
            ->formBuilder
            ->expects($this->exactly(2))
            ->method('add')
            ->withConsecutive(
                [
                    $this->equalTo('email'),
                    $this->equalTo('email'),
                    $this->equalTo(['constraints' => [
                        new NotBlank(),
                        new Email()
                    ]])
                ],
                [
                    $this->equalTo('password'),
                    $this->equalTo('password'),
                    $this->equalTo(['constraints' => [
                        new NotBlank(),
                        new Length(['min' => 8])
                    ]])
                ]
            )
            ->will($this->returnValue($this->formBuilder));

        $this->getForm()->buildForm($this->formBuilder, []);
    }

    /**
     * Returns the name of the form.
     *
     * @return string
     */
    protected function getFormName()
    {
        return 'login';
    }

    /**
     * Returns an instance of your form.
     *
     * @return FormTypeInterface
     */
    protected function getForm()
    {
        if (is_null($this->form)) {
            $this->form = new Login();
        }

        return $this->form;
    }
}
