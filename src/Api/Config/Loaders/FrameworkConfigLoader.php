<?php
namespace DinoTech\Phelix\Api\Config\Loaders;

use DinoTech\Phelix\Api\Config\ConfigLoader;
use DinoTech\Phelix\Api\Config\FileMatcher;
use DinoTech\Phelix\Api\Config\MergeFromSubKeysProcessor;
use DinoTech\Phelix\Api\Config\Wrappers\FrameworkConfig;
use DinoTech\Phelix\Env;
use DinoTech\Phelix\Framework;
use DinoTech\StdLib\Filesys\Path;

/**
 * Given a root, base config file (e.g., `a.yaml`, and an optional environment
 * (e.g. `dev.b`):
 *
 * 1. find and load `a.yaml` as base config, or throw exception
 * 2. find all `a.*.yml`
 * 3. for each `a.*.yml`
 *     1. if `*` is captured in environment, load and merge into base config
 * 4. return merged config
 *
 * Aside from the standard YAML tag processors, `!config path/to/config` is
 * supported -- if it's a part of the `includes` key, the results will be merged
 * into the enclosing object of `includes` through `MergeFromSubKeysProcessor`.
 *
 * @todo rewrite description to be specific to framework config structure and behavior
 * @todo think about refactoring the general behavior since this will be a common pattern with config resolution
 */
class FrameworkConfigLoader extends ConfigLoader {
    /** @var string */
    protected $root;
    /** @var string */
    protected $configFile;
    /** @var Env */
    protected $env;
    /** @var MergeFromSubKeysProcessor */
    protected $mergeProcessor;

    public function __construct(string $root, string $configFile) {
        $this->root = $root;
        $this->configFile = $configFile;
        $this->mergeProcessor =
            (new MergeFromSubKeysProcessor(['properties', 'framework'], ['includes', 'include']))
            ->markKeyAsList('includes');
    }

    public function setEnvironment(Env $env) : FrameworkConfigLoader {
        $this->env = $env;
        return $this;
    }

    /**
     * Loads initial config then checks for environment-specific configs to merge.
     * @return FrameworkConfig
     */
    public function loadAndMergeConfigs() : FrameworkConfig {
        $path = Path::joinAndNormalize($this->root, $this->configFile);
        Framework::debug("frameworkCfgLoader: root file $path");
        $baseConfig = $this->mergeProcessor->process($this->loadYamlFromFile($path));
        $frameworkCfg = FrameworkConfig::makeWithDefaults($baseConfig);
        return $this->resolveAndMergeEnvironmentConfigs($frameworkCfg);
    }

    protected function resolveAndMergeEnvironmentConfigs(FrameworkConfig $config) : FrameworkConfig {
        if (!$this->env) {
            return $config;
        }

        $fm = new FileMatcher($this->configFile, $this->root);
        $confSuffixes = $fm->getMatchingSuffixes();
        foreach ($confSuffixes as $confSuffix) {
            if ($this->env->is($confSuffix)) {
                $configFile = $fm->getFullPathBySuffix($confSuffix);
                Framework::debug("frameworkCfgLoader: merging $configFile");
                $config = $config->mergeToNew(
                    $this->loadAndProcessMerge($configFile)
                );
            }
        }

        return $config;
    }

    public function loadAndProcessMerge(string $configFile) : array {
        $arr = $this->loadYamlFromFile($configFile);
        return $this->mergeProcessor->process($arr);
    }

    /**
     * Attempts to load !config include file. The GenericConfig loader is used
     * to process these sub-includes so that we don't resolve nested !config tags.
     * @param $confPath
     * @param string $tag
     * @param $flags
     * @return mixed
     */
    public function processConfigTag($confPath, string $tag, $flags) {
        $path = Path::joinAndNormalize($this->root, $confPath);
        Framework::debug("frameworkCfgLoader: !config -> $path");
        return (new GenericConfig())->loadYamlFromFile($path);
    }

    const TAG_CONFIG = '!config';

    public function getCallbacks(): array {
        $callbacks = (new StandardYamlCallbacks())->getCallbacks();
        $callbacks[self::TAG_CONFIG] = [$this, 'processConfigTag'];
        return $callbacks;
    }
}
