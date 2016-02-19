<?php

/**
 *
 * @param object gsx model instance
 * @author John Eberle (tuxudo)
 **/

// Function run by gsx module
function get_gsx_stats(&$gsx_model)
{
	// Error message
	$error = '';

    // Import gsxlib - https://github.com/filipp/gsxlib
    // Set up variables
    require_once(conf('application_path').'helpers/gsxlib.php');
    $_ENV['GSX_CERT'] = conf('gsx_cert');
    $_ENV['GSX_KEYPASS'] = conf('gsx_cert_keypass');
    $sold_to = conf('gsx_sold_to');
    $username = conf('gsx_username');
    
    // Pull from gsxlib
    //$serialnumber = $gsx_model->serial_number;
    $gsx = GsxLib::getInstance($sold_to, $username);
    try {
    $result = $gsx->warrantyStatus($gsx_model->serial_number);
    }
    
    // Catch errors
    catch (Exception $e) {  
        
        // If obsolete, process and run stock warranty lookup
        if( $e->getMessage() === "The serial number entered has been marked as obsolete. If you feel this is in error, please verify and re-enter the serial number.")
   	    { 
        // Load warranty_helper and run stock warranty functions
        require_once(conf('application_path').'helpers/warranty_helper.php');
        $gsx_model->productDescription = model_description_lookup($gsx_model->serial_number);
        $gsx_model->estimatedPurchaseDate = estimate_manufactured_date($gsx_model->serial_number);
        $gsx_model->coverageStartDate = $gsx_model->estimatedPurchaseDate;
        $gsx_model->registrationDate = $gsx_model->estimatedPurchaseDate;
        $gsx_model->coverageEndDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($gsx_model->estimatedPurchaseDate)) . " + 365 day"));
        $gsx_model->warrantyStatus = 'Obsolete';
        $gsx_model->partCovered = 'No';
        $gsx_model->laborCovered = 'No';
        $gsx_model->daysRemaining = '0';
        $gsx_model->isLoaner = 'No';
        $gsx_model->isObsolete = 'Yes';
        $gsx_model->isVintage = 'No';
            
        $gsx_model->save();
        $error = 'GSX Lookup failed - machine is Obsolete - running stock warranty lookup';
       
        //check_status();
        return $error;
        }
        // If error is not obsolete, return error
        else 
        {
            return $e->getMessage();
        }
    }
    
    // Catch GSX lookup fails
	if( $result === FALSE)
	{        
        // Load warranty_helper and run stock warranty functions
        require_once(conf('application_path').'helpers/warranty_helper.php');
        $gsx_model->productDescription = model_description_lookup($gsx_model->serial_number);
        $gsx_model->estimatedPurchaseDate = estimate_manufactured_date($gsx_model->serial_number);
        $gsx_model->coverageEndDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($gsx_model->estimatedPurchaseDate)) . " + 365 day"));
        $gsx_model->coverageStartDate = $gsx_model->estimatedPurchaseDate;
        $gsx_model->registrationDate = $gsx_model->estimatedPurchaseDate;
        $gsx_model->warrantyStatus = 'GSX lookup failed';
        $gsx_model->save();
        
		$error = 'GSX Lookup failed - running stock warranty lookup';
        return $error;
	}
    
	else
	{
        // Rename warranty status for stock warranty stuff
        $local_warrantyStatus = str_replace(array('AppleCare Protection Plan','Out Of Warranty (No Coverage)','Apple Limited Warranty'), array('Supported','Expired','No Applecare'), $result->warrantyStatus);
    
        // Coverage Status
        if(empty($result->contractType))
        {
           $gsx_model->contractType = "";
        }
         else
        {
            $gsx_model->contractType = str_replace(array('LI','LP','RP','IR','LS','CC','CS','DO','MU','OO','PA','QP','RE','G9','RA','PP','C1','C2','C3','C4','C5','TC','PT','EC','CL','CP','CI','CW','VW','VP','RI','RW'), array('Apple Limited Warranty','Apple Limited Warranty','Repeat Service','Internal Repairs','Lost Shipments (Coverage)','Custom Bid Contracts','Customer Satisfaction (CS) Code','DOA Coverage','Missing Upon 1st Use','Out Of Warranty (No Coverage)','AppleCare Parts Agreement','Quality Program','Repeat Service','Pending Coverage Check','AppleCare Repair Agreement','AppleCare Protection Plan','AppleCare Protection Plan','AppleCare Parts Agreement','AppleCare Repair Agreement','Custom Bid Contracts','Extended Contract','Edu/Govt Warranty (Australia)','Additional Part Coverage','Additional Service Coverage','Consumer Law Coverage','Consumer Law Coverage','Consumer Law Repeat Coverage','Consumer Law Repeat Coverage','Variable Warranty','Variable Warranty','Variable Warranty Repeat','Variable Warranty Repeat'), $result->contractType);
        }
        
        // Fix for non-AppleCare machines
        if(empty($result->contractCoverageEndDate))
        {
            $gsx_model->contractCoverageEndDate = ''; 
        }
         else
        {
             $gsx_model->contractCoverageEndDate = date('Y-m-d', strtotime($result->contractCoverageEndDate));
        }
        
        if(empty($result->contractCoverageStartDate))
        {
            $gsx_model->contractCoverageStartDate = ''; 
        }
         else
        {
             $gsx_model->contractCoverageStartDate = date('Y-m-d', strtotime($result->contractCoverageStartDate));
        }
        
        if(empty($result->laborCovered))
        {
            $gsx_model->laborCovered = 'No';
        }
         else
        {
            $gsx_model->laborCovered = str_replace(array('Y','N'), array('Yes','No'), $result->laborCovered);
        }
        
        if(empty($result->partCovered))
        {
            $gsx_model->partCovered = 'No'; 
        }
         else
        {
            $gsx_model->partCovered = str_replace(array('Y','N'), array('Yes','No'), $result->partCovered);
        }
        
        // Update the stock machine tables
		$machine = new Machine_model($gsx_model->serial_number);
		//$machine->img_url = $matches[1]; Todo: get image url for VM
		$machine->machine_desc = $result->productDescription;
		$machine->save();
        
        // Translate gsxlib to MunkiReport DB
        $gsx_model->warrantyMod = $local_warrantyStatus;
        $gsx_model->warrantyStatus = $result->warrantyStatus;
        $gsx_model->daysRemaining = $result->daysRemaining; 
        $gsx_model->estimatedPurchaseDate = date('Y-m-d', strtotime($result->estimatedPurchaseDate));
        $gsx_model->purchaseCountry = $result->purchaseCountry;
        $gsx_model->registrationDate = date('Y-m-d', strtotime($result->registrationDate));
        $gsx_model->productDescription = $result->productDescription;
        $gsx_model->configDescription = $result->configDescription;
        $gsx_model->isLoaner = str_replace(array('Y','N'), array('Yes','No'), $result->isLoaner);
        $gsx_model->isObsolete = 'No';
        
        // Check if Vintage and write flag
        $vintageCheck = substr($gsx_model->productDescription, 0, 4);
        if ($vintageCheck === "VIN,")
        {
        $gsx_model->isVintage = 'Yes';
        }
        else
        {        
            $gsx_model->isVintage = 'No';
        }
        
        // Fix for non-obsolete and out of warranty machines
        if(empty($result->coverageEndDate))
        {
            if ($result->warrantyStatus === "Out Of Warranty (No Coverage)" )
            {
               if(empty($result->contractCoverageEndDate)) 
               {
                $gsx_model->coverageEndDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($result->registrationDate)) . " + 365 day"));
                }
                else
                    {
                    $gsx_model->coverageEndDate = date('Y-m-d', strtotime($result->contractCoverageEndDate));  
                    }
        }
            else
            {
            $gsx_model->coverageEndDate = date("Y-m-d", strtotime(date("Y-m-d", strtotime($result->registrationDate)) . " + 365 day"));
            }
        }
         else
        {
             $gsx_model->coverageEndDate = date('Y-m-d', strtotime($result->coverageEndDate));
        }
        
        if(empty($result->coverageStartDate))
        {
            if ($result->warrantyStatus === "Out Of Warranty (No Coverage)" )
            {
            $gsx_model->coverageStartDate = date('Y-m-d', strtotime($result->registrationDate));
            }
            else
            {
            $gsx_model->coverageStartDate = ''; 
            }
        }
         else
        {
             $gsx_model->coverageStartDate = date('Y-m-d', strtotime($result->coverageStartDate));
        }
        
        // Service Level Agreement
        // Fix for different warranty type returns from GSX
        if(empty($result->slaGroupDescription))
        {
            $gsx_model->warrantyReferenceNo = $result->warrantyReferenceNo; 
        }
         else
        {
            $gsx_model->warrantyReferenceNo = $result->slaGroupDescription; 
        }
        
        // Update the stock warranty tables
		$warranty = new Warranty_model($gsx_model->serial_number);
		$warranty->purchase_date = $gsx_model->estimatedPurchaseDate;
		$warranty->end_date = $gsx_model->contractCoverageEndDate;
        $warranty->status = $local_warrantyStatus;
		$warranty->save();
        
        // Save that stuff :D
		$gsx_model->save();
        $error = 'GSX data processed';
        return $error;
	}
    	return $error;
}