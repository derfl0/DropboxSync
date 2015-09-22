# DropboxSync
A Studip Plugin to Sync with Dropbox

Install:
- Rename key_demo.php to key.php
- Go to https://www.dropbox.com/developers/apps
- Generate API Key
- Paste into key.php
- Add Redirect URI to Dropbox (https://yourstudip/plugins.php/dropboxsyncplugin/show/auth)

Sync runs with 30 Threads by default. To change edit const MAX_THREADS in DropboxThreadstarter.php
