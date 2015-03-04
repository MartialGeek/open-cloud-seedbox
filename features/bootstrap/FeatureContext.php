<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Then I should be logged in
     */
    public function iShouldBeLoggedIn()
    {
        $profile = $this->getSession()->getPage()->find('css', '#profile-status');
        PHPUnit_Framework_Assert::assertSame('MartialGeek', $profile->getText());
    }
}
