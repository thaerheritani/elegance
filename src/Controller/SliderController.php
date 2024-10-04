<?php

namespace App\Controller;

use App\Entity\Slider;
use App\Form\SliderType;
use App\Repository\SliderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SliderController extends AbstractController
{
    #[Route('/slider/new', name: 'slider_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $slider = new Slider();
        $form = $this->createForm(SliderType::class, $slider);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($slider);
            $entityManager->flush();

            return $this->redirectToRoute('slider_list');
        }

        return $this->render('slider/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/slider', name: 'slider_list')]
    public function list(SliderRepository $sliderRepository): Response
    {
        $sliders = $sliderRepository->findAll();

        return $this->render('slider/list.html.twig', [
            'sliders' => $sliders,
        ]);
    }
}
