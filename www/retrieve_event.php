<?php
include(getcwd() . '/../app/init.php');

/**
 * This endpoint returns the detail of an event with the ID passed in with the GET parameter id
 *  JSON:
 *  {
 *
 *  }
 *
 * If there is a problem the error code will be 400 with a json response of the form:
 *  {
 *      "error": {
 *          "message": "Error message"
 *      }
 *  }
 */

$response = array();

if (isset($_GET['id'])) {
    $event_id=$_GET['id'];
    try {
        // get event by id
        $event = new Event($event_id);
        $response = $event->toDetailArray();
    } catch (fExpectedException $e) {
        // Assuming event not found
        $response['error'] = array(
            'message' => $e->printMessage()
        );
    }
}
else {
    $response['error'] = array(
        'message' => "Request incomplete, please pass an id in the url"
    );
}


fJSON::output($response);
