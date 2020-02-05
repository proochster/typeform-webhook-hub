<?php

//Make sure that this is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    //If it isn't, send back a 405 Method Not Allowed header.
    header($_SERVER["SERVER_PROTOCOL"]." 405 Method Not Allowed", true, 405);
    exit;
}

$ref = $_SERVER["HTTP_REFERER"];
$type = $_SERVER["CONTENT_TYPE"];

$logref  = "Date: ".date("F j, Y, g:i a").PHP_EOL.
"Ref: ".$ref.PHP_EOL.
"Content type: ".$type.PHP_EOL.
"-------------------------".PHP_EOL;

//Save string to log, use FILE_APPEND to append.
file_put_contents('./errors/log_'.date("j.n.Y").'.log', $logref, FILE_APPEND);

/**
 * Capture incoming data
 * 
 */

    //Get the raw POST data from PHP's input stream.
    //This raw data should contain XML.
    $postData = file_get_contents('php://input');

    if (substr($postData, 0, 5) == "<?xml") {

        $flgData = $postData;

        $log  = "Date: ".date("F j, Y, g:i a").PHP_EOL.
        "FLG response: ".$flgData.PHP_EOL.
        "-------------------------".PHP_EOL;

        //Save string to log, use FILE_APPEND to append.
        file_put_contents('./flg_response/log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

    } else if ($json = json_decode(($postData), true)) {

        $webhookData = $json;

    } else {

        print('no data');
        return;

    }

    $submitted_at = htmlentities($webhookData['form_response']['submitted_at']);
    $utm_medium = htmlentities($webhookData['form_response']['hidden']['utm_medium']);
    $utm_source = htmlentities($dawebhookDatata['form_response']['hidden']['utm_source']);
    $utm_term = htmlentities($webhookData['form_response']['hidden']['utm_term']);
    $first_name = htmlentities($webhookData['form_response']['answers'][0]['text']);
    $last_name = htmlentities($webhookData['form_response']['answers'][1]['text']);
    $investment_objective = htmlentities($webhookData['form_response']['answers'][2]['choice']['label']);
    $term = htmlentities($webhookData['form_response']['answers'][3]['choice']['label']);
    $email = htmlentities($webhookData['form_response']['answers'][4]['email']);
    $phone_number = htmlentities($webhookData['form_response']['answers'][5]['phone_number']);


/**
 * Output in a logfile
 * /public_html/isa-form/webhook/
 */
    $log  = "Log time: ".date("F j, Y, g:i a").PHP_EOL.
            "Submitted at: ".$submitted_at.PHP_EOL.
            "Utm medium: ".$utm_medium.PHP_EOL.
            "Utm source: ".$utm_source.PHP_EOL.
            "Utm term: ".$utm_term.PHP_EOL.
            "First name: ".$first_name.PHP_EOL.
            "Last name: ".$last_name.PHP_EOL.
            "Investment objective: ".$investment_objective.PHP_EOL.
            "Term: ".$term.PHP_EOL.
            "Email: ".$email.PHP_EOL.
            "Phone number: ".$phone_number.PHP_EOL.        
            "-------------------------".PHP_EOL;
    //Save string to log, use FILE_APPEND to append.
    file_put_contents('./logs/log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

/**
 * Push Webhook to FLG CRM
 */
    
    // Update these values
    // $url = 'https://clickspay.flg360.co.uk/api/APILeadCreateUpdate.php';
    // $key = "YOURKEY";
    // $leadgroup = 012345;
    // $site = 01234;


    //The XML string that you want to send.
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
    <data>
        <lead>
        <key>'.$key.'</key>
        <leadgroup>'.$leadgroup.'</leadgroup>
        <site>'.$site.'</site>
        <introducer></introducer>
        <type></type>
        <user></user>
        <status></status>
        <reference></reference>
        <source>'.$utm_source.'</source>
        <medium>'.$utm_medium.'</medium>
        <term>'.$utm_term.'</term>
        <cost></cost>
        <value></value>
        <title></title>
        <firstname>'.$first_name.'</firstname>
        <lastname>'.$last_name.'</lastname>
        <company></company>
        <jobtitle></jobtitle>
        <phone1>'.$phone_number.'</phone1>
        <phone2></phone2>
        <fax></fax>
        <email>'.$email.'</email>
        <address></address>
        <address2></address2>
        <address3></address3>
        <towncity></towncity>
        <postcode></postcode>
        <dobday></dobday>
        <dobmonth></dobmonth>
        <dobyear></dobyear>
        <contactphone>Unknown</contactphone>
        <contactsms>Unknown</contactsms>
        <contactemail>Unknown</contactemail>
        <contactmail>Unknown</contactmail>
        <contactfax>Unknown</contactfax>
        <contacttime></contacttime>
        <data1>Yes</data1>
        <data2>Value 1</data2>
        <data3>Value 2</data3>
        <data4>Value 3</data4>
        <data5>Value 4</data5>
        <data6></data6>
        <data7></data7>
        <data8></data8>
        <data9></data9>
        <data10></data10>
        <data11></data11>
        <data12></data12>
        <data13></data13>
        <data14></data14>
        <data15></data15>
        <data16>'.$investment_objective.'</data16>
        <data17>'.$term.'</data17>
        <data18>Action</data18>
    </lead>
</data>';
    
   
    //Initiate cURL
    $curl = curl_init($url);
    
    //Set the Content-Type to text/xml.
    curl_setopt ($curl, CURLOPT_HTTPHEADER, array("Content-Type: text/xml;charset=UTF-8"));
    
    //Set CURLOPT_POST to true to send a POST request.
    curl_setopt($curl, CURLOPT_POST, true);
    
    //Attach the XML string to the body of our request.
    curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
    
    //Tell cURL that we want the response to be returned as
    //a string instead of being dumped to the output.
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    

    $result = curl_exec($curl);

    //Do some basic error checking.
    if(curl_errno($curl)){
        throw new Exception(curl_error($curl));
    }

    // try {
    //     //Execute the POST request and send our XML.
    //     $result = curl_exec($curl);
    //     }
        
    //     //catch exception
    //     catch(Exception $e) {

    //     $log  = "Date: ".date("F j, Y, g:i a").PHP_EOL.
    //     "Error: ".$e.PHP_EOL.
    //     "-------------------------".PHP_EOL;

    //     //Save string to log, use FILE_APPEND to append.
    //     file_put_contents('./errors/log_'.date("j.n.Y").'.log', $log, FILE_APPEND);
    //     }
    
    //Close the cURL handle.
    curl_close($curl);
    
    //Print out the response output.
    // echo $result;

