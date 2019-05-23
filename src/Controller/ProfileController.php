<?php

namespace App\Controller;

use App\Entity\User\Checkword;
use App\Entity\User\User;
use App\Form as Form;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation as Http;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class ProfileController
 *
 * @Route("/profile")
 *
 * @package App\Controller
 */
class ProfileController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * @Template("profile/login.html.twig")
     *
     * @param Http\Request $request
     * @param AuthenticationUtils $authenticationUtils
     *
     * @return array
     */
    public function login(Http\Request $request, AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return [
            'last_username' => $request->get('username', $lastUsername),
            'error' => $error,
        ];
    }

    /**
     * @Route("/reset", name="reset")
     *
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function reset(Http\Request $request): Http\Response
    {
        $entity = new Form\ProfileReset();

        $form = $this->createForm(Form\ProfileResetType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneByEmail($entity->getUsername());

            if ($user) {
                $user->scenario = User::SCENARIO_USER_RESET;
                $user->setCheckword();

                ($manager = $this->getDoctrine()->getManager())->persist($user);
                $manager->flush();

                return $this->redirectToRoute('password', ['username' => $user->getUsername()]);
            }
            else {
                $form->get('username')->addError(new FormError('form.reset.invalid_username'));
            }
        }

        return $this->render('profile/reset.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/password", name="password")
     *
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function password(Http\Request $request): Http\Response
    {
        $entity = new Form\ProfilePassword([
            'username' => $request->get('username'),
            'checkword' => $request->get('checkword')
        ]);

        $form = $this->createForm(Form\ProfilePasswordType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneByEmail($entity->getUsername());

            if ($user) {
                $userCheckword = $user->getUserCheckword();
                if ($userCheckword && $userCheckword->isValid($entity->getCheckword())) {
                    $user->setPassword($entity->getPassword());

                    ($manager = $this->getDoctrine()->getManager())->persist($user);
                    $manager->flush();

                    return $this->redirectToRoute('login', ['username' => $entity->getUsername()]);
                }
            }

            $form->addError(new FormError('form.password.invalid_username_or_checkword'));
        }

        return $this->render('profile/password.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/register", name="register")
     *
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function register(Http\Request $request): Http\Response
    {
        $form = $this->createForm(Form\ProfileRegisterType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entity = $form->getData();
            $entity->setRoles($this->getDoctrine()->getRepository('App:Role')->findBy(['code' => 'ROLE_USER']));

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($entity);
            $manager->flush();

            return $this->redirectToRoute('login', ['username' => $entity->getUsername()]);
        }

        return $this->render('profile/register.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/confirm", name="confirm")
     *
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function confirm(Http\Request $request): Http\Response
    {
        $user = null;
        if ($username = $request->get('username')) {
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->findOneByEmail($username);
        }

        if ($user) {
            $userCheckword = $user->getUserCheckword();
            if ($userCheckword && $userCheckword->isValid($request->get('checkword'))) {
                $user->setIsConfirmed(true);

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->flush();

                $this->getDoctrine()
                    ->getRepository(Checkword::class)
                    ->expireUserCheckwords($user);

                $this->addFlash('success', 'profile.confirm.success');

                return $this->redirectToRoute('login', ['username' => $user->getUsername()]);
            }
        }

        return $this->render("profile/confirm.html.twig", [
            'username' => $request->get('username')
        ]);
    }

    /**
     * @Route("/profile", name="profile")
     *
     * @param Http\Request $request
     *
     * @return Http\Response
     */
    public function profile(Http\Request $request): Http\Response
    {
        /* @var $entity User */
        $entity = $this->getUser();
        $entity->scenario = User::SCENARIO_USER_UPDATE;

        $form = $this->createForm(Form\ProfileType::class, $entity);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($entity);
            $manager->flush();

            $this->addFlash('success', 'form.profile.success');
            return $this->redirectToRoute('profile');
        }

        return $this->render("profile/profile.html.twig", [
            'form' => $form->createView(),
            'user' => $entity
        ]);
    }
}