<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class LocalController extends AbstractController
{
    #[Route('/change-locale/{locale}', name: 'change_locale')]
    public function locale($locale, Request $request)
    {
        // Stocker la langue dans la session
        $request->getSession()->set('_locale', $locale);
        // Retourner à la page précédente
        return $this->redirect($request->headers->get('referer'));
    }
}
