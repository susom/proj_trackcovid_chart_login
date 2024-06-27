<?php

namespace Stanford\ChartLogin\ChartLogin;

/** @var ChartLogin $module */

try {
    $dob = htmlspecialchars($_POST['verification_field']);
    $recordId = htmlspecialchars($_POST['record_id']);
    if (!$link = $module->verifyUser($dob, $recordId)) {
        throw new \LogicException($module->getProjectSetting('failed-login-error-message') ?: "No user was found for provided information");
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
