<?php

class PVX_API
{
	const ITEMS_PER_PAGE = 10;
	const PVX_NS = "http://www.peoplevox.net/";
	
	private $clientID;
	private $sessionID;
	private $loggedIn;
	private $client;
	public $debugmode;
	private $templateName;
	private $searchClause;
	private $url;
	private $saveAction;
	
	public $totalRows;
	public $errorOccurred;
	public $errorMessage;
	public $currentPageNo;
	public $morePages;
	public $SaveDataDetail;
	public $partial_import;

			
	function __construct($clientID,$Username,$Password,$URL)
	{
		$this->saveAction=0;  // set to default action
		$this->debugmode = false;
		$this->loggedIn = false;
		$this->errorOccurred = false;
		$this->url = $URL;
		$this->logintoAPI ($clientID,$Username,$Password, $this->url);
	}
	
	public function DeleteData($templateName, $csv_data)
	{
		$this->saveAction = 2;  // delete
		$response = $this->SaveData($templateName, $csv_data);
		$this->saveAction = 0;  // reset to default action
		return $response;
	}


	public function SaveData($templateName, $csv_data)
	{
		// Import Data in PVX WMS
		// Returns: 	Number of rows imported (if successful).
		//			 	False (if unsuccessful)
		//  Also sets: 	SaveDataDetail (details of import errors)
		//				partial_import (true - if partially successful, false otherwise)
		
		if(!$this->loggedIn)
		{
			$this->errorOccurred = true;
			$this->errorMessage = "PVX object: not logged into PVX API";
			return false;
		}
			
		$this->errorOccurred = false;	
			
		// create the SOAP request body
		$saveRequest = array('TemplateName' => $templateName, 'CsvData' => $csv_data, 'Action' => $this->saveAction);  // Actions: 0 - default action; 1 - do not allocate; 2 - delete
		$saveRequestObj = array('saveRequest' => $saveRequest);
		
		if ($this->debugmode) {echo('<PRE>DEBUGMODE: GET DATA PARAMS: '.print_r($saveRequestObj, true)."</PRE>");}
	
		// make the SOAP call to PVX GetData
		$response = $this->doSOAPCall('SaveData', $saveRequestObj);
		
	
		if ($response) {
			// SOAP Call succeeded
			$this->partial_import = ($response->SaveDataResult->TotalCount > 0) && ($response->SaveDataResult->ResponseId == -1);
			$this->SaveDataDetail = $response->SaveDataResult->Detail;
			return (($response->SaveDataResult->TotalCount == 0) ? false : $response->SaveDataResult->TotalCount);
		}
		// SOAP Call failed - errorMessage and  errorOccurred should be set...
		return false;	
	}
	
	
	public function GetNextPage()
	// get next data page
	{
		if($this->morePages)
		{
			return ($this->GetData($this->templateName, ++$this->currentPageNo, $this->searchClause));
		}
		else
		{
			return null;
		}
	}	
		
	public function GetData($templateName, $pageNo, $searchClause)
	{
		if(!$this->loggedIn)
		{
			$this->errorOccurred = true;
			$this->errorMessage = "PVX object: not logged into PVX API";
			return false;
		}
		
		$this->errorOccurred = false;	
		
		// create the SOAP request body
		$getRequest = array('TemplateName' => $templateName, 'PageNo' => $pageNo, 'ItemsPerPage' => self::ITEMS_PER_PAGE, 'SearchClause' => $searchClause);
		$getRequestObj = array('getRequest' => $getRequest);
		
		if ($this->debugmode) {echo('<PRE>DEBUGMODE: GET DATA PARAMS: '.print_r($getRequestObj, true)."</PRE>");}
	
		// make the SOAP call to PVX GetData
		$response = $this->doSOAPCall('GetData', $getRequestObj);
		
	
		if (($response) && $response->GetDataResult->ResponseId == 0)
		{
			$this->totalRows = $response->GetDataResult->TotalCount;
			$this->currentPageNo = $pageNo;
			$this->templateName = $templateName;
			$this->searchClause = $searchClause;
			$this->morePages = (($this->currentPageNo) <= ($this->totalRows / self::ITEMS_PER_PAGE));
			
			return($response->GetDataResult->Detail);
		}
				
		// Update error message for invalid response, otherwise should already be set to the SOAP exceptions_enabled
		if ($response) { $this->errorMessage = $response->GetDataResult->Detail; }
		return false;
			
	}
	
	public function GetReportData($templateName, $pageNo, $searchClause, $Columns, $OrderBy)
	{
		if(!$this->loggedIn)
		{
			$this->errorOccurred = true;
			$this->errorMessage = "PVX object: not logged into PVX API";
			return false;
		}
		
		$this->errorOccurred = false;	
			
		// create the SOAP request body
		$getReportRequest = array('TemplateName' => $templateName, 'PageNo' => $pageNo, 'ItemsPerPage' => self::ITEMS_PER_PAGE, 'OrderBy' => $OrderBy, 'Columns' => $Columns, 'SearchClause' => $searchClause);
		$getRequestObj = array('getReportRequest' => $getReportRequest);
		
		if ($this->debugmode) {echo('<PRE>DEBUGMODE: GET DATA PARAMS: '.print_r($getRequestObj, true)."</PRE>");}
	
		// make the SOAP call to PVX GetData
		$response = $this->doSOAPCall('getReportData', $getRequestObj);
		
		
	
		if (($response) && $response->GetReportDataResult->ResponseId == 0)
		{

			$this->totalRows = $response->GetReportDataResult->TotalCount;
			$this->currentPageNo = $pageNo;
			$this->templateName = $templateName;
			$this->searchClause = $searchClause;
			$this->morePages = (($this->currentPageNo) <= ($this->totalRows / self::ITEMS_PER_PAGE));
			
			if ($this->debugmode) { echo('<PRE>DEBUGMODE: morePages: '.($this->morePages?'True':'False')."</PRE>"); }
			
			return($response->GetReportDataResult->Detail);
		}
		
		// Update error message for invalid response, otherwise should already be set to the SOAP exceptions_enabled
		if ($response) { $this->errorMessage = $response->GetReportDataResult->Detail; }
		return false;
			
	}
	
