<?php
ignore_user_abort(true);
ob_start();
header('Connection: close');
header('Content-Encoding: none');
header('Content-Length: '.ob_get_length());
http_response_code(200);
ob_end_flush();
flush();
if (strpos($_SERVER['SERVER_SOFTWARE'], 'FPM')) {
    fastcgi_finish_request();
}
set_time_limit(0);
if (session_id()) {
    session_write_close();
}

// your code here ...
