# GitHub Label Synchroniser

This PHP class creates labels in a GitHub repository from a provided JSON template file. If a label already exists in the remote repository, its colour and description are updated to that of the template.

You can pass a boolean to the `synchroniseLabels` function to force deletion of remote labels which are not in the template file. If deletion is not forced, existing labels colours are set to black.

An example template file can be found [here](https://gist.githubusercontent.com/iforwms/fabbbe262c344cbee3cde07360e84f34/raw/labels.json).
