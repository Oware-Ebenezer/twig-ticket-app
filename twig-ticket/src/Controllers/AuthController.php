<?php

namespace Eben\TwigTicketapp\Controllers;

use Eben\TwigTicketapp\Utils\JsonStore;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AuthController
{
    private Twig $view;
    private JsonStore $store;

    public function __construct(Twig $view)
    {
        $this->view = $view;


        $this->store = new JsonStore(__DIR__ . '/../public/users.json');

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }


    public function showLogin(Request $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        return $this->view->render($response, 'auth/login.twig');
    }


    public function showSignup(Request $request, ResponseInterface $response, array $args = []): ResponseInterface
    {
        return $this->view->render($response, 'auth/signup.twig');
    }

    public function signup(Request $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $name = trim($data['name'] ?? '');
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!$name || !$email || !$password) {
            $_SESSION['error'] = "All fields are required!";
            return $response->withHeader('Location', '/auth/signup')->withStatus(302);
        }

        $users = $this->store->getAll();
        foreach ($users as $user) {
            if ($user['email'] === $email) {
                $_SESSION['error'] = "User with this email already exists!";
                return $response->withHeader('Location', '/auth/login')->withStatus(302);
            }
        }

        $newUser = [
            'name' => $name,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT)
        ];

        // append new user to the loaded users and persist the JSON file directly
        $users[] = $newUser;
        $file = __DIR__ . '/../public/users.json';
        file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $_SESSION['user'] = $newUser;

        return $response->withHeader('Location', '/dashboard')->withStatus(302);
    }

    public function login(Request $request, ResponseInterface $response): ResponseInterface
    {
        $data = $request->getParsedBody();
        $email = trim($data['email'] ?? '');
        $password = trim($data['password'] ?? '');

        if (!$email || !$password) {
            $_SESSION['error'] = "Please fill all fields.";
            return $response->withHeader('Location', '/auth/login')->withStatus(302);
        }

        $users = $this->store->getAll();
        foreach ($users as $user) {
            if ($user['email'] === $email && password_verify($password, $user['password'])) {
                $_SESSION['user'] = $user;
                return $response->withHeader('Location', '/dashboard')->withStatus(302);
            }
        }

        $_SESSION['error'] = "Invalid credentials!";
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }

    public function logout(Request $request, ResponseInterface $response): ResponseInterface
    {
        session_destroy();
        return $response->withHeader('Location', '/auth/login')->withStatus(302);
    }
}
