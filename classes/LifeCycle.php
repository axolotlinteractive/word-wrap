<?php
/*
    "WordPress Plugin Template" Copyright (C) 2015 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

namespace WordWrap;

use Exception;
use WordWrap\Admin\AdminController;
use WordWrap\AssetManager\AssetManager;
use WordWrap\Configuration\RootConfig;

class LifeCycle extends InstallIndicator {

    /**
     * @var AssetManager the asset manager for this plugin
     */
    public $assetManager;

    /**
     * @var AdminController the controller that runs on admin pages
     */
    public $adminController;

    /**
     * LifeCycle constructor.
     *
     * @param RootConfig $config
     * @param $pluginDirectory
     */
    final function __construct(RootConfig $config, $pluginDirectory) {
        parent::__construct($config, $pluginDirectory);

        $this->initAssets();

        if(is_admin() && $config->LifeCycle->Admin)
            $this->adminController = new AdminController($this, $config->LifeCycle->Admin);
    }

    /**
     * initializes the asset manager as well as any asset locations needed
     */
    private function initAssets() {

        $this->assetManager = new AssetManager();

        foreach($this->rootConfig->LifeCycle->AssetLocation as $assetLocation) {
            $serverDirectory = $this->pluginDirectory . '/' . $assetLocation->location;
            if(!substr($serverDirectory, -1) !== '/')
                $serverDirectory.= '/';

            $this->assetManager->registerAssetType($assetLocation->type, $assetLocation->location, $serverDirectory, $assetLocation->fileExtension);
        }

    }

    /**
     * called the first time the plugin is activated. Runs a number of install helpers
     */
    public final function install() {
        $this->initOptions();

        $this->installDatabase();

        $this->onInstall();

        $this->saveInstalledVersion();

        $this->markAsInstalled();
    }

    /**
     * Removes anything that needs to be removed for this plugin
     */
    public function uninstall() {
        $this->onUninstall();
        $this->unInstallDatabaseTables();
        $this->deleteSavedOptions();
        $this->markAsUnInstalled();
    }

    /**
     * Perform any version-upgrade activities prior to activation (e.g. database changes)
     * @return void
     */
    public final function upgrade() {

        $oldVersion = $this->getVersionSaved();

        $this->installTables();

        $this->onUpgrade($oldVersion);

        $this->saveInstalledVersion();

    }

    /**
     * Called whenever an update happens wih the version we are upgrading from
     *
     * @param $oldVersion
     */
    protected function onUpgrade($oldVersion) {

    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=105
     * @return void
     */
    public function activate() {
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=105
     * @return void
     */
    public function deactivate() {
    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=31
     * @return void
     */
    protected function initOptions() {
    }

    /**
     * creates any actions and hooks defined within configuration also runs hook for extended plugin
     */
    public final function initActionsAndFilters() {

        $nameSpace = $this->rootConfig->rootNameSpace . "\\";
        foreach ($this->rootConfig->LifeCycle->ShortCode as $shortCode) {

            $fullClassName = $nameSpace . $shortCode->className;
            $sc = new $fullClassName($this);

            if (!is_subclass_of($sc, "WordWrap\\ShortCodeLoader"))
                throw new Exception("Your Short Codes Must extend ShortCodeLoader");

            $sc->register($shortCode->name);
        }

        $this->onInitActionsAndFilters();
    }

    /**
     * allows a plugin to manually initialize actions and filters
     */
    public function onInitActionsAndFilters() {

    }

    /**
     * Installs the tables for the plugin
     */
    private function installTables () {
        $nameSpace = $this->rootConfig->rootNameSpace . "\\";

        foreach ($this->rootConfig->LifeCycle->Model as $model) {
            $fullClassName = $nameSpace . $model->className;
            $fullClassName::installTable();
        }
    }

    /**
     * runs the creation of any defined database tables within configuration as well as triggering additional hooks
     */
    public function installDatabase() {

        $this->installTables();

        $this->onInstallDatabase();
    }

    /**
     * runs directly after the database is installed. Run any seeds needed here
     */
    protected function onInstallDatabase() {

    }

    /**
     * See: http://plugin.michael-simpson.com/?page_id=101
     * Drop plugin-created tables on uninstall.
     * @return void
     */
    protected function unInstallDatabaseTables() {
    }

    /**
     * Override to add any additional actions to be done at install time
     * See: http://plugin.michael-simpson.com/?page_id=33
     * @return void
     */
    protected function onInstall() {
    }

    /**
     * Override to add any additional actions to be done at uninstall time
     * See: http://plugin.michael-simpson.com/?page_id=33
     * @return void
     */
    protected function onUninstall() {
    }

    /**
     * Puts the configuration page in the Plugins menu by default.
     * Override to put it elsewhere or create a set of submenus
     * Override with an empty implementation if you don't want a configuration page
     * @return void
     */
    public function addSettingsSubMenuPage() {
        $this->addSettingsSubMenuPageToPluginsMenu();
        //$this->addSettingsSubMenuPageToSettingsMenu();
    }


    protected function requireExtraPluginFiles() {
        require_once(ABSPATH . 'wp-includes/pluggable.php');
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }

    /**
     * @return string Slug name for the URL to the Setting page
     * (i.e. the page for setting options)
     */
    protected function getSettingsSlug() {
        return urlencode($this->rootConfig->pluginName) . ' Settings';
    }

    protected function addSettingsSubMenuPageToPluginsMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_submenu_page('plugins.php',
                         $displayName,
                         $displayName,
                         'manage_options',
                         $this->getSettingsSlug(),
                         array(&$this, 'settingsPage'));
    }


    protected function addSettingsSubMenuPageToSettingsMenu() {
        $this->requireExtraPluginFiles();
        $displayName = $this->getPluginDisplayName();
        add_options_page($displayName,
                         $displayName,
                         'manage_options',
                         $this->getSettingsSlug(),
                         array(&$this, 'settingsPage'));
    }

    /**
     * @param  $name string name of a database table
     * @return string input prefixed with the WordPress DB table prefix
     * plus the prefix for this plugin (lower-cased) to avoid table name collisions.
     * The plugin prefix is lower-cases as a best practice that all DB table names are lower case to
     * avoid issues on some platforms
     */
    public function prefixTableName($name) {
        global $wpdb;
        return $wpdb->prefix .  strtolower($this->prefix($name));
    }


    /**
     * Convenience function for creating AJAX URLs.
     *
     * @param $actionName string the name of the ajax action registered in a call like
     * add_action('wp_ajax_actionName', array(&$this, 'functionName'));
     *     and/or
     * add_action('wp_ajax_nopriv_actionName', array(&$this, 'functionName'));
     *
     * If have an additional parameters to add to the Ajax call, e.g. an "id" parameter,
     * you could call this function and append to the returned string like:
     *    $url = $this->getAjaxUrl('myaction&id=') . urlencode($id);
     * or more complex:
     *    $url = sprintf($this->getAjaxUrl('myaction&id=%s&var2=%s&var3=%s'), urlencode($id), urlencode($var2), urlencode($var3));
     *
     * @return string URL that can be used in a web page to make an Ajax call to $this->functionName
     */
    public function getAjaxUrl($actionName) {
        return admin_url('admin-ajax.php') . '?action=' . $actionName;
    }

}
