<?php

class ShowController extends StudipController {

    public function __construct($dispatcher) {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;
    }

    public function before_filter(&$action, &$args) {
        parent::before_filter($action, $args);

        $this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox.php'));

        // Fetch stored client
        $this->sync = new DropboxSync(User::findCurrent()->id);

        // Navigation hack
        PageLayout::setTabNavigation('/links/settings');
        Navigation::activateItem('/links/settings/dropboxsyncplugin');
    }

    public function index_action() {
        $this->authorizeUrl = $this->getWebAuth()->start();

        // Sidebar
        $sidebar = Sidebar::Get();
        $sidebar->setImage($this->plugin->getPluginURL() . '/assets/sidebar-dropbox.png');

        $client = $this->sync->getClient();
        if ($client) {
            try {
                $account = $client->getAccountInfo();
                $this->displayname = $account['display_name'];
            } catch (Exception $ex) {
                
            }
        }

        //$actions = new ActionsWidget();
        //$sidebar->addWidget($actions);
    }

    public function auth_action() {
        if (Request::submitted('code')) {
            list($accessToken, $userId, $urlState) = $this->getWebAuth()->finish($_GET);
            $this->sync->secret = $accessToken;
            $this->sync->store();
        }
        $this->redirect('show/index');
    }

    public function sync_action() {

        // Since this can take quite a while
        if (time() - $_SESSION['dropbox'] > 86400) {
            $_SESSION['dropbox'] = time();
            $this->sync->sync();
            unset($_SESSION['dropbox']);
        }
        $this->redirect('show/index');
    }

    public function kill_action() {
        $this->sync->kill();
        $this->redirect('show/index');
    }

    private function getWebAuth() {
        if (file_exists(dirname(__DIR__) . '/key.php')) {
            include dirname(__DIR__) . '/key.php';
        } else {
            $key = Config::get()->DROPBOX_APP_KEY;
            $secret = Config::get()->DROPBOX_APP_SECRET;
        }
        $appInfo = new Dropbox\AppInfo($key, $secret);
        return new Dropbox\WebAuth($appInfo, "Studip/1.0", $GLOBALS['ABSOLUTE_URI_STUDIP'] . 'plugins.php/dropboxsyncplugin/show/auth', new Dropbox\ArrayEntryStore($_SESSION, 'dropbox-auth-csrf-token'));
    }

    // customized #url_for for plugins
    function url_for($to) {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    }

}
