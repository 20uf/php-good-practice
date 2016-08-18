<?php

/*
 * This file is part of the php good practice démo.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Client;

use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FacebookClient
 *
 * @author Michael COULLERET <michael@coulleret.pro>
 */
class FacebookClient implements ClientInterface
{
    const API_BASE_URL = "https://graph.facebook.com/v2.6";

    /**
     * @var array
     */
    protected $configConsumer;

    /**
     * @var Client
     */
    protected $guzzle;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var string
     */
    protected $accessToken;

    /**
     * constructor FacebookClient
     *
     * @param array           $configConsumer
     * @param Client          $guzzle
     * @param LoggerInterface $logger
     */
    public function __construct(array $configConsumer, Client $guzzle, LoggerInterface $logger)
    {
        $this->configConsumer = $configConsumer;
        $this->guzzle = $guzzle;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageFromUrl($url)
    {
        if (!preg_match('`^https?://www.facebook.com/([A-Za-z0-9]+)+/posts/([0-9]+)$`', $url, $match)) {
            throw new \Exception('Invalid URL facebook');
        }

        $facebookUser = $this->getUser($match[1]);
        $posts = $this->getPosts($match[1]);

        $messageId = sprintf('%s_%s', $facebookUser['id'], $match[2]);

        foreach ($posts['data'] as $post) {
            if ($post['id'] === $messageId) {
                $post['name'] = $facebookUser['name'];
                $post['avatar'] = sprintf('http://graph.facebook.com/%s/picture', $match[1]);

                return $post;
            }
        }

        throw new NotFoundHttpException(sprintf('Message id %s do not found', $messageId));
    }

    /**
     * Returns a user facebook entity
     *
     * @param string $id
     *
     * @return string
     */
    protected function getUser($id)
    {
        if ($this->accessToken === null) {
            $this->initAccessToken();
        }

        try {
            $response = $this->guzzle->get(sprintf('%s/%s', self::API_BASE_URL, $id), [
                'query' => [
                    'access_token' => $this->accessToken,
                ],
            ])->getBody()->getContents();

            return json_decode($response, true);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Shows only the posts that were published by this page.
     *
     * @param string $id
     *
     * @return array
     */
    protected function getPosts($id)
    {
        if ($this->accessToken === null) {
            $this->initAccessToken();
        }

        try {
            $response = $this->guzzle->get(sprintf('%s/%s/posts', self::API_BASE_URL, $id), [
                'query' => [
                    'access_token' => $this->accessToken,
                ],
            ])->getBody()->getContents();

            return json_decode($response, true);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * Get access token
     *
     * @ codeCoverageIgnore
     *
     * @throws \Exception
     */
    private function initAccessToken()
    {
        try {
            $response = $this->guzzle->get(sprintf('%s/%s', self::API_BASE_URL, 'oauth/access_token'), [
                'query' => [
                    'client_id' => $this->configConsumer['app_id'],
                    'client_secret' => $this->configConsumer['app_secret'],
                    'grant_type' => 'client_credentials',
                ],
            ])->getBody()->getContents();

            $json = json_decode($response, true);

            $this->accessToken = (isset($json['access_token'])) ? $json['access_token'] : null;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());

            throw new \RuntimeException('Bad credentials oAuth Facebook client.');
        }
    }
}
