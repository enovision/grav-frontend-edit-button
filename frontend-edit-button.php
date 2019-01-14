<?php
/**
 * frontend-edit-button
 *
 * This plugin adds an 'edit this page' button on the frontend
 *
 * Licensed under MIT, see LICENSE.
 */

namespace Grav\Plugin;

use Grav\Common\Page;
use Grav\Common\Plugin;
use Grav\Plugin\Login\Events\UserLoginEvent;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class FrontendEditButtonPlugin
 * @package Grav\Plugin
 */
class FrontendEditButtonPlugin extends Plugin
{

    private $_config = null;
    private $adminRoute = '/admin';

    private $adminCookieSet = false;
    private $adminCookie = '';
    private $editUrl = null;

    // private $adminCookieSuffix = '-admin-authenticated'; // till version 1.12
    private $adminCookieSuffix = '-admin';    // since version 1.12.1

    /**
     * @function getSubscribedEvents
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * @event onPluginsInitialized
     *
     * It is only allowed to process when:
     * - we are not on an admin page already
     * - Admin is logged in in any of the other tabs
     * - Login plugin is enabled
     * - Admin plugin is enabled
     * - This plugin is enabled (but that it is)
     * - Page has no frontmatter: protectEdit: true
     *
     */
    public function onPluginsInitialized()
    {
        $this->adminCookie = session_name();

        $this->enable([
            'onUserLogin'  => ['onUserLogin', 0],
            'onUserLogout' => ['onUserLogout', 0]
        ]);

        if ($this->isAdmin()) {
            return;
        }

        $config = $this->grav['config'];

        if ($config->get('plugins.frontend-edit-button.requiresAuth')) {
            if (isset($_COOKIE[$this->adminCookie]) === false) {
                return;
            }
            $this->adminCookieSet = true;
        }

        /* Stop if no users exist */
        if ($this->doAnyUsersExist() === false) {
            return;
        }

        $plugins = $config->get('plugins');

        $adminPlugin = isset($plugins['admin']) ? $this->config->get('plugins.admin') : false;
        $loginPlugin = isset($plugins['login']) ? $this->config->get('plugins.login') : false;

        $this->adminRoute = $adminPlugin !== false ? $adminPlugin['route'] : $this->adminRoute;

        // Works only with the login and admin plugin installed and enabled
        if ($adminPlugin === false || $loginPlugin === false) {
            return;
        } else {
            if ($adminPlugin['enabled'] === false || $loginPlugin['enabled'] === false) {
                return;
            }
        }

        $this->enable([
            'onPageContentProcessed' => ['onPageContentProcessed', 0],
            'onTwigSiteVariables'    => ['onTwigSiteVariables', 0],
            'onOutputGenerated'      => ['onOutputGenerated', 0],
            'onTwigTemplatePaths'    => ['onTwigTemplatePaths', 0]
        ]);
    }

    /**
     * check for any users, see admin.php
     */
    private function doAnyUsersExist()
    {
        $account_dir = $file_path = $this->grav['locator']->findResource('account://');
        $user_check = glob($account_dir . '/*.yaml');

        // If no users found, stop here !!!
        return $user_check == false || count((array)$user_check) == 0 ? false : true;
    }

    /**
     * @event onPageContentProcessed
     *
     * @param Event $event
     */
    public function onPageContentProcessed(Event $event)
    {
        $page = $event['page'];
        $this->_config = $this->mergeConfig($page);
    }

