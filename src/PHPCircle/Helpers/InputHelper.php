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
use Symfony\Component\Console\Input\InputInterface;

class InputHelper
{
    /** @var ConfigReader */
    private $configReader;

    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
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
     * Requires option 'filter' to be configured.
     *
     * @param InputInterface $input
     *
     * @return string[] Array of filters
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
