<?php

namespace Wobeto\GoogleRecaptchaCheckbox;

use Exception;
use stdClass;

class Recaptcha
{

    private string $siteVerifyUrl = "https://www.google.com/recaptcha/api/siteverify";
    private string $secret;
    private string $remoteIp;
    private string $expectedHostname;

    private array $errorCodesTranslate = [
        'missing-input-secret'   => 'The secret parameter is missing.',
        'invalid-input-secret'   => 'The secret parameter is invalid or malformed.',
        'missing-input-response' => 'The response parameter is missing.',
        'invalid-input-response' => 'The response parameter is invalid or malformed.',
        'bad-request'            => 'The request is invalid or malformed.',
        'timeout-or-duplicate'   => 'The response is no longer valid: either is too old or has been used previously.',
        'hostname-mismatch'      => 'The hostname is incompatible',
        'generic'                => 'Error ocurring'
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

    public function setExpectedHostname(string $hostname): Recaptcha
    {
        $this->expectedHostname = $hostname;

        return $this;
    }

    public function verify(string $gRecaptchaResponse)
    {
        if (!$gRecaptchaResponse) {
            $this->exceptionMissingInputResponse();
        }

        $getResponse = $this->submitHttpGet(
            $this->siteVerifyUrl,
            $this->defineData($gRecaptchaResponse)
        );

        $answer = json_decode($getResponse);

        if ($answer->success === false) {
            $this->responseException($answer);
        }

        if (
            isset($this->expectedHostname) &&
            strcasecmp($this->expectedHostname, $answer->hostname) !== 0
        ) {
            $this->exceptionExpectedHostname();
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
    private function submitHttpGet(string $path, array $data): string
    {
        return file_get_contents($path . '?' . http_build_query($data));
    }

    private function responseException(stdClass $answer): never
    {
        $error = reset($answer->{"error-codes"});

        if (isset($this->errorCodesTranslate[$error])) {
            throw new Exception($this->errorCodesTranslate[$error]);
        }

        throw new Exception($this->errorCodesTranslate['general']);
    }

    private function defineData(string $gRecaptchaResponse): array
    {
        $data = [
            'secret'   => $this->secret,
            'response' => $gRecaptchaResponse
        ];

        if (isset($this->remoteIp)) {
            $data['remoteip'] = $this->remoteIp;
        }

        return $data;
    }

    private function exceptionMissingInputResponse(): never
    {
        throw new Exception($this->errorCodesTranslate['missing-input-response']);
    }

    private function exceptionExpectedHostname(): never
    {
        throw new Exception($this->errorCodesTranslate['hostname-mismatch']);
    }

}