<?php
namespace Apitude\User\OAuth\Storage;


use Apitude\Core\Application;
use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;
use League\OAuth2\Server\AbstractServer;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AccessTokenInterface;

class AccessTokenStorage extends AbstractStorage implements AccessTokenInterface, ContainerAwareInterface, EntityManagerAwareInterface
{
    use ContainerAwareTrait;
    use EntityManagerAwareTrait;

    /**
     * Get an instance of Entity\AccessTokenEntity
     *
     * @param string $token The access token
     *
     * @return \League\OAuth2\Server\Entity\AccessTokenEntity | null
     */
    public function get($token)
    {
        foreach (
            $this->getEntityManager()->getConnection()->fetchAll(
                'SELECT * FROM oauth_access_token WHERE access_token=:token',
                ['token' => $token]
            ) as $row
        ) {
            return (new AccessTokenEntity($this->server))
                ->setId($row['access_token'])
                ->setExpireTime($row['expire_time']);
        };
        return null;
    }

    /**
     * Get the scopes for an access token
     *
     * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
     *
     * @return \League\OAuth2\Server\Entity\ScopeEntity[] Array of \League\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(AccessTokenEntity $token)
    {
        $response = [];
        foreach ($this->getEntityManager()->getConnection()->fetchAll(
            'SELECT s.id, s.description
            FROM oauth_access_token_scope ats
            INNER JOIN oauth_scope s ON(ats.scope=s.id)
            WHERE ats.access_token = :accessToken',
            ['accessToken' => $token->getId()]
        ) as $row) {
            $response[] = (new ScopeEntity($this->server))->hydrate([
                'id' => $row['id'],
                'description' => $row['description']
            ]);
        }

        return $response;
    }

    /**
     * Creates a new access token
     *
     * @param string $token The access token
     * @param integer $expireTime The expire time expressed as a unix timestamp
     * @param string|integer $sessionId The session ID
     *
     * @return void
     */
    public function create($token, $expireTime, $sessionId)
    {
        $this->getEntityManager()->getConnection()->insert('oauth_access_token', [
            'access_token' => $token,
            'session_id' => $sessionId,
            'expire_time' => $expireTime
        ]);
    }

    /**
     * Associate a scope with an acess token
     *
     * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token
     * @param \League\OAuth2\Server\Entity\ScopeEntity $scope The scope
     *
     * @return void
     */
    public function associateScope(AccessTokenEntity $token, ScopeEntity $scope)
    {
        $this->getEntityManager()->getConnection()->insert('oauth_access_token_scope', [
            'access_token' => $token->getId(),
            'scope' => $scope->getId(),
        ]);
    }

    /**
     * Delete an access token
     *
     * @param \League\OAuth2\Server\Entity\AccessTokenEntity $token The access token to delete
     *
     * @return void
     */
    public function delete(AccessTokenEntity $token)
    {
        $this->getEntityManager()->getConnection()->delete('oauth_access_token_scope',[
            'access_token' => $token->getId()
        ]);
        $this->getEntityManager()->getConnection()->delete('oauth_access_token', [
            'access_token' => $token->getId()
        ]);
    }

}