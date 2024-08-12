<?php

namespace Drupal\Tests\dhl_api\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\dhl_api\DhlSyncHttpClientServices;
use GuzzleHttp\ClientInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Database\Connection;
use Prophecy\Argument;
use GuzzleHttp\Psr7\Response;
use Drupal\Component\Serialization\Json;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test the Auth service methods.
 *
 * @covers Drupal\dhl_api\DhlSyncHttpClientServices
 * @group dhl_api
 */
class DownloadAuthorizeServiceTest extends KernelTestBase
{
  /**
   * The Service under test.
   *
   * @var \Drupal\dhl_api\DhlSyncHttpClientServices
   */
    protected $dhlApi;


  /**
   * {@inheritdoc}
   */
    protected $responseDhl;

  /**
   * {@inheritdoc}
   */
    protected static $modules = [
    'dhl_api',
    'system'
    ];

  /**
   * {@inheritdoc}
   */
    protected function setUp(): void
    {
        parent::setUp();
    }

  /**
   * @covers ::getLocationDataByquery
   * @dataProvider getApiData
   */
    public function testRedirectLogicWithQueryRetaining($uri, $query, $expected)
    {
        $data = $this->container->get('dhl_api.eo_client')->getLocationDataByquery($query);
        if (isset($data['locations'])) {
            $data = empty($data['locations']) ? [] : $data['locations'];
        } else {
            $data = !empty($data[0]) ? array_keys($data[0]) : [];
        }
        $this->assertEquals($data, $expected);
    }

  /**
   * Data provider for both tests.
   */
    public function getApiData()
    {
        return [
          [
            'https://api.dhl.com/location-finder/v1/find-by-address',
            'countryCode=GB&addressLocality=London',
            [
              'url',
              'location',
              'name',
              'distance',
              'place',
              'openingHours',
              'closurePeriods',
              'serviceTypes',
              'averageCapacityDayOfWeek'
            ],
          ],
          [
          'https://api.dhl.com/location-finder/v1/find-by-address',
          'countryCode=GB&addressLocality=abc',
          []
          ],
          [
          'https://api.dhl.com/location-finder/v1/find-by-address',
          'countryCode=GB',
          []
          ],
        ];
    }

  /**
   * Helper function for send https kernel request.
   *
   * @param string $uri
   *   The uri.
   * @param string $method
   *   The method.
   * @param array $headers
   *   The headers.
   * @param array|null $body
   *   The body.
   *
   * @return object
   *   The response.
   */
    protected function drupalHttpKernelRequest($uri, $method, $query)
    {
        $httpKernel = $this->container->get('http_kernel');
        $headers = [
        'headers' => [
        'DHL-API-Key' => 'demo-key',
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
        ],
        ];

        $request = Request::create(
            $uri . "?" . $query,
            $method,
            [],
            [],
            [],
        );
        $request->headers->set('DHL-API-Key', 'demo-key');
        $request->headers->set('Accept', 'application/json');
        $request->headers->set('Content-Type', 'application/json');
        $response = $httpKernel->handle($request);
        return $response;
    }
}
