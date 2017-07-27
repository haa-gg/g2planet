<?php

class CreditCardValidator {

	public $arrCardInfo = array(
		'status' 	=> null, 
		'type' 		=> null, 
		'substring' => null, 
		'reason' 	=> null
		);
	
	
	/*
	Here is where we store the different card types and their standards
	 */
	public $arrCardTypes = array(
		'amex' => array(
			'name'		=>	'American Express',
			'iinrange' 	=> 	'34,37',
			'length'	=> 	15
			), 
		'mastercard' => array(
			'name'		=>	'MasterCard',
			'iinrange' 	=> 	'51-55',
			'length'	=> 	16
			), 
		'visa' => array(
			'name'		=>	'VISA',
			'iinrange' 	=> 	'4',
			'length'	=> 	16
			)
		);
	
	
	//Set what to look for in a major industry identifier (first card digit)
	public $arrAcceptedMII = array(3, 4, 5);
	

	//This is where we actually test our inputted value against the industry standards
	public function Validate($strCardNumber=null, $strCardType=null) {
		
		// Check if there is anything entered
		if($strCardNumber === null) {
			$this->arrCardInfo['failure'] = 'format';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		
		// We either need no card type passed, or a valid card type passed
		if(($strCardType !== null) && !in_array($strCardType, $this->arrCardTypes)) {
			$this->arrCardInfo['failure'] = 'cardtype';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		// Run the check major industry identifier (MII) function against the number
		if(!$this->CheckMII($strCardNumber)) {
			$this->arrCardInfo['failure'] = 'mii';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		
		// Check the first 6 digits to see if they meet IIN standards
		if(!$this->CheckIIN($strCardNumber)) {
			$this->arrCardInfo['failure'] = 'iin';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		// Check the Luhn Algorithm
		if(!$this->CheckLuhn($strCardNumber)) {
			$this->arrCardInfo['failure'] = 'algorithm';
			$this->arrCardInfo['status'] = 'invalid';
			return false;
		}
		
		// If we get here, it's valid and we go ahead and set the arrCardInfo details
		$this->arrCardInfo['status'] = 'valid';
		$this->arrCardInfo['substring'] = $this->GetCardSubstring($strCardNumber);
		
		return true;
		
	} # END METHOD Validate()
	
	
	//Clean out all non-numeric characters
	public function CleanCardNumber($strCardNumber=null) {

		return preg_replace('/[^0-9]/', '', $strCardNumber);
		
	}

	//Check if we need to turncate the card number
	public function GetCardSubstring($strCardNumber=null) {
		
		/*If we got passed the already truncated / short form card number,
		 then just send that back. But before we do, make sure we're not 
		 sending back the whole number!*/
		 if(strstr($strCardNumber, '*') && (substr($strCardNumber) < 10)) return $strCardNumber;
		//If the format isn't what we want, run the clean function
		 $strCardNumber = $this->CleanCardNumber($strCardNumber);

		// Return the truncated card number, or just an empty string if the param was null
		 return $strCardNumber ? '***'.substr($strCardNumber, (strlen($strCardNumber) - 4), 4) : '';

		}

	//Here is how we check if the MII is valid
		public function CheckMII($strCardNumber=null) {

		// Clean the card number before we eval it
			$strCardNumber = $this->CleanCardNumber($strCardNumber);

		// If there's no number, don't do anything
			if(!$strCardNumber) return false;

		// Get the first digit and see if it is in the whitelist we have up near the top
			$intFirstDigit = (int) substr($strCardNumber, 0, 1);

			if(!in_array($intFirstDigit, $this->arrAcceptedMII)) return false;

		// If we get here, it is legit so return true
			return true;
	} #
	
	
	/*
	Now for some pretty straightforward math with our cleaned up credit card number.
	The steeps for the Luhn algorithm are listed up on Wikipedia as:

	1) From the rightmost digit, which is the check digit, and moving left, double the value of every second digit. If the result of this doubling operation is greater than 9 (e.g., 8 × 2 = 16), then add the digits of the product (e.g., 16: 1 + 6 = 7, 18: 1 + 8 = 9) or alternatively subtract 9 from the product (e.g., 16: 16 - 9 = 7, 18: 18 - 9 = 9).

2)Take the sum of all the digits.

3) If the total modulo 10 is equal to 0 (if the total ends in zero) then the number is valid according to the Luhn formula; else it is not valid.
	 */
public function CheckLuhn($strCardNumber=null) {

		// Clean the number passed in
	$strCardNumber = (string) $this->CleanCardNumber($strCardNumber);

		// First, get the check digit (the last digit)
	$strCheckDigit = substr($strCardNumber, (strlen($strCardNumber) - 1), 1);

		// Now reverse the card number, double every second values (and combine their digits), and tally them all
	$strCardNumberReverse = strrev($strCardNumber);
	$intTotal = 0;
	for($i = 1; $i <= strlen($strCardNumberReverse); $i++) {
			// Double every other number
		$intVal = (int) ($i % 2) ? $strCardNumberReverse[$i-1] : ($strCardNumberReverse[$i-1] * 2);
			// Sum any double digits
		if($intVal > 9) {
			$strVal = (string) $intVal;
			$intVal = (int) ($strVal[0] + $strVal[1]);
		}

			// Throw it in the array to be tallied
		$intTotal += $intVal;
	}

		// Now check to see if our sum mod 10 == 0
	return (($intTotal % 10) == 0) ? true : false;

	} 
	
	
	//Check the first 6 numbers to see if they are in valid ranges
	public function CheckIIN($strCardNumber=null) {

		// Clean the number passed in
		$strCardNumber = $this->CleanCardNumber($strCardNumber);
		
		// Gotta have a number passed in
		if(!$strCardNumber) return false;
		
		// This will hold any matches. Hopefully we'll only have one!
		$arrCardTypePossibilities = array();
		
		// Loop through all the accepted card types from the array near the top and check our num against them
		foreach($this->arrCardTypes as $strShortName => $arrCardType) {
			

				// First, do the easy job of checking the length
			$strLen = strlen($strCardNumber);
			if($strLen == $arrCardType['length']) {

					// Now, unpack the IINs and compare against them
					// This will get all the "range sets", which are the comma delimited items
				$arrRangeSets = explode(',', $arrCardType['iinrange']);
				foreach($arrRangeSets as $strRangeSetItem) {

						// Get any ranges that are hyphen delimited items, denoting ranges (looking at you, MasterCard)
					$arrStrRanges = explode('-', $strRangeSetItem);

						// arrStrRanges should contain either an array (if it was a hyphenated range)
						// or a single number. If it's an array, we need to check overy value in the range.

						// Check every value in the range
					if(count($arrStrRanges) > 1) {
						for($i = $arrStrRanges[0]; $i <= $arrStrRanges[1]; $i++) {

							if(
								(strpos((string) $strCardNumber, (string) $i) === 0) &&
								!in_array($strShortName, $arrCardTypePossibilities)
								) $arrCardTypePossibilities[] = $strShortName; 

						}

						// Check against one value
				} else {
					if(
						(strpos((string) $strCardNumber, (string) trim($arrStrRanges[0])) === 0) &&
						!in_array($strShortName, $arrCardTypePossibilities)
						) $arrCardTypePossibilities[] = $strShortName; 

				}

					} 
					
				} 
				
		} 
		
		// Now assign the possible card type values to the arrCardInfo property
		$this->arrCardInfo['type'] = implode('|', $arrCardTypePossibilities);
		
		// Return true if we found at least one possibile type
		return count($arrCardTypePossibilities) ? true : false;
		
	} # END METHOD CheckIIN()
	
	//Return the card info to the main page
	public function GetCardInfo() {
		return $this->arrCardInfo;
	} # END METHOD GetCardInfo()
		
	
} # END CLASS CreditCardValidator()
?>