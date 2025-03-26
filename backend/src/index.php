<?php

define('BASE_DIR', __DIR__ . '/');

require_once BASE_DIR . 'config.php';


try {

} catch(Exception $e) {
    if ($IS_DEV) {
        throw $e;
    }
    http_response_code(500);
}

?>
