<?php

/**
 * Copyright 2014 François Kooman <fkooman@tuxed.net>.
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

namespace fkooman\Rest\Plugin\Authentication\Mellon;

use fkooman\Http\Request;
use PHPUnit_Framework_TestCase;

class MellonAuthenticationTest extends PHPUnit_Framework_TestCase
{
    public function testMellonAuthCorrect()
    {
        $request = new Request(
            array(
                'SERVER_NAME' => 'www.example.org',
                'SERVER_PORT' => 80,
                'QUERY_STRING' => '',
                'REQUEST_URI' => '/',
                'SCRIPT_NAME' => '/index.php',
                'REQUEST_METHOD' => 'GET',
                'MELLON_NAME_ID' => 'foo',
            )
        );
        $basicAuth = new MellonAuthentication('MELLON_NAME_ID');
        $userInfo = $basicAuth->execute($request, array());
        $this->assertEquals('foo', $userInfo->getUserId());
    }

    /**
     * @expectedException fkooman\Http\Exception\UnauthorizedException
     * @expectedExceptionMessage no_credentials
     */
    public function testMissingNameId()
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
        $basicAuth = new MellonAuthentication('MELLON_NAME_ID');
        $basicAuth->execute($request, array());
    }
}
