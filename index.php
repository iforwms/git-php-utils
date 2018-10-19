<?php

require 'vendor/autoload.php';
require './GitLabel.php';
require './env.php';

$repos = [
    ['owner' => 'REPO_OWNER', 'name' => 'REPO_NAME'],
];

foreach ($repos as $repo) {
    (new GitLabel(
        $gitApiToken = $githubToken,
        $labelUrl = "https://gist.githubusercontent.com/iforwms/fabbbe262c344cbee3cde07360e84f34/raw/labels.json",
        $repoOwner = $repo['owner'],
        $repoName = $repo['name']
    ))
    ->synchroniseLabels($forceDelete = false);
}
