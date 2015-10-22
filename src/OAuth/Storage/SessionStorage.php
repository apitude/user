<?php
namespace Apitude\User\OAuth\Storage;


use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use League\OAuth2\Server\Entity\AccessTokenEntity;
use League\OAuth2\Server\Entity\AuthCodeEntity;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\SessionInterface;

class SessionStorage extends AbstractStorage implements SessionInterface, ContainerAwareInterface, EntityManagerAwareInterface
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
     * Get a session from an access token
     *
     * @param \League\OAuth2\Server\Entity\AccessTokenEntity $accessToken The access token
     *
     * @return \League\OAuth2\Server\Entity\SessionEntity | null
     */
    public function getByAccessToken(AccessTokenEntity $accessToken)
    {
        $sql = <<<SQL
SELECT os.id, os.owner_type, os.owner_id, os.client_id, os.client_redirect_uri
FROM oauth_session
INNER JOIN oauth_access_token oat ON(oat.session_id = os.id)
WHERE oat.access_token = :token
SQL;
        foreach ($this->getDbConnection()->fetchAll($sql, ['token' => $accessToken->getId()]) as $row) {
            if ($row) {
                return (new SessionEntity($this->server))
                    ->setId($row['id'])
                    ->setOwner($row['owner_type'], $row['owner_id']);
            }
        }
        return null;
    }

    /**
     * Get a session from an auth code
     *
     * @param \League\OAuth2\Server\Entity\AuthCodeEntity $authCode The auth code
     *
     * @return \League\OAuth2\Server\Entity\SessionEntity | null
     */
    public function getByAuthCode(AuthCodeEntity $authCode)
    {
        $sql = <<<SQL
SELECT os.id, os.owner_type, os.owner_id, os.client_id, os.client_redirect_uri
FROM oauth_session
INNER JOIN oauth_auth_code oac ON(oac.session_id = os.id)
WHERE oac.auth_code = :authCode
SQL;
        foreach ($this->getDbConnection()->fetchAll($sql, ['authCode' => $authCode->getId()]) as $row) {
            if ($row) {
                return (new SessionEntity($this->server))
                    ->setId($row['id'])
                    ->setOwner($row['owner_type'], $row['owner_id']);
            }
        }
        return null;
    }

    /**
     * Get a session's scopes
     *
     * @param  \League\OAuth2\Server\Entity\SessionEntity
     *
     * @return \League\OAuth2\Server\Entity\ScopeEntity[] Array of \League\OAuth2\Server\Entity\ScopeEntity
     */
    public function getScopes(SessionEntity $session)
    {
        $sql = <<<SQL
SELECT os.* FROM oauth_session oses
INNER JOIN oauth_session_scope oss ON(oses.id=oss.session_id)
INNER JOIN oauth_scope os ON(os.id=oss.scope)
WHERE oses.id = :sessionId
SQL;
        foreach ($this->getDbConnection()->fetchAll($sql, ['sessionId' => $session->getId()]) as $row) {
            if ($row) {
                return (new ScopeEntity($this->server))
                    ->hydrate([
                        'id' => $row['id'],
                        'description' => $row['description']
                    ]);
            }
        }
        return null;
    }

    /**
     * Create a new session
     *
     * @param string $ownerType Session owner's type (user, client)
     * @param string $ownerId Session owner's ID
     * @param string $clientId Client ID
     * @param string $clientRedirectUri Client redirect URI (default = null)
     *
     * @return integer The session's ID
     */
    public function create($ownerType, $ownerId, $clientId, $clientRedirectUri = null)
    {
        $this->getDbConnection()->insert('oauth_session', [
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'client_id' => $clientId
        ]);
        return $this->getDbConnection()->lastInsertId();
    }

    /**
     * Associate a scope with a session
     *
     * @param \League\OAuth2\Server\Entity\SessionEntity $session The session
     * @param \League\OAuth2\Server\Entity\ScopeEntity $scope The scope
     *
     * @return void
     */
    public function associateScope(SessionEntity $session, ScopeEntity $scope)
    {
        $this->getDbConnection()->insert('oauth_session_scope', [
            'session_id' => $session->getId(),
            'scope' => $scope->getId()
        ]);
    }

}