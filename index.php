<?php

require 'vendor/autoload.php';
require './GitLabel.php';

$dotenv = new Dotenv\Dotenv(__DIR__);
$dotenv->load();

$git = (new GitLabel(
    $gitApiToken = getenv('GITHUB_API_TOKEN'),
    $labelUrl = "https://gist.githubusercontent.com/iforwms/fabbbe262c344cbee3cde07360e84f34/raw/labels.json",
    $repoOwner = 'DominoChinese',
    $repoName = 'dc-website'
))->synchroniseLabels();
