<?php
define("BANK_NAME_MISSING_MESSAGE","Bank name missing");
define("BANK_CODE_MISSING_MESSAGE","Bank code missing");
define("BANK_ACCOUNT_NUMBER_MISSING_MESSAGE","Bank account number missing");
define("BANK_BRANCH_CODE_MISSING_MESSAGE","Bank branch code missing");
define("END_TO_END_ID_MISSING_MESSAGE","End to end id missing");
define("FILE_OPENING_FAILED_MESSAGE","File Opening Failed");
define("AMMOUNT_UNITS_FACTOR",100);
define("BANK_ACCOUNT_LENGTH",11);
class FinalResult 
    {
    function results($filePath)
        {
            $document               = fopen($filePath, "r");
            $successfulFileOpening  = false;
            if($document)
                {
                    $documentHeader = fgetcsv($document);
                    $currency = $documentHeader[0];
                    $parserCode = $documentHeader[1];
                    $parserMessage = $documentHeader[2];
                    $records = [];
                    while(!feof($document))
                        {
                            $documentLine      =  fgetcsv($document);
                            $ammount           = !$documentLine[8] || $documentLine[8] == "0" || !is_numeric($documentLine[8])? 0 : floatval($documentLine[8]);
                            $bankAccountNumber = !$documentLine[6] || strlen((string)$documentLine[6]) < BANK_ACCOUNT_LENGTH ? BANK_ACCOUNT_NUMBER_MISSING_MESSAGE : intval($documentLine[6]);
                            $branchCode        = !$documentLine[2] || !is_numeric($documentLine[2])? BANK_BRANCH_CODE_MISSING_MESSAGE : intval($documentLine[2]);
                            $e2eID             = !$documentLine[10] && !$documentLine[11] ? END_TO_END_ID_MISSING_MESSAGE : $documentLine[10] . $documentLine[11];
                            $bankName          = !$documentLine[7]  || !preg_match("/[a-z]/i",strval($documentLine[7])) ? BANK_NAME_MISSING_MESSAGE : str_replace(" ", "_", strtolower($documentLine[7]));
                            $bankCode          = !$documentLine[0] || !is_numeric($documentLine[0])? BANK_CODE_MISSING_MESSAGE  : intval($documentLine[0]);
                            $singleRecord      = [
                                                    "amount"                => [
                                                                                    "currency" => $currency,
                                                                                    "subunits" => intval($ammount * AMMOUNT_UNITS_FACTOR)
                                                                                ],
                                                    "bank_account_name"     => $bankName,
                                                    "bank_account_number"   => $bankAccountNumber,
                                                    "bank_branch_code"      => $branchCode,
                                                    "bank_code"             => $bankCode,
                                                    "end_to_end_id"         => $e2eID,
                                                ];
                            $records[]         = $singleRecord;
                        }
                    
                    return [
                                "filename"       => basename($filePath),
                                "failure_code"    => $parserCode,
                                "failure_message" => $parserMessage,
                                "records"        => $records
                            ];
                }
            else
                {
                    return [
                                "filename"        => basename($filePath),
                                "failure_message" => FILE_OPENING_FAILED_MESSAGE
                            ];
                    
                }
        }
    }

?>
