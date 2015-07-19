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
use fkooman\Rest\Plugin\Authentication\AuthenticationPluginInterface;
use fkooman\Http\Exception\UnauthorizedException;

class MellonAuthentication implements AuthenticationPluginInterface
{
    /** @var string */
    private $userIdAttribute;

    /** @var array */
    private $authParams;

    public function __construct($userIdAttribute = 'MELLON_NAME_ID', array $authParams = array())
    {
        $this->userIdAttribute = $userIdAttribute;
        $this->authParams = $authParams;
    }

    public function getScheme()
    {
        return 'Mellon';
    }

    public function getAuthParams()
    {
        return $this->authParams;
    }

    public function isAttempt(Request $request)
    {
        return null !== $request->getHeader($this->userIdAttribute);
    }

    public function execute(Request $request, array $routeConfig)
    {
        if ($this->isAttempt($request)) {
            return new MellonUserInfo(
                $request->getHeader($this->userIdAttribute)
            );
        }

        // no attempt
        if (array_key_exists('requireAuth', $routeConfig)) {
            if (!$routeConfig['requireAuth']) {
                // no authentication required
                return;
            }
        }

        // no attempt, but authentication required
        $e = new UnauthorizedException(
            'no_credentials',
            sprintf('the required header "%s" was not set', $this->userIdAttribute)
        );
        $e->addScheme('Mellon', $this->authParams);
        throw $e;
    }
}
