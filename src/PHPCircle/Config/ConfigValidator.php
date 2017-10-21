<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;

use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;

class ConfigValidator
{
    /**
     * @param Config $config
     *
     * @throws InvalidConfig
     */
    public function check(Config $config)
    {
        $this->checkIfEmpty($config);

        $this->checkNameDuplication($config);
    }

    /**
     * @param Config $config
     *
     * @throws InvalidConfig
     */
    private function checkIfEmpty(Config $config)
    {
        if (empty($config->getModules()) && empty($config->getModuleDetectors())) {
            throw new InvalidConfig('Configuration cannot be empty');
        }
    }

    /**
     * @param Config $config
     *
     * @throws InvalidConfig
     */
    private function checkNameDuplication(Config $config)
    {
        $modules = [];
        foreach ($config->getModules() as $module) {
            $modules[] = $module->getName();
        }

        if (\count($modules) != \count(\array_unique($modules))) {
            throw new InvalidConfig('Two or more of your configured modules have the same name');
        }
    }
}