    /**
     * @event onOutputGenerated
     */
    public function onOutputGenerated()
    {
        if ($this->isAdmin()) {
            return;
        }

        // frontend !!!
        // $this->adminCookie = session_name() . '-admin-authenticated'; // till version 1.12
        $this->adminCookie = session_name() . $this->adminCookieSuffix; // since version 1.12.1

        $page = $this->grav['page'];

        $header = $page->header();

        $adminCookie = $this->getAdminCookie();

        if ((isset($header->protectEdit) && $header->protectEdit == true) || $adminCookie === false) {
            return;
        }

        $content = $this->grav->output;

        $twig = $this->grav['twig'];

        $position = $this->config->get('plugins.frontend-edit-button.position');

        $vertical = substr($position, 0, 1) === 't' ? 'top' : 'bottom';
        $horizontal = substr($position, 1, 1) === 'l' ? 'left' : 'right';

        //$pageUrl = $page->url( false, false, true, false );
        $uri = $this->grav['uri'];
        //$pageUrl = $uri->url(false, false);
        $pageUrl = $uri->path();

        /* otherwise the home page can't be edited */
        if ($pageUrl == '/') {
            $pageUrl .= $page->slug();
        }

        if (isset($header->editUrl)) {
            $editUrl = $header->editUrl;
        } else {
            $editUrl = $uri->rootUrl(true) . $this->adminRoute . '/pages' . $pageUrl;
        }

        $this->editUrl = $editUrl;

        $icon = $uri->base() . '/' . $this->config->get('plugins.frontend-edit-button.iconSrc');

        $params = array(
            'config'         => $this->_config,
            'header'         => $header,
            'horizontal'     => $horizontal,
            'vertical'       => $vertical,
            'pageUrl'        => $pageUrl,
            'editUrl'        => $editUrl,
            'icon'           => $icon,
            'adminCookieSet' => $adminCookie
        );

        $insertThis = $twig->processTemplate('partials/edit-button.html.twig', $params);

        $pos = strpos($content, '<body', 0);

        if ($pos > 0) {

            $pos = strpos($content, '>', $pos);

            if ($pos > 0) {

                $str1 = substr($content, 0, $pos + 1);
                $str2 = substr($content, $pos + 1);

                $content = $str1 . $insertThis . $str2;

                $this->grav->output = $content;
            }
        }
    }

    private function getAdminCookie()
    {
        $this->adminCookieSet = false;

        if ($this->config->get('plugins.frontend-edit-button.requiresAuth')) {
            if (isset($_COOKIE[$this->adminCookie]) === true) {
                $this->adminCookieSet = true;
            }
        } else {
            $this->adminCookieSet = true;
        }

        return $this->adminCookieSet;
    }

    /**
     * @event onTwigSiteVariables
     */
    public function onTwigSiteVariables()
    {
        $page = $this->grav['page'];
        $header = $page->header();

        if (isset($header->protectEdit) && $header->protectEdit == true) {
            return;
        }

        $this->adminCookie = session_name() . $this->adminCookieSuffix;
        $adminCookie = $this->getAdminCookie();

        $this->grav['assets']
            ->addCss('plugin://frontend-edit-button/assets/css/style.css');

        if ($this->config->get('plugins.frontend-edit-button.autoRefresh') === true && $adminCookie === true) {
            $this->grav['assets']
                ->addJs('plugin://frontend-edit-button/assets/js/script.js');
        }
    }

    /**
     * @event onTwigTemplatePaths
     */
    public function onTwigTemplatePaths()
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }

    /**
     * @event onUserLogout
     *
     * Hook on onUserLogout of the Login plugin
     * It should remove the cookie
     */
    public function onUserLogout(UserLoginEvent $event)
    {
        $user = $event->getUser();

        $params = session_get_cookie_params();
        // $cookieName = session_name() . '-authenticated'; // till version 1.12
        $cookieName = session_name(); // since version 1.12.1

        setcookie(
            $cookieName,
            session_id(),
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    public function onUserLogin(UserLoginEvent $event)
    {
        $user = $event->getUser();

        if ($user->authenticated) {

            $params = session_get_cookie_params();
            // $cookieName = session_name() . '-authenticated'; // till version 1.12
            $cookieName = session_name(); // since version 1.12.1

            setcookie(
                $cookieName,
                session_id(),
                time() + $params['lifetime'],
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );

        }
    }

}
