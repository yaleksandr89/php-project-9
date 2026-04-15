<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';

use App\Controller\HomeController;
use App\Controller\UrlCheckController;
use App\Controller\UrlController;
use App\Repository\UrlCheckRepository;
use App\Repository\UrlRepository;
use App\Service\SeoAnalyzer;
use App\Service\UrlCheckPageService;
use App\Service\UrlCheckService;
use App\Service\UrlPageService;
use App\Service\UrlService;
use App\Support\CheckViewFormatter;
use App\Support\WebResponder;
use DI\Container;
use GuzzleHttp\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Interfaces\RouteParserInterface;
use Slim\Views\PhpRenderer;

session_start();

$container = new Container();

// >>> Базовая инфраструктура
// БД
$container->set(\PDO::class, function (): \PDO {
    return getPDO();
});
// Флеш сообщения
$container->set(Messages::class, function (): Messages {
    return new Messages();
});
// Рендер шаблонов
$container->set(PhpRenderer::class, function (): PhpRenderer {
    $renderer = new PhpRenderer(__DIR__ . '/../templates');
    $renderer->setLayout('layout.phtml');

    return $renderer;
});
// Базовая инфраструктура <<<

// Передаём контейнер в инициализируем приложение
AppFactory::setContainer($container);
$app = AppFactory::create();

// Роутинг
$container->set(RouteParserInterface::class, function () use ($app): RouteParserInterface {
    return $app->getRouteCollector()->getRouteParser();
});

// >>> Репозитории
$container->set(UrlRepository::class, function (ContainerInterface $container): UrlRepository {
    return new UrlRepository($container->get(\PDO::class));
});

$container->set(UrlCheckRepository::class, function (ContainerInterface $container): UrlCheckRepository {
    return new UrlCheckRepository($container->get(\PDO::class));
});
// Репозитории <<<

// >>> Вспомогательные классы
$container->set(CheckViewFormatter::class, function (): CheckViewFormatter {
    return new CheckViewFormatter();
});

$container->set(WebResponder::class, function (ContainerInterface $container): WebResponder {
    return new WebResponder(
        $container->get(PhpRenderer::class),
        $container->get(Messages::class),
        $container->get(RouteParserInterface::class)
    );
});
// Вспомогательные классы <<<

// >>> Сервисы
$container->set(UrlService::class, function (): UrlService {
    return new UrlService();
});

$container->set(SeoAnalyzer::class, function (): SeoAnalyzer {
    return new SeoAnalyzer();
});

$container->set(Client::class, function (): Client {
    return new Client([
        'timeout' => 15,
        'allow_redirects' => true,
    ]);
});

$container->set(UrlCheckService::class, function (ContainerInterface $container): UrlCheckService {
    return new UrlCheckService(
        $container->get(Client::class),
        $container->get(SeoAnalyzer::class)
    );
});

$container->set(UrlPageService::class, function (ContainerInterface $container): UrlPageService {
    return new UrlPageService(
        $container->get(UrlRepository::class),
        $container->get(UrlCheckRepository::class),
        $container->get(CheckViewFormatter::class)
    );
});

$container->set(UrlCheckPageService::class, function (ContainerInterface $container): UrlCheckPageService {
    return new UrlCheckPageService(
        $container->get(UrlRepository::class),
        $container->get(UrlCheckRepository::class),
        $container->get(UrlCheckService::class)
    );
});
// Сервисы <<<

// >>> Контроллеры
$container->set(HomeController::class, function (ContainerInterface $container): HomeController {
    return new HomeController($container->get(WebResponder::class));
});

$container->set(UrlController::class, function (ContainerInterface $container): UrlController {
    return new UrlController(
        $container->get(WebResponder::class),
        $container->get(UrlService::class),
        $container->get(UrlPageService::class)
    );
});

$container->set(UrlCheckController::class, function (ContainerInterface $container): UrlCheckController {
    return new UrlCheckController(
        $container->get(WebResponder::class),
        $container->get(UrlCheckPageService::class)
    );
});
// Контроллеры <<<

// >>> Обработка ошибок
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use (
        $app,
        $container
    ) {
        $response = $app->getResponseFactory()->createResponse(404);
        $flash = $container->get(Messages::class);

        return $container->get(PhpRenderer::class)->render(
            $response,
            'errors/404.phtml',
            [
                'flash' => $flash->getMessage('success')[0] ?? null,
                'errorFlash' => $flash->getMessage('error')[0] ?? null,
                'routeParser' => $container->get(RouteParserInterface::class),
            ]
        );
    }
);
$errorMiddleware->setDefaultErrorHandler(
    function (
        ServerRequestInterface $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use (
        $app,
        $container
    ) {
        $response = $app->getResponseFactory()->createResponse(500);
        $flash = $container->get(Messages::class);

        return $container->get(PhpRenderer::class)->render(
            $response,
            'errors/500.phtml',
            [
                'flash' => $flash->getMessage('success')[0] ?? null,
                'errorFlash' => $flash->getMessage('error')[0] ?? null,
                'routeParser' => $container->get(RouteParserInterface::class),
            ]
        );
    }
);
// Обработка ошибок <<<

// Регистрация маршрутов
$registerRoutes = require __DIR__ . '/../src/routes.php';
$registerRoutes($app, $container);

$app->run();
