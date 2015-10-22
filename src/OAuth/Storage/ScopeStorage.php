<?php
namespace Apitude\User\OAuth\Storage;


use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use League\OAuth2\Server\Entity\ScopeEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\ScopeInterface;

class ScopeStorage extends AbstractStorage implements ScopeInterface, ContainerAwareInterface, EntityManagerAwareInterface
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
     * Return information about a scope
     *
     * @param string $scope The scope
     * @param string $grantType The grant type used in the request (default = "null")
     * @param string $clientId The client sending the request (default = "null")
     *
     * @return \League\OAuth2\Server\Entity\ScopeEntity | null
     */
    public function get($scope, $grantType = null, $clientId = null)
    {
        foreach ($this->getDbConnection()->fetchAll(
            'SELECT * from oauth_scope WHERE id = :scope',
            ['scope' => $scope]
        ) as $row) {
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

}