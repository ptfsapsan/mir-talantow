<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace ApplicationTest\Controller;

use Application\Classes\Redis;
use Application\Controller\IndexController;
use Application\Model\Crypt;
use Zend\Debug\Debug;
use Zend\Stdlib\ArrayUtils;
use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        // The module configuration should still be applicable for tests.
        // You can override configuration here with test case specific values,
        // such as sample view templates, path stacks, module_listener_options,
        // etc.
        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/../../../../config/application.config.php',
            $configOverrides
        ));

        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
        $this->dispatch('/', 'GET');
        $this->assertResponseStatusCode(200);
        $this->assertModuleName('application');
        $this->assertControllerName(IndexController::class); // as specified in router's controller name alias
        $this->assertControllerClass('IndexController');
        $this->assertMatchedRouteName('home');
    }


    public function testInvalidRouteDoesNotCrash()
    {
        $this->dispatch('/invalid/route', 'GET');
        $this->assertResponseStatusCode(404);
    }

   public function testLoginController()
   {
      $password = '12345qwerty';
      $pass = Crypt::decrypt(Crypt::encrypt($password));
      $this->assertContains($pass, $password);
   }

   public function testRedis()
   {
      $cache = new \Zend\Cache\Storage\Adapter\Redis();
      //$redis = $sm->get('cache');

      Debug::dump(get_class($cache));

      if (!$check = $cache->hasItem("test")) {
         $cache->getOptions()->setTtl("60");
         $cache->setItem("test", "test123");
      }

      $item = $cache->getItem("test");

      Debug::dump($check, $item);

      return $this->getResponse();
   }
}