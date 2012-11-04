<?php
class CurlWrapper{	
	public static function performPostRequest($url,$requestData){
		return CurlWrapper::performRequest('POST',$url,$requestData);
	}
	
	public static function performPutRequest($url,$requestData){
		return CurlWrapper::performRequest('Put',$url,$requestData);
	}
	
	public static function performDeleteRequest($url,$requestData){
		return CurlWrapper::performRequest('DELETE',$url,$requestData);
	}
				
	public static function performGetRequest($url){
		return CurlWrapper::performRequest('GET',$url,NULL);
	}
	
	private static function performRequest($verb,$url,$requestData){
		$strCookie = session_name() . '=' . $_COOKIE[ session_name() ] . '; path=/';
		session_write_close();			
		$httpHeader = array ("Accept: application/json");
		$options = array(
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_COOKIE => session_name() . '=' . session_id(),
	        CURLOPT_CAINFO => dirname(__FILE__).'/ca.pem',				
			CURLOPT_SSL_VERIFYPEER => TRUE,
			CURLOPT_SSL_VERIFYHOST => 2,
	        CURLOPT_CUSTOMREQUEST => $verb,
	    );		
		
		$ch  = curl_init( CurlWrapper::getServiceRootUrl($url)."/".$url );
    	curl_setopt_array( $ch, $options );
		
		if(isset($requestData)){
			array_push($httpHeader,"Content-Type: application/json");
			curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($requestData));
		}								

		curl_setopt($ch,CURLOPT_HTTPHEADER,$httpHeader);
		
		$responseData = curl_exec($ch);		
		$err     = curl_errno( $ch );
	    $errMsg  = curl_error( $ch );
	    $header  = curl_getinfo( $ch );
		curl_close($ch);
		
		//DEBUGGING
		//return "<br/>cURL response data:".print_r($responseData,true)."<br/>cURL header:" .print_r($header,true)."<br/>cURL error number:" .print_r($err,true)."<br/>cURL error message:" .print_r($errMsg,true);						
		
		return CurlWrapper::handleRequestResponse($responseData, $err, $errMsg, $header);
	}
	
	public static function performBasicAuthPostRequest($url,$userPwd,$requestData){
		return CurlWrapper::performBasicAuthRequest('POST',$url,$userPwd,$requestData);
	}
	
	public static function performBasicAuthPutRequest($url,$userPwd,$requestData){
		return CurlWrapper::performBasicAuthRequest('Put',$url,$userPwd,$requestData);
	}
	
	public static function performBasicAuthDeleteRequest($url,$userPwd,$requestData){
		return CurlWrapper::performBasicAuthRequest('DELETE',$url,$userPwd,$requestData);
	}
				
	public static function performBasicAuthGetRequest($url,$userPwd){
		return CurlWrapper::performBasicAuthRequest('GET',$url,$userPwd,NULL);
	}
	
	private static function performBasicAuthRequest($verb,$url,$userPwd,$requestData){		
		session_write_close();			
		$httpHeader = array ("Accept: application/json");
		$options = array(
	        CURLOPT_RETURNTRANSFER => true,
	        CURLOPT_HTTPAUTH => CURLAUTH_BASIC,
			CURLOPT_USERPWD => $userPwd,
	        CURLOPT_CAINFO => dirname(__FILE__).'/ca.pem',				
			CURLOPT_SSL_VERIFYPEER => TRUE,
			CURLOPT_SSL_VERIFYHOST => 2,	      
	        CURLOPT_CUSTOMREQUEST => $verb,
	    );		
		
		$ch  = curl_init( CurlWrapper::getServiceRootUrl($url)."/".$url );
    	curl_setopt_array( $ch, $options );
		
		if(isset($requestData)){
			array_push($httpHeader,"Content-Type: application/json");
			curl_setopt($ch,CURLOPT_POSTFIELDS,json_encode($requestData));
		}								

		curl_setopt($ch,CURLOPT_HTTPHEADER,$httpHeader);
		
		$responseData = curl_exec($ch);		
		$err     = curl_errno( $ch );
	    $errMsg  = curl_error( $ch );
	    $header  = curl_getinfo( $ch );
		curl_close($ch);
		
		//DEBUGGING
		//return "<br/>cURL response data:".print_r($responseData,true)."<br/>cURL header:" .print_r($header,true)."<br/>cURL error number:" .print_r($err,true)."<br/>cURL error message:" .print_r($errMsg,true);						

		return CurlWrapper::handleRequestResponse($responseData, $err, $errMsg, $header);
	}
	
	private static function handleRequestResponse($responseData, $err, $errMsg, $header){
		//Grab the returning JSON
		if($header['http_code'] == '200'){			
			if($header['content_type'] != 'application/json'){
				//TODO
				$unknownError = array(	
					'errorCode' => 'unknown',
					'errorMsg' => $responseData
				);				
				return $unknownError;
			}
			else{				
				return json_decode($responseData,true);
			}
		}		
		else{
			if($header['http_code'] == "403"){			
				header('Location: /content/notification/error/invalid_session.php');
			}
			else {
				$httpError = array('httpErrorCode' => $header['http_code']);			
				return $httpError;
			}		
		}
	}
	
	public static function getServiceRootUrl($url){
		//TODO need to use the service registry in the future
		$absoluteUrl = GUI_PROTOCOL.'://'.HTTP_FQDN;
		return $absoluteUrl;
	}
}
?>