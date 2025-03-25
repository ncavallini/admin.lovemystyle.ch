<?php
require_once __DIR__ . "/classes/Utils.php";
require_once __DIR__ . "/classes/Logging.php";
    function global_exception_handler(Exception|Error $e) {
        Logging::get_logger()->handleException($e);
        die;
    }

    set_exception_handler('global_exception_handler');
?>