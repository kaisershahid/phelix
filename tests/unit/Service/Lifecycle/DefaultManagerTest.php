<?php
namespace DinoTech\Phelix\tests\unit\Service\Lifecycle;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Bundle\BundleManifest;
use DinoTech\Phelix\Api\Bundle\Loaders\FilesysReader;
use DinoTech\Phelix\Api\Service\Lifecycle\DefaultManager;
use DinoTech\Phelix\Api\Service\Registry\Index;
use DinoTech\Phelix\Api\Service\ServiceRegistry;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Collections\ArrayUtils;

class DefaultManagerTest extends Unit {
    /** @var DefaultManager */
    protected $subject;
    /** @var Index */
    protected $services;
    /** @var ServiceRegistry */
    protected $registry;
    /** @var BundleManifest */
    protected $bundleManifestA;
    /** @var BundleManifest */
    protected $bundleManifestB;
    /** @var BundleManifest */
    protected $bundleManifestC;

    public function _before() {
        Framework::$debugEnabled = true;
        Framework::$debugFunc = 'codecept_debug';
        $root = codecept_data_dir() . '/framework/3rd-party/test-bundles';
        // @todo make a bundle helper
        Framework::registerNamespace('DinoTech\\BundleA', "{$root}/bundle-a/src");
        Framework::registerNamespace('DinoTech\\BundleB', "{$root}/bundle-b/src");
        Framework::registerNamespace('DinoTech\\BundleC', "{$root}/bundle-c/src");
        Framework::registerAutoloader();

        $this->services = new Index();
        $this->subject = new DefaultManager($this->services);
        $this->registry = new ServiceRegistry($this->services, $this->subject);
        $this->bundleManifestA = (new FilesysReader())->setRoot($root . '/bundle-a')->loadManifest();
        $this->bundleManifestB = (new FilesysReader())->setRoot($root . '/bundle-b')->loadManifest();
        $this->bundleManifestC = (new FilesysReader())->setRoot($root . '/bundle-c')->loadManifest();
    }

    /**
     * Loads bundles A, B, and C, and ensures bundle B's service is activated once all bundles are started.
     */
    public function testLoadBundleAServices() {
        $this->registry->loadBundle($this->bundleManifestA);
        $this->registry->loadBundle($this->bundleManifestB);
        $this->registry->loadBundle($this->bundleManifestC);

        $expectBundleBSatisfied = [
            'DinoTech\BundleB\DependentService' => [
                [
                    'status' => 'SATISFIED'
                ]
            ]
        ];

        $this->assertArraySubset($expectBundleBSatisfied, $this->services->jsonSerialize()['services']);
    }
}
