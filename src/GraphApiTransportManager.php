<?php

namespace LaravelGraphApiMailDriver;

use Exception;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LaravelGraphApiMailDriver\Exceptions\ConfigException;
use LaravelGraphApiMailDriver\Exceptions\SendingFailedException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\MessageConverter;

class GraphApiTransportManager extends AbstractTransport{

    /**
     * @throws ConfigException
     */
    public function __construct(array $config){

        $this->validateConfig($config);

        parent::__construct();
    }

    /**
     * @throws ConfigException
     */
    private function validateConfig(array $config): void{

        $validator = Validator::make($config, [
           'transport' => ['required', Rule::in(['microsoft-graph-api'])],
           'client_id' => 'required',
           'client_secret' => 'required',
           'tenant_id' => 'required',
           'saveToSentItems' => 'required|boolean',
        ]);

        if($validator->fails()){

            throw new ConfigException($validator->messages()->messages());
        }
    }

    /**
     * @throws Exception
     * @throws SendingFailedException
     */
    protected function doSend(SentMessage $message): void{

        $rawMessage = $message->getOriginalMessage();

        if($rawMessage instanceof Message){
            $email = MessageConverter::toEmail($rawMessage);
        }
        else{
            throw new Exception('Wrong Mail Class');
        }

        try{
            $response = Http::withToken(AccessTokenManager::getInstance()->getAccessToken())
                            ->withBody($this->generateBody($email))
                            ->post('https://graph.microsoft.com/v1.0/users/' . $this->getSenderAddress($message) . '/sendMail')
                            ->throwUnlessStatus(200);
        }
        catch(RequestException $ex){

            Log::error('Graph Api responded with error', [$ex]);

            throw new SendingFailedException('Graph Api responded with error, see log files for details');
        }
    }

    private function generateBody(Email $email): string{

        return json_encode([
            'message'         => [
                'subject'        => $email->getSubject(),
                'body'           => [
                    'contentType' => $email->getHtmlBody() === null ? 'Text' : 'HTML',
                    'content'     => $email->getHtmlBody() ?? $email->getTextBody()
                ],
                'toRecipients'   => $this->buildAddressArrays($email->getTo()),
                'ccRecipients'   => $this->buildAddressArrays($email->getCc()),
                'bccRecipients'  => $this->buildAddressArrays($email->getBcc()),
                'hasAttachments' => !empty($email->getAttachments()),
                'attachments'    => $this->attachAttachments($email->getAttachments())
            ],
            'saveToSentItems' => config('mail.mailers.microsoft-graph-api.saveToSentItems', true)
        ]);
    }

    private function getSenderAddress(SentMessage $message): string{

        return $message->getEnvelope()->getSender()->getAddress();
    }

    private function getRecipients(SentMessage $message): array{
        return $message->getEnvelope()->getRecipients();
    }

    private function buildAddressArrays(array $recipients): array{

        $result = [];

        foreach($recipients as $recipient){

            $result[] = [
                'emailAddress' => [
                    'address' => $recipient->getAddress()
                ],
            ];
        }

        return $result;
    }

    private function attachAttachments(array $attachments): array{

        $files = [];

        foreach($attachments as $attachment){

            $files[] = [
                '@odata.type' => '#Microsoft.graph.FileAttachment',
                'name' => $attachment->getFilename(),
                'contentBytes' => base64_encode($attachment->getBody()),
            ];
        }

        return $files;
    }

    public function __toString(): string{
        return config('mail.mailers.microsoft-graph-api.transport');
    }
}
