<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;

use DOMDocument;
use DOMElement;
use Koriit\PHPCircle\Config\Exceptions\InvalidConfig;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\Modules\Module;
use function dirname;
use Koriit\PHPCircle\Modules\ModuleDetector;
use function libxml_use_internal_errors;

class ConfigReader
{
    /** @var ConfigValidator */
    private $validator;

    public function __construct(ConfigValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param string $filePath Path to XML config file
     *
     * @return Config
     * @throws InvalidSchema
     * @throws InvalidConfig
     */
    public function readConfig($filePath)
    {
        $document = new DOMDocument();
        $document->load($filePath);

        $this->validateSchema($document);

        $dir = realpath(dirname($filePath));

        $modules = $this->readModules($document, $dir);
        $moduleDetectors = $this->readModuleDetectors($document, $dir);

        $config = new Config($modules, $moduleDetectors);

        $this->validator->check($config);

        return $config;
    }

    /**
     * @param DOMDocument $document
     *
     * @throws InvalidSchema
     */
    private function validateSchema($document)
    {
        $libxmlUseInternalErrors = libxml_use_internal_errors(true);
        if (!$document->schemaValidate(__DIR__ . '/../phpcircle.xsd')) {
            throw new InvalidSchema();
        }
        libxml_use_internal_errors($libxmlUseInternalErrors);
    }

    /**
     * @param DOMDocument $document
     * @param string      $dir Absolute path to relative directory
     *
     * @return Module[]
     */
    private function readModules(DOMDocument $document, $dir)
    {
        $modules = [];
        /** @var DOMElement $module */
        foreach ($document->getElementsByTagName("Module") as $module) {
            $name = $module->getElementsByTagName("Name")->item(0)->nodeValue;
            $namespace = $module->getElementsByTagName("Namespace")->item(0)->nodeValue;
            $path = $this->toAbsolutePath($module->getElementsByTagName("Path")->item(0)->nodeValue, $dir);

            $modules[] = new Module($name, $namespace, $path);
        }

        return $modules;
    }

    /**
     * @param DOMDocument $document
     * @param string      $dir Absolute path to relative directory
     *
     * @return ModuleDetector[]
     */
    private function readModuleDetectors(DOMDocument $document, $dir)
    {
        $moduleDetectors = [];
        /** @var DOMElement $moduleDetector */
        foreach ($document->getElementsByTagName("ModuleDetector") as $moduleDetector) {
            $namespace = $moduleDetector->getElementsByTagName("Namespace")->item(0)->nodeValue;
            $path = $this->toAbsolutePath($moduleDetector->getElementsByTagName("Path")->item(0)->nodeValue, $dir);

            $moduleDetectors[] = new ModuleDetector($namespace, $path);
        }

        return $moduleDetectors;
    }

    /**
     * @param string $path
     * @param string $dir Absolute path to relative directory
     *
     * @return string
     * @see https://github.com/sebastianbergmann/phpunit/blob/976b986778e2962577440b93d481e67576124e0d/src/Util/Configuration.php#L1156
     *
     */
    private function toAbsolutePath($path, $dir)
    {
        $path = trim($path);
        if ($path[0] === '/') {
            return $path;
        }

        // Matches the following on Windows:
        //  - \\NetworkComputer\Path
        //  - \\.\D:
        //  - \\.\c:
        //  - C:\Windows
        //  - C:\windows
        //  - C:/windows
        //  - c:/windows
        if (defined('PHP_WINDOWS_VERSION_BUILD') &&
              ($path[0] === '\\' || (strlen($path) >= 3 && preg_match('#^[A-Z]\:[/\\\]#i', substr($path, 0, 3))))) {
            return $path;
        }
        // Stream
        if (strpos($path, '://') !== false) {
            return $path;
        }

        return $dir . DIRECTORY_SEPARATOR . $path;
    }
}
