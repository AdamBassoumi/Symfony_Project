<?php
// src/Controller/ErrorController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    #[Route('/error', name: 'error_page')]
    public function showErrorPage(\Exception $exception): Response
    {
        // Default status code for general errors
        $statusCode = 500;
        
        // Check for specific exceptions and adjust status codes
        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
        }

        if ($exception instanceof NotFoundHttpException) {
            $statusCode = 404;
        }

        // Handle Access Denied (403)
        if ($exception instanceof AccessDeniedHttpException) {
            $statusCode = 403;
        }

        // Handle 404 error
        if ($statusCode === 404) {
            return $this->render('error/404.html.twig');
        }

        // Handle Access Denied error (403)
        if ($statusCode === 403) {
            return $this->render('error/403.html.twig');
        }

        // Default error page for other status codes (500, etc.)
        return $this->render('error/default.html.twig', [
            'statusCode' => $statusCode,
        ]);
    }
}

