<?php
/**
 * Created by PhpStorm.
 * User: arnarfjodur
 * Date: 29/11/16
 * Time: 12:33
 */

namespace Karolina;
use Aws\Ses\SesClient;
use Aws\SesV2\SesV2Client;
use Karolina\Language\Field;
use PHPMailer\PHPMailer\PHPMailer;
use GuzzleHttp\Promise;

use Aws\Sns\SnsClient;


use Spatie\IcalendarGenerator\Components\Calendar;
use Spatie\IcalendarGenerator\Components\Event;
use Spatie\IcalendarGenerator\PropertyTypes\TextPropertyType;

class Notification
{

    private $toEmail = array();
    private $ccEmail = array();
    private $bccEmail = array();
    private $fromEmail;
    private $fromName;
    private $subject;
    private $tags = array();
    private $platform;
    private $htmlBody;
    private $plaintextBody;
    private $templateEngine;
    private $userInteractor;
    private $attachments = [];


    public function __construct()
    {

        $platformRepository = new \Karolina\PlatformRepository();
        $this->platform =  $platformRepository->getPlatformByKey(getenv('platform_key'));
        $this->subject = "Notification";
        $this->fromEmail = $this->platform->conf('email_noreply');
        $this->fromName = $this->platform->getPlatformName();
        $this->toEmail[] = $this->platform->conf('email_info');

    }

    public function addToEmailQueue () {


        if (getenv('emailtesting') == "mailtrap") { // When doing tests
 
            $this->sendWithMailTrapTester();

        }

        // for every 20 emails in $this->toEmail, who add to the queue
        $emails = array_chunk($this->toEmail, 20);

        $i = 1;

        foreach ($emails as $emailChunk) {

            $model = new \Karolina\Database\Table\EmailQueue();

            $model->html_message = $this->htmlBody;
            $model->recipients = json_encode($emailChunk);
            $model->cc_recipients = json_encode($this->ccEmail);
            $model->bcc_recipients = json_encode($this->bccEmail);
            $model->attachments = json_encode($this->attachments);

            $uuid = (string) new UUID();
            $model->uuid = $uuid;
            $model->subject = $this->subject;
            $model->from_email = $this->fromEmail;
            $model->from_name = $this->fromName;
            $model->platform = getenv('platform_key');

            $model->save();


            $sqsMessage = array(
            );

            $sqsMessage['html_message'] = $this->htmlBody;
            $sqsMessage['recipients'] = $emailChunk;
            $sqsMessage['cc_recipients'] = $this->ccEmail;
            $sqsMessage['bcc_recipients'] = $this->bccEmail;
            $sqsMessage['attachments'] = $this->attachments;
            $sqsMessage['uuid'] = $uuid;
            $sqsMessage['subject'] = $this->subject;
            $sqsMessage['from_email'] = $this->fromEmail;
            $sqsMessage['from_name'] = $this->fromName;
            $sqsMessage['platform'] = getenv('platform_key');
            $sqsMessage['started_sending_at'] = date('Y-m-d H:i:s');

            $awsRegion = $this->platform->conf('aws_region') ?: 'eu-west-1'; // Default to Ireland region if not set

            $sqsClient = new \Aws\Sqs\SqsClient([
                'region'  => $awsRegion,
                'version' => 'latest'
            ]);

            $queueUrl = getenv('send_email_queue_url');

            try {
                $sqsClient->sendMessage([
                    'QueueUrl'    => $queueUrl,
                    'MessageBody' => json_encode($sqsMessage),
                    'DelaySeconds' => $i // Set your desired delay in seconds
                ]);
            } catch (\Exception $e) {
                error_log('Failed to send SQS message: ' . $e->getMessage());
            }

            $i = $i + 5;





        }

        return true;

    }

    public function sendEmailInQueue ($uuid) {

        $model = new \Karolina\Database\Table\EmailQueue();
        $email = $model->where('uuid', $uuid)->first();

        putenv("platform_key=".$email->platform);

        if (!$email) {
            error_log("Email with UUID $uuid not found in queue");
            throw new \Karolina\Exception("Email not found in queue", 'email_not_found');
        }

        if ($email->finished_sending_at != null) {
            error_log("Email with UUID $uuid already sent");
            throw new \Karolina\Exception("Email already sent", 'email_already_sent');
        }


        if ($email->started_sending_at != null) {
            error_log("Email with UUID $uuid already started sending");
            throw new \Karolina\Exception("Already started sending email", 'email_already_started_sending');
        }

        $currentDateTime = new \DateTime();
        $email->started_sending_at = $currentDateTime;
        $email->save();

        $this->setMultipleRecipientsEmail(json_decode($email->recipients, true));
        $this->ccEmail = json_decode($email->cc_recipients, true);
        $this->bccEmail= json_decode($email->bcc_recipients, true);
        $this->attachments = json_decode($email->attachments, true);
        $this->setSubject($email->subject);
        $this->setBodyFromHtml($email->html_message);
        $this->setFromEmail($email->from_email);
        $this->fromName = $email->from_name;    

        $this->send();

        $currentDateTime = new \DateTime();
        $email->finished_sending_at = $currentDateTime;
        $email->save();

        $report = [
            'emails_sent' => count(json_decode($email->recipients, true)),
            'uuid' => $uuid,
            'time_started' => $email->started_sending_at,
            'time_finished' => $email->finished_sending_at,
            'time_taken' => $email->finished_sending_at->diff($email->started_sending_at)->format('%H:%I:%S')
        ];

        return $report;

    }

