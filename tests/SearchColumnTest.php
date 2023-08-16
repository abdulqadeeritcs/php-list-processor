<?php
use PHPUnit\Framework\TestCase;

require_once './script.php';
$te = new FileProcessor();

class SearchColumnTest extends TestCase
{
    public function testSearchColumnWithExistingField()
    {
        global $te;
        $columns = array(
            array("brand_name" => 0),
            array("model_name" => 1),
            array("condition_name" => 2),
            array("grade_name" => 3),
            array("gb_spec_name" => 4),
            array("colour_name" => 5),
            array("network_name" => 6)
        );
        $field = "network_name";

        $result = $te->searchColumn($columns, $field);
        $this->assertEquals(6, $result);
    }

    public function testSearchColumnWithNonExistingField()
    {
        global $te;
        $columns = array(
            array("brand_name" => 0),
            array("model_name" => 1),
            array("condition_name" => 2),
            array("grade_name" => 3),
            array("gb_spec_name" => 4),
            array("colour_name" => 5),
            array("network_name" => 6)
        );
        $field = "model_name";

        $result = $te->searchColumn($columns, $field);
        $this->assertNull(null, $result);
    }
}
