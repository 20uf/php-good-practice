<?php

/*
 * This file is part of the php good practice démo.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Client;

use App\FacebookClient;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FacebookClientTest
 *
 * @author Michael COULLERET <michael@coulleret.pro>
 */
class FacebookClientTest extends \PHPUnit_Framework_TestCase
{
    private $clientFacebook;
    private $guzzleClient;
    private $logger;
    private $config = ['app_id' => 'mockId', 'app_secret' => 'mockSecret'];
    private $mockJsonOauth = '{
           "access_token": "880669872306650|r5ofttdtk1rC07NtoCeSjKHERjs",
           "token_type": "bearer"
        }';
    private $mockJsonUser = '{
            "name": "Barack Obama",
            "id": "6815841748"
        }';
    private $mockJsonPosts = '{
        "data": [
            {
                "message": "It\'s been more than 150 days since President Obama nominated Judge Garland.\nSenate leaders: Do your jobs.",
                "created_time": "2016-08-16T21:19:19+0000",
                "id": "6815841748_10154083688666749"
            },
            {
                "message": "For a record-breaking 153 days, Senate obstructionists have refused to hold a hearing for one of the most well qualified Supreme Court nominees in U.S. history. That\'s unacceptable.",
                "created_time": "2016-08-16T16:03:16+0000",
                "id": "6815841748_10154083065776749"
            },
            {
                "message": "Protecting access to affordable health care, economic opportunity, and equal pay for women makes a difference for everyone.",
                "created_time": "2016-08-15T21:54:10+0000",
                "id": "6815841748_10154081443366749"
            }
          ]
        }';

    /**
     * @setup
     */
    public function setUp()
    {
        $this->logger = \Phake::mock(LoggerInterface::class);

    }

    /**
     * @test
     */
    public function shouldInitAccessTokenWithOAuthException()
    {
        $guzzleMock = new MockHandler([
            new RequestException("Bad request", new Request('GET', 'v2.6/oauth/access_token')),
        ]);

        $this->guzzleClient = new Client(['handler' => HandlerStack::create($guzzleMock)]);

        $this->clientFacebook = \Phake::partialMock(FacebookClient::class, $this->config, $this->guzzleClient, $this->logger);

        $this->expectException(\Exception::class);

        $this->clientFacebook->getMessageFromUrl('https://www.facebook.com/barackobama/posts/10154081443366749');
    }

    /**
     * @test
     */
    public function shouldGetMessageFromUrl()
    {
        $guzzleMock = new MockHandler([
            new Response(200, [], $this->mockJsonOauth),
            new Response(200, [], $this->mockJsonUser),
            new Response(200, [], $this->mockJsonPosts),
        ]);

        $this->guzzleClient = new Client(['handler' => HandlerStack::create($guzzleMock)]);

        $this->clientFacebook = \Phake::partialMock(FacebookClient::class, $this->config, $this->guzzleClient, $this->logger);

        $result = $this->clientFacebook->getMessageFromUrl('https://www.facebook.com/barackobama/posts/10154081443366749');

        $mockToArray = json_decode($this->mockJsonPosts, true);
        $mockVerify = $mockToArray['data'][2];
        $mockVerify['name'] = 'Barack Obama';
        $mockVerify['avatar'] = 'http://graph.facebook.com/barackobama/picture';

        $this->assertEquals($result, $mockVerify);
    }

    /**
     * @test
     */
    public function shouldGetMessageNotFoundExeption()
    {
        $guzzleMock = new MockHandler([
            new Response(200, [], $this->mockJsonOauth),
            new Response(200, [], $this->mockJsonUser),
            new Response(200, [], $this->mockJsonPosts),
        ]);

        $this->guzzleClient = new Client(['handler' => HandlerStack::create($guzzleMock)]);

        $this->clientFacebook = \Phake::partialMock(FacebookClient::class, $this->config, $this->guzzleClient, $this->logger);

        $this->expectException(NotFoundHttpException::class);

        $this->clientFacebook->getMessageFromUrl('https://www.facebook.com/barackobama/posts/123456789');
    }

    /**
     * @test
     */
    public function shouldGetMessageWithBadUrl()
    {
        $guzzleMock = new MockHandler([
            new Response(200, [], $this->mockJsonOauth),
        ]);

        $this->guzzleClient = new Client(['handler' => HandlerStack::create($guzzleMock)]);

        $this->clientFacebook = \Phake::partialMock(FacebookClient::class, $this->config, $this->guzzleClient, $this->logger);

        $this->expectException(\Exception::class);

        $this->clientFacebook->getMessageFromUrl('https://mock.test');
    }

    /**
     * @test
     */
    public function shouldGetUserException()
    {
        $guzzleMock = new MockHandler([
            new Response(200, [], $this->mockJsonOauth),
            new RequestException("Bad request", new Request('GET', '/')),
        ]);

        $this->guzzleClient = new Client(['handler' => HandlerStack::create($guzzleMock)]);

        $this->clientFacebook = \Phake::partialMock(FacebookClient::class, $this->config, $this->guzzleClient, $this->logger);

        $this->expectException(\Exception::class);

        $this->clientFacebook->getMessageFromUrl('https://www.facebook.com/barackobama/posts/123456789');
    }

    /**
     * @test
     */
    public function shouldGetPostsException()
    {
        $guzzleMock = new MockHandler([
            new Response(200, [], $this->mockJsonOauth),
            new Response(200, [], $this->mockJsonUser),
            new RequestException("Bad request", new Request('GET', '/')),
        ]);

        $this->guzzleClient = new Client(['handler' => HandlerStack::create($guzzleMock)]);

        $this->clientFacebook = \Phake::partialMock(FacebookClient::class, $this->config, $this->guzzleClient, $this->logger);

        $this->expectException(\Exception::class);

        $this->clientFacebook->getMessageFromUrl('https://www.facebook.com/barackobama/posts/123456789');
    }
}
