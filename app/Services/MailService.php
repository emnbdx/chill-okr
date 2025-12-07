<?php

namespace App\Services;

class MailService
{
  private string $apiKey;
  private string $senderEmail;
  private string $senderName;

  public function __construct(array $config)
  {
    $this->apiKey = $config['brevo']['api_key'];
    $this->senderEmail = $config['brevo']['sender_email'];
    $this->senderName = $config['brevo']['sender_name'];
  }

  public function sendResetPasswordEmail(string $toEmail, string $toName, string $resetUrl): bool
  {
    $subject = "Reset Your Password";
    $htmlContent = "
      <h1>Password Reset</h1>
      <p>Hello {$toName},</p>
      <p>You have requested to reset your password.</p>
      <p>Click the link below to set a new password:</p>
      <p><a href=\"{$resetUrl}\">{$resetUrl}</a></p>
      <p>This link will expire in 1 hour.</p>
      <p>If you did not request this reset, please ignore this email.</p>
    ";

    return $this->send($toEmail, $toName, $subject, $htmlContent);
  }

  public function sendWelcomeEmail(string $toEmail, string $toName): bool
  {
    $subject = "Welcome to Chill OKR";
    $htmlContent = "
      <h1>Welcome to Chill OKR!</h1>
      <p>Hello {$toName},</p>
      <p>Your account has been created successfully.</p>
      <p>You can now log in and start managing your OKRs.</p>
    ";

    return $this->send($toEmail, $toName, $subject, $htmlContent);
  }

  public function sendInvitationEmail(string $toEmail, string $toName, string $companyName, string $setupUrl): bool
  {
    $subject = "Invitation to join {$companyName}";
    $htmlContent = "
      <h1>Invitation to join {$companyName} on OKR Flow</h1>
      <p>Hello {$toName},</p>
      <p>You have been invited to join <strong>{$companyName}</strong> on OKR Flow.</p>
      <p>To activate your account and set your password, click the link below:</p>
      <p><a href=\"{$setupUrl}\" style=\"background-color: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;\">Activate my account</a></p>
      <p>This link is valid for 7 days.</p>
    ";

    return $this->send($toEmail, $toName, $subject, $htmlContent);
  }

  private function send(string $toEmail, string $toName, string $subject, string $htmlContent): bool
  {
    $data = [
      'sender' => [
        'email' => $this->senderEmail,
        'name' => $this->senderName
      ],
      'to' => [
        [
          'email' => $toEmail,
          'name' => $toName
        ]
      ],
      'subject' => $subject,
      'htmlContent' => $htmlContent
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Accept: application/json',
      'Content-Type: application/json',
      'api-key: ' . $this->apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    return $httpCode >= 200 && $httpCode < 300;
  }
}
