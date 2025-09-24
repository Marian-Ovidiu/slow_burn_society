<?php
if (!function_exists('phpmailer_init_sbs')) {
    function phpmailer_init_sbs($phpmailer)
    {
        // 1) Leggi prima da define(), poi da .env, poi default
        $host   = defined('SMTP_HOST') ? SMTP_HOST : my_env('SMTP_HOST', 'smtp.sendgrid.net');
        $port   = defined('SMTP_PORT') ? (int)SMTP_PORT : (int)my_env('SMTP_PORT', 587);
        $secure = defined('SMTP_SECURE') ? SMTP_SECURE : my_env('SMTP_SECURE', 'tls');
        $user   = defined('SMTP_USER') ? SMTP_USER : my_env('SMTP_USER', '');
        $pass   = defined('SMTP_PASS') ? SMTP_PASS : my_env('SMTP_PASS', '');
        $from   = defined('SMTP_FROM') ? SMTP_FROM : my_env('SMTP_FROM', $user);
        $name   = defined('SMTP_NAME') ? SMTP_NAME : my_env('SMTP_NAME', 'WordPress');

        // 2) Config PHPMailer
        $phpmailer->isSMTP();
        $phpmailer->Host       = $host;
        $phpmailer->Port       = $port;
        $phpmailer->SMTPSecure = $secure ?: null; // evita 'tls' vuoto
        $phpmailer->SMTPAuth   = true;
        $phpmailer->Username   = $user;
        $phpmailer->Password   = $pass;

        // setFrom deve essere un'email valida (NON 'apikey')
        if (filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $phpmailer->setFrom($from, $name);
        }

    }
}
