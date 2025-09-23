<?php

if (!function_exists('phpmailer_init')) {
    function phpmailer_init($phpmailer)
    {

    $host   = my_env('SMTP_HOST') ?: 'smtp.gmail.com';
    $port   = (int) (my_env('SMTP_PORT') ?: 587);
    $secure = my_env('SMTP_SECURE') ?: 'tls';
    $user   = my_env('SMTP_USER') ?: '';
    $pass   = my_env('SMTP_PASS') ?: '';
    $from   = my_env('SMTP_FROM') ?: $user;
    $name   = my_env('SMTP_NAME') ?: 'WordPress';

    $phpmailer->isSMTP();
    $phpmailer->Host       = $host;
    $phpmailer->Port       = $port;
    $phpmailer->SMTPSecure = $secure;
    $phpmailer->SMTPAuth   = true;
    $phpmailer->Username   = $user;
    $phpmailer->Password   = $pass;
    $phpmailer->setFrom($from, $name);
    }
}
