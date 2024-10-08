<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\ChangeEmailFormType;
use App\Form\ChangeAddressFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Form\FormError;

class ProfileController extends AbstractController
{
    #[Route('/profile/general-info', name: 'app_profile_general_info')]
    public function generalInfo(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->render('profile/_general_information.html.twig');
        }

        return $this->render('profile/index.html.twig', [
            'content' => $this->renderView('profile/_general_information.html.twig'),
        ]);
    }

    #[Route('/profile/change-password', name: 'app_profile_change_password')]
    public function changePassword(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $currentPassword = $form->get('currentPassword')->getData();
            $newPassword = $form->get('plainPassword')->getData();

            // Check if the current password is valid
            if ($passwordHasher->isPasswordValid($user, $currentPassword)) {
                // Encode and set the new password
                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
                $this->getDoctrine()->getManager()->flush();

                $this->addFlash('success', 'Password changed successfully!');
                return $this->redirectToRoute('app_profile_general_info');
            } else {
                // If the current password is incorrect
                $form->get('currentPassword')->addError(new FormError('Current password is incorrect.'));
            }
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('profile/_change_password.html.twig', [
                'changePasswordForm' => $form->createView(),
            ]);
        }

        return $this->render('profile/index.html.twig', [
            'content' => $this->renderView('profile/_change_password.html.twig', [
                'changePasswordForm' => $form->createView(),
            ]),
        ]);
    }

    #[Route('/profile/change-email', name: 'app_profile_change_email')]
    public function changeEmail(Request $request): Response
    {
        $form = $this->createForm(ChangeEmailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $newEmail = $form->get('newEmail')->getData();
            $user->setEmail($newEmail);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Email changed successfully!');
            return $this->redirectToRoute('app_profile_general_info');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('profile/_change_email.html.twig', [
                'changeEmailForm' => $form->createView(),
            ]);
        }

        return $this->render('profile/index.html.twig', [
            'content' => $this->renderView('profile/_change_email.html.twig', [
                'changeEmailForm' => $form->createView(),
            ]),
        ]);
    }

    #[Route('/profile/change-address', name: 'app_profile_change_address')]
    public function changeAddress(Request $request): Response
    {
        $form = $this->createForm(ChangeAddressFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Address changed successfully!');
            return $this->redirectToRoute('app_profile_general_info');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('profile/_change_address.html.twig', [
                'changeAddressForm' => $form->createView(),
            ]);
        }

        return $this->render('profile/index.html.twig', [
            'content' => $this->renderView('profile/_change_address.html.twig', [
                'changeAddressForm' => $form->createView(),
            ]),
        ]);
    }

    #[Route('/profile/delete-account', name: 'app_profile_delete_account')]
    public function deleteAccount(Request $request): Response
    {
        if ($request->isXmlHttpRequest()) {
            return $this->render('profile/_delete_account.html.twig');
        }

        return $this->render('profile/index.html.twig', [
            'content' => $this->renderView('profile/_delete_account.html.twig'),
        ]);
    }
}
