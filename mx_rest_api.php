<?php
// REST eworx Marketing Suite API helper

namespace eworxMarketingSuite;

class EmsServiceAgent {
    //////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////
    private $log = false;
	
    private $url = 'https://mailworx.marketingsuite.info/Services/JSON/ServiceAgent.svc';
	private $securityContext = array(
        'Account' => '', 
        'Username' => '',
        'Password' => '', 
        'Source'  => ''
    );
	private $language = 'EN';
        
    //////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////
        
    // Description: Sets the default values for the helper
    function __construct($log = false) {
        $this->log = $log;
    }
	        
    // Description: Sets the credentials for your requests.
    // Parameter account: Account name (Mandant) of the eMS to login.
    // Parameter username: User name to use the login.
    // Parameter password: The user's password.
    // Parameter source: The name of the registered application.
    public function useCredentials($account = '', $username = '', $password = '', $source = '') {
        if ($account != '') {
            $this->securityContext['Account'] = $account;
        }
        if ($username != '') {
            $this->securityContext['Username'] = $username;
        }
        if ($password != '') {
            $this->securityContext['Password'] = $password;
        }
        if ($source != '') {
            $this->securityContext['Source'] = $source;
        }
    }
	        
    // Description: Sets the language.
    public function useLanguage($language ) {
        if ($language != '') {
            $this->language = $language;
        }
    }
	        
    // Description: Sets the service url.
    public function useServiceUrl($url) {
        if ($url != '') {
            $this->url = $url;
        }
    }
	
    // Description: Creates a request with the given method and sets the credentials and the language, as these always need to be sent.
    // Parameter method: The API method you want to call.
    // Returnes the created request.
	public function createRequest($method){
		$request = new JSON($this->log);
		$request->setUrl($this->url);
		$request->setCredentialsByObject($this->securityContext);
		$request->setLanguage($this->language);
		$request->setMethod($method);
		
		return $request;
	}
}

	
class JSON {
    //////////////////////////////////////////////////////
    // PROPERTIES
    //////////////////////////////////////////////////////
    private $request = array();
    private $url;
    private $method;
    private $log = false;
        
    //////////////////////////////////////////////////////
    // METHODS
    //////////////////////////////////////////////////////
        
    // Description: Sets the default values for the helper.
    function __construct($log = false) {
        $this->log = $log;
        // change the credentials for your API or use the method setCredentials
        $this->reset();
    }
                
    // Description: Sets the credentials by object.
    public function setCredentialsByObject($securityContext) {
        if (!is_null($securityContext)) {
            $this->setCredentials($securityContext['Account'], $securityContext['Username'], $securityContext['Password'], $securityContext['Source']);
        }
    }

    // Description: Sets the language of the connection
    public function setLanguage($language) {
        if (!isset($language)) {
            return false;
        }
        $this->request['Language'] = strtoupper(trim($language));
        return true;
    }
	
    // Description: Sets the method for the next request
    public function setMethod($method) {
        if (!isset($method)) {
            return false;
        }
        // remove useless whitespaces
        $this->method = trim($method);
        return true;
    }
	
    // Description: Sets the url to the service
    public function setUrl($url) {
        if (!isset($url)) {
            return false;
        }
        // remove useless whitespaces
        $this->url = trim($url);
        return true;
    }
	
    // Description: Sets the property of a request.
    // Parameter name: The name of the property.
    // Parameter data: The data of the property.
    public function setProperty($name, $data) {
        $this->request[$name] = $data;
    }
    
    // Description: Gets the data from a request.
    // Returns the received data.
    public function getData() {
        return json_decode($this->getJSON());
    }
	
    // Description: Gets the timestamp
    public function getTime($time = '', $gmt = '+0200') {
        // set timezone
        if (!isset($gmt)) {
            $gmt = '+0100';
        }
        if (!is_numeric($time)) {
            $time = time() - 86400; // last day
        }
        $time_format = '/Date(' . date('U', $time)*1000 . $gmt . ')/';
        return $time_format;
    }

    // Description: Converts a timestamp to a date format.
    // Returns the formatted date.
    public function getJSONTime($dateObject) {
        preg_match('/\/Date\((\d+)([+-]\d{4})\)\//', $dateObject, $matches);

        if (count($matches) !== 3) {
            return false; 
        }

        $timestamp = $matches[1];
        $timezoneOffset = $matches[2];

        $timestampSeconds = $timestamp / 1000;

        $dateTime = new \DateTime("@$timestampSeconds");
        $dateTime->setTimezone(new \DateTimeZone($timezoneOffset));

        $formattedDate = $dateTime->format('d.m.Y H:i:s');

        return $formattedDate;
    }
        	
    // Description: Sets the credentials of the connection
    private function setCredentials($account = '', $username = '', $password = '', $source = '') {
        if ($account != '') {
            $this->request['SecurityContext']['Account'] = $account;
        }
        if ($username != '') {
            $this->request['SecurityContext']['Username'] = $username;
        }
        if ($password != '') {
            $this->request['SecurityContext']['Password'] = $password;
        }
        if ($source != '') {
            $this->request['SecurityContext']['Source'] = $source;
        }
    }
	
    // Description: Check for all needed infos of the request
    // Returns true if method is not '' and the credentials are set.
    private function checkRequestData() {
        // method is needed
        if ($this->method == '') {
            return false;
        }
        // credentials are needed
        if ($this->request['SecurityContext']['Account'] == '' ||
            $this->request['SecurityContext']['Username'] == '' ||
            $this->request['SecurityContext']['Password'] == '' ||
            $this->request['SecurityContext']['Source'] == '') {
            return false;
        }
        return true;
    }
	
    // Description: Gets the url;
    private function getURL() {
        return $this->url . "/" . $this->method;
    }
    
    // Description: Gets the request in JSON format.
    private function getRequestJSON() {
        if ($this->checkRequestData()) {
            return json_encode(array(
                'request' => $this->request
            ));
        }
        return "the request data is not completely configured";
    }
    
    // Description: Calls the API 
    private function getJSON() {
        $start = microtime(true);
        if ($this->checkRequestData()) {
            $json = json_encode(array(
                'request' => $this->request
            ));
                
            $json = str_replace("&lt;", "<", $json);
            $json = str_replace("&gt;", ">", $json);
                
            // set request data
            $ch = curl_init($this->getURL());
                                                                                     
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json))
            );
                                                                                                                                     
            // execute the request
            $result = curl_exec($ch);
                
            if (curl_errno($ch)) {
                $this->log(curl_error($ch));
            }
                
            $this->log($this->getURL());
            $this->log($this->request);
            $this->log(json_encode($this->request));
            $this->log(json_decode($result));
            // reset all settings of the last request
            $this->reset();
            $this->log('Execution time ' . round(microtime(true) - $start, 2) . 's');
            // return result of the last request
            return $result;
        }
        $this->log("the request data is not completely configured");
        return '';
    }
	
    // Description: Resets the request, the url and the method.
    private function reset() {
        $this->request = array();
        $this->url = 'https://mailworx.marketingsuite.info/Services/JSON/ServiceAgent.svc';
        $this->method = '';
    }
   
   // for logging values of the helper
    public function log($var) {
        if ($this->log === true) {
            echo "<pre>";
            print_r($var);
            echo "</pre>";
        }
    }
}
