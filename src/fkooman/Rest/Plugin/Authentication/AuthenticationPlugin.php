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

use fkooman\Http\Exception\UnauthorizedException;
use fkooman\Http\Request;
use fkooman\Rest\Service;
use fkooman\Rest\ServicePluginInterface;
use RuntimeException;

class AuthenticationPlugin implements ServicePluginInterface
{
    /** @var array */
    private $plugins;

    public function __construct()
    {
        $this->plugins = array();
    }

    public function init(Service $service)
    {
        foreach ($this->plugins as $friendlyName => $plugin) {
            if (method_exists($plugin, 'init')) {
                $plugin->init($service);
            }
        }
    }

    public function register(AuthenticationPluginInterface $plugin, $friendlyName)
    {
        $this->plugins[$friendlyName] = $plugin;
    }

    public function execute(Request $request, array $routeConfig)
    {
        if (0 === count($this->plugins)) {
            throw new RuntimeException('no authentication plugins registered');
        }

        $checkPlugins = array();
        if (array_key_exists('only', $routeConfig)) {
            // only ONE mechanism is supported for this route
            $checkPlugins[] = $this->plugins[$routeConfig['only']];
        } elseif (array_key_exists('or', $routeConfig)) {
            // a number of mechanisms is supported for this route
            foreach ($routeConfig['or'] as $o) {
                $checkPlugins[] = $this->plugins[$o];
            }
        } else {
            $checkPlugins = $this->plugins;
        }

        foreach ($checkPlugins as $plugin) {
            // first check to see if it is an attempt based on Request
            if ($plugin->isAttempt($request)) {
                // it is an attempt, so it MUST succeed
                return $plugin->execute($request, array());
            }
        }

        // check if authentication is optional
        if (array_key_exists('require', $routeConfig)) {
            if (!$routeConfig['require']) {
                return;
            }
        }

        $e = new UnauthorizedException(
            'no_credentials',
            'credentials must be provided'
        );

        foreach ($checkPlugins as $plugin) {
            $e->addScheme($plugin->getScheme(), $plugin->getAuthParams());
        }

        throw $e;
    }
}
