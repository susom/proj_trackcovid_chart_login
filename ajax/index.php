<?php

namespace Stanford\ChartLogin\ChartLogin;

/** @var ChartLogin $module */

try {
    $dob = filter_var($_POST['dob'], FILTER_SANITIZE_STRING);
    $recordId = filter_var($_POST['record_id'], FILTER_SANITIZE_STRING);
    if (!$link = $module->verifyUser($dob, $recordId)) {
        throw new \LogicException("No user was found for provided information");
    } else {
        echo json_encode(array('status' => 'success', 'link' => $link));
    }
} catch (\Exception  $e) {
    $module->emError($e->getMessage());
    http_response_code(404);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
} catch (\LogicException  $e) {
    $module->emError($e->getMessage());
    http_response_code(404);
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}