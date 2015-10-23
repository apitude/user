<?php
namespace Apitude\User\OAuth\Storage;


use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use League\OAuth2\Server\AbstractServer;
use League\OAuth2\Server\Entity\ClientEntity;
use League\OAuth2\Server\Entity\SessionEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ClientInterface;

class ClientStorage extends AbstractStorage implements ClientInterface, ContainerAwareInterface, EntityManagerAwareInterface
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
     * Validate a client
     *
     * @param string $clientId The client's ID
     * @param string $clientSecret The client's secret (default = "null")
     * @param string $redirectUri The client's redirect URI (default = "null")
     * @param string $grantType The grant type used (default = "null")
     *
     * @return \League\OAuth2\Server\Entity\ClientEntity | null
     */
    public function get($clientId, $clientSecret = null, $redirectUri = null, $grantType = null)
    {
        $select = ['oc.*'];
        $from = 'FROM oauth_client oc';
        $join = '';
        $where = ['oc.id = :clientId'];
        $params = ['clientId' => $clientId];

        if ($clientSecret) {
            $where[] = 'oc.secret = :secret';
            $params['secret'] = $clientSecret;
        }

        if ($redirectUri) {
            $join = 'INNER JOIN oauth_client_redirect_uri ocru ON(ocru.client_id = oc.id)';
            $where[] = 'ocru.redirect_uri = :uri';
            $params['uri'] = $redirectUri;
        }

        $sql = "SELECT ".implode($select)." {$from} {$join} WHERE ".implode(' AND ', $where);

        foreach ($this->getDbConnection()->fetchAll($sql, $params) as $row) {
            if ($row) {
                return (new ClientEntity($this->server))
                    ->hydrate([
                        'id' => $row['id'],
                        'name' => $row['name']
                    ]);
            }
        }
        return null;
    }

    /**
     * Get the client associated with a session
     *
     * @param \League\OAuth2\Server\Entity\SessionEntity $session The session
     *
     * @return \League\OAuth2\Server\Entity\ClientEntity | null
     */
    public function getBySession(SessionEntity $session)
    {
        foreach($this->getDbConnection()->fetchAll(
            'SELECT oc.id, oc.name FROM oauth_client oc
            INNER JOIN oauth_session os ON(oc.id = os.client_id)
            WHERE os.id = :id',
            ['id' => $session->getId()]
        ) as $row) {
            if ($row) {
                return (new ClientEntity($this->server))
                    ->hydrate([
                        'id' => $row['id'],
                        'name' => $row['name']
                    ]);
            }
        }
        return null;
    }
}