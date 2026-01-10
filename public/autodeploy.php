<?php
function cidr_match($ip, $ranges)
{
    $ranges = (array)$ranges;
    foreach ($ranges as $range) {
        list($subnet, $mask) = explode('/', $range);
        if ((ip2long($ip) & ~((1 << (32 - $mask)) - 1)) == ip2long($subnet)) {
            return true;
        }
    }
    return false;
}
$github_ips = array('207.97.227.253', '50.57.128.197', '108.171.174.178', '50.57.231.61');
$github_cidrs = array('204.232.175.64/27', '192.30.252.0/22');
// if(in_array($_SERVER['REMOTE_ADDR'], $github_ips) || cidr_match($_SERVER['REMOTE_ADDR'], $github_cidrs)) {
if (true) {
    $dir = $_SERVER['DOCUMENT_ROOT'];
    chdir($dir);
    exec("git pull 2>&1", $output);
    exec("git -C {$dir} pull 2>&1", $output);
    exec("php ../artisan migrate", $output);
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
} else {
    header('HTTP/1.1 404 Not Found');
    echo '404 Not Found.';
    exit;
}
