<?php

require 'vendor/autoload.php';
require './GitUtils.php';
require './env.php';

if (defined('STDIN')) {
    echo PHP_EOL;
    echo "What would you like to do?".PHP_EOL;
    echo PHP_EOL;
    echo "1) Update labels from a template".PHP_EOL;
    echo "2) Copy issues from one repo to another".PHP_EOL;
    echo PHP_EOL;
    echo "Please select 1 or 2: ";

    $handle = fopen("php://stdin", "r");
    $selection = trim(fgets($handle));
    if ($selection != 1 && $selection != 2) {
        echo PHP_EOL."Invalid selection.";
        exit;
    }
    fclose($handle);

    if ($selection == 1) {
        echo PHP_EOL."Enter the repo you want to copy the labels to.".PHP_EOL;
        echo "Optionally add a second argument to delete labels not in the template.".PHP_EOL;
        echo PHP_EOL;
        echo "(repo_owner/repo_name [delete issue]): ";

        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));

        echo PHP_EOL;

        $input = explode(' ', $input);

        if (isset($input) && isset($input[0])) {
            return (new GitUtils($githubToken))
                ->beginSynchroniseLabels(
                    $labelUrl = "https://gist.githubusercontent.com/iforwms/fabbbe262c344cbee3cde07360e84f34/raw/labels.json",
                    $repoFullName = $input[0],
                    $forceDelete = isset($input[1])
                );
        }

        echo PHP_EOL."Done!";

        fclose($handle);
    }

    if ($selection == 2) {
        echo PHP_EOL."Enter the repos you want to copy the issues to and from.".PHP_EOL;
        echo "Optionally add a third argument to close all isues in 'from' repo.".PHP_EOL;
        echo PHP_EOL;
        echo "(from_repo_owner/from_repo_name to_repo_owner/to_repo_name [close issues]): ";

        $handle = fopen("php://stdin", "r");
        $input = trim(fgets($handle));

        echo PHP_EOL;

        $input = explode(' ', $input);

        if (isset($input) && isset($input[0]) && isset($input[1])) {
            return (new GitUtils($githubToken))
                ->copyIssues(
                    $fromRepo = $input[0],
                    $toRepo = $input[1],
                    $forceDelete = isset($input[2])
                );
        }

        echo PHP_EOL."Done!";

        fclose($handle);
    }
}