    private function addAttachment ($content, $encoding, $type, $name) {

            $this->attachments[] = array(
                'content' => $content,
                'encoding' => $encoding,
                'type' => $type,
                'name' => $name
            );
    }

    public function addCalendarEvent ($calendar, $summary, $description, $location, $start, $end) {


        $fileContent = Calendar::create($calendar)
        ->appendProperty(
            TextPropertyType::create('METHOD', 'REQUEST')
        )
        ->event(Event::create($summary)
            ->startsAt(new \DateTime($start))
            ->endsAt(new \DateTime($end))
            ->address($location)
            ->organizer($this->fromEmail, $this->fromName)
            ->description($description)
            )
        ->get();

        $this->addAttachment($fileContent, 'base64', 'text/calendar; charset="UTF-8"', 'invite.ics');

        return $this;
    }

    public function setUserInteractor ($interactor) {

        $this->userInteractor = $interactor;

    }

    public function setToUserIds ($idArray) {

        if (!is_array($idArray)) {
            $idArray = [$idArray];
        }

        $docs = $this->userInteractor->getDocumentsByIds($idArray);
        $emails = [];
        foreach ($docs as $user) {

            $emails[] = $user['email'];

        }
        $this->setMultipleRecipientsEmail($emails);

        return $this;

    }

    public function setToAdmin () {

        $this->setSingleRecipientEmail($this->platform->conf('email_info'));

        return $this;

    }

    public function setFromEmail ($email) {

        $this->fromEmail = $email;
        return $this;

    }

    public function addBccEmail ($email) {

        $this->bccEmail[] = $email;

    }

    public function addCCEmail ($email) {

        $this->ccEmail[] = $email;

    }


    private function getTemplate ($key) {

        return "/notifications/".$key.".twig";

    }

    public function setMultipleRecipientsEmail (array $emails) {

        $this->toEmail = $emails;
        return $this;
    }


    public function setSingleRecipientEmail ($email) {

        $this->toEmail = array($email);
        return $this;
    }

    public function setTemplateEngine ($engine) {

        $this->templateEngine = $engine;

    }

    public function setSubject ($subjectLine) {

        $this->subject = $subjectLine;

        return $this;

    }

    public function safeHTML ($content) {

        return htmlentities($content, ENT_QUOTES, 'UTF-8');

    }
    public function setSubjectFromTemplate ($templateKey, $args = array()) {

        $template = $this->getTemplate($templateKey);
        $this->setSubjectFromHtml($this->templateEngine->render($template, $args));

    }

    public function getBodyFromTemplate ($templateKey, $args = array()) {

        $template = $this->getTemplate($templateKey);
        return $this->templateEngine->render($template, $args);

    }

    public function setBodyFromTemplate ($templateKey, $args = array()) {

        $this->setBodyFromHtml($this->getBodyFromTemplate($templateKey, $args));
        return $this;

    }

    public function setSubjectFromHtml ($html) {

        $field = new Field($html, 'html');
        $this->setSubject(html_entity_decode(strip_tags($field->getAsHTML())));

    }

    public function setBodyFromHtml ($html) {

        $field = new Field($html, 'html');

        $this->htmlBody = $field->getAsHTML();
        $this->plaintextBody = strip_tags($field->getAsHTML());

    }


    public function setBodyFromPlaintext ($plaintext) {

        $this->htmlBody = nl2br($this->safeHtml($plaintext));
        $this->plaintextBody = $plaintext;

    }


    public function send () {

        if (getenv('emailtesting') == "mailtrap") { // When doing tests
 
            $this->sendWithMailTrapTester();

        } else {

            $this->sendWithSES();
        }


    }

    public function sendBulkWithSES () {

        $client = new SesV2Client([
            'version' => 'latest',
            'region' => $this->platform->conf('aws_region')
        ]);

        $result = $client->sendBulkEmail([
            'BulkEmailEntries' => [ // REQUIRED
                [
                    'Destination' => [ // REQUIRED
                        'ToAddresses' => ['']
                    ]
                ],
                // ...
            ],
            'DefaultContent' => [ // REQUIRED
                'Template' => [
                    'TemplateData' => 'Whooooop whoop data',
                    'TemplateName' => "Whoop template"
                ],
            ],
            'DefaultEmailTags' => [
                [
                    'Name' => 'nafn', // REQUIRED
                    'Value' => 'Arnarinn', // REQUIRED
                ],
                // ...
            ],
            'FromEmailAddress' => '',
            'FromEmailAddressIdentityArn' => '<string>',
            'ReplyToAddresses' => [''],
        ]);                

    }


