<?php
require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel
{
    public function import($file_path)
    {
        // check validation of file path
        if (!file_exists($file_path)) {
            return array('status' => FALSE, 'message' => 'File not found');
        }
        try {
            // Load the spreadsheet file
            $spreadsheet = IOFactory::load($file_path);
            // Get the first worksheet
            $worksheet = $spreadsheet->getActiveSheet();

            // Get the highest row and column indexes
            $highest_row = $worksheet->getHighestRow();
            $highest_column = $worksheet->getHighestColumn();
            // return data for use controller
            $data = array();
            // Loop through each row of the worksheet in turn
            for ($row = 2; $row <= $highest_row; $row++) {
                // Read a row of data into an array
                $row_data = $worksheet->rangeToArray('A' . $row . ':' . $highest_column . $row, NULL, TRUE, FALSE);
                // return data for use controller
                $data[] = $row_data[0];
            }
            return $data;
        } catch (Exception $e) {
            return array('status' => FALSE, 'message' => $e->getMessage());
        }

    }
}
