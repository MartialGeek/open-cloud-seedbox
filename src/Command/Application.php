<?php

namespace Martial\Warez\Command;

use Silex\Application as SilexApplication;
use Symfony\Component\Console\Application as BaseApplication;

class Application extends BaseApplication
{
    /**
     * @var SilexApplication
     */
    private $app;

    public function __construct(SilexApplication $application)
    {
        $this->app = $application;
        parent::__construct('Warez console', '0.0.0');
    }
}
