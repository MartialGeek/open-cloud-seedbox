<?php

namespace Martial\Warez\Tests\Form;

use Martial\Warez\Form\Login;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoginTest extends \PHPUnit_Framework_TestCase
{
    public function testLoginForm()
    {
        $formBuilder = $this->getMock('\Symfony\Component\Form\FormBuilderInterface');
        $form = new Login();

        $formBuilder
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
            ->will($this->returnValue($formBuilder));

        $form->buildForm($formBuilder, []);
        $this->assertSame('login', $form->getName());
    }
}
