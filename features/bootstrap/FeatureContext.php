<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Doctrine\ORM\EntityManager;
use Martial\Warez\Application\Bootstrap;
use Martial\Warez\User\Entity\User;
use Martial\Warez\User\Repository\UserRepositoryInterface;
use Martial\Warez\User\UserService;
use Silex\Application;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    const TEST_USER_NAME = 'Behat';
    const TEST_USER_EMAIL = 'behat@seedbox.io';
    const TEST_USER_PASSWORD = 'behatroxx';

    /**
     * @var User
     */
    protected static $testUser;

    /**
     * @var Application
     */
    protected static $app;

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
        PHPUnit_Framework_Assert::assertSame(self::TEST_USER_NAME, $profile->getText());
    }

    /**
     * @BeforeFeature @authentication
     */
    public static function createTestAccount()
    {
        self::$app = self::bootstrapApplication();

        /**
         * @var UserService $userService
         */
        $userService = self::$app['user.service'];
        self::$testUser = new User();
        self::$testUser
            ->setUsername(self::TEST_USER_NAME)
            ->setEmail(self::TEST_USER_EMAIL)
            ->setPassword(self::TEST_USER_PASSWORD);

        $userService->register(self::$testUser);
    }

    /**
     * @AfterFeature @authentication
     */
    public static function deleteTestAccount()
    {
        /**
         * @var EntityManager $em
         */
        $em = self::$app['doctrine.entity_manager'];

        /**
         * @var UserRepositoryInterface $repo
         */
        $repo = $em->getRepository('\Martial\Warez\User\Entity\User');
        $user = $repo->findUserByEmail(self::$testUser->getEmail());
        $em->remove($user);
        $em->flush();
    }

    /**
     * @return Application
     */
    protected static function bootstrapApplication()
    {
        $app = new Application();
        $config = require __DIR__ . '/../../config/app.php';
        Bootstrap::createApplication($app, $config);

        return $app;
    }
}
