<?php

/**
 * This is a single class that updates labels in a GitHub repository with
 * those from a label template.
 *
 * If labels currently exist, their color and description are updates.
 *
 * If a remote label does not exist in the template, they are removed.
 *
 * PHP version 5
 *
 * @category Utility
 * @package  GitHub_Label
 * @author   Ifor Waldo Williams <ifor@designedbywaldo.com>
 * @license  [http://mit.com] MIT
 * @link     http://github.com/iforwms/gitLabel
 */

/**
 * A PHP Class for Synchronising GitHub Repo Labels
 *
 * @category Utility
 * @package  GitHub_Label
 * @author   Ifor Waldo Williams <ifor@designedbywaldo.com>
 * @license  [http://mit.com] MIT
 * @link     http://github.com/iforwms/gitLabel
 */
class GitUtils
{
    protected $client;
    protected $gitApiToken;
    protected $labelUrl;
    protected $repoFullName;
    protected $fromRepo;
    protected $toRepo;
    protected $issuesToCopy;
    protected $templateLabels;
    protected $remoteLabels;

    /**
     * GitLabel Constructor.
     *
     * @param String $gitApiToken GitHub API Token
     */
    public function __construct($gitApiToken)
    {
        $this->client = new GuzzleHttp\Client(
            [
                'base_uri' => "https://api.github.com/",
                'timeout'  => 5,
            ]
        );
        $this->gitApiToken = $gitApiToken;
    }

    /**
     * Copy issues from one repository to another.
     *
     * @param string  $fromRepo    Owner/Name of repo to copy issues from.
     * @param string  $toRepo      Owner/Name of repo to copy issues to.
     * @param boolean $forceDelete Whether issues should be deleted from repo.
     *
     * @return null
     */
    public function copyIssues($fromRepo, $toRepo, $forceDelete = false)
    {
        foreach ($this->fetchIssuesFromRepo($fromRepo) as $issue) {
            echo "Copying issue from '{$fromRepo}' to '{$toRepo}': {$issue['title']}".PHP_EOL;

            $this->createIssue($toRepo, $this->parseRemoteIssue($issue));

            if ($forceDelete) {
                echo "{$fromRepo} Closing issue: {$issue['title']}".PHP_EOL;

                $this->closeIssue($fromRepo, $issue['number']);
            }
        }
    }

