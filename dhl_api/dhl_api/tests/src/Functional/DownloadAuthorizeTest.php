<?php

namespace Drupal\Tests\dhl_api\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Tests the API form authorise flow.
 *
 * @group dhl_api
 */
class DownloadAuthorizeTest extends BrowserTestBase
{
  /**
   * Modules to install.
   *
   * @var array
   */
    protected static $modules = [
    'dhl_api',
    ];

  /**
   * {@inheritdoc}
   */
    protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
    protected function setUp(): void
    {
        parent::setUp();
    }

  /**
   * Test User for Token field Update.
   */
    public function testApiForm()
    {
        $edit['eo_settings[country]'] = "GB";
        $edit['eo_settings[city]'] = "London";
        // Check if anonymous user can get the form without : drupalLogin()
        $req = $this->drupalGet('admin/config/services/dhl-api');
        $this->submitForm($edit, 'Send Request');
        // Check we are on the same page and not redirecting.
        $this->assertSession()->addressEquals('admin/config/services/dhl-api');
        $this->assertSession()->pageTextContains('API Output');
    }
}
