<?php
define("BANK_ACCOUNT_NUMBER_MISSING_MESSAGE","Bank account number missing");
define("BANK_BRANCH_CODE_MISSING_MESSAGE","Bank branch code missing");
define("END_TO_END_ID_MISSING_MESSAGE","End to end id missing");
define("FILE_OPENING_FAIL_MESSAGE","failed to open file maybe wrong path");
class FinalResult {
    //refactored variables to make them more readable also added more readable spacing
    function results($filePath) {
        $document = fopen($filePath, "r");
        //validate if file opened up
        if($document)
            {
                $header = fgetcsv($document);
                $records = [];
                while(!feof($document)) {
                    $documentLine = fgetcsv($document);
                    //if(count($documentLine) == 16) { //why cell limits 17 cells, do we expect longer rows? a count of 17 cells will not guaranty data integrity
                        $ammount           = !$documentLine[8] || $documentLine[8] == "0" ? 0 : (float) $documentLine[8];
                        $bankAccountNumber = !$documentLine[6] ? BANK_ACCOUNT_NUMBER_MISSING_MESSAGE : (int) $documentLine[6];
                        $branchCode        = !$documentLine[2] ? BANK_BRANCH_CODE_MISSING_MESSAGE : $documentLine[2];
                        $e2e               = !$documentLine[10] && !$documentLine[11] ? END_TO_END_ID_MISSING_MESSAGE : $documentLine[10] . $documentLine[11];
                        $singleRecord      = [
                                                "amount"                => [
                                                                                "currency" => $header[0],
                                                                                "subunits" => (int) ($ammount * 100)
                                                                            ],
                                                "bank_account_name"     => str_replace(" ", "_", strtolower($documentLine[7])),
                                                "bank_account_number"   => $bankAccountNumber ,
                                                "bank_branch_code"      => $branchCode,
                                                "bank_code"             => $documentLine[0],
                                                "end_to_end_id"         => $e2e,
                                            ];
                        $records[]         = $singleRecord;
                    }
                //}
                //$records = array_filter($records); // is it necessary to filter? there is a lot of control during creation, need more details!, removing empty cells will break row identification
                fclose($document); //file close was missing
                //will not touch original return dictionary in case all this values are expected
                return [
                            "filename" => basename($filePath),
                            "document" => $document, // why include the whole file in case of success?
                            "failure_code" => $header[1], //misleading? maybe add success pair
                            "failure_message" => $header[2],
                            "records" => $records 
                        ];
            }
        else
            {
                // return in case file was missing
                return [
                            "filename" => basename($filePath),
                            "document" => false,
                            "failure_code" => "", 
                            "failure_message" => FILE_OPENING_FAIL_MESSAGE,
                            "records" => []
                        ];
                
            }
    }
}

?>
