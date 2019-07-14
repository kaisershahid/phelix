<?php
namespace DinoTech\Phelix\tests\unit\Config\Binders;

use Codeception\Test\Unit;
use DinoTech\Phelix\Api\Config\Binders\FilesysBinder;
use DinoTech\Phelix\Env;

class FilesysTest extends Unit {
    const SERVICE_ID = 'Company.Package.ServiceId';

    public function testGatherAndSortDirs() {
        $subject = new FilesysBinder(codecept_data_dir() . 'config-bindings', new Env('test.level2'));
        $expected = ['default', 'test', 'test.level2'];
        $this->assertEquals($expected, $subject->getDirs());
    }

    public function _getFileResolutionScenarios() {
        return [
            'default' => ['default', 'Company.Package.ServiceId.yml'],
            'test' => ['test', 'Company/Package/ServiceId.yml'],
            'test.level2' => ['test.level2', 'Company/Package.ServiceId.yml']
        ];
    }

    /**
     * @dataProvider _getFileResolutionScenarios
     */
    public function testFileResolution(string $envId, string $expectedFile) {
        $env = $envId;
        $subject = new FilesysBinder(codecept_data_dir() . 'config-bindings', new Env($envId));
        $this->assertStringEndsWith($expectedFile, $subject->resolveConfigPath(self::SERVICE_ID));
    }

    public function testLoadingConfig() {
        $subject = new FilesysBinder(codecept_data_dir() . 'config-bindings', new Env('test.level2'));
        $expected = ['default', 'test', 'test.level2'];
        $this->assertEquals(['foundAt' => 'test.level2'],
            $subject->getConfigBinding(self::SERVICE_ID)->jsonSerialize());
    }
}
