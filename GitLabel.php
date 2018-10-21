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
class GitLabel
{
    protected $client;
    protected $gitApiToken;
    protected $labelUrl;
    protected $repoOwner;
    protected $repoName;
    protected $templateLabels;
    protected $remoteLabels;

    /**
     * GitLabel Constructor.
     *
     * @param String $gitApiToken GitHub API Token
     * @param String $labelUrl    URL to Label Template JSON
     * @param String $repoOwner   Repo Owner
     * @param String $repoName    Repo Name
     */
    public function __construct($gitApiToken, $labelUrl, $repoOwner, $repoName)
    {
        $this->client = new GuzzleHttp\Client(
            [
                'base_uri' => 'https://api.github.com',
                'timeout'  => 5,
            ]
        );
        $this->gitApiToken = $gitApiToken;
        $this->labelUrl = $labelUrl;
        $this->repoOwner = $repoOwner;
        $this->repoName = $repoName;
        $this->templateLabels = $this->fetchTemplateLabels();
        $this->remoteLabels = $this->fetchRemoteLabels();
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
            "/repos/{$this->repoOwner}/{$this->repoName}/labels",
            [
                'verify' => false,
                'headers' => ['Authorization' => 'token ' . $this->gitApiToken]
            ]
        )->getBody();

        return json_decode($repoLabels->getContents(), true);
    }

    /**
     * Update labels from a template to a repository.
     *
     * @param Boolean $forceDelete Force deletion of remote labels not in template.
     *
     * @return Null
     */
    public function synchroniseLabels($forceDelete = false)
    {
        foreach ($this->templateLabels as $templateLabel) {
            $inRemoteRepo = false;

            foreach ($this->remoteLabels as $remoteLabel) {
                if (mb_strtolower($templateLabel['name']) === mb_strtolower($remoteLabel['name'])) {
                    $inRemoteRepo = true;

                    echo "{$this->repoOwner}/{$this->repoName} Updating label: ".$templateLabel['name'].PHP_EOL;

                    $this->updateLabel($templateLabel);
                }
            }

            if (!$inRemoteRepo) {
                echo "{$this->repoOwner}/{$this->repoName} Creating new label: ".$templateLabel['name'].PHP_EOL;

                $this->createLabel($templateLabel);
            }
        }

        foreach ($this->remoteLabels as $remoteLabel) {
            $inTemplate = false;

            foreach ($this->templateLabels as $templateLabel) {
                if (mb_strtolower($templateLabel['name']) === mb_strtolower($remoteLabel['name'])) {
                    $inTemplate = true;
                }
            }

            if (!$inTemplate) {
                if ($forceDelete) {
                    echo "{$this->repoOwner}/{$this->repoName} Deleting label: ".$remoteLabel['name'].PHP_EOL;

                    $this->deleteLabel($remoteLabel['name']);
                } else {
                    echo "{$this->repoOwner}/{$this->repoName} Setting label color to black: ".$remoteLabel['name'].PHP_EOL;

                    $remoteLabel['color'] = "22292f";

                    $this->updateLabel($remoteLabel);
                }

            }
        }
    }

    /**
     * Create a label in the specified repository.
     *
     * @param Object $label Label object
     *
     * @return Null
     */
    public function createLabel($label)
    {
        $label['color'] = str_replace('#', '', $label['color']);

        $label = json_encode($label);

        $this->client->request(
            'POST',
            "/repos/{$this->repoOwner}/{$this->repoName}/labels",
            [
                'verify' => false,
                'headers' => ['Authorization' => 'token ' . $this->gitApiToken],
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
    public function updateLabel($label)
    {
        $label['color'] = str_replace('#', '', $label['color']);

        $encodedLabel = json_encode($label);

        $this->client->request(
            'PATCH',
            "/repos/{$this->repoOwner}/{$this->repoName}/labels/".$label['name'],
            [
                'verify' => false,
                'headers' => ['Authorization' => 'token ' . $this->gitApiToken],
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
            "/repos/{$this->repoOwner}/{$this->repoName}/labels/{$labelName}",
            [
                'verify' => false,
                'headers' => ['Authorization' => 'token ' . $this->gitApiToken]
            ]
        );
    }
}
