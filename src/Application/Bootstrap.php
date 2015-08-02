<?php

namespace Martial\OpenCloudSeedbox\Application;

use Alchemy\Zippy\Zippy;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\FilesystemCache;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;
use GuzzleHttp\Client as GuzzleClient;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\Naming\IdenticalPropertyNamingStrategy;
use JMS\Serializer\SerializerBuilder;
use Martial\OpenCloudSeedbox\Command\Application as CLIApplication;
use Martial\OpenCloudSeedbox\Download\TransmissionManager;
use Martial\OpenCloudSeedbox\Filesystem\FileBrowser;
use Martial\OpenCloudSeedbox\Filesystem\ZipArchiver;
use Martial\OpenCloudSeedbox\Front\Controller\AbstractController;
use Martial\OpenCloudSeedbox\Front\Twig\FileExtension;
use Martial\OpenCloudSeedbox\Front\Twig\TransmissionExtension;
use Martial\OpenCloudSeedbox\MessageQueuing\Freebox\FreeboxMessageConsumer;
use Martial\OpenCloudSeedbox\MessageQueuing\Freebox\FreeboxMessageProducer;
use Martial\OpenCloudSeedbox\Security\AuthenticationProvider;
use Martial\OpenCloudSeedbox\Security\BlowfishHashPassword;
use Martial\OpenCloudSeedbox\Security\CookieTokenizer;
use Martial\OpenCloudSeedbox\Security\Firewall;
use Martial\OpenCloudSeedbox\Security\OpenSSLEncoder;
use Martial\OpenCloudSeedbox\Security\RememberMeListener;
use Martial\OpenCloudSeedbox\Settings\FreeboxSettings;
use Martial\OpenCloudSeedbox\Settings\FreeboxSettingsDataTransformer;
use Martial\OpenCloudSeedbox\Settings\TrackerSettings;
use Martial\T411\Api\Client;
use Martial\T411\Api\Data\DataTransformer;
use Martial\T411\Api\Search\QueryFactory;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxAuthenticationProvider;
use Martial\OpenCloudSeedbox\Upload\Freebox\FreeboxManager;
use Martial\OpenCloudSeedbox\Upload\UploadAdapterFactory;
use Martial\OpenCloudSeedbox\Upload\UploadUrlResolver;
use Martial\OpenCloudSeedbox\User\UserService;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Pikirasa\RSA;
use Silex\Application;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Symfony\Component\Filesystem\Filesystem;

class Bootstrap
{
    /**
     * @var Application
     */
    private $app;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $env;

    /**
     * @param Application $app
     * @param array $config
     */
    public function __construct(Application $app, array $config)
    {
        $this->app = $app;
        $this->config = $config;
        $this->env = $config['application']['env'];
        $this->registerServiceProviders();
        $this->registerServices();
        $this->configureApplication();
    }

    /**
     * @param Application $app
     * @param array $config
     * @param string $env
     * @return Bootstrap
     */
    public static function createApplication(Application $app, array $config, $env = 'prod')
    {
        return new self($app, $config, $env);
    }

    public function registerControllers(array $controllers)
    {
        $app = $this->app;

        foreach ($controllers as $serviceKey => $parameters) {
            $app[$serviceKey] = $app->share(function() use ($app, $parameters) {
                return $this->getControllerInstance($parameters['class'], $parameters);
            });
        }
    }

    protected function configureApplication()
    {
        $this->app['env'] = $this->env;
        $this->app['debug'] = $this->env == 'dev';

        foreach ($this->config['twig']['paths'] as $namespace => $paths) {
            $this->app['twig.loader.filesystem']->setPaths($paths, $namespace);
        }

        $this->app['dispatcher']->addListener('kernel.request', [
            $this->app['security.remember_me_listener'],
            'onKernelRequest'
        ], 10);

        $this->app['dispatcher']->addListener('kernel.request', [
            $this->app['security.firewall'],
            'onKernelRequest'
        ], 0);

        $this->app['dispatcher']->addSubscriber($this->app['application.exception_listener']);

        $this->app['twig']->addExtension(new TransmissionExtension());
        $this->app['twig']->addExtension(new FileExtension());

        AnnotationRegistry::registerAutoloadNamespace(
            'JMS\Serializer\Annotation',
            __DIR__ . '/../../vendor/jms/serializer/src'
        );
    }

