<?php

namespace LaravelGraphApiMailDriver;

use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AccessTokenManager{

    private Carbon $expires_on;
    private string $access_token;
    private static ?AccessTokenManager $instance = null;

    public static function getInstance(): AccessTokenManager{

        if(self::$instance === null){
            self::$instance = new AccessTokenManager();
        }

        return self::$instance;
    }

    /**
     * @throws RequestException
     */
    public function getAccessToken(): string{

        if(!$this->hasToken() || $this->isExpired()){

            $response = Http::asForm()
                            ->post('https://login.microsoftonline.com/' . config('mail.mailers.microsoft-graph-api.tenant_id') . '/oauth2/token',
                                [

                                    'client_id'     => config('mail.mailers.microsoft-graph-api.client_id'),
                                    'client_secret' => config('mail.mailers.microsoft-graph-api.client_secret'),
                                    'resource'      => 'https://graph.microsoft.com',
                                    'grant_type'    => 'client_credentials',

                                ]);

            $response->throwUnlessStatus(200)->throwIf(fn(Response $response) => (bool) (json_decode($response->body())->access_token));

            $this->access_token = json_decode($response->body())->access_token;
            $this->expires_on = Carbon::createFromTimestamp(json_decode($response->body())->expires_on);
        }

        return $this->access_token;
    }

    /**
     * @return bool
     */
    private function hasToken(): bool{

        return isset($this->access_token);
    }

    /**
     * Check if token is expired
     *
     * @return bool
     */
    private function isExpired(): bool{

        return $this->expires_on->isPast();
    }
}
