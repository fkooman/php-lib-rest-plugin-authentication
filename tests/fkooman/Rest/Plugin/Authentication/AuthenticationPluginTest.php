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

use fkooman\Rest\Service;
use fkooman\Http\Request;
use PHPUnit_Framework_TestCase;
use fkooman\Http\Exception\UnauthorizedException;

class AuthenticationPluginTest extends PHPUnit_Framework_TestCase
{
    public function testNoAuthenticationAttemptWithTwoRegisteredMethods()
    {
        try {
            $service = new Service();
            $auth = new AuthenticationPlugin();

            $basic = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
            $basic->method('isAttempt')->willReturn(false);
            $basic->method('getScheme')->willReturn('Basic');
            $basic->method('init')->willReturn(null);
            $basic->method('getAuthParams')->willReturn(array('realm' => 'Basic Foo'));

            $bearer = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
            $bearer->method('isAttempt')->willReturn(false);
            $bearer->method('getScheme')->willReturn('Bearer');
            $bearer->method('init')->willReturn(null);
            $bearer->method('getAuthParams')->willReturn(array('realm' => 'Bearer Foo'));

            $auth->register($basic, 'basic');
            $auth->register($bearer, 'bearer');
            $auth->init($service);

            $request = new Request(
                array(
                    'SERVER_NAME' => 'www.example.org',
                    'SERVER_PORT' => 80,
                    'QUERY_STRING' => '',
                    'REQUEST_URI' => '/',
                    'SCRIPT_NAME' => '/index.php',
                    'REQUEST_METHOD' => 'GET',
                )
            );

            $auth->execute($request, array());
            $this->assertTrue(false);
        } catch (UnauthorizedException $e) {
            $this->assertEquals(
                array(
                    'HTTP/1.1 401 Unauthorized',
                    'Content-Type: application/json',
                    'Www-Authenticate: Basic realm="Basic Foo", Bearer realm="Bearer Foo"',
                    '',
                    '{"error":"no_credentials","error_description":"credentials must be provided"}',
                ),
                $e->getJsonResponse()->toArray()
            );
        }
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage no authentication plugins registered
     */
    public function testNoAuthPlugins()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );
        $auth = new AuthenticationPlugin();
        $auth->execute($request, array());
    }

    public function testAuthAttempt()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );

        $auth = new AuthenticationPlugin();

        $basicUserInfo = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\UserInfoInterface')->getMock();
        $basicUserInfo->method('getUserId')->willReturn('foo');

        $basic = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $basic->method('isAttempt')->willReturn(true);
        $basic->method('getScheme')->willReturn('Basic');
        $basic->method('getAuthParams')->willReturn(array('realm' => 'Basic Foo'));
        $basic->method('execute')->willReturn($basicUserInfo);

        $auth->register($basic, 'basic');

        $this->assertEquals('foo', $auth->execute($request, array())->getUserId());
    }

    public function testOnly()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );

        $auth = new AuthenticationPlugin();

        $userOne = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\UserInfoInterface')->getMock();
        $userOne->method('getUserId')->willReturn('foo');

        $userTwo = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\UserInfoInterface')->getMock();
        $userTwo->method('getUserId')->willReturn('bar');

        $one = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $one->method('isAttempt')->willReturn(true);
        $one->method('getScheme')->willReturn('Basic');
        $one->method('getAuthParams')->willReturn(array('realm' => 'Basic Foo'));
        $one->method('execute')->willReturn($userOne);

        $two = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $two->method('isAttempt')->willReturn(true);
        $two->method('getScheme')->willReturn('Basic');
        $two->method('getAuthParams')->willReturn(array('realm' => 'Basic Foo'));
        $two->method('execute')->willReturn($userTwo);

        $auth->register($one, 'one');
        $auth->register($two, 'two');

        $this->assertSame('bar', $auth->execute($request, array('only' => 'two'))->getUserId());
    }

    public function testOr()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
            )
        );

        $auth = new AuthenticationPlugin();

        $userOne = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\UserInfoInterface')->getMock();
        $userOne->method('getUserId')->willReturn('foo');

        $userTwo = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\UserInfoInterface')->getMock();
        $userTwo->method('getUserId')->willReturn('bar');

        $one = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $one->method('isAttempt')->willReturn(false);
        $one->method('getScheme')->willReturn('Basic');
        $one->method('getAuthParams')->willReturn(array('realm' => 'Basic Foo'));
        $one->method('execute')->willReturn($userOne);

        $two = $this->getMockBuilder('fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface')->getMock();
        $two->method('isAttempt')->willReturn(true);
        $two->method('getScheme')->willReturn('Basic');
        $two->method('getAuthParams')->willReturn(array('realm' => 'Basic Foo'));
        $two->method('execute')->willReturn($userTwo);

        $auth->register($one, 'one');
        $auth->register($two, 'two');

        $this->assertSame('bar', $auth->execute($request, array('or' => array('one', 'two')))->getUserId());
    }
}
