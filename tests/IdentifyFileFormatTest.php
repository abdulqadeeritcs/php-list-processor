<?php

use PHPUnit\Framework\TestCase;

require_once './script.php';
$te = new FileProcessor();

class IdentifyFileFormatTest extends TestCase
{

    public function testIdentifyFileFormatWithExtension()
    {
        global $te;
        $filename = 'products_1.csv';
        $format = $te->identifyFileFormat($filename);
        $this->assertEquals('csv', $format);
    }

    public function testIdentifyFileFormatWithoutExtension()
    {
        global $te;
        $filename = 'products_1no_extension';
        $format = $te->identifyFileFormat($filename);
        $this->assertNull($format);
    }

    public function testIdentifyFileFormatWithUppercaseExtension()
    {
        global $te;
        $filename = 'products_1.CSV';
        $format = $te->identifyFileFormat($filename);
        $this->assertEquals('csv', $format);
    }

    public function testIdentifyFileFormatWithMultipleDots()
    {
        global $te;
        $filename = 'products_1.tar.gz';
        $format = $te->identifyFileFormat($filename);
        $this->assertEquals('gz', $format);
    }

    public function testIdentifyFileFormatWithNoFilename()
    {
        global $te;
        $format = $te->identifyFileFormat();
        $this->assertNull($format);
    }
}
?>