    private function sendWithSES () {

        $emails = array();

        foreach ($this->toEmail as $toEmail) {

            $email = array();

            $email['from_name'] = $this->fromName;
            $email['from_email'] = $this->fromEmail;
            $email['to_emails'] = array($toEmail);
            $email['cc_emails'] = $this->ccEmail;
            $email['bcc_emails'] = $this->bccEmail;
            $email['subject'] = $this->subject;
            $email['plaintext_body'] = $this->plaintextBody;
            $email['html_body'] = $this->htmlBody;
            $email['reply_to_addresses'] = array($this->fromEmail);
            $email['return_email'] = $this->fromEmail;
            
            $emails[] = $email;
        }

//        $this->sendWithSESAsyncAndAttachment($emails);
        $this->sendWithSESNotAsync($emails);


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


        foreach ($this->attachments as $attachment) {

            $mail->addStringAttachment($attachment['content'], $attachment['name'] ,$attachment['encoding'], $attachment['type']);

        }

        $mail->preSend();
        $rawMessage = $mail->getSentMIMEMessage();        
        return $rawMessage;

    }


    public function sendWithSESAsyncAndAttachment ($emailsToSend) {
    
        $client = new SesClient([
            'version' => 'latest',
            'region' => $this->platform->conf('aws_region')
        ]);
    
        $promises = array();
    
        foreach ($emailsToSend as $email) {
            $rawMessages = 
            [
                "RawMessage" => [
                    "Data" => $this->getRawMessage($email)
                ]
            ];
    
    
            $promises[] = $client->sendRawEmailAsync($rawMessages);                        
    
        }
    
        $aggregate = Promise\Each::ofLimit($promises, 5);
    
        // You can then() off of this promise or synchronously wait
        $aggregate->then(function () use ($promises) {
            foreach ($promises as $promise) {
                $promise->wait();
            }
        })->wait();
    }

    public function sendWithSESNotAsync ($emailsToSend) {
        
        $client = new SesClient([
            'version' => 'latest',
            'region' => $this->platform->conf('aws_region')
        ]);

        foreach ($emailsToSend as $email) {
            $rawMessages = 
            [
                "RawMessage" => [
                    "Data" => $this->getRawMessage($email)
                ]
            ];

            $client->sendRawEmail($rawMessages);                        

        }
    }


    public function sendWithSESAsync ($emailsToSend) {

        $client = new SesClient([
            'version' => 'latest',
            'region' => $this->platform->conf('aws_region')
        ]);

        foreach ($emailsToSend as $email) {
            $promises[] = $client->sendEmailAsync(array(
                // Source is required
                'Source' => $email['from_name'].' <'.$email['from_email'].'>',
                // Destination is required
                'Destination' => array(
                    'ToAddresses' => $email['to_emails'],
                    'CcAddresses' => $email['cc_emails'],
                    'BccAddresses' => $email['bcc_emails']
                ),
                // Message is required
                'Message' => array(
                    // Subject is required
                    'Subject' => array(
                        // Data is required
                        'Data' => $email['subject'],
                        'Charset' => 'UTF-8',
                    ),
                    // Body is required
                    'Body' => array(
                        'Text' => array(
                            // Data is required
                            'Data' => $email['plaintext_body'],
                            'Charset' => 'UTF-8',
                        ),
                        'Html' => array(
                            // Data is required
                            'Data' => $email['html_body'],
                            'Charset' => 'UTF-8',
                        ),
                    ),
                ),
                'ReplyToAddresses' => $email['reply_to_addresses'],
                'ReturnPath' => $email['return_email']
                ));                        

        }

        $aggregate = Promise\Each::ofLimit($promises, 5);

        // You can then() off of this promise or synchronously wait
        $aggregate->wait();

    }


    private function sendWithMailTrapTester () {

        foreach ($this->toEmail as $toEmail) {

            // See results on https://mailtrap.io/
            $mail = new PHPMailer();

            $mail->SMTPDebug = 0;

            $mail->isSMTP();
            $mail->Host = 'smtp.mailtrap.io';
            $mail->SMTPAuth = true;
            $mail->Username = '';
            $mail->Password = '';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 2525;

            $mail->setFrom($this->fromEmail);


            $mail->addAddress($toEmail);


            $mail->addReplyTo($this->fromEmail);

            foreach ($this->ccEmail as $email) {
                $mail->addCC($email);
            }
            foreach ($this->bccEmail as $email) {
                $mail->addBCC($email);
            }

            $mail->isHTML(true);

            $mail->Subject = $this->subject;
            $mail->Body    = $this->htmlBody;
            $mail->AltBody = $this->plaintextBody;


            if(!$mail->send()) {
                echo 'Message could not be sent.';
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {

                return true;

            }

        }

    }

}