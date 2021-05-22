<?php

namespace Wobeto\GoogleRecaptchaCheckbox;

use Exception;

class Recaptcha
{
    private string $siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify";
    private string $secret;
    private string $remoteIp;

    private array $errorCodesTranslate = [
        'missing-input-secret' => 'The secret parameter is missing.',
        'invalid-input-secret' => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
        'bad-request' => 'The request is invalid or malformed.',
        'timeout-or-duplicate' => 'The response is no longer valid: either is too old or has been used previously.',
        'generic' => 'Error ocurring'
    ];

    public function __construct(string $secret)
    {
        $this->secret = $secret;
    }

    public function setErrorCodesTranslate(array $errorCodesTranslate): Recaptcha
    {
        $this->errorCodesTranslate = $errorCodesTranslate;

        return $this;
    }

    public function setRemoteIp(string $remoteIp): Recaptcha
    {
        $this->remoteIp = $remoteIp;

        return $this;
    }

    public function verify(string $gRecaptchaResponse)
    {
        if (!$gRecaptchaResponse) {
            throw new Exception($this->errorCodesTranslate['missing-input-response']);
        }

        $getResponse = $this->submitHttpGet(
            $this->siteVerifyUrl,
            $this->defineData($gRecaptchaResponse)
        );

        $answer = json_decode($getResponse);

        if ($answer->success === false) {
            $this->responseException($answer);
        }

        return true;
    }

    /**
     * Submits an HTTP GET to a reCAPTCHA server.
     *
     * @param string $path url path to recaptcha server.
     * @param array $data array of parameters to be sent.
     *
     * @return array response
     */
    private function submitHttpGet($path, $data): string
    {
        return file_get_contents($path . '?' . http_build_query($data));
    }

    private function responseException($answer): never
    {
        $error = reset($answer->{"error-codes"});

        if (isset($this->errorCodesTranslate[$error])) {
            throw new Exception($this->errorCodesTranslate[$error]);
        }

        throw new Exception($this->errorCodesTranslate['general']);
    }

    private function defineData($gRecaptchaResponse): array
    {
        $data = [
            'secret' => $this->secret,
            'response' => $gRecaptchaResponse
        ];

        if ($this->remoteIp) {
            $data['remoteip'] = $this->remoteIp;
        }

        return $data;
    }

}