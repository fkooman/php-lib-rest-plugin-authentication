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

interface AuthenticationPluginInterface
{
    /**
     * @return mixed false when not authenticated, UserInfoInterface when 
     *               authenticated.
     */
    public function isAuthenticated(Request $request);

    /**
     * return response 401 or show html page or something.
     */
    public function requestAuthentication(Request $request);

    /**
     * Init, maybe register URL callbacks etc.
     */
    public function init(Service $service);
}
