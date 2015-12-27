<?php
namespace Apitude\User\OAuth\Storage;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\AuthCodeInterface;

class AuthCodeStorage extends AbstractStorage implements AuthCodeInterface, ContainerAwareInterface, EntityManagerAwareInterface
{
    use ContainerAwareTrait;
    use EntityManagerAwareTrait;

    /**
     * @return \Doctrine\DBAL\Connection
     */
    private function getDbConnection() {
        static $conn;
        if (!$conn) {
            $conn = $this->getEntityManager()->getConnection();
        }
        return $conn;
    }
    /**
     * Get the auth code
     *
     * @param string $code
     *
     * @return \League\OAuth2\Server\Entity\AuthCodeEntity | null
     */
    public function get($code)
    {
        foreach ($this->getDbConnection()->fetchAll(
            'SELECT * from oauth_auth_code
            WHERE auth_code = :authCode AND expire_time > '.time(),
            [
                'authCode' => $code
            ]
        ) as $row) {
            if ($row) {
                return (new AuthCodeEntity($this->server))
                    ->setRedirectUri($row['client_redirect_uri'])
                    ->setExpireTime($row['expire_time'])
                    ->setId($row['auth_code']);
            }
        }
        return null;
    }

    /**
     * Create an auth code.
     *
     * @param string $token The token ID
     * @param integer $expireTime Token expire time
     * @param integer $sessionId Session identifier
     * @param string $redirectUri Client redirect uri
     *
     * @return void
     */
    public function create($token, $expireTime, $sessionId, $redirectUri)
    {
        $this->getDbConnection()->insert('oauth_auth_code', [
            'auth_code' => $token,
            'client_redirect_uri' => $redirectUri,
            'session_id' => $sessionId,
            'expire_time' => $expireTime,
        ]);
    }

    /**
     * Get the scopes for an access token
     *
     * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
     *
     * @return \League\OAuth2\Server\Entity\ScopeEntity[] Array of \League\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(AuthCodeEntity $token)
    {
        $result = $this->getDbConnection()->fetchAll(
            <<<SQL
SELECT s.id, s.description FROM oauth_auth_code_scope acs
INNER JOIN oauth_scope s ON(s.id=acs.oauth_scope)
WHERE auth_code = :authCode
SQL
            , [
                'authCode' => $token->getId()
            ]
        );
        $response = [];

        foreach($result as $row) {
            $response[] = (new ScopeEntity($this->server))->hydrate([
                'id' => $row['id'],
                'description' => $row['description']
            ]);
        }

        return $response;
    }

    /**
     * Associate a scope with an acess token
     *
     * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The auth code
     * @param \League\OAuth2\Server\Entity\ScopeEntity $scope The scope
     *
     * @return void
     */
    public function associateScope(AuthCodeEntity $token, ScopeEntity $scope)
    {
        $this->getDbConnection()->insert('oauth_auth_code_scope', [
            'auth_code' => $token->getId(),
            'oauth_scope' => $scope->getId()
        ]);
    }

    /**
     * Delete an access token
     *
     * @param \League\OAuth2\Server\Entity\AuthCodeEntity $token The access token to delete
     *
     * @return void
     */
    public function delete(AuthCodeEntity $token)
    {
        $this->getDbConnection()->delete('oauth_auth_code_scope', [
            'auth_code' => $token->getId()
        ]);

        $this->getDbConnection()->delete('oauth_auth_code', [
            'auth_code' => $token->getId()
        ]);
    }

}
