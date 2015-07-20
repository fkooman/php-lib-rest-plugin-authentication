<?php

/**
 * Copyright 2015 François Kooman <fkooman@tuxed.net>.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace fkooman\Rest\Plugin\Authentication;

use fkooman\Http\Request;
use PHPUnit_Framework_TestCase;
use fkooman\Http\Exception\UnauthorizedException;

class AuthenticationPluginTest extends PHPUnit_Framework_TestCase
{
    public function testNoAuthenticationAttemptWithTwoRegisteredMethods()
    {
        try {
            $auth = new AuthenticationPlugin();
            $auth->register($this->getNoAttemptPlugin('One'), 'one');
            $auth->register($this->getNoAttemptPlugin('Two'), 'two');
            $auth->execute($this->getRequest(), array());
            $this->assertTrue(false);
        } catch (UnauthorizedException $e) {
            $this->assertEquals(
                array(
                    'HTTP/1.1 401 Unauthorized',
                    'Content-Type: application/json',
                    'Www-Authenticate: One realm="Foo", Two realm="Foo"',
                    '',
                    '{"error":"no_credentials","error_description":"credentials must be provided"}',
                ),
                $e->getJsonResponse()->toArray()
            );
        }
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage no active authentication plugins for this route
     */
    public function testNoAuthPlugins()
    {
        $auth = new AuthenticationPlugin();
        $auth->execute(
            $this->getRequest(),
            array()
        );
    }

    public function testAuthAttempt()
    {
        $auth = new AuthenticationPlugin();
        $auth->register($this->getSuccessfulAttemptPlugin(), 'one');
        $this->assertEquals(
            'foo',
            $auth->execute(
                $this->getRequest(),
                array()
            )->getUserId()
        );
    }

    public function testActiveOne()
    {
        $auth = new AuthenticationPlugin();
        $auth->register($this->getSuccessfulAttemptPlugin('foo'), 'one');
        $auth->register($this->getSuccessfulAttemptPlugin('foobar'), 'two');

        $this->assertSame(
            'foobar',
            $auth->execute(
                $this->getRequest(),
                array(
                    'activate' => array('two'),
                )
            )->getUserId()
        );
    }

    public function testActiveTwo()
    {
        $auth = new AuthenticationPlugin();
        $auth->register($this->getNoAttemptPlugin(), 'one');
        $auth->register($this->getSuccessfulAttemptPlugin('foobar'), 'two');
        $auth->register($this->getSuccessfulAttemptPlugin('xyz'), 'three');
        $this->assertSame(
            'foobar',
            $auth->execute(
                $this->getRequest(),
                array(
                    'activate' => array('one', 'two'),
                )
            )->getUserId()
        );
    }

    public function testOptionalAuth()
    {
        $auth = new AuthenticationPlugin();
        $auth->register($this->getNoAttemptPlugin(), 'one');
        $this->assertNull(
            $auth->execute(
                $this->getRequest(),
                array('require' => false)
            )
        );
    }

    private function getRequest()
    {
        return new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );
    }

    private function getSuccessfulAttemptPlugin($userId = 'foo')
    {
        $userInfo = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\UserInfoInterface')->getMock();
        $userInfo->method('getUserId')->willReturn($userId);

        $plugin = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $plugin->method('isAttempt')->willReturn(true);
        $plugin->method('getScheme')->willReturn('Basic');
        $plugin->method('getAuthParams')->willReturn(array('realm' => 'Basic Foo'));
        $plugin->method('execute')->willReturn($userInfo);

        return $plugin;
    }

    private function getNoAttemptPlugin($scheme = 'Basic')
    {
        $plugin = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $plugin->method('isAttempt')->willReturn(false);
        $plugin->method('getScheme')->willReturn($scheme);
        $plugin->method('getAuthParams')->willReturn(array('realm' => 'Foo'));
        $plugin->method('execute')->willReturn(null);

        return $plugin;
    }

    private function getFailedAttemptPlugin($scheme = 'Basic')
    {
        $plugin = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $plugin->method('isAttempt')->willReturn(true);
        $plugin->method('getScheme')->willReturn($scheme);
        $plugin->method('getAuthParams')->willReturn(array('realm' => 'Foo'));

        $e = new UnauthorizedException(
            'invalid_credentials',
            'provided credentials not valid'
        );
        $e->addScheme($scheme, $plugin->getAuthParams());

        $plugin->method('execute')->will($this->throwException($e));

        return $plugin;
    }
}