    protected function registerServiceProviders()
    {
        $this->app
            ->register(new ServiceControllerServiceProvider())
            ->register(new TwigServiceProvider(), $this->config['twig'])
            ->register(new MonologServiceProvider(), $this->config['monolog'])
            ->register(new SessionServiceProvider())
            ->register(new FormServiceProvider())
            ->register(new ValidatorServiceProvider())
            ->register(new TranslationServiceProvider(), $this->config['translator'])
            ->register(new UrlGeneratorServiceProvider())
            ->register(new DoctrineServiceProvider(), $this->config['doctrine']['dbal'])
            ->register(new HttpFragmentServiceProvider());
    }

    protected function registerServices()
    {
        $app = $this->app;
        $config = $this->config;

        $app['doctrine.entity_manager'] = $app->share(function() {

            if ('dev' == $this->env) {
                $cache = new ArrayCache();
            } else {
                $cache = new FilesystemCache($this->config['doctrine']['orm']['cache_dir']);
            }

            $config = Setup::createAnnotationMetadataConfiguration(
                $this->config['doctrine']['orm']['paths'],
                $this->app['debug'],
                $this->config['doctrine']['orm']['proxy_dir'],
                $cache
            );

            return EntityManager::create($this->config['doctrine']['dbal']['db.options'], $config);
        });

        $app['filesystem'] = $app->share(function() {
            return new Filesystem();
        });
        
        $app['t411.api.http_client'] = $app->share(function() use ($config) {
            return new GuzzleClient([
                'base_url' => $config['tracker']['base_url']
            ]);
        });

        $app['t411.api.data.data_transformer'] = $app->share(function() {
            return new DataTransformer();
        });

        $app['t411.api.query_factory'] = $app->share(function() {
            return new QueryFactory();
        });

        $app['t411.api.client'] = $app->share(function() use ($app, $config) {
            return new Client(
                $app['t411.api.http_client'],
                $app['t411.api.data.data_transformer'],
                $app['filesystem'],
                $app['t411.api.query_factory'],
                $config['tracker']['client']
            );
        });

        $app['security.password_hash'] = $app->share(function() {
            return new BlowfishHashPassword();
        });

        $app['security.authentication_provider'] = $app->share(function() use ($app) {
            return new AuthenticationProvider($app['security.password_hash']);
        });

        $app['security.encoder'] = $app->share(function() use ($config) {
            $rsa = new RSA(
                $config['security']['certificate']['public'],
                $config['security']['certificate']['private']
            );

            return new OpenSSLEncoder($rsa);
        });

        $app['security.cookie.tokenizer'] = $app->share(function() use ($app) {
            return new CookieTokenizer($app['doctrine.entity_manager']);
        });

        $app['security.remember_me_listener'] = $app->share(function() use ($app) {
            return new RememberMeListener($app['security.cookie.tokenizer'], $app['session']);
        });

        $app['user.service'] = $app->share(function() use ($app) {
            return new UserService(
                $app['doctrine.entity_manager'],
                $app['security.authentication_provider'],
                $app['security.password_hash'],
                $app['settings.tracker']
            );
        });

        $app['settings.tracker'] = $app->share(function() use ($app) {
            return new TrackerSettings($app['security.encoder'], $app['doctrine.entity_manager']);
        });

        $app['settings.freebox'] = $app->share(function() use ($app) {
            return new FreeboxSettings($app['doctrine.entity_manager']);
        });

        $app['settings.freebox.data_transformer'] = $app->share(function() {
            return new FreeboxSettingsDataTransformer();
        });

        $app['security.firewall'] = $app->share(function() use ($app) {
            return new Firewall($app['session'], $app['url_generator']);
        });

        $app['upload.http_client'] = $app->share(function() use ($app) {
            return new GuzzleClient();
        });

        $app['upload.url_resolver'] = $app->share(function() use ($config) {
            $revolver = new UploadUrlResolver();
            $revolver->setHost($config['application']['host']);

            return $revolver;
        });

        $app['upload.adapter_factory'] = $app->share(function() use ($app, $config) {
            return new UploadAdapterFactory($app['upload.http_client'], $app['upload.url_resolver']);
        });

        $app['upload.freebox_adapter'] = $app->share(function() use ($app, $config) {
            return $app['upload.adapter_factory']->get(
                $config['upload']['adapter']
            );
        });

        $app['upload.freebox_authentication_provider'] = $app->share(function() use ($app) {
            return new FreeboxAuthenticationProvider($app['upload.http_client']);
        });

        $app['upload.freebox.manager'] = $app->share(function() use ($app, $config) {
            $manager = new FreeboxManager(
                $app['upload.freebox_adapter'],
                $app['upload.freebox_authentication_provider'],
                $app['settings.freebox'],
                $app['settings.freebox.data_transformer'],
                new GuzzleClient(),
                $app['url_generator'],
                $app['filesystem.archiver.zip'],
                $app['filesystem'],
                $app['message_queuing.freebox.producer']
            );

            $manager->setArchivePath($config['upload']['archive_path']);
            $manager->setDownloadDir($config['download_dir']);

            return $manager;
        });

        $app['transmission.http_client'] = $app->share(function() use ($config) {
            $url = sprintf(
                'http://%s:%d',
                $config['transmission']['host'],
                $config['transmission']['port']
            );

            return new GuzzleClient([
                'base_url' => $url
            ]);
        });

        $app['transmission.manager'] = $app->share(function() use ($app, $config) {
            return new TransmissionManager($app['transmission.http_client'], $config['transmission']);
        });

        $app['filesystem.archiver.zip'] = $app->share(function() use ($app) {
            return new ZipArchiver($app['zippy']);
        });

        $app['filesystem.file_browser'] = $app->share(function() use ($app, $config) {
            return new FileBrowser($config['file_browser']['root_path']);
        });

        $app['zippy'] = function() {
            return Zippy::load();
        };

        $app['message_queuing.freebox.connection'] = $app->share(function() use ($app, $config) {
            $messageConfig = $config['message_queuing']['freebox'];

            return new AMQPStreamConnection(
                $messageConfig['connection']['host'],
                $messageConfig['connection']['port'],
                $messageConfig['connection']['user'],
                $messageConfig['connection']['password'],
                $messageConfig['connection']['vhost']
            );
        });

        $app['message_queuing.freebox.consumer'] = $app->share(function() use ($app) {
            $consumer = new FreeboxMessageConsumer($app['message_queuing.freebox.connection']);
            $consumer->setLogger($app['logger']);
            $consumer->setFreeboxManager($app['upload.freebox.manager']);
            $consumer->setUserService($app['user.service']);

            return $consumer;
        });

        $app['message_queuing.freebox.producer'] = $app->share(function() use ($app) {
            $consumer = new FreeboxMessageProducer($app['message_queuing.freebox.connection']);
            $consumer->setLogger($app['logger']);

            return $consumer;
        });

        $app['console'] = $app->share(function() use ($app) {
            return new CLIApplication($app, $this->config);
        });

        $app['serializer'] = $app->share(function() use ($app, $config) {
            $jsonSerializer = new JsonSerializationVisitor(new IdenticalPropertyNamingStrategy());
            $jsonSerializer->setOptions(JSON_UNESCAPED_SLASHES);

            return SerializerBuilder::create()
                ->setSerializationVisitor('json', $jsonSerializer)
                ->setCacheDir($config['serializer']['cache_dir'])
                ->setDebug($app['debug'])
                ->build();
        });

        $app['application.exception_listener'] = $app->share(function() use ($app) {
            return new ExceptionHandler(
                $this->getControllerInstance('\Martial\OpenCloudSeedbox\Front\Controller\ErrorController'),
                $app['debug']
            );
        });
    }

    /**
     * Returns an instance of a controller class.
     *
     * @param string $controllerClass
     * @param array $parameters
     * @return AbstractController
     */
    protected function getControllerInstance($controllerClass, array $parameters = [])
    {
        $reflectionClass = new \ReflectionClass($controllerClass);
        $dependencies = isset($parameters['dependencies']) ? $parameters['dependencies'] : [];

        $defaultDependencies = [];

        if ($reflectionClass->isSubclassOf('\Martial\OpenCloudSeedbox\Front\Controller\AbstractController')) {
            $defaultDependencies = [
                $this->app['twig'],
                $this->app['form.factory'],
                $this->app['session'],
                $this->app['url_generator'],
                $this->app['user.service']
            ];
        }

        $controller = $reflectionClass->newInstanceArgs(array_merge($defaultDependencies, $dependencies));

        if (isset($parameters['calls'])) {
            foreach ($parameters['calls'] as $method => $argument) {
                call_user_func_array([$controller, $method], [$argument]);
            }
        }

        return $controller;
    }
}
