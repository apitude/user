<?php
namespace Apitude\User\Controller;

use Apitude\Core\API\Controller\JsonRequestTrait;
use Apitude\Core\API\Controller\ValidatorTrait;
use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use Apitude\Core\Validator\Constraints\NotBlankOrWhitespace;
use Apitude\User\Entities\PasswordResetToken;
use Apitude\User\Exception\TokenNotFoundException;
use Apitude\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;

abstract class PasswordController implements ContainerAwareInterface, EntityManagerAwareInterface
{
    use ContainerAwareTrait;
    use EntityManagerAwareTrait;
    use ValidatorTrait;
    use JsonRequestTrait;

    const ERROR_TOKEN_NOT_FOUND = 'TOKEN_NOT_FOUND';

    /**
     * @param Request $request
     * @return string
     */
    abstract protected function getUsername(Request $request);

    /**
     * Sends email to the user
     * @param PasswordResetToken $token
     * @return mixed
     */
    abstract protected function sendEmail(PasswordResetToken $token);

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->container[UserService::class];
    }

    public function requestPasswordReset(Request $request) {
        try {
            $token = $this->getUserService()->requestPasswordResetTokenByUsername($this->getUsername($request));
            $this->sendEmail($token);
        } catch(\Exception $e) {
            // swallow exception (most likely a user-not-found)
        }

        return new Response();
    }

    public function setPassword(Request $request) {
        $constraints = [
            'token' => new Assert\Required(),
            'password' => new Assert\Required([
                new NotBlankOrWhitespace(),
            ])
        ];

        $data = $this->getJsonContents($request);

        $errors = $this->getValidator()->validate($data, $constraints);

        if ($errors->count()) {
            return new JsonResponse($this->getViolationRecursiveArray($errors), Response::HTTP_NOT_ACCEPTABLE);
        }

        try {
            $this->getUserService()->resetPassword($data['token'], $data['password']);
        } catch (TokenNotFoundException $e) {
            return new JsonResponse([
                'message' => $e->getMessage()
            ], 404);
        }

        return new Response();
    }
}
