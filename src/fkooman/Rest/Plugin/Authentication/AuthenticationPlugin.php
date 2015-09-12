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

    private function getActiveList(array $routeConfig)
    {
        if (!array_key_exists('activate', $routeConfig)) {
            return array_keys($this->plugins);
        }

        foreach (array_keys($this->plugins) as $friendlyName) {
            if (in_array($friendlyName, $routeConfig['activate'])) {
                $active[] = $friendlyName;
            }
        }

        return $active;
    }

    public function execute(Request $request, array $routeConfig)
    {
        $activeList = $this->getActiveList($routeConfig);

        if (0 === count($activeList)) {
            throw new RuntimeException('no active authentication plugins for this route');
        }

        foreach ($activeList as $friendlyName) {
            // first check to see if it is an attempt based on Request
            if ($this->plugins[$friendlyName]->isAttempt($request)) {
                // it is an attempt, so it MUST succeed
                return $this->plugins[$friendlyName]->execute($request, array());
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

        foreach ($activeList as $friendlyName) {
            $e->addScheme(
                $this->plugins[$friendlyName]->getScheme(),
                $this->plugins[$friendlyName]->getAuthParams()
            );
        }

        throw $e;
    }
}
