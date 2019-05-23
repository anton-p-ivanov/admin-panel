<?php

namespace App\Service;

use App\Entity\Workflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * Class DeleteEntity
 * @package App\Service
 */
class EntityService
{
    /**
     * @var UserPasswordEncoderInterface 
     */
    private $encoder;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    /**
     * @var CsrfTokenManagerInterface 
     */
    private $tokenManager;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * DeleteEntity constructor.
     *
     * @param RequestStack $requestStack
     * @param EntityManagerInterface $entityManager
     * @param TokenStorageInterface $tokenStorage
     * @param CsrfTokenManagerInterface $tokenManager
     * @param UserPasswordEncoderInterface $encoder
     */
    public function __construct(
        RequestStack $requestStack,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface $tokenManager,
        UserPasswordEncoderInterface $encoder)
    {
        $this->requestStack = $requestStack;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
        $this->tokenManager = $tokenManager;
        $this->encoder = $encoder;
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return $this->tokenStorage->getToken()->getUser();
    }

    /**
     * @return null|Request
     */
    private function getRequest(): ?Request
    {
        return $this->requestStack->getCurrentRequest();
    }

    /**
     * @param mixed $entity
     * @param array $refreshData
     *
     * @return JsonResponse
     */
    public function delete($entity, array $refreshData): JsonResponse
    {
        if (!$this->isRequestValid()) {
            return new JsonResponse(['error' => 'Пароль указан неверно'], 400);
        }

        $workflow = null;

        if (method_exists($entity, 'getWorkflow')) {
            $workflow = $entity->getWorkflow();
        }

        if ($workflow instanceof Workflow && $this->getRequest()->get('force') === null) {
            $workflow->markAsDeleted([
                'updated' => $this->getUser(),
                'updatedAt' => new \DateTime(),
            ]);

            $this->entityManager->persist($workflow);
        }
        else {
            $this->entityManager->remove($entity);
        }

        $this->entityManager->flush();

        return new JsonResponse($refreshData);
    }

    /**
     * @return bool
     */
    public function isRequestValid()
    {
        $request = $this->getRequest();
        $token = new CsrfToken('confirm', $request->get('_csrf_token'));
        
        $isValid = $this->tokenManager->isTokenValid($token);
        return $isValid && $this->encoder->isPasswordValid($this->getUser(), $request->get('password'));
    }
}