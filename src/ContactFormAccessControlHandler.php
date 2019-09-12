<?php

namespace Drupal\cd_core;

use Drupal\contact\ContactFormAccessControlHandler as DefaultAccessControlHandler;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Extends the access control handler for the contact form entity type.
 *
 * @see \Drupal\contact\Entity\ContactForm.
 */
class ContactFormAccessControlHandler extends DefaultAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation == 'view') {
      $contact_form_view_perm = ContactFormPermissions::viewContactFormPermissionName($entity->id());
      return AccessResult::allowedIfHasPermissions($account, ['access site-wide contact form', $contact_form_view_perm], 'OR')->andIf(AccessResult::allowedIf($entity->id() !== 'personal'));
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
