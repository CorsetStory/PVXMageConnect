<?php
define("DEBUGMODE", false);
define("TESTMODE", True);


if (defined('TESTMODE') && TESTMODE == True)
{
// Test/QA PVX System
	define("CLIENT_ID", "corsets2661Qa");
	define("USER_NAME", "ReadOnly");
	define("PASSWORD", "r0enaldy14");
	define("URL", "http://qa1.peoplevox.net/corsets2661Qa/resources/integrationservicev4.asmx?wsdl");
}
else
{
// Live PVX system
//	define("CLIENT_ID", "corsetsuk2600");
//	define("USER_NAME", "ReadOnly");
//	define("PASSWORD", "r0enaldy14");
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

	
	public function subscribeEvent($eventType, $callBackURL)
	{
			$request = "<soap:Envelope xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" 
										xmlns:xsd=\"http://www.w3.org/2001/XMLSchema\" 
										xmlns:soap=\"http://schemas.xmlsoap.org/soap/envelope/\">
							<soap:Header>
								<UserSessionCredentials xmlns=\"http://www.peoplevox.net/\">
									<ClientId>".$this->clientID."</ClientId>
									<SessionId>".$this->sessionID."</SessionId>
								</UserSessionCredentials>
							</soap:Header>
 							<soap:Body>
								<SubscribeEvent xmlns=\"http://www.peoplevox.net/\">
									<eventType>".$eventType."</eventType>
									<filter></filter>
									<callbackUrl>".$callBackURL."</callbackUrl>
								</SubscribeEvent>
							</soap:Body>
						</soap:Envelope>";
			//$response = $request;
			$response = $this->postPVXRequest($this->url, $request);
			return $response;
	}
	
	public function resetSubscribe()
	{
	
	
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
			$this->errorOccurred = false;
			// set headers to contain session ID and client ID
			$UserSessionCredentials	= array('ClientId' => $this->clientID, 'SessionId' => $this->sessionID, 'UserId' => null);
			$header = new SoapHeader(self::PVX_NS, 'UserSessionCredentials', $UserSessionCredentials);
			$this->client->__setSoapHeaders($header);
			
			// create the SOAP request body
			$getRequest = array('TemplateName' => $templateName, 'PageNo' => $pageNo, 'ItemsPerPage' => self::ITEMS_PER_PAGE, 'SearchClause' => $searchClause);
			$getRequestObj = array('getRequest' => $getRequest);
			
			if ($this->debugmode) {echo('<PRE>DEBUGMODE: GET DATA PARAMS: '.print_r($getRequestObj, true)."</PRE>");}
		
			// make the SOAP call to PVX GetData
			$response = $this->client->GetData($getRequestObj);
			
			if ($this->debugmode) 
			{
				echo('<PRE>DEBUGMODE: GetData XML Exchange:</PRE>');
				echo "<PRE>REQUEST:\n" . htmlentities(str_ireplace('><', ">\n<", $this->client->__getLastRequest())) . "\n</PRE>";
				echo "<PRE>RESPONSE:\n" . htmlentities(str_ireplace('><', ">\n<", $this->client->__getLastResponse())) . "\n</PRE>";
			}
			
			if (!is_soap_fault($response) && $response->GetDataResult->ResponseId == 0)
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
				return(null);
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
				$this->client = new SoapClient($url, array("trace" => 1, "exception" => 0));
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
	
	private function postPVXRequest($url,$request){
        try{
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 4);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: close'));
            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
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
            return $e->getMessage();
            //return false;
        }
    }
}






