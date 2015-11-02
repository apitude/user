<?php
namespace Apitude\User;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use Apitude\Core\Provider\ShutdownInterface;
use Apitude\User\Entities\PasswordResetToken;
use Apitude\User\Entities\User;
use Apitude\User\Exception\TokenNotFoundException;
use Apitude\User\Security\UserProvider;

class UserService implements ContainerAwareInterface, EntityManagerAwareInterface, ShutdownInterface
{
    use ContainerAwareTrait;
    use EntityManagerAwareTrait;

    const ERROR_USER_DISABLED = 'ERROR_USER_DISABLED';

    private $cleanResetTokens = false;

    /**
     * @return UserProvider
     */
    private function getUserProvider()
    {
        return $this->container[UserProvider::class];
    }

    /**
     * @param string $username
     * @param string $email
     * @param null|string $password
     * @param bool|true $enabled
     * @return User
     */
    public function create($username, $email, $password = null, $enabled = true)
    {
        $userEntity = $this->container['user.entity'];
        /** @var User $user */
        $user = (new $userEntity);
        $user->setUsername($username)
            ->setEmail($email)
            ->setPassword($password ?: sha1(microtime()))
            ->setEnabled($enabled);
        $em = $this->getEntityManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    public function requestPasswordResetToken($username)
    {
        $this->cleanResetTokens = true;

        /** @var User $user */
        $user = $this->getUserProvider()->loadUserByUsername($username);

        if ($user && $user->isEnabled()) {
            $expires = isset($this->container['user.password_reset_token_expire']) ?
                new \DateTime($this->container['user.password_reset_token_expire']) :
                new \DateTime('+24 hours');
            $token = (new PasswordResetToken())
                ->setExpires($expires)
                ->setUser($user);
            $this->getEntityManager()->persist($token);
            $this->getEntityManager()->flush();
            return $token;
        } else {
            throw new \Exception(self::ERROR_USER_DISABLED);
        }
    }

    public function resetPassword($token, $password)
    {
        $this->cleanResetTokens = true;

        /** @var PasswordResetToken $token */
        $token = $this->getEntityManager()->find(PasswordResetToken::class, $token);

        if (!$token) {
            throw new TokenNotFoundException;
        }

        $user = $token->getUser();
        $user->setPassword($password);

        $em = $this->getEntityManager();
        $em->remove($token);
        $em->flush();
    }

    public function shutdown()
    {
        if ($this->cleanResetTokens) {
            $tokenEntity = PasswordResetToken::class;
            $this->getEntityManager()->createQuery(
                "DELETE FROM {$tokenEntity} t WHERE t.expires < :now"
            )->execute(['now' => new \DateTime()]);
        }
    }
}
