<?php

# Copyright (c)  2013 - <mlunzena@uos.de>
#
# Permission is hereby granted, free of charge, to any person obtaining a copy
# of this software and associated documentation files (the "Software"), to deal
# in the Software without restriction, including without limitation the rights
# to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
# copies of the Software, and to permit persons to whom the Software is
# furnished to do so, subject to the following conditions:
#
# The above copyright notice and this permission notice shall be included in all
# copies or substantial portions of the Software.
#
# THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
# IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
# FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
# AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
# LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
# OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
# SOFTWARE.

require_once 'StatsdSettings.php';

class StatsdAdminPlugin extends StudipPlugin implements SystemPlugin
{
    function __construct()
    {
        parent::__construct();
        $this->setupNavigation();
    }


    function show_action()
    {
        $flash = $this->popFlash();

        $this->requireRoot();

        Navigation::activateItem("/admin/config/statsdadmin");

        $parameters = array(
              'plugin'        => $this
            , 'flash'         => $flash
            , 'settings'      => StatsdSettings::getStatsdSettings()
            , 'statsd_active' => $this->isStatsdPluginActivated()
        );

        $factory = new Flexi_TemplateFactory(dirname(__FILE__).'/templates');
        echo $factory->render('show'
                            , $parameters
                            , $GLOBALS['template_factory']->open('layouts/base_without_infobox')
        );
    }


    function settings_action()
    {
        $this->requireRoot();

        if (Request::method() !== 'POST') {
            throw new AccessDeniedException();
        }

        # get settings
        $settings = Request::getArray("statsd");

        # validate them
        list($valid, $err) = $this->validateSettings($settings);
        if (!$valid) {
            $this->redirect('show', compact('err'));
            return;
        }

        # store them
        StatsdSettings::setStatsdSettings($settings);

        # activate statsd plugin
        $this->activateStatsdPlugin();

        # redirect to /statsdadminplugin/show
        $this->redirect('show', array('info' => _('Statsd-Plugin aktiviert.')));
    }


    function deactivate_action()
    {
        $this->requireRoot();

        if (Request::method() !== 'POST') {
            throw new AccessDeniedException();
        }

        self::deactivateStatsPlugin();

        $this->redirect('show', array('info' => _('Statsd-Plugin deaktiviert.')));
    }


    static function onDisable($id)
    {
        self::deactivateStatsPlugin();
    }


    #######################################################################

    private function setupNavigation()
    {
        global $perm;
        if (!$perm->have_perm("root")) {
            return;
        }

        $url = PluginEngine::getURL('statsdadminplugin/show');
        $navigation = new Navigation(_('Statsd-Admin'), $url);
        $navigation->setImage(Assets::image_path('icons/16/white/test.png'));
        $navigation->setActiveImage(Assets::image_path('icons/16/black/test.png'));

        Navigation::addItem('/admin/config/statsdadmin', $navigation);
    }

    private function isStatsdPluginActivated()
    {
        $info = PluginManager::getInstance()->getPluginInfo("StatsdPlugin");
        return $info && $info['enabled'];
    }

    private function activateStatsdPlugin()
    {
          $plugin_manager = PluginManager::getInstance();

          # register
          $additional_class = 'StatsdPlugin';
          $pluginpath = 'luniki/StatsdAdminPlugin';
          $pluginid = $this->getPluginId();
          $id = $plugin_manager->registerPlugin($additional_class, $additional_class, $pluginpath, $pluginid);

          # and activate
          $plugin_manager->setPluginEnabled($id, TRUE);
    }

    private static function deactivateStatsPlugin()
    {
        $info = PluginManager::getInstance()->getPluginInfo("StatsdPlugin");
        PluginManager::getInstance()->unregisterPlugin($info["id"]);
    }

    private function validateSettings($settings)
    {
        $errors = array();

        # IP adress of statsd host
        if (!filter_var($settings['ip'], FILTER_VALIDATE_IP)) {
            $errors[] = _("IP ist ungültig.");
        }

        # port of statsd host
        if (!filter_var($settings['port'], FILTER_VALIDATE_INT)) {
            $errors[] = _("Port ist ungültig.");
        }

        if (!preg_match('/^[a-zA-Z0-9]{1,5}$/', $settings['prefix'])) {
            $errors[] = _("Port ist ungültig.");
        }

        return array(sizeof($errors) === 0, $errors);
    }

    private function requireRoot()
    {
        global $perm;
        if (!$perm->have_perm("root")) {
            throw new AccessDeniedException();
        }
    }

    private function redirect($action, $flash = null)
    {
        if ($flash) {
            $_SESSION['statsd_flash'] = $flash;
        }
        header("Location: " . PluginEngine::getURL("statsdadminplugin/$action"));
    }

    private function popFlash()
    {
        $flash = @$_SESSION['statsd_flash'];
        unset($_SESSION['statsd_flash']);
        return $flash;
    }
}
