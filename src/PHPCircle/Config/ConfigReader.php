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

        $dirModules = $this->readDirModules($document);
        $classModules = $this->readClassModules($document);
        $fileModules = $this->readFileModules($document);
        $dirDetectors = $this->readDirDetectors($document);

        return new Config($dirModules, $classModules, $fileModules, $dirDetectors);
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
     * @return DirModule[]
     */
    private function readDirModules($document)
    {
        $dirModules = [];
        /** @var DOMElement $dirModule */
        foreach ($document->getElementsByTagName("DirModule") as $dirModule) {
            $dirModules[] = new DirModule(
                  $dirModule->getElementsByTagName("Name")->item(0)->nodeValue,
                  $dirModule->getElementsByTagName("Namespace")->item(0)->nodeValue,
                  $dirModule->getElementsByTagName("Path")->item(0)->nodeValue
            );
        }

        return $dirModules;
    }

    /**
     * @param DOMDocument $document
     *
     * @return ClassModule[]
     */
    private function readClassModules($document)
    {
        $classModules = [];
        /** @var DOMElement $classModule */
        foreach ($document->getElementsByTagName("ClassModule") as $classModule) {
            $classModules[] = new ClassModule(
                  $classModule->getElementsByTagName("Name")->item(0)->nodeValue,
                  $classModule->getElementsByTagName("Class")->item(0)->nodeValue
            );
        }

        return $classModules;
    }

    /**
     * @param DOMDocument $document
     *
     * @return FileModule[]
     */
    private function readFileModules($document)
    {
        $fileModules = [];
        /** @var DOMElement $fileModule */
        foreach ($document->getElementsByTagName("FileModule") as $fileModule) {
            $fileModules[] = new FileModule(
                  $fileModule->getElementsByTagName("Name")->item(0)->nodeValue,
                  $fileModule->getElementsByTagName("Namespace")->item(0)->nodeValue,
                  $fileModule->getElementsByTagName("Path")->item(0)->nodeValue
            );
        }

        return $fileModules;
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
