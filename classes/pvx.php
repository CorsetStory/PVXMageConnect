<?php
define("DEBUGMODE", True);
define("TESTMODE", True);


if (defined('TESTMODE') && TESTMODE == True)
{
// Test/QA PVX System
	define("CLIENT_ID", "corsets2661Qa");
	define("USER_NAME", "ReadOnly");
	define("PASSWORD", "r0enaldy14");
	define("URL", "http://qa1.peoplevox.net/corsets2661Qa/resources/integrationservicev4.asmx");
}
else
{
// Live PVX system
//	define("CLIENT_ID", "<hide>");
//	define("USER_NAME", "<hide>");
//	define("PASSWORD", "<hide>");
//	define("URL", "http://emea.peoplevox.net/corsetsuk2600/resources/integrationservicev4.asmx?wsdl");
}

class PVX_API
{
	const ITEMS_PER_PAGE = 10;
	const PVX_NS = "http://www.peoplevox.net/";
	
	private $clientID;
	private $sessionID;
	private $loggedIn;
	private $client;
	private $debugmode;
	private $currentPageNo;
	private $templateName;
	private $searchClause;
	private $morePages;
	private $url;
	public $errorOccurred;
	public $errorMessage;

			
	function __construct()
	{
		$this->debugmode = (defined('DEBUGMODE') && DEBUGMODE == True);
		$this->loggedIn = false;
		$this->errorOccurred = false;
		$this->url = URL;
		$this->logintoAPI (CLIENT_ID, USER_NAME, PASSWORD, $this->url);
		If($this->debugmode) 
		{ 
			if($this->loggedIn) { print "<BR>DEBUGMODE: LOGGED IN: ".$this->sessionID."; clientID = ".$this->clientID;} else { print "<BR>DEBUGMODE: LOG IN FAILED :-(";}
		}
	}

	
	public function GetPurchaseOrderData()
	{
		//get some data
	
	}
	
	public function GetNextPage()
	{
		if($this->morePages)
		{
			return ($this->GetData($this->templateName, $this->currentPageNo + 1, $this->searchClause));
		}
		else
		{
			return null;
		}
	}	
		
	public function GetData($templateName, $pageNo, $searchClause)
	{
		if($this->loggedIn)
		{
			
			// create the SOAP request body
			$getRequest = array('TemplateName' => $templateName, 'PageNo' => $pageNo, 'ItemsPerPage' => self::ITEMS_PER_PAGE, 'SearchClause' => $searchClause);
			$getRequestObj = array('getRequest' => $getRequest);
			
			if ($this->debugmode) {echo('<PRE>DEBUGMODE: GET DATA PARAMS: '.print_r($getRequestObj, true)."</PRE>");}
		
			// make the SOAP call to PVX GetData
			$response = doSOAPCall('GetData', $getRequestObj);
			
			if ($this->debugmode) 
		
			if (($response) && $response->GetDataResult->ResponseId == 0)
			{
				$this->TotalRows = $response->GetDataResult->TotalCount;
				$this->currentPageNo = $pageNo;
				$this->templateName = $templateName;
				$this->searchClause = $searchClause;
				$this->morePages = (($this->currentPageNo) <= ($this->TotalRows / self::ITEMS_PER_PAGE));
				return($response->GetDataResult->Detail);
			}
			else
			{
				$this->errorOccurred = true;
				return false;
			}
				
			
		}
	}
	
	public function LoggedIn()
	{
		return $this->loggedIn;
	}
	
	private function logintoAPI($clientID, $username, $password, $url)
	{
		
		
		If(defined('DEBUGMODE') && DEBUGMODE == True) { print "<BR>DEBUGMODE: ENTERED logintoAPI(".$clientID.", ".$username.", ".$password.", ".$url; }
		
		if(!$this->loggedIn)
		{
			$params = array('clientId' => $clientID,
							'username' => $username,
							'password' => base64_encode($password));
			if(defined('DEBUGMODE') && DEBUGMODE == True)
			{ 
				echo('<PRE>DEBUGMODE: '.print_r($params, true).'</PRE>');
				$this->client = new SoapClient($url."?wsdl", array("trace" => 1, "exception" => 0));
				//echo('<PRE>DEBUGMODE: '.print_r($client->__getTypes(), true).'</PRE>');
			} 
			else 
			{ 
				$this->client = new SoapClient($url);
			}
		
			$response = $this->client->Authenticate($params);
		
			if(defined('DEBUGMODE') && DEBUGMODE == True) {echo('<PRE>DEBUGMODE: AUTHENTICATION RESPONSE: '.print_r($response, true)."</PRE>");}
		
			if(!is_soap_fault($response) && $response->AuthenticateResult->ResponseId == 0)
			{
				if(defined('DEBUGMODE') && DEBUGMODE == True) {echo('DEBUGMODE: NO ERRORS DETECTED AFTER CALLING PVX "Authenticate" FUNCTION');}
				$result = explode(',',$response->AuthenticateResult->Detail);
				$this->sessionID = $result[1];
				$this->clientID = $result[0];
				$this->loggedIn = true;
			}
		}
	} 
	
