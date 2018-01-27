<?php
/**
 * frontend-edit-button
 *
 * This plugin adds an 'edit this page' button on the frontend
 *
 * Licensed under MIT, see LICENSE.
 */

namespace Grav\Plugin;

use Grav\Common\Plugin;
use Grav\Common\Uri;
use Grav\Common\Page;
use RocketTheme\Toolbox\Event\Event;

/**
 * Class FrontendEditButtonPlugin
 * @package Grav\Plugin
 */
class FrontendEditButtonPlugin extends Plugin {

	private $_config    = null;
	private $adminRoute = '/admin';

	/**
	 * @function getSubscribedEvents
	 * @return array
	 */
	public static function getSubscribedEvents() {
		return [
			'onPluginsInitialized' => [ 'onPluginsInitialized', 0 ]
		];
	}

	/**
	 * @event onPluginsInitialized
	 *
	 * It is only allowed to process when:
	 * - Admin is logged in in any of the other tabs
	 * - Login plugin is enabled
	 * - Admin plugin is enabled
	 * - This plugin is enabled (but that it is)
	 * - Page has no frontmatter: pageProtect: true
	 *
	 */
	public function onPluginsInitialized() {
		if ( $this->isAdmin() ) {
			return;
		}

		$adminCookie = session_name() . '-admin';
		if ( isset( $_COOKIE[ $adminCookie ] ) === false ) {
			return;
		}

		// check for existence of a user account
		$account_dir = $file_path = $this->grav['locator']->findResource( 'account://' );
		$user_check = glob( $account_dir . '/*.yaml' );

		// If no users found, stop here !!!
		if ( $user_check == false || count( (array) $user_check ) == 0 ) {
			// dump($this->isAdminPath());

			if ( ! $this->isAdminPath() ) {
				return;
			}
		}

		$config = $this->grav['config'];
		$plugins = $config->get( 'plugins' );

		$adminPlugin = isset( $plugins['admin'] ) ? $this->config->get( 'plugins.admin' ) : false;
		$loginPlugin = isset( $plugins['login'] ) ? $this->config->get( 'plugins.login' ) : false;

		$this->adminRoute = $adminPlugin !== false ? $adminPlugin['route'] : $this->adminRoute;

		// Works only with the login and admin plugin installed and enabled
		if ( $adminPlugin === false || $loginPlugin === false ) {
			return;
		} else {
			if ( $adminPlugin['enabled'] === false || $loginPlugin['enabled'] === false ) {
				return;
			}
		}

		$this->enable( [
			'onPageContentProcessed' => [ 'onPageContentProcessed', 0 ],
			'onTwigSiteVariables' => [ 'onTwigSiteVariables', 0 ],
			'onOutputGenerated' => [ 'onOutputGenerated', 0 ],
			'onTwigTemplatePaths' => [ 'onTwigTemplatePaths', 0 ]
		] );
	}

	/**
	 * @event onPageContentProcessed
	 *
	 * @param Event $event
	 */
	public function onPageContentProcessed( Event $event ) {
		$page = $event['page'];
		$this->_config = $this->mergeConfig( $page );
	}

	/**
	 * @event onOutputGenerated
	 */
	public function onOutputGenerated() {
		if ( $this->isAdmin() ) {
			return;
		}

		$page = $this->grav['page'];
		$header = $page->header();

		if ( isset( $header->protectEdit ) && $header->protectEdit == true ) {
			return;
		}

		$content = $this->grav->output;

		$twig = $this->grav['twig'];

		$position = $this->config->get( 'plugins.frontend-edit-button.position' );

		$vertical = substr( $position, 0, 1 ) === 't' ? 'top' : 'bottom';
		$horizontal = substr( $position, 1, 1 ) === 'l' ? 'left' : 'right';

		//$pageUrl = $page->url( false, false, true, false );
		$uri = $this->grav['uri'];
		$pageUrl = $uri->url(false, false);

		/* otherwise the home page can't be edited */
		if ( $pageUrl == '/' ) {
			$pageUrl .= $page->slug();
		}


		$editUrl = $uri->rootUrl( true ) . $this->adminRoute . '/pages' . $pageUrl;

		$params = array(
			'config' => $this->_config,
			'header' => $header,
			'horizontal' => $horizontal,
			'vertical' => $vertical,
			'pageUrl' => $pageUrl,
			'editUrl' => $editUrl
		);

		$insertThis = $twig->processTemplate( 'partials/edit-button.html.twig', $params );

		$pos = strpos( $content, '<body', 0 );

		if ( $pos > 0 ) {

			$pos = strpos( $content, '>', $pos );

			if ( $pos > 0 ) {

				$str1 = substr( $content, 0, $pos + 1 );
				$str2 = substr( $content, $pos + 1 );

				$content = $str1 . $insertThis . $str2;

				$this->grav->output = $content;
			}
		}
	}

	/**
	 * @event onTwigSiteVariables
	 */
	public function onTwigSiteVariables() {
		$page = $this->grav['page'];
		$header = $page->header();

		if ( isset( $header->protectEdit ) && $header->protectEdit == true ) {
			return;
		}

		$this->grav['assets']
			->addCss( 'plugin://frontend-edit-button/css-compiled/style.css' );
		$this->grav['assets']
			->addJs( 'plugin://frontend-edit-button/js/script.js' );
	}

	/**
	 * @event onTwigTemplatePaths
	 */
	public function onTwigTemplatePaths() {
		$this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
	}
}