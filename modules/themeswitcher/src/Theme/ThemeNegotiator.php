<?php

namespace Drupal\themeswitcher\Theme;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Session\AccountInterface;

/**
 * The Theme Switcher Theme Negotiator.
 */
class ThemeNegotiator implements ThemeNegotiatorInterface {

  /**
   * Theme machine name from cookie.
   *
   * @var string|null
   */
  public $themeName;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $account;

  /**
   * Construct Theme Switcher's ThemeNegotiator.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, RequestStack $request_stack, AccountInterface $account) {
    $this->themeHandler = $theme_handler;
    $this->themeName = $request_stack->getCurrentRequest()->cookies->get('themeswitcher', NULL);
    $this->account = $account;
  }

  /**
   * Whether this theme negotiator should be used to set the theme.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match object.
   *
   * @return bool
   *   TRUE if this negotiator should be used or FALSE to let other negotiators
   *   decide.
   */
  public function applies(RouteMatchInterface $route_match) {
    return $this->account->hasPermission('choose preferred theme') && !empty($this->themeName);
  }

  /**
   * Determine the active theme for the request.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match object.
   *
   * @return string|null
   *   Returns the active theme name, else return NULL.
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    // Get available non-hidden themes.
    $themes_visible = array_filter($this->themeHandler->listInfo(), function (Extension $theme) {
      return empty($theme->info['hidden']);
    });
    $themes_available = array_filter(array_keys($themes_visible), function ($theme_name) {
      return $this->themeHandler->hasUi($theme_name);
    });

    return in_array($this->themeName, $themes_available) ? $this->themeName : NULL;
  }

}
