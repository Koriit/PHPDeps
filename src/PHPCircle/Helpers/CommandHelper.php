<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Helpers;

use Koriit\PHPCircle\Config\Config;
use Koriit\PHPCircle\Config\ConfigReader;
use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\Modules\Module;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CommandHelper
{
    /** @var ConfigReader */
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    /**
     * @param Module[]     $modules
     * @param SymfonyStyle $io
     *
     * @return bool True if everything is valid, false otherwise
     */
    public function validateModules(array $modules, SymfonyStyle $io)
    {
        $duplicatedModules = $this->findModuleDuplicates($modules);
        if (!empty($duplicatedModules)) {
            $io->error('Two or more of your configured modules have the same name');
            $io->section('Duplicated modules');
            $io->listing($duplicatedModules);

            return false;
        }

        return true;
    }

    /**
     * @param InputInterface $input
     *
     * @throws InvalidConfig
     * @throws InvalidSchema
     *
     * @return Config
     */
    public function readConfig(InputInterface $input)
    {
        $configFile = $input->getOption('config');

        return $this->configReader->readConfig($configFile);
    }


    /**
     * @param Module[] $modules
     *
     * @return string[] Names of duplicated modules
     */
    public function findModuleDuplicates(array $modules)
    {
        $moduleNames = [];
        foreach ($modules as $module) {
            $moduleNames[] = $module->getName();
        }

        $names = [];
        $duplicatedModules = [];
        foreach ($moduleNames as $name) {
            if (\in_array($name, $names)) {
                $duplicatedModules[] = $name;
            } else {
                $names[] = $name;
            }
        }

        return $duplicatedModules;
    }

    /**
     * @param Config $config
     *
     * @return Module[]
     */
    public function findModules(Config $config)
    {
        $modules = $config->getModules();
        foreach ($config->getModuleDetectors() as $detector) {
            $modules = \array_merge($modules, $detector->findModules());
        }

        return $modules;
    }

    /**
     * @param InputInterface $input
     *
     * @return string[] Array of filtered module names
     */
    public function readFilters(InputInterface $input)
    {
        $filters = $input->getOption('filter');
        if (empty(\trim($filters))) {
            return [];
        }

        $filters = \explode(',', $filters);
        $filters = \array_map('trim', $filters);
        \sort($filters);

        return $filters;
    }
}
