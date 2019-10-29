<?php

namespace Drupal\themeswitcher\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\PageCache\ResponsePolicy\KillSwitch;
use Drupal\Core\Session\AccountInterface;

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
   * The kill switch.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $killSwitch;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Constructs ThemeSwitcherForm.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   The request stack.
   * @param \Drupal\Core\PageCache\ResponsePolicy\KillSwitch $kill_switch
   *   The kill switch.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, RequestStack $request_stack, KillSwitch $kill_switch, AccountInterface $current_user) {
    $this->themeHandler = $theme_handler;
    $this->request = $request_stack->getCurrentRequest();
    $this->killSwitch = $kill_switch;
    $this->currentUser = $current_user;
    $this->currentTheme = $request_stack->getCurrentRequest()->cookies->get('themeswitcher', NULL);

    foreach ($this->themeHandler->listInfo() as $theme_name => $theme) {
      $options[$theme_name] = $theme->info['name'];
    }

    $this->availableThemes = $options;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler'),
      $container->get('request_stack'),
      $container->get('page_cache_kill_switch'),
      $container->get('current_user')
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
    if ($this->currentUser->isAnonymous()) {
      $this->killSwitch->trigger();
    }

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
    $form['#cache']['contexts'][] = 'cookies:themeswitcher';
    $form['#access'] = $this->currentUser->hasPermission('choose preferred theme');

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
