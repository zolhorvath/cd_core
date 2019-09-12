<?php

namespace Drupal\Tests\cd_core\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests contact form canonical route redirection.
 *
 * @group contact
 */
class ContactFormPermissionTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'contact_test', 'cd_core'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Tests node canonical route access.
   */
  public function testContactFormPermissions() {
    // Test that feedback contact form is unaccessible.
    $this->drupalGet('contact/feedback');
    $this->assertSession()->statusCodeEquals('403');

    user_role_grant_permissions('anonymous', ['access feedback contact form']);

    // Test that feedback contact form is accessible.
    $this->drupalGet('contact/feedback');
    $this->assertSession()->statusCodeEquals('200');

    user_role_grant_permissions('anonymous', ['access site-wide contact form']);

    // Test that feedback contact form is still accessible.
    $this->drupalGet('contact/feedback');
    $this->assertSession()->statusCodeEquals('200');

    user_role_revoke_permissions('anonymous', ['access feedback contact form']);

    // Still should be accessible.
    $this->drupalGet('contact/feedback');
    $this->assertSession()->statusCodeEquals('200');

    user_role_revoke_permissions('anonymous', ['access site-wide contact form']);

    // Unaccessible again.
    $this->drupalGet('contact/feedback');
    $this->assertSession()->statusCodeEquals('403');
  }

}
