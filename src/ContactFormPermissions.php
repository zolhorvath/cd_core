<?php

namespace Drupal\cd_core;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides additional dynamic permissions for contact form entities.
 */
class ContactFormPermissions implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ContactFormPermissions constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'));
  }

  /**
   * Returns the new view permission.
   *
   * @param string $bundle
   *   The entity id of a contact_form.
   *
   * @return string
   *   String used as permission.
   */
  public static function viewContactFormPermissionName($bundle) {
    return "access {$bundle} contact form";
  }

  /**
   * Returns an array of contact form permissions.
   *
   * @return array
   *   An array of permissions keyed by permission name.
   */
  public function permissions() {
    if (!$this->entityTypeManager->hasDefinition('contact_form')) {
      return [];
    }

    // Generate permissions for each contact form.
    $permissions = [];
    $contact_forms = $this->entityTypeManager->getStorage('contact_form')->loadMultiple();

    if (!empty($contact_forms)) {
      foreach ($contact_forms as $contact_form_id => $contact_form) {
        $permissions[$this->viewContactFormPermissionName($contact_form_id)] = [
          'title' => $this->t('Access contact form of type @contact-form-type', [
            '@contact-form-type' => $contact_form->get('label'),
          ]),
          'description' => [
            '#markup' => $this->t('Allows to view the page of this contact form.'),
          ],
        ];
      }
    }

    return $permissions;
  }

}