	public function subscribeEvent($eventType, $callBackURL)
	{
	if($this->loggedIn)
		{
			// create the SOAP request body
			$getRequestObj = array('eventType' => $eventType, 'filter' => "", 'callbackUrl' => $callBackURL );
			
			$response = $this->doSOAPCall('SubscribeEvent', $getRequestObj);
			

			if (($response) && $response->SubscribeEventResult->ResponseId == 0)
			{
				return $response->SubscribeEventResult->Detail ;
			}
			else
			{
				$this->errorOccurred = true;
				return false;
			}
				
		}
	}
	
	public function unsubscribeEvent($subscriptionID)
	{
			// create the SOAP request body
			$getRequestObj = array('subscriptionId' => $subscriptionID);
			
			if ($this->debugmode) {echo('<PRE>DEBUGMODE: UnsubscribeEvent:  SubscriptionID '.print_r($getRequestObj, true)."</PRE>");}
		
			
			$response = $this->doSOAPCall('UnsubscribeEvent', $getRequestObj);
			

			if (($response) && $response->UnsubscribeEventResult->ResponseId == 0)
			{
				return true;
			}
			else
			{
				$this->errorOccurred = true;
				return false;
			}
				
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
	
	
	
		/*
	public function subscribeEvent($eventType, $callBackURL)
	{
			$request = '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
										xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
										xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
							<soap:Header>
								<UserSessionCredentials xmlns="http://www.peoplevox.net/">
									<UserId></UserId>
									<ClientId>'.$this->clientID.'</ClientId>
									<SessionId>'.$this->sessionID.'</SessionId>
								</UserSessionCredentials>
							</soap:Header>
 							<soap:Body>
								<SubscribeEvent xmlns="http://www.peoplevox.net/">
									<eventType>'.$eventType.'</eventType>
									<filter></filter>
									<callbackUrl>'.$callBackURL.'</callbackUrl>
								</SubscribeEvent>
							</soap:Body>
						</soap:Envelope>';
			If($this->debugmode) 
			{ 
				print "<BR>DEBUGMODE: SubscribeEvent:<BR>\$request: <br><pre> ";
				htmlout($request);
				print "</pre><BR>\$this->url:<br>";
				htmlout($this->url, true);
				print "</pre>";
			}
			$response = $this->postPVXRequest($this->url.'?op=SubscribeEvent', $request);
			return $response;
	}
	
	private function postPVXRequest($url,$request){
        try{
            $ch = curl_init($url);
            
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT,        10);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POST,           true );               
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

            //$httpheaderarray = array('Host: peoplevox.net','Content-Type: text/xml; charset=utf-8', 'Content-Length: '.strlen($request),'SOAPAction: "http://www.peoplevox.net/SubscribeEvent"');
			$httpheaderarray = array('POST /appa/resources/integrationservicev4.asmx HTTP/1.1', 'Host: qa1.peoplevox.net', 'Content-Type: text/xml; charset=utf-8', 'SOAPAction: "http://www.peoplevox.net/SubscribeEvent"', 'Content-Length: '.strlen($request));

            if(defined('DEBUGMODE') && DEBUGMODE == True) { echo ('<BR><PRE>DEBUGMODE: postPVXRequest: httpheaderarray:<BR>'.print_r($httpheaderarray, true));}

            curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaderarray);
            
            $response = curl_exec($ch);
            
            if(defined('DEBUGMODE') && DEBUGMODE == True) { echo ('<BR><PRE>DEBUGMODE:  postPVXRequest: Curl command completed<br>');}
             
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            if(defined('DEBUGMODE') && DEBUGMODE == True) { echo ('<BR><PRE>DEBUGMODE:  postPVXRequest: Curl command completed, HTTP_CODE:'.$httpcode);}
    
            
            curl_close($ch);

            if ($httpcode === 200) {
                return $response;
            }else{
                $error = strip_tags($response);
            	//Mage::log($e->getMessage(),null,Invent_Chasepaymentech_Model_Source_Consts::CHASE_ERROR_LOGFILE);
                //return false;
                return $error;
            }
        } catch (Exception $e) {
            // Log Exception.
            //Mage::log($e->getMessage(),null,Invent_Chasepaymentech_Model_Source_Consts::CHASE_ERROR_LOGFILE);
            if(defined('DEBUGMODE') && DEBUGMODE == True) { echo ('<BR><PRE>DEBUGMODE: Exception caught');}
            return $e->getMessage();
            //return false;
        }
    }
    */
   private function html($text)
	{
	  return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}

	function htmlout($text)
	{
	  echo html($text);
	}
}






