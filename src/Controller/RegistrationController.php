<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, UsersAuthenticator $authenticator, EntityManagerInterface $entityManager, SendMailService $mail, JWTService $jwt): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email
            // On génère le JWT de l'utilisateur
            // On génère le Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            //on génère le payload
            $payload = [
                'user_id' => $user->getId()
            ];

            //on génère le token
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

           

            //on envoie le mail
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation de votre compte sur le site e-commerce',
                'register',
                compact('user', 'token')
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UsersRepository $usersRepository, EntityManagerInterface $em): Response
    {
        // on vérifie la validité, expiration, et la modification du token
        if($jwt->isValid($token) && !$jwt->isExpired($token) && 
        $jwt->check($token, $this->getParameter('app.jwtsecret')))
        {
            //on récupère le payload
            $payload = $jwt->getPayload($token);

            //on récupère le user du token
            $user = $usersRepository->find($payload['user_id']);

            // on vérifie que le user existe et compte déjà activé
            if($user && !$user->getIsVerified())
            {
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_index');
            }
        }
        // ici un problème se pose dans le token

        $this->addFlash('warning', 'Le token est invalide ou a 
        expiré');
        return $this->redirectToRoute('app_login');
    }

    #[Route('/renvoiverif', name: 'resend_verif')]
    public function resendVerif(JWTService $jwt, SendMailService $mail, UsersRepository $usersRepository): Response
    {
        $user = $this->getUser();


        if(!$user)
        {
            $this->addFlash('danger', 'Vous devez être connecté 
            pour accéder à cette page');

            return $this->redirectToRoute('app_login');
        }

        if($user->getIsVerified())
        {
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('app_index');
        }

        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        //on génère le payload
        $payload = [
            'user_id' => $user->getId()
        ];

        //on génère le token
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

       

        //on envoie le mail
        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation de votre compte sur le site e-commerce',
            'register',
            compact('user', 'token')
        );

        $this->addFlash('success', 'Email de vérification envoyé');
         return $this->redirectToRoute('app_index');
    }
}
