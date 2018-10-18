<?php

/**
 * This is a single class that replaces all labels in a GitHub repository with those
 * from a label template.
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
 * A PHP Class for Resetting GitHub Repo Labels
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
     * GitLabel Constructor
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
    protected function fetchLabelsFromRepo()
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
     * Remove an array of labels from a repository.
     *
     * @param Array $repoLabels An array of labels
     *
     * @return Null
     */
    protected function synchroniseLabels($repoLabels)
    {
        foreach ($repoLabels as $label) {
            if (! in_array($label['name'], $this->templateLabels)) {
                $this->client->request(
                    'DELETE',
                    "/repos/{$this->repoOwner}/{$this->repoName}/labels/{$label['name']}",
                    [
                        'verify' => false,
                        'headers' => ['Authorization' => 'token ' . $this->gitApiToken]
                    ]
                );
            }
        }
    }

    /**
     * Create labels in the specified repository.
     *
     * @param Array $labels Array of labels
     *
     * @return Null
     */
    public function createLabelsInRepo($labels)
    {
        foreach ($labels as $label) {
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
    }
}
