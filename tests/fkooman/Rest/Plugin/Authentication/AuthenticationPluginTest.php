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

require_once __DIR__.'/Test/TestAuthentication.php';
require_once __DIR__.'/Test/TestUserInfo.php';

use fkooman\Http\Request;
use PHPUnit_Framework_TestCase;
use fkooman\Rest\Plugin\Authentication\Test\TestAuthentication;
use fkooman\Rest\Service;

class AuthenticationPluginTest extends PHPUnit_Framework_TestCase
{
    public function testSimpleAuthenticated()
    {
        $request = new Request(
             array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Test foo',
            )
        );

        $a = new AuthenticationPlugin();
        $a->register(new TestAuthentication(), 'test');
        $a->init(new Service());
        $this->assertSame('foo', $a->execute($request, array())->getUserId());
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage no_credentials
     */
    public function testSimpleNotAuthenticated()
    {
        $request = new Request(
             array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Test bar',
            )
        );

        $a = new AuthenticationPlugin();
        $a->register(new TestAuthentication(), 'test');
        $a->init(new Service());
        $a->execute($request, array());
    }

    public function testSimpleNotAuthenticatedNotRequired()
    {
        $request = new Request(
             array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Test bar',
            )
        );

        $a = new AuthenticationPlugin();
        $a->register(new TestAuthentication(), 'test');
        $a->init(new Service());
        $this->assertNull(
            $a->execute(
                $request,
                array(
                    'require' => false,
                )
            )
        );
    }

    public function testSimpleAuthenticatedActivate()
    {
        $request = new Request(
             array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Test foo',
            )
        );

        $a = new AuthenticationPlugin();
        $a->register(new TestAuthentication(), 'test');
        $a->init(new Service());
        $this->assertSame(
            'foo',
            $a->execute(
                $request,
                array(
                    'activate' => array('test'),
                )
            )->getUserId()
        );
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage plugin not registered
     */
    public function testSimpleUnregisteredPlugin()
    {
        $request = new Request(
             array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'HTTP_AUTHORIZATION' => 'Test bar',
            )
        );

        $a = new AuthenticationPlugin();
        $a->register(new TestAuthentication(), 'testa');
        $a->register(new TestAuthentication(), 'testb');
        $a->register(new TestAuthentication(), 'testc');
        $a->init(new Service());
        $a->execute($request, array('activate' => array('test')));
    }
}