    /**
     * Closes an issue in a repo.
     *
     * @param string $repoUrl     Repo owner and name.
     * @param string $issueNumber Issue name.
     *
     * @return null
     */
    public function closeIssue($repoUrl, $issueNumber)
    {
        $this->client->request(
            'PATCH',
            "repos/{$repoUrl}/issues/{$issueNumber}",
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => "token {$this->gitApiToken}",
                    'Accept' => 'application/vnd.github.symmetra-preview+json'
                ],
                'body' => json_encode(['state' => 'closed'])
            ]
        );
    }

    /**
     * Create a new Gith]Hub issue.
     *
     * @param string $repoUrl Full owner and name of repo.
     * @param object $issue   GitHub issue JSON string.
     *
     * @return null
     */
    public function createIssue($repoUrl, $issue)
    {
        $this->client->request(
            'POST',
            "repos/{$repoUrl}/issues",
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => "token {$this->gitApiToken}",
                    'Accept' => 'application/vnd.github.symmetra-preview+json'
                ],
                'body' => $issue
            ]
        );
    }

    /**
     * Parse full issue object into issue request object.
     *
     * @param object $issue GitHub issue object.
     *
     * @return array
     */
    public function parseRemoteIssue($issue)
    {
        $assignees = [];
        $labels = [];

        foreach ($issue['assignees'] as $assignee) {
            $assignees[] = $assignee['login'];
        }

        foreach ($issue['labels'] as $label) {
            $labels[] = $label['name'];
        }

        return json_encode(
            [
                'title' => $issue['title'],
                'body' => $issue['body'],
                'assignees' => $assignees,
                'labels' => $labels,
            ]
        );
    }

    /**
     * Fetches issues from a repo.
     *
     * @param string $repoName Repo owner and name.
     *
     * @return array
     */
    public function fetchIssuesFromRepo($repoName)
    {
        $repoIssues = $this->client->request(
            'GET',
            "repos/{$repoName}/issues",
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => "token {$this->gitApiToken}",
                    'Accept' => 'application/vnd.github.symmetra-preview+json'
                ],
            ]
        )->getBody();

        return json_decode($repoIssues->getContents(), true);
    }

    /**
     * Fetch a template JSON file of labels.
     *
     * @return Array An array of labels
     */
    protected function fetchTemplateLabels()
    {
        $labels = $this->client
            ->request('GET', $this->labelUrl, ['verify' => false])
            ->getBody()
            ->getContents();

        return json_decode($labels, true);
    }

    /**
     * Fetches all labels for a repository
     *
     * @return Array An array of label objects
     */
    protected function fetchRemoteLabels()
    {
        $repoLabels = $this->client->request(
            'GET',
            "repos/{$this->repoFullName}/labels",
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => "token {$this->gitApiToken}",
                    'Accept' => 'application/vnd.github.symmetra-preview+json'
                ],
            ]
        )->getBody();

        return json_decode($repoLabels->getContents(), true);
    }

    /**
     * Check if a template label already exists in remote repo.
     *
     * @param string $templateName Template label name.
     * @param string $remoteName   Remote label name.
     *
     * @return boolean
     */
    public function labelExistsInRepo($templateName, $remoteName)
    {
        return mb_strtolower($templateName) ===
            mb_strtolower($remoteName);

        // $regex = "/(?::[\w]+:)? ([\w ]+)/";
        // $replacement = '${1}';

        // return mb_strtolower(preg_replace($regex, $replacement, $templateName)) ===
        //     mb_strtolower(preg_replace($regex, $replacement, $remoteName));
    }

    /**
     * Prepare class for synchronising labels.
     *
     * @param string  $labelUrl     URL for label template.
     * @param string  $repoFullName Full name of repo.
     * @param boolean $forceDelete  Whether labels not in template should be deleted.
     *
     * @return null
     */
    public function beginSynchroniseLabels($labelUrl, $repoFullName, $forceDelete)
    {

        $this->labelUrl = $labelUrl;
        $this->repoFullName = $repoFullName;
        $this->templateLabels = $this->fetchTemplateLabels();
        $this->remoteLabels = $this->fetchRemoteLabels();
        $this->synchroniseLabels($forceDelete);
    }

    /**
     * Update labels from a template to a repository.
     *
     * @param boolean $forceDelete Force deletion of remote labels not in template.
     *
     * @return null
     */
    public function synchroniseLabels($forceDelete = false)
    {
        foreach ($this->templateLabels as $templateLabel) {
            $inRemoteRepo = false;

            foreach ($this->remoteLabels as $remoteLabel) {
                if ($this->labelExistsInRepo($remoteLabel['name'], $templateLabel['name'])) {
                    $inRemoteRepo = true;

                    echo "{$this->repoFullName} Updating label: '{$remoteLabel['name']}' to '{$templateLabel['name']}'".PHP_EOL;

                    $this->updateLabel($templateLabel, $remoteLabel['name']);
                }
            }

            if (!$inRemoteRepo) {
                echo "{$this->repoFullName} Creating new label: {$templateLabel['name']}".PHP_EOL;

                $this->createLabel($templateLabel);
            }
        }

        foreach ($this->remoteLabels as $remoteLabel) {
            $inTemplate = false;

            foreach ($this->templateLabels as $templateLabel) {
                if ($this->labelExistsInRepo($remoteLabel['name'], $templateLabel['name'])) {
                    $inTemplate = true;
                }
            }

            if (!$inTemplate) {
                if ($forceDelete) {
                    echo "{$this->repoFullName} Deleting label: {$remoteLabel['name']}".PHP_EOL;

                    $this->deleteLabel($remoteLabel['name']);
                } else {
                    echo "{$this->repoFullName} Setting label color to black: {$remoteLabel['name']}".PHP_EOL;

                    $remoteLabel['color'] = "22292f";

                    $this->updateLabel($remoteLabel);
                }

            }
        }
    }

    /**
     * Create a label in the specified repository.
     *
     * @param object $label Label object
     *
     * @return null
     */
    public function createLabel($label)
    {
        $label['color'] = str_replace('#', '', $label['color']);

        $label = json_encode($label);

        $this->client->request(
            'POST',
            "repos/{$this->repoFullName}/labels",
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => "token {$this->gitApiToken}",
                    'Accept' => 'application/vnd.github.symmetra-preview+json'
                ],
                'body' => $label
            ]
        );
    }

    /**
     * Update a label in the specified repository.
     *
     * @param Object $label Label object
     *
     * @return Null
     */
    public function updateLabel($label, $currentName = null)
    {
        $label['color'] = str_replace('#', '', $label['color']);

        $encodedLabel = json_encode($label);

        $labelName = $currentName ? $currentName : $label['name'];

        $this->client->request(
            'PATCH',
            "repos/{$this->repoFullName}/labels/{$labelName}",
            [
                'verify' => false,
                'headers' => [
                    'Authorization' => "token {$this->gitApiToken}",
                    'Accept' => 'application/vnd.github.symmetra-preview+json'
                ],
                'body' => $encodedLabel
            ]
        );
    }

    /**
     * Delete a label in the specified repository.
     *
     * @param String $labelName Label name
     *
     * @return Null
     */
    public function deleteLabel($labelName)
    {
        $this->client->request(
            'DELETE',
            "repos/{$this->repoFullName}/labels/{$labelName}",
            [
                'verify' => false,
                'headers' => ['Authorization' => "token {$this->gitApiToken}"]
            ]
        );
    }
}
