<?php
/*
 * A way to test this endpoint is to use curl
 * Create a file test_data.json with the json you want to submit
 * curl -H 'Content-Type: application/json' -X POST --data-binary "@test.json" http://localhost:8080/shift-flourish/www/manage_event.php
 */


include(getcwd() . '/../app/init.php');

function build_json_response($input) {
    $data = json_decode($input, true);
    if (!$data) {
        return array(
            'error' => array(
                'message' => "JSON could not be decoded"
            )
        );
    }

    $_POST = $data; // fValidation inspects $_POST for field data
    $validator = new fValidation();
    $validator->addRequiredFields('title', 'address', 'comic'); //TODO: add 'start_date', 'start_time'
    $validator->addEmailFields('email');
    $validator->addValidValuesRule('comic', array(true));
    $validator->addRegexReplacement('#^(.*?): (.*)$#', '\2 for \1');
    
    $messages = $validator->validate(TRUE, TRUE);
    if ($messages) {
        return array(
            'error' => array(
                'message' => 'There were errors in your fields',
                'fields' => $messages
            )
        );
    }

    return $data;
}

$response = build_json_response(file_get_contents('php://input'));
if (array_key_exists('error', $response))
    http_response_code(400);
header('Content-Type: application/json');
header('Accept: application/json');
echo json_encode($response);
