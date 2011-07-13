<?php

/**
 * define the application settings API url
 */
define("API_APPLICATION_SETTINGS_URL", "https://api.wibiya.com/Handlers/App.php");

/**
 * Handle the application settings API calls
 *
 * @author Wibiya team
 */
class Api {

    /**
     * Hold information on errors
     * @var string
     */
    var $error;

    /**
     * API message request template
     * @var string
     */
    var $requestTemplate;

    /**
     * The class' constructor, initialize the $requestTemplate var
     */
    public function  __construct() {
        $this->requestTemplate = file_get_contents("api_request_template.xml");
    }

    /**
     * Perform an API operation
     * @param   string      $token          An API token used to authenticate the operation
     * @param   sring       $applicationXml The application settings XML
     * @return  SimpleXML   SimpleXML object containig the API result message. In case of an error, false will be returned.
     */
    public function performOperation($token, $applicationXml = ""){
        $request = str_replace("_TOKEN_", $token, $this->requestTemplate);
        $request = str_replace("_APP_XML_", $applicationXml, $request);

        return $this->invokeRequest($request);
    }


    /**
     * Invoke the request against the API web service
     * @param   string      $request    The XML request message
     * @return  SimpleXML   SimpleXML object containig the API result message. In case of an error, false will be returned.
     */
    private function invokeRequest($request){
        $requestArray = array("msg"=>$request);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, API_APPLICATION_SETTINGS_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $requestArray);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


        $result = curl_exec($ch);
        if($result === false){
            $this->error = curl_error($ch);
            curl_close($ch);
            return false;
        }

        curl_close($ch);
        $messageXml = simplexml_load_string($result,'SimpleXMLElement', LIBXML_NOCDATA);
        if($messageXml === false || $messageXml === null){
            $this->error = "Unable to parse result xml";
            return false;
        }

        if(intVal($messageXml->header->result) !== 10001){
            $this->error = $messageXml->header->resultMessage;
            return false;
        }

        return $messageXml;
    }
}
?>
