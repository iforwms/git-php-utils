<?php

require 'vendor/autoload.php';
require './GitUtils.php';
require './env.php';

if (defined('STDIN')) {
    if (isset($argv) && isset($argv[1]) && isset($argv[2])) {
        return (new GitUtils(
            $gitApiToken = $githubToken
        ))
        ->copyIssues(
            $fromRepo = $argv[1],
            $toRepo = $argv[2],
            $forceDelete = isset($argv[3])
        );
    }

    echo PHP_EOL;
    echo "ERROR: Failed to run.".PHP_EOL;
    echo PHP_EOL;
    echo "Please provide the repo to copy from [REPO_OWNER/REPO_NAME] and the repo to copy to [REPO_OWNER/REPO_NAME] as the first and second arguements respectively.".PHP_EOL;
    echo PHP_EOL;
    echo "E.g. php issue-copy.php FROM_REPO_OWNER/FROM_REPO_NAME TO_REPO_OWNER/TO_REPO_NAME";
    echo PHP_EOL;

    return false;
}

echo "This should be run via the command line.";
return false;
