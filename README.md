# PHP Product File Processor
The PHP Product File Processor is a PHP class that processes various types of files (CSV, TSV, JSON) and performs specific operations on them, such as counting unique combinations of products data.

## Features

- Reads and processes CSV, TSV, and JSON files.
- Counts unique combinations of specified fields.
- Writes processed data to output files.

## Prerequisites
- PHP 7.x or later
- Git
- Composer

## Installation

- Clone the repository:
- Navigate to the project directory:
- Run the script from the command line with the following options:
- php script.php --file input_file_path --unique-combinations output_file_path
- Replace input_file_path with the path to your input file (CSV, TSV, or JSON) and output_file_path with the desired path for the output file containing unique combinations.
- The script will process the input file, count unique combinations, and generate the output file with counted combinations.
- Install the dependencies and devDependencies and start the server.

## Class Methods
- identifyFileFormat($filename): Identifies the file format based on its extension.
- readJsonFile($filename, $destination_file_name): Reads and processes a JSON file, counting unique combinations.
- readTsvFile($filename, $destination_file_name): Reads and processes a TSV file, counting unique combinations.
- readCsvFile($filename, $destination_file_name): Reads and processes a CSV file, counting unique combinations.
- columnsMapping($columns_to_map): Maps column positions to column names.
- searchColumn($columns, $field): Searches for a column in the columns mapping.
- logMessage($message, $level): Logs a message with a specified level.
- dd($d): Dumps and dies to print the content of a variable (for debugging).
- dp($d): Dumps and prints the content of a variable (for debugging).
- main(): The main entry point to execute the processing based on command-line arguments.

