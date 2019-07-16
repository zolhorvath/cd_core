<?php

namespace Drupal\themeswitcher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Extension\Extension;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Provides the Theme Switcher form.
 */
class ThemeSwitcherForm extends FormBase {

  /**
   * Theme machine name from cookie.
   *
   * @var string|null
   */
  public $currentTheme;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The current Request object.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Constructs ThemeSwitcherForm.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, RequestStack $request_stack) {
    $this->themeHandler = $theme_handler;
    $this->request = $request_stack->getCurrentRequest();
    $this->currentTheme = $request_stack->getCurrentRequest()->cookies->get('themeswitcher', NULL);

    // Get available non-hidden themes.
    $themes_visible = array_filter($this->themeHandler->listInfo(), function (Extension $theme) {
      return empty($theme->info['hidden']);
    });
    $themes_available = array_filter(array_keys($themes_visible), function ($theme_name) {
      return $this->themeHandler->hasUi($theme_name);
    });

    $options = [];

    foreach ($themes_available as $theme_name) {
      $options[$theme_name] = $themes_visible[$theme_name]->info['name'];
    }

    $this->availableThemes = $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'themeswitcher_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['preferred_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Preferred theme'),
      '#default_value' => !empty($this->availableThemes[$this->currentTheme]) ? $this->currentTheme : NULL,
      '#options' => $this->availableThemes,
      '#empty_option' => $this->t('Use default'),
      '#wrapper_attributes' => ['class' => ['themeswitcher-form__form-item']],
    ];

    $form['actions'] = [
      '#type' => 'actions',
      '#attributes' => ['class' => ['js-hide']],
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#button_type' => 'primary',
    ];

    $form['#theme_wrappers'] = [
      'form' => [
        '#attributes' => [
          'class' => ['themeswitcher-form', 'js-themeswitcher-form'],
        ],
      ],
    ];
    $form['#attached']['library'][] = 'themeswitcher/form';
    $form['#cache']['contexts'][] = 'session';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $preferred_theme = $form_state->getValue('preferred_theme');

    if (empty($preferred_theme)) {
      setcookie('themeswitcher', '', REQUEST_TIME - 86400 * 2, '/');
    }
    else {
      setcookie('themeswitcher', $preferred_theme, REQUEST_TIME + 86400 * 100, '/');
    }
  }

}
