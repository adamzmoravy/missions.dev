<?php

namespace MissionsWorker;
use Aws\Ses\SesClient;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;



class EmailSender
{
    public function __construct()
    {
        // Constructor logic here
    }

    // Add your methods here

    public function sendEmail($data)

    {

        return $this->sendWithSESNotAsync($data);


    }


    public function sendWithSESNotAsync ($data) {

        $startTime = microtime(true);
        $client = new SesClient([
            'version' => 'latest',
            'region' => 'eu-west-1',
            'credentials' => [
                'key'    => $_ENV['AWS_ACCESS_KEY_ID'], // Use environment variable for security
                'secret' => $_ENV['AWS_SECRET_ACCESS_KEY'], // Use environment variable for security
            ]
        ]);

        foreach ($data['recipients'] as $to) {

            $email = [
                'from_email' => $data['from_email'],
                'from_name' => $data['from_name'],
                'to_emails' => [$to],
                'subject' => $data['subject'],
                'html_body' => $data['html_message'],
                'plaintext_body' => strip_tags($data['html_message']),
                'attachments' => isset($data['attachments']) ? $data['attachments'] : []
            ];

            $rawMessages = 
            [
                "RawMessage" => [
                    "Data" => $this->getRawMessage($email)
                ]
            ];

            try {
                $client->sendRawEmail($rawMessages);
            } catch (\Aws\Exception\AwsException $e) {
                // Log the error or handle it as needed
                return "Failed to send email to {$to}: " . $e->getAwsErrorMessage();
            } catch (\Exception $e) {
                return "Failed to send email to {$to}: " . $e->getMessage();
            }

        }

        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        $executionTimeFormatted = number_format($executionTime, 2);

        return "Sent email to " . count($data['recipients']) . " with subject: " . $data['subject'].
            " in " . $executionTimeFormatted . " seconds: " . implode(", ", $data['recipients']) ;

    }


    private function getRawMessage ($email) {

        $mail = new \PHPMailer\PHPMailer\PHPMailer;

        $mail->setFrom($email['from_email'], $email['from_name']);
        foreach ($email['to_emails'] as $toEmail) {
            $mail->addAddress($toEmail);
        }

        $mail->Subject = $email['subject'];
        $mail->Body = $email['html_body'];
        $mail->AltBody = $email['plaintext_body'];
        $mail->CharSet = 'UTF-8';
        if (isset($this->attachments[0])) {
            $mail->Ical = $this->attachments[0]['content'];
        }


        foreach ($email['attachments'] as $attachment) {

            $mail->addStringAttachment($attachment['content'], $attachment['name'] ,$attachment['encoding'], $attachment['type']);

        }

        $mail->preSend();
        $rawMessage = $mail->getSentMIMEMessage();        
        return $rawMessage;

    }


}