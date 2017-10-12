<?php
/**
 * @copyright 2017 Aleksander Stelmaczonek <al.stelmaczonek@gmail.com>
 * @license   MIT License, see license file distributed with this source code
 */

namespace Koriit\PHPCircle\Config;


use DOMDocument;
use DOMElement;
use function each;
use Koriit\PHPCircle\Config\Exceptions\InvalidSchema;
use Koriit\PHPCircle\Module;
use function libxml_use_internal_errors;

class ConfigReader
{
    /**
     * @param string $filePath Path to XML config file
     *
     * @return Config
     * @throws InvalidSchema
     */
    public function readConfig($filePath) {
        $document = new DOMDocument();
        $document->load($filePath);

        $this->validateSchema($document);

        $modules = $this->readModules($document);
        $dirDetectors = $this->readDirDetectors($document);

        return new Config($modules, $dirDetectors);
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
     *
     * @return Module[]
     */
    private function readModules($document)
    {
        $modules = [];
        /** @var DOMElement $module */
        foreach ($document->getElementsByTagName("Module") as $module) {
            $modules[] = new Module(
                  $module->getElementsByTagName("Name")->item(0)->nodeValue,
                  $module->getElementsByTagName("Namespace")->item(0)->nodeValue,
                  $module->getElementsByTagName("Path")->item(0)->nodeValue
            );
        }

        return $modules;
    }

    /**
     * @param DOMDocument $document
     *
     * @return DirDetector[]
     */
    private function readDirDetectors($document)
    {
        $dirDetectors = [];
        /** @var DOMElement $dirDetector */
        foreach ($document->getElementsByTagName("DirDetector") as $dirDetector) {
            $dirDetectors[] = new DirDetector(
                  $dirDetector->getElementsByTagName("Namespace")->item(0)->nodeValue,
                  $dirDetector->getElementsByTagName("Path")->item(0)->nodeValue
            );
        }

        return $dirDetectors;
    }
}
