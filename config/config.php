<?php
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

return [
  "db" => [
    "host" => $_ENV['DB_HOST'],
    "name" => $_ENV['DB_NAME'],
    "user" => $_ENV['DB_USER'],
    "pass" => $_ENV['DB_PASS']
  ],
  "brevo" => [
    "api_key" => $_ENV['BREVO_API_KEY'],
    "sender_email" => $_ENV['BREVO_SENDER_EMAIL'],
    "sender_name" => $_ENV['BREVO_SENDER_NAME']
  ],
  "app" => [
    "url" => $_ENV['APP_URL'],
    "name" => $_ENV['APP_NAME']
  ]
];
