<?php

namespace Drupal\Tests\devhelp\Functional;

use Drupal\Tests\system\Functional\Module\ModuleTestBase;
use Drupal\user\Entity\Role;

/**
 * Install/uninstall devhelp module and confirm the expected changes.
 *
 * @group devhelp
 * @group cd_core
 */
class InstallUninstallTest extends ModuleTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['system', 'user'];

  /**
   * Tests that install/uninstal hooks are fine.
   */
  public function testInstallUninstall() {
    $this->assertEqual($this->container->get('state')->get('devhelp.kint_was_enabled'), NULL);

    $this->container->get('module_installer')->install(['devhelp']);
    $this->resetAll();

    $module_handler = $this->container->get('module_handler');

    $this->assertEqual($module_handler->moduleExists('devhelp'), TRUE);
    $this->assertEqual($module_handler->moduleExists('kint'), TRUE);

    foreach (Role::loadMultiple() as $role) {
      $this->assertEqual($role->hasPermission('access kint'), TRUE);
    }

    $this->container->get('module_installer')->uninstall(['devhelp']);
    $this->resetAll();

    $module_handler = $this->container->get('module_handler');

    $this->assertEqual($module_handler->moduleExists('devhelp'), FALSE);
    $this->assertEqual($module_handler->moduleExists('kint'), FALSE);

    foreach (Role::loadMultiple() as $role) {
      $this->assertEqual($role->hasPermission('access kint'), FALSE);
    }

    $this->assertEqual($this->container->get('state')->get('devhelp.kint_was_enabled'), NULL);

    //
    // Now test install and uninstall with enabled Kint module.
    //
    $this->container->get('module_installer')->install(['kint']);
    $this->container->get('module_installer')->install(['devhelp']);
    $this->resetAll();

    $this->assertEqual($this->container->get('module_handler')->moduleExists('devhelp'), TRUE);

    foreach (Role::loadMultiple() as $role) {
      $this->assertEqual($role->hasPermission('access kint'), FALSE);

      // Add the permission.
      $role->grantPermission('access kint');
      $role->trustData()->save();
    }

    $this->container->get('module_installer')->uninstall(['devhelp']);
    $this->resetAll();

    $module_handler = $this->container->get('module_handler');

    $this->assertEqual($module_handler->moduleExists('devhelp'), FALSE);
    $this->assertEqual($module_handler->moduleExists('kint'), TRUE);

    foreach (Role::loadMultiple() as $role) {
      $this->assertEqual($role->hasPermission('access kint'), TRUE);
    }

    $this->assertEqual($this->container->get('state')->get('devhelp.kint_was_enabled'), NULL);
  }

}
