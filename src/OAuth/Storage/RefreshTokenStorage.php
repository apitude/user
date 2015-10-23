<?php
namespace Apitude\User\OAuth\Storage;

use Apitude\Core\Provider\ContainerAwareInterface;
use Apitude\Core\Provider\ContainerAwareTrait;
use Apitude\Core\Provider\Helper\EntityManagerAwareInterface;
use Apitude\Core\Provider\Helper\EntityManagerAwareTrait;
use League\OAuth2\Server\Entity\RefreshTokenEntity;
use League\OAuth2\Server\Storage\AbstractStorage;
use League\OAuth2\Server\Storage\RefreshTokenInterface;

class RefreshTokenStorage extends AbstractStorage implements RefreshTokenInterface, ContainerAwareInterface, EntityManagerAwareInterface
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
     * Return a new instance of \League\OAuth2\Server\Entity\RefreshTokenEntity
     *
     * @param string $token
     *
     * @return \League\OAuth2\Server\Entity\RefreshTokenEntity | null
     */
    public function get($token)
    {
        foreach ($this->getDbConnection()->fetchAll(
            'SELECT * FROM oauth_refresh_token
            WHERE refresh_token = :token', [
                'token' => $token
            ]
        ) as $row) {
            if ($row) {
                return (new RefreshTokenEntity($this->server))
                    ->setAccessTokenId($row['access_token'])
                    ->setExpireTime($row['expire_time'])
                    ->setId($row['refresh_token']);
            }
        }
        return null;
    }

    /**
     * Create a new refresh token_name
     *
     * @param string $token
     * @param integer $expireTime
     * @param string $accessToken
     *
     * @return \League\OAuth2\Server\Entity\RefreshTokenEntity
     */
    public function create($token, $expireTime, $accessToken)
    {
        $this->getDbConnection()->insert('oauth_refresh_token', [
            'refresh_token' => $token,
            'access_token' => $accessToken,
            'expire_time' => $expireTime
        ]);
    }

    /**
     * Delete the refresh token
     *
     * @param \League\OAuth2\Server\Entity\RefreshTokenEntity $token
     *
     * @return void
     */
    public function delete(RefreshTokenEntity $token)
    {
        $this->getDbConnection()->delete('oauth_refresh_token', [
            'refresh_token' => $token->getId()
        ]);
    }

}