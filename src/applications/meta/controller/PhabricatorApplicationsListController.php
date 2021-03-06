<?php

/*
 * Copyright 2012 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

final class PhabricatorApplicationsListController
  extends PhabricatorApplicationsController {

  public function processRequest() {
    $request = $this->getRequest();
    $user = $request->getUser();

    $applications = PhabricatorApplication::getAllInstalledApplications();
    $applications = msort($applications, 'getName');

    foreach ($applications as $key => $application) {
      if (!$application->shouldAppearInLaunchView()) {
        unset($applications[$key]);
      }
    }

    $status = array();
    foreach ($applications as $key => $application) {
      $status[$key] = $application->loadStatus($user);
    }

    $views = array();
    foreach ($applications as $key => $application) {
      $views[] = id(new PhabricatorApplicationLaunchView())
        ->setApplication($application)
        ->setApplicationStatus(idx($status, $key, array()))
        ->setUser($user);
    }

    $view = phutil_render_tag(
      'div',
      array(
        'class' => 'phabricator-application-list',
      ),
      id(new AphrontNullView())->appendChild($views)->render());

    return $this->buildStandardPageResponse(
      $view,
      array(
        'title' => 'Applications',
      ));
  }

}

