<?php
/*
 * A way to test this endpoint is to use curl
 * Create a file test_data.json with the json you want to submit
 * curl -H 'Content-Type: application/json' -X POST --data-binary "@test.json" http://localhost:8080/shift-flourish/www/manage_event.php
 */

/**
 * This endpoint updates events, expecting a POST with json of the form:
 *  JSON:
 *  {
 *      TODO: Document from Event::fromArray code
 *  }
 *
 *  If there is a problem the error code will be 400 with a json response of the form:
 *  {
 *      "error": {
 *          "message": "Error message"
 *          "fields": {
 *              "field1": "Error for field 1",
 *              ...
 *          }
 *      }
 *  }
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

    $validator->addRequiredFields('title', 'venue', 'address', 'organizer', 'email', 'read_comic');
    $validator->addEmailFields('email');
    $validator->addRegexReplacement('#^(.*?): (.*)$#', '\2 for \1');
    // If id is specified require secret
    $validator->addConditionalRule(
        array('id'),
        NULL,
        array('secret')
    );

    $messages = $validator->validate(TRUE, TRUE);
    if (!$data['read_comic']) {
        $messages['read_comic'] = 'You must have read the Ride Leading Comic';
    }
    if ($messages) {
        return array(
            'error' => array(
                'message' => 'There were errors in your fields',
                'fields' => $messages
            )
        );
    }

    // Converts data to an event, loading the existing one if id is included in data
    $event = Event::fromArray($data);

    // Else
    if ($event->exists() && !$event->secretValid($data['secret'])) {
        return array(
            'error' => array(
                'message' => 'Invalid secret, use link from email'
            )
        );
    }

    $messages = $event->validate($return_messages=TRUE, $remove_column_names=TRUE);

    $inputDateStrings = get($data['dates'], array());
    $validDates = array();
    $invalidDates = array();
    foreach ($inputDateStrings as $dateString) {
        $date =  DateTime::createFromFormat('Y-m-d', $dateString);
        if ($date) {
            $validDates []= $date;
        }
        else {
            $invalidDates []= $dateString;
        }
    }

    if ($invalidDates) {
        $messages['dates'] = "Invalid dates: " . implode(', ', $invalidDates);
    }

    if ($messages) {
        return array(
            'error' => array(
                'message' => 'There were errors in your fields',
                'fields' => $messages
            )
        );
    }

    // if needs secret generate and email
    if (!$event->exists()) {
        $includeSecret = true;
    }
    else {
        $includeSecret = false;
    }

    // If there are validation errors this starts spewing html, so we validate before
    $event->store();

    // Create/delete EventTimes to match the list of dates included
    EventTime::matchEventTimesToDates($event, $validDates);

    // Returns the created object
    $details = $event->toDetailArray();
    if ($includeSecret) {
        $details['secret'] = $event->getPassword();
        // Wait until after it is stored to ensure it has an id
        $event->emailSecret();
    }
    return $details;
}

ob_start();
$response = build_json_response(file_get_contents('php://input'));
$contents = ob_get_contents();
ob_end_clean();
if ($contents) {
    $response['contents'] = $contents;
}
if (array_key_exists('error', $response))
    http_response_code(400);
header('Content-Type: application/json');
header('Accept: application/json');
echo json_encode($response);
