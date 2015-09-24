<?php

require_once 'dropbox/autoload.php';

/**
 * DropboxsyncPlugin.class.php
 *
 * ...
 *
 * @author  Florian Bieringer <florian.bieringer@uni-passau.de>
 * @version 0.1a
 */
class DropboxsyncPlugin extends StudIPPlugin implements SystemPlugin {

    public function __construct() {
        parent::__construct();

        if (Navigation::hasItem('/links/settings')) {
            $navigation = new AutoNavigation(_('DropboxSync'));
            $navigation->setURL(PluginEngine::GetURL($this, array(), 'show'));
            Navigation::addItem('/links/settings/dropboxsyncplugin', $navigation);
        }
    }

    public function perform($unconsumed_path) {
        $this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
                $this->getPluginPath(), rtrim(PluginEngine::getLink($this, array(), null), '/'), 'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);
    }

    public static function onEnable($pluginId) {

        // Insert cronjob
        require_once "DropboxCronjob.php";
        $task = new DropboxCronjob();
        $task_id = CronjobScheduler::getInstance()->registerTask($task);
        CronjobScheduler::schedulePeriodic($task_id, -1)->activate();
    }

    public static function onDisable($pluginId) {
        
        // Delete Cronjob
        $task = CronjobTask::findByClass("DropboxCronjob");
        CronjobScheduler::getInstance()->cancelByTask($task[0]->id);
        $task->delete();

        parent::onDisable($pluginId);
    }

    private function setupAutoload() {
        if (class_exists('StudipAutoloader')) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }

}
