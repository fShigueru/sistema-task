<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Symfonyx\Silex\ElasticsearchServiceProvider;

define('PATH_CONFIG_YML', __DIR__ . '/../config/parameters.yml');

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());
$app['twig'] = $app->extend('twig', function ($twig, $app) {
    // add custom globals, filters, tags, ...
    return $twig;
});

$app['debug'] = true;

$app->before(function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $app['twig']->addGlobal('current_route', $request->get('_route') );
});

$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.version' => 'v1',
    'assets.version_format' => '%s?version=%s',
    'assets.named_packages' => array(
        'css_site' => array('version' => 'css2', 'base_path' => '/css'),
        'js_site' => array('version' => 'js2', 'base_path' => '/js'),
        'css' => array('version' => 'css2', 'base_path' => '/assets/css'),
        'vendor' => array('version' => 'vendor', 'base_path' => '/assets/vendor'),
        'js' => array('version' => 'js', 'base_path' =>'/assets/scripts'),
        'files' => array('base_path' =>'/upload'),
        'img' => array('version' => 'img', 'base_path' =>'/assets/img'),
        'images' => array('base_urls' => array('http://localhost:8087/web/')),
    ),
));
$app->register(new Rpodwika\Silex\YamlConfigServiceProvider(PATH_CONFIG_YML));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\LocaleServiceProvider());
$app->register(new Silex\Provider\TranslationServiceProvider(), array(
    'locale_fallbacks' => array('pt_BR'),
    'locale' => 'pt_BR'
));

$app['translator.domains'] = array(
    'validators' => array(
        'pt_BR' => array(
            'This value should not be blank.' => 'Valor não pode ser enviado em branco',
        ),
    ),
);

$app['service.task'] = $app->factory(function ($app) {
    return new \services\Task($app['db']);
});

$app['service.user'] = $app->factory(function ($app) {
    return new \services\User($app['db']);
});

$app['service.anexo'] = $app->factory(function ($app) {
    return new \services\Anexo($app['db']);
});

$app['elasticsearch.params'] = [
    'hosts' => [
        $app['config']['host']['local']
    ]
];
$app->register(new ElasticsearchServiceProvider('elasticsearch'));

$app->register(new Gigablah\Silex\OAuth\OAuthServiceProvider(), array(
    'oauth.services' => array(
        'Google' => array(
            'key' => $app['config']['google']['api_key'],
            'secret' => $app['config']['google']['api_secret'],
            'scope' => array(
                'https://www.googleapis.com/auth/userinfo.email',
                'https://www.googleapis.com/auth/userinfo.profile'
            ),
            'user_endpoint' => 'https://www.googleapis.com/oauth2/v1/userinfo'
        )
    )
));

// Provides CSRF token generation
// You will have to include symfony/form in your composer.json
$app->register(new Silex\Provider\FormServiceProvider());

// Provides session storage
$app->register(new Silex\Provider\SessionServiceProvider(), array(
    'session.storage.save_path' => '/tmp'
));

$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'default' => array(
            'pattern' => '^/',
            'anonymous' => true,
            'oauth' => array(
                //'login_path' => '/auth/{service}',
                //'callback_path' => '/auth/{service}/callback',
                //'check_path' => '/auth/{service}/check',
                'failure_path' => '/login',
                'with_csrf' => true
            ),
            'logout' => array(
                'logout_path' => '/logout',
                'with_csrf' => true
            ),
            // OAuthInMemoryUserProvider returns a StubUser and is intended only for testing.
            // Replace this with your own UserProvider and User class.
            'users' => new Gigablah\Silex\OAuth\Security\User\Provider\OAuthInMemoryUserProvider()
        )
    ),
    'security.access_rules' => array(
        array('^/auth', 'ROLE_USER')
    )
));

$app->before(function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
    if (isset($app['security.token_storage'])) {
        $token = $app['security.token_storage']->getToken();
    } else {
        $token = $app['security']->getToken();
    }

    $app['user'] = null;

    if ($token && !$app['security.trust_resolver']->isAnonymous($token)) {
        $app['user'] = $token->getUser();
        $user = $app['service.user']->findOneByEmail($app['user']->getEmail());
        if (empty($user)) {
            $datetime = new \DateTime();
            $data = [
                'nome' => $app['user']->getUsername(),
                'email' => $app['user']->getEmail(),
                'created_at' => $datetime->format('Y-m-d h:m:s'),
                'updated_at' => $datetime->format('Y-m-d h:m:s')
            ];
            $app['service.user']->insert($data);
        }
    }
});

$app->get('/login', function (Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $services = array_keys($app['oauth.services']);

    if (!empty($app['user'])) {
        return $app->redirect('/task/');
    }

    return $app['twig']->render('login.html.twig', array(
        'login_paths' => $app['oauth.login_paths'],
        'logout_path' => $app['url_generator']->generate('logout', array(
            '_csrf_token' => $app['oauth.csrf_token']('logout')
        )),
        'error' => $app['security.last_error']($request)
    ));
})->bind('login');

$app->match('/logout', function () {})->bind('logout');

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => [
        'driver'   => $app['config']['database']['driver'],
        'host'     => $app['config']['database']['host'],
        'dbname'   => $app['config']['database']['dbname'],
        'user'     => $app['config']['database']['user'],
        'password' => $app['config']['database']['password']
    ],
));

$app->register(
    new \Kurl\Silex\Provider\DoctrineMigrationsProvider(),
    array(
        'migrations.directory' => __DIR__ . '/../db/migrations',
        'migrations.name' => 'Acme Migrations',
        'migrations.namespace' => 'Acme\Migrations',
        'migrations.table_name' => 'acme_migrations',
    )
);

return $app;
