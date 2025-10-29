<?php

use Slim\Views\Twig;
use Eben\TwigTicketApp\Controllers\AuthController;
use Eben\TwigTicketApp\Controllers\TicketController;

return function ($app) {

    // ======================
    // LANDING PAGE
    // ======================
    $app->get('/', function ($request, $response) {
        $view = Twig::fromRequest($request);
        return $view->render($response, 'landing.twig');
    });

    // ======================
    // AUTH ROUTES
    // ======================
    $container = $app->getContainer();
    $container->set(AuthController::class, function ($c) {
        return new AuthController($c->get('view'));
    });

    // Login
    $app->get('/auth/login', [AuthController::class, 'showLogin']);
    $app->post('/auth/login', [AuthController::class, 'login']);

    // ✅ Signup (corrected from /auth/register → /auth/signup)
    $app->get('/auth/signup', [AuthController::class, 'showSignup']);
    $app->post('/auth/signup', [AuthController::class, 'signup']);

    // Logout
    $app->get('/auth/logout', [AuthController::class, 'logout']);


    // ======================
    // DASHBOARD
    // ======================
    $app->get('/dashboard', function ($request, $response) {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $view = Twig::fromRequest($request);
        $ticketStore = new \Eben\TwigTicketApp\Utils\JsonStore(__DIR__ . '/public/tickets.json');
        $tickets = $ticketStore->getAll();

        $stats = [
            'total' => count($tickets),
            'open' => count(array_filter($tickets, fn($t) => $t['status'] === 'open')),
            'in_progress' => count(array_filter($tickets, fn($t) => $t['status'] === 'in_progress')),
            'closed' => count(array_filter($tickets, fn($t) => $t['status'] === 'closed')),
        ];

        return $view->render($response, 'dashboard.twig', [
            'user' => $_SESSION['user'],
            'stats' => $stats,
        ]);
    });


    // ======================
    // TICKETS
    // ======================
    $container->set(TicketController::class, function ($c) {
        return new TicketController($c->get('view'));
    });

    $app->get('/tickets', [TicketController::class, 'index']);             // Show all tickets
    $app->post('/tickets/create', [TicketController::class, 'create']);    // Handle ticket creation
    $app->get('/tickets/{id}/edit', [TicketController::class, 'edit']);    // Edit form
    $app->post('/tickets/{id}/update', [TicketController::class, 'update']); // Handle update
    $app->get('/tickets/{id}/delete', [TicketController::class, 'delete']);  // Delete ticket
};

