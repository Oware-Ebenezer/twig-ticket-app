<?php

namespace Eben\TwigTicketapp\Controllers;

use Eben\TwigTicketapp\Utils\JsonStore;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class TicketController
{
    private Twig $view;
    private JsonStore $store;

    public function __construct(Twig $view)
    {
        $this->view = $view;
        $this->store = new JsonStore(__DIR__ . '/../public/tickets.json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /** Show all tickets + create form */
    public function index(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $tickets = $this->store->getAll();
        return $this->view->render($response, 'tickets/tickets.twig', [
            'tickets' => array_reverse($tickets),
            'user' => $_SESSION['user']
        ]);
    }

    /** Handle ticket creation */
    public function create(Request $request, Response $response): Response
    {
        if (!isset($_SESSION['user'])) {
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $data = $request->getParsedBody();
        $title = trim($data['title'] ?? '');
        $description = trim($data['description'] ?? '');
        $status = trim($data['status'] ?? 'open');

        if (!$title || !$description) {
            $_SESSION['error'] = "All fields are required!";
            return $response->withHeader('Location', '/tickets')->withStatus(302);
        }

        $newTicket = [
            'id' => uniqid(),
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'user' => $_SESSION['user']['email'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $this->store->add($newTicket);
        $_SESSION['success'] = "Ticket created successfully!";
        return $response->withHeader('Location', '/tickets')->withStatus(302);
    }

    /** Show edit form */
    public function edit(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $ticket = $this->store->find($id);

        if (!$ticket) {
            $_SESSION['error'] = "Ticket not found!";
            return $response->withHeader('Location', '/tickets')->withStatus(302);
        }

        return $this->view->render($response, 'tickets/tickets.twig', [
            'ticket' => $ticket,
            'tickets' => $this->store->getAll(),
        ]);
    }

    /** Handle ticket update */
    public function update(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = $request->getParsedBody();

        $patch = [
            'title' => trim($data['title'] ?? ''),
            'description' => trim($data['description'] ?? ''),
            'status' => trim($data['status'] ?? 'open'),
        ];

        $updated = $this->store->update($id, $patch);

        if (!$updated) {
            $_SESSION['error'] = "Failed to update ticket.";
        } else {
            $_SESSION['success'] = "Ticket updated successfully.";
        }

        return $response->withHeader('Location', '/tickets')->withStatus(302);
    }

    /** Handle delete */
    public function delete(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $deleted = $this->store->delete($id);

        if ($deleted) {
            $_SESSION['success'] = "Ticket deleted.";
        } else {
            $_SESSION['error'] = "Ticket not found.";
        }

        return $response->withHeader('Location', '/tickets')->withStatus(302);
    }
}
