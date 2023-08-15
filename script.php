<?php

/**
 * @author Abdul Qadeer
 * @author Abdul Qadeer <abdulqadeerit@gmail.com>
 */

class FileProcessor {
    
    public $column_defination = ["brand_name", "model_name", "condition_name", "grade_name", "gb_spec_name", "colour_name", "network_name", "count"];

    public function main() {
        $cmd_options = getopt(null, ["file:", "unique-combinations:"]);

        if (count($cmd_options) > 0 && $cmd_options !== false) {
            if (!isset($cmd_options["file"]) || !isset($cmd_options["unique-combinations"])) {
                echo("Command line arguments are not in proper format. \n Tested example: php script.php --file example_1.csv --unique-combinations=combination_count.csv\n\n");
            }
    
            $source_file_name = $cmd_options["file"];
            $destination_file_name = $cmd_options["unique-combinations"];
    
            $file_type = $this->identifyFileFormat($source_file_name);
            
            $allowed_file_types = ["csv", "tsv", "json"];
    
            if (!in_array($file_type, $allowed_file_types)) {
                echo("We are not accepting this file type. Please define it in configuration to make it allow in allowed_file_types.\n\n");
            }
    
            if ($file_type === "csv") {
                $this->logMessage("File type is csv",'info');
                $this->readCsvFile($source_file_name, $destination_file_name);
            } elseif ($file_type === "tsv") {
                $this->logMessage("File type is tsv",'info');
                $this->readTsvFile($source_file_name, $destination_file_name);
            } elseif($file_type === "json"){
                $this->logMessage("File type is JSON",'info');
                $this->readJsonFile($source_file_name, $destination_file_name);
            }
        } else {
            $this->logMessage("Could not get value of command line option.\n\n", 'info');
        }
    }

    public function identifyFileFormat($filename = null) {  // php unit test included
        if ($filename && strpos($filename, '.') !== false) {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            return strtolower($extension);
        }
        return null;
    }

    public function readJsonFile($filename = null, $destination_file_name = null){
        if ($filename !== null) {
            if (!file_exists($filename)) {
                die("File doesn't exist");
            }
    
            $this->logMessage("Reading the JSON file " . $filename, 'info');
    
            $file_handler = fopen($filename, 'r');
            if (!$file_handler) {
                die('Error opening JSON file.\n\n');
            }
    
            $row = 1;
            $group_counts = [];
            $mapped_columns = [];
            $buffer = ''; // Initialize a buffer to store a chunk of data
            $data = [];
            $item_counter = 0;
            while (($chunk = fread($file_handler, 4096)) !== false) {
                $buffer .= $chunk;
                
                // Check if the buffer contains a complete JSON object or array
                while (($pos = strpos($buffer, '}')) !== false ||
                       ($pos = strpos($buffer, ']')) !== false) {
                    $jsonChunk = substr($buffer, 0, $pos + 1);
                    $buffer = substr($buffer, $pos + 1);
                    
                    $json_chunk_start = strpos($jsonChunk, '{');
                    $json_chunk_end = strpos($jsonChunk, '}');
                    $jsonChunkNew = substr($jsonChunk, $json_chunk_start,$json_chunk_end);

                    if(strlen($jsonChunkNew) > 0){
                        if($row == 1){
                            $mapped_columns = $this->columnsMapping(array_keys(json_decode($jsonChunkNew, true)));
                            $row++;
                        }
                        $combination = implode('|', array_slice(json_decode($jsonChunkNew, true), 0, 7));
                        $group_counts[$combination] = isset($group_counts[$combination]) ? $group_counts[$combination] + 1 : 1;

                        if ($data === null) {
                            $this->logMessage("Error decoding JSON chunk.\n",'info');
                            continue;
                        }
                        $item_counter++;
                    }
                }

                if(strlen($chunk) <= 0){
                    break;
                }
            }

            $this->logMessage("Total JSON Items : " . $item_counter);
            fclose($file_handler);

            $this->writeFile($destination_file_name, $this->column_defination, $group_counts, $mapped_columns, "JSON");

            $this->logMessage("Closing JSON file " . $filename, 'info');
            $this->logMessage("Output file name " . $destination_file_name, 'info');
        }
    }

