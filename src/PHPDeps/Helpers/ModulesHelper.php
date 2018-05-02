<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPDeps\Helpers;

use Koriit\PHPDeps\Config\Config;
use Koriit\PHPDeps\Graph\Vertex;
use Koriit\PHPDeps\Modules\Module;
use Symfony\Component\Console\Style\SymfonyStyle;

class ModulesHelper
{
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
     * @param SymfonyStyle $io
     * @param Vertex       $vertex Module's vertex
     * @param int          $index  List index
     */
    public function renderModuleDependencies(SymfonyStyle $io, Vertex $vertex, $index)
    {
        /** @var Module $module */
        $module = $vertex->getValue();
        $io->section($index . '. ' . $module->getName() . ' [<fg=magenta>' . $module->getNamespace() . '</>]');

        $dependencies = [];
        foreach ($vertex->getNeighbours() as $neighbour) {
            /** @var Module $dependency */
            $dependency = $neighbour->getValue();
            $dependencies[] = $dependency->getName() . ' [<fg=magenta>' . $dependency->getNamespace() . '</>]';
        }

        if (!empty($dependencies)) {
            $io->listing($dependencies);
        } else {
            $io->text('No dependencies');
        }
    }

    /**
     * @param Vertex[] $vertices Module vertices to filter
     * @param string[] $filters  Allowed Module names
     *
     * @return Vertex[] Filtered vertices array
     */
    public function filterVerticesByModuleName(array $vertices, array $filters)
    {
        if (!empty($filters)) {
            $filterExpression = function (Vertex $v) use ($filters) {
                return \in_array($v->getValue()->getName(), $filters);
            };

            return \array_filter($vertices, $filterExpression);
        }

        return $vertices;
    }
}
