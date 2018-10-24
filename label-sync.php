<?php

require 'vendor/autoload.php';
require './GitUtils.php';
require './env.php';

if (defined('STDIN')) {
    if (isset($argv) && isset($argv[1])) {
        return (new GitUtils(
            $gitApiToken = $githubToken
        ))
        ->beginSynchroniseLabels(
            $labelUrl = "https://gist.githubusercontent.com/iforwms/fabbbe262c344cbee3cde07360e84f34/raw/labels.json",
            $repoFullName = $argv[1],
            $forceDelete = isset($argv[2])
        );
    }

    echo PHP_EOL;
    echo "ERROR: Failed to run.".PHP_EOL;
    echo PHP_EOL;
    echo "Please provide the [REPO_OWNER/REPO_NAME] as the first arguement, and optionally whether you wan to delete non-existing labels.".PHP_EOL;
    echo PHP_EOL;
    echo "E.g. php index.php repo_owner repo_name.";
    echo PHP_EOL;

    return false;
}

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
