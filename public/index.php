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
use GuzzleHttp\Client;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\PhpRenderer;

session_start();

// Подключение к БД и инициализация flash сообщений
$pdo = getPDO();
$flash = new Messages();

// Инициализация репозиториев и вспомогательных классов
$urlRepository = new UrlRepository($pdo);
$urlCheckRepository = new UrlCheckRepository($pdo);
$checkViewFormatter = new CheckViewFormatter();

// Инициализация сервисов
$urlService = new UrlService();
$seoAnalyzer = new SeoAnalyzer();
$httpClient = new Client([
    'timeout' => 15,
    'allow_redirects' => true,
]);
$urlCheckService = new UrlCheckService($httpClient, $seoAnalyzer);

// Создание приложения
$app = AppFactory::create();

// Инициализация рендерера шаблонов
$renderer = new PhpRenderer(__DIR__ . '/../templates');
$renderer->setLayout('layout.phtml');

// Подготовка общих данных для шаблонов
$routeParser = $app->getRouteCollector()->getRouteParser();
$webResponder = new WebResponder($renderer, $flash, $routeParser);

// Сервис подготовки данных для страниц URL
$urlPageService = new UrlPageService(
    $urlRepository,
    $urlCheckRepository,
    $checkViewFormatter
);

// Сервис проверки URL
$urlCheckPageService = new UrlCheckPageService(
    $urlRepository,
    $urlCheckRepository,
    $urlCheckService
);

// Настройка кастомной обработки ошибок
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

// Обработчик 404 Not Found
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use (
        $app,
        $renderer,
        $flash,
        $routeParser
    ) {
        $response = $app->getResponseFactory()->createResponse(404);

        return $renderer->render(
            $response,
            'errors/404.phtml',
            [
                'flash' => $flash->getMessage('success')[0] ?? null,
                'errorFlash' => $flash->getMessage('error')[0] ?? null,
                'routeParser' => $routeParser,
            ]
        );
    }
);

// Обработчик остальных ошибок
$errorMiddleware->setDefaultErrorHandler(
    function (
        ServerRequestInterface $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use (
        $app,
        $renderer,
        $flash,
        $routeParser
    ) {
        $response = $app->getResponseFactory()->createResponse(500);

        return $renderer->render(
            $response,
            'errors/500.phtml',
            [
                'flash' => $flash->getMessage('success')[0] ?? null,
                'errorFlash' => $flash->getMessage('error')[0] ?? null,
                'routeParser' => $routeParser,
            ]
        );
    }
);

// Инициализация контроллеров
$homeController = new HomeController($webResponder);
$urlController = new UrlController(
    $webResponder,
    $urlService,
    $urlPageService
);
$urlCheckController = new UrlCheckController(
    $webResponder,
    $urlCheckPageService
);

// Регистрация маршрутов
$registerRoutes = require __DIR__ . '/../src/routes.php';
$registerRoutes(
    $app,
    $homeController,
    $urlController,
    $urlCheckController
);

$app->run();
