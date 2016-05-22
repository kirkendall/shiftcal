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
        $include_hidden = isset($_GET['secret']) && $event->secretValid($_GET['secret']);
        $response = $event->toDetailArray($include_hidden);
    } catch (fExpectedException $e) {
        $response['error'] = array(
            'message' => $e->getMessage()
        );
    }
}
else {
    $response['error'] = array(
        'message' => "Request incomplete, please pass an id in the url"
    );
}


fJSON::output($response);
