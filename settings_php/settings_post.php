<?php

//_debug prints out all relevant variables for debug. Set to false for production use.
$_debug = false;
$_debugOutput = "--------------------- START DEBUG -------------------<br/>";


//This file should handle the publisher's posted data, send it to the API web service and refresh the top page
if($_SERVER['REQUEST_METHOD'] != "POST"){
    die("Wrong HTTP method");
}

if(!isset ($_POST['txtDomain']) || $_POST['txtDomain'] == ""){
    die("Invalid txtDomain param");
}

//Get information from the session
session_start();
$token = $_SESSION['API_TOKEN'];
$applicationSettingsXML = $_SESSION['API_APPLICATION_SETTINGS_XML'];
$callback = $_SESSION['API_CALLBACK'];
$operation = $_SESSION['API_OPERATION'];

$_debugOutput .= "read from SESSION['token']: " . $token . "<br/>";
$_debugOutput .= "read from SESSION['API_APPLICATION_SETTINGS_XML']: <textarea style=\"width:800px;height:150px\">" . $applicationSettingsXML . "</textarea><br/><br/>";
$_debugOutput .= "read from SESSION['API_CALLBACK']: " . $callback . "<br/>";
$_debugOutput .= "read from SESSION['API_OPERATION']: " . $operation . "<br/>";
switch ($operation){
    case "add":
        $applicationSettingsXML = str_replace("_MY_DOMAIN_NAME_", $_POST['txtDomain'], $applicationSettingsXML);
        break;
    case "edit":
        $xmlObject = simplexml_load_string($applicationSettingsXML,'SimpleXMLElement', LIBXML_NOCDATA);
        $xmlObject->settings->replacement->entry->value = $_POST['txtDomain'];
        $applicationSettingsXML = $xmlObject->asXML();
        $applicationSettingsXML = str_replace('<?xml version="1.0"?>','',$applicationSettingsXML);
        break;
    default:
        die("Invalid object in session");
        break;
}

include 'api.class.php';
$api = new Api();
$_debugOutput .= "Calling API with XML: <textarea style=\"width:800px;height:150px\">" . $applicationSettingsXML . "</textarea><br/><br/>";
$_debugOutput .= "--------------------- END DEBUG -------------------<br/>";

if ($_debug) { echo "<div>" . $_debugOutput . "</div>";}

$result = $api->performOperation($token, $applicationSettingsXML);
if($result === false){
    die("Something went wrong. please contact us with the following message: $api->error");
}
?>
<script type="text/javascript">
    window.top.location.href = '<?php echo $callback."&token=".$token; ?>';
</script>