    // Updated save function
    private function writeFile($filename, $columnDefination, $groupCounts, $mappedColumns, $file_type)
    {
        $outputHandler = fopen($filename, 'w');

        $this->logMessage("$file_type Header created", 'info');

        fputcsv($outputHandler, $columnDefination);

        $this->logMessage("$file_type Data writing started", 'info');

        foreach ($groupCounts as $combination => $count) {
            $productDeconstruct = explode("|", $combination);
            $outputData = array_map(function ($columnName) use ($mappedColumns, $productDeconstruct) {
                return $productDeconstruct[$this->searchColumn($mappedColumns, $columnName)];
            }, ["brand_name", "model_name", "condition_name", "grade_name", "gb_spec_name", "colour_name", "network_name"]);

            $outputData[] = $count;

            fputcsv($outputHandler, $outputData);
        }

        $this->logMessage("$file_type Data writing finished", 'info');
        fclose($outputHandler);
    }


    public function readTsvFile($filename = null, $destination_file_name = null) {
        if ($filename !== null) {
            if (!file_exists($filename)) {
                die("File doesn't exist");
            }
    
            $this->logMessage("Reading the TSV file " . $filename, 'info');
    
            $file_handler = fopen($filename, 'r');
            if (!$file_handler) {
                die('Error opening TSV file.\n\n');
            }
    
            $row = 1;
            $group_counts = [];
            $mapped_columns = [];
    
            while (($data = fgets($file_handler)) !== false) {
                if ($row === 1) {
                    $mapped_columns = $this->columnsMapping(explode("\t", trim($data)));
                } else {
                    $combination = implode('|', array_map(function ($item) {
                        return str_replace('"','',$item);
                    }, array_slice(explode("\t",$data),0,7)));
                    $group_counts[$combination] = isset($group_counts[$combination]) ? $group_counts[$combination] + 1 : 1;
                }
                $row++;
            }
    
            fclose($file_handler);

            $this->writeFile($destination_file_name, $this->column_defination, $group_counts, $mapped_columns, "TSV");

            $this->logMessage("Closing TSV file " . $filename, 'info');
            $this->logMessage("Output file name " . $destination_file_name, 'info');
        }
    }

    public function readCsvFile($filename = null, $destination_file_name = null) {
        if ($filename !== null) {
            if (!file_exists($filename)) {
                die("File doesn't exist");
            }
    
            $this->logMessage("Reading the CSV file " . $filename, 'info');
    
            $file_handler = fopen($filename, 'r');
            if (!$file_handler) {
                die('Error opening CSV file.\n\n');
            }
    
            $row = 1;
            $group_counts = [];
            $mapped_columns = [];
    
            while (($data = fgetcsv($file_handler)) !== false) {
                if ($row === 1) {
                    $mapped_columns = $this->columnsMapping($data);
                } else {
                    $combination = implode('|', array_slice($data, 0, 7));
                    $group_counts[$combination] = isset($group_counts[$combination]) ? $group_counts[$combination] + 1 : 1;
                }
                $row++;
            }
    
            fclose($file_handler);

            $this->writeFile($destination_file_name, $this->column_defination, $group_counts, $mapped_columns, "CSV");

            $this->logMessage("Closing CSV file " . $filename, 'info');
            $this->logMessage("Output file name " . $destination_file_name, 'info');
        }
    }

    public function columnsMapping($columns_to_map = null) {
        
        $mapped_columns = [];
        if ($columns_to_map !== null) {
            foreach ($columns_to_map as $index => $columnName) {
                foreach ($this->column_defination as $position => $name) {
                    if (str_replace('"','',$columnName) === $name) {
                        $mapped_columns[$position] = [$name => $index];
                    }
                }
            }
        }
        
        ksort($mapped_columns);
        return $mapped_columns;
    }

    public function searchColumn($columns, $field) {
        foreach ($columns as $column) {
            if (array_key_exists($field, $column)) {
                return $column[$field];
            }
        }
        return false;
    }

    function logMessage($message, $level = 'info') {    // php unit test included
        $timestamp = date('Y-m-d H:i:s');
        $logLine = "[$timestamp][$level] $message\n";
        echo $logLine;
    }

    public function dd($d){
        print_r($d); die;
    }
    
    public function dp($d){
        echo "-->\n-->"; print_r($d);
    }
} 

$fileprocessor = new FileProcessor();
$fileprocessor->main();

?>