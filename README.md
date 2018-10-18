# GitHub Label Synchroniser

This PHP class creates labels in a GitHub repository from a provided JSON template file. If a label already exists in the remote repository, its colour and description are updated to that of the template.

You can pass a boolean to the `synchroniseLabels` function to force deletion of remote labels which are not in the template file. If deletion is not forced, existing label colours are set to black.

An example template file can be found [here](https://gist.githubusercontent.com/iforwms/fabbbe262c344cbee3cde07360e84f34/raw/labels.json).

## Running

1. Clone the repository.
2. Rename `example.env.php` to `env.php` and update the token to your own.
3. Update the `REPO_NAME` and `REPO_OWNER` in `index.php`.
4. In the command line `cd` into the repo directory and run `php index.php`.
