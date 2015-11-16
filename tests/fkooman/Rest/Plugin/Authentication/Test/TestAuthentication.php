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
namespace fkooman\Rest\Plugin\Authentication\Test;

use fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface;
use fkooman\Http\Request;
use fkooman\Rest\Service;
use fkooman\Http\Exception\UnauthorizedException;

class TestAuthentication implements AuthenticationPluginInterface
{
    public function __construct()
    {
    }

    public function isAuthenticated(Request $request)
    {
        if ('Test foo' === $request->getHeader('Authorization')) {
            return new TestUserInfo('foo');
        }

        return false;
    }

    public function requestAuthentication()
    {
        $e = new UnauthorizedException(
            'no_credentials',
            'credentials must be provided'
        );
        $e->addScheme('Test', array('realm' => 'TestRealm'));

        throw $e;
    }

    public function init(Service $service)
    {
        // NOP
    }
}