	public function LoggedIn()
	{
		return $this->loggedIn;
	}
	

	public function subscribeEvent($eventType, $callBackURL)
	{
	
		if(!$this->loggedIn)
		{
			$this->errorOccurred = true;
			$this->errorMessage = "PVX object: not logged into PVX API";
			return false;
		}
		
		$this->errorOccurred = false;	
		
		// create the SOAP request body
		$getRequestObj = array('eventType' => $eventType, 'filter' => "", 'callbackUrl' => $callBackURL );
		
		$response = $this->doSOAPCall('SubscribeEvent', $getRequestObj);
		
		if (($response) && $response->SubscribeEventResult->ResponseId == 0)
		{
			return $response->SubscribeEventResult->Detail ;
		}
		
		// Update error message for invalid response, otherwise should already be set to the SOAP exceptions_enabled
		if ($response) { $this->errorMessage = $response->SubscribeEvent->Detail; }
		return false;
	}
	
	public function unsubscribeEvent($subscriptionID)
	{
			
			if(!$this->loggedIn)
			{
				$this->errorOccurred = true;
				$this->errorMessage = "PVX object: not logged into PVX API";
				return false;
			}
			
			$this->errorOccurred = false;	
			
			// create the SOAP request body
			$getRequestObj = array('subscriptionId' => $subscriptionID);
			
			if ($this->debugmode) {echo('<PRE>DEBUGMODE: UnsubscribeEvent:  SubscriptionID '.print_r($getRequestObj, true)."</PRE>");}
		
			
			$response = $this->doSOAPCall('UnsubscribeEvent', $getRequestObj);
			

			if (($response) && $response->UnsubscribeEventResult->ResponseId == 0)
			{
				return true;
			}
			// Update error message for invalid response, otherwise should already be set to the SOAP exceptions_enabled
			if ($response) { $this->errorMessage = $response->UnsubscribeEventResult->Detail; }
			return false;
				
	}
	
	private function doSOAPCall($function_name, $soap_request_body)
	{
		try
		{
			$this->errorOccurred = false;
			$UserSessionCredentials	= array('ClientId' => $this->clientID, 'SessionId' => $this->sessionID, 'UserId' => null);
			$header = new SoapHeader(self::PVX_NS, 'UserSessionCredentials', $UserSessionCredentials);
			$this->client->__setSoapHeaders($header);
					if ($this->debugmode) {echo('<PRE>DEBUGMODE: GET DATA PARAMS: '.print_r($soap_request_body, true)."</PRE>");}
		
			// make the SOAP call to PVX GetData
			$response = call_user_func(array($this->client, $function_name), $soap_request_body);
			
			if ($this->debugmode) 
			{
				echo('<PRE>DEBUGMODE: SubscribeEventResponse XML Exchange:</PRE>');
				echo "<PRE>REQUEST:\n" . htmlentities(str_ireplace('><', ">\n<", $this->client->__getLastRequest())) . "\n</PRE>";
				echo "<PRE>RESPONSE:\n" . htmlentities(str_ireplace('><', ">\n<", $this->client->__getLastResponse())) . "\n</PRE>";
			}
			
			return $response;
			
		}
		catch (Exception $e)
		{
			$this->errorOccurred = true;
			$this->errorMessage =  $e->getMessage();
			return false;	
		}			
			
	}
	
	private function logintoAPI($clientID, $username, $password, $url)
	{
		try
		{
			$this->errorOccurred = false;
			if($this->loggedIn) return true; //already logged in.
			
			$params = array('clientId' => $clientID,
							'username' => $username,
							'password' => base64_encode($password));
			if($this->debugmode)
			{ 
				echo('<PRE>DEBUGMODE: '.print_r($params, true).'</PRE>');
				$this->client = new SoapClient($url."?wsdl", array("trace" => 1, "exception" => 0));
			} 
			else 
			{ 
				$this->client = new SoapClient($url."?wsdl");
			}
	
			$response = $this->client->Authenticate($params);
	
			if($this->debugmode) {echo('<PRE>DEBUGMODE: AUTHENTICATION RESPONSE: '.print_r($response, true)."</PRE>");}
	
			if(!is_soap_fault($response) && $response->AuthenticateResult->ResponseId == 0)
			{
				if($this->debugmode) {echo('DEBUGMODE: NO ERRORS DETECTED AFTER CALLING PVX "Authenticate" FUNCTION');}
				$result = explode(',',$response->AuthenticateResult->Detail);
				$this->sessionID = $result[1];
				$this->clientID = $result[0];
				$this->loggedIn = true;
				return true;
			}
			$this->errorOccurred = true;
			$this->errorMessage = ($response) ? $response->GetDataResult->Detail : 'PVX->logintoAPI : Call failed';
			return false;	
		}
		catch (Exception $e)
		{
			$this->errorOccurred = true;
			$this->errorMessage =  $e->getMessage();
			return false;	
		}			
	} 
	
	
}






