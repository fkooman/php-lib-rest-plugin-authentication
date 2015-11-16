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
            $plugin->init($service);
        }
    }

    public function register(AuthenticationPluginInterface $plugin, $friendlyName)
    {
        $this->plugins[$friendlyName] = $plugin;
    }

    public function execute(Request $request, array $routeConfig)
    {
        if (!array_key_exists('activate', $routeConfig)) {
            // if no plugin is specified here, we assume only one is registered
            // and use that
            if (1 !== count($this->plugins)) {
                throw new RuntimeException('unable to determine the authentication plugin');
            }
            $activePlugin = array_values($this->plugins)[0];
        } else {
            if (!is_array($routeConfig['activate']) || 1 !== count($routeConfig['activate'])) {
                throw new RuntimeException('activate key must be array of length 1');
            }
            $activate = array_values($routeConfig['activate'])[0];
            if (!array_key_exists($activate, $this->plugins)) {
                throw new RuntimeException('plugin not registered');
            }
            $activePlugin = $this->plugins[$activate];
        }

        $isAuthenticated = $activePlugin->isAuthenticated($request);
        if (false !== $isAuthenticated) {
            return $isAuthenticated;
        }

        // check if authentication is optional
        if (array_key_exists('require', $routeConfig)) {
            if (!$routeConfig['require']) {
                return;
            }
        }

        return $activePlugin->requestAuthentication($request);
    }
}
