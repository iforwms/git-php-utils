<?php

require 'vendor/autoload.php';
require './GitLabel.php';
require './env.php';

$git = (new GitLabel(
    $gitApiToken = $githubToken,
    $labelUrl = "https://gist.githubusercontent.com/iforwms/fabbbe262c344cbee3cde07360e84f34/raw/labels.json",
    $repoOwner = 'DominoChinese',
    $repoName = 'dc-app'
))->synchroniseLabels($forceDelete = false);
