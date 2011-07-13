<?php

//_debug prints out all relevant variables for debug. Set to false for production use.
$_debug = false;
$_debugOutput = "--------------------- START DEBUG -------------------<br/>";

//This page should be called with the following query string params: token, operation and callback
if(!isset ($_GET['token']) || !isset ($_GET['operation']) || !isset ($_GET['callback'])) {
    $validation = false;
}
else {
    $validation = true;
    session_start();
    // Save the callback in the session for later usage
    $_SESSION['API_CALLBACK'] = $_GET['callback'];
	$_debugOutput .= "Storing in SESSION['API_CALLBACK']: " . $_SESSION['API_CALLBACK'] . "<br/>";
}

$domainValue = "";
if($validation) {
	$_debugOutput .= "Operation: " . $_GET['operation'] . "<br/>";
    switch($_GET['operation']) {
        case "add":
            // Save the application seetings xml, token and operation in the session for later usage
            $_SESSION['API_APPLICATION_SETTINGS_XML'] = file_get_contents("settings.xml");
            $_SESSION['API_TOKEN'] = $_GET['token'];
            $_SESSION['API_OPERATION'] = "add";
			$_debugOutput .= "Storing in SESSION['API_TOKEN']: " . $_SESSION['API_TOKEN'] . "<br/>";
			$_debugOutput .= "Storing in SESSION['API_OPERATION']: add<br/>";
			$_debugOutput .= "Storing in SESSION['API_APPLICATION_SETTINGS_XML']: <textarea style=\"width:800px;height:150px\">" . $_SESSION['API_APPLICATION_SETTINGS_XML'] . "</textarea><br/>";
			break;
        case "edit":
            include 'api.class.php';
            $api = new Api();
            $result = $api->performOperation($_GET['token']);
            if($result === false) {
                $validation = false;
            }
            else {
                $url = (string)$result->message->settings->app->settings->replacement->entry->value;
                // Save the application settings xml, token and operation in the session for usage by settings_post.php
                $_SESSION['API_TOKEN'] = (string)$result->message->token;
                $_SESSION['API_APPLICATION_SETTINGS_XML'] = $result->message->settings->app->asXML();
                $_SESSION['API_OPERATION'] = "edit";
				$_debugOutput .= "Storing in SESSION['API_TOKEN']: " . $_SESSION['API_TOKEN'] . "<br/>";
				$_debugOutput .= "Storing in SESSION['API_OPERATION']: edit<br/>";
				$_debugOutput .= "Storing in SESSION['API_APPLICATION_SETTINGS_XML']: <textarea style=\"width:800px;height:150px\">" . $_SESSION['API_APPLICATION_SETTINGS_XML'] . "</textarea><br/>";
            }
            break;
        default:
            $validation = false;
            break;
    }
}
$_debugOutput .= "--------------------- END DEBUG -------------------<br/>";
if ($_debug) { echo "<div>$_debugOutput</div>"; }

if ($validation === false) {
    die("something went wrong");
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <title>My Application - Settings page</title>
    </head>
    <body>
        <form name="settingsForm" id="settingsForm" method="post" action="settings_post.php">
            <div>
                <span>Enter your domain in google apps: </span>
                <input type="text" value="<?php echo $url; ?>" name="txtDomain" id="txtDomain" />
            </div>
            <div>
                <input type="submit" value="Save" id="btnSave" name="btnSave" />
            </div>
        </form>
    </body>
</html>