<?php

namespace Telegram\Bot\Traits;

use Telegram\Bot\Exceptions\CouldNotUploadInputFile;
use Telegram\Bot\Exceptions\TelegramSDKException;
use Telegram\Bot\FileUpload\InputFile;
use Telegram\Bot\HttpClients\HttpClientInterface;
use Telegram\Bot\TelegramClient;
use Telegram\Bot\TelegramRequest;
use Telegram\Bot\TelegramResponse;

/**
 * Http
 */
trait Http
{
    use Validator;

    /** @var string Telegram Bot API Access Token. */
    protected $accessToken = null;

    /** @var TelegramClient The Telegram client service. */
    protected $client = null;

    /** @var HttpClientInterface|null Http Client Handler */
    protected $httpClientHandler = null;

    /** @var bool Indicates if the request to Telegram will be asynchronous (non-blocking). */
    protected $isAsyncRequest = false;

    /** @var int Timeout of the request in seconds. */
    protected $timeOut = 60;

    /** @var int Connection timeout of the request in seconds. */
    protected $connectTimeOut = 10;

    /** @var TelegramResponse|null Stores the last request made to Telegram Bot API. */
    protected $lastResponse;

    /**
     * Set Http Client Handler.
     *
     * @param HttpClientInterface $httpClientHandler
     *
     * @return $this
     */
    public function setHttpClientHandler(HttpClientInterface $httpClientHandler)
    {
        $this->httpClientHandler = $httpClientHandler;

        return $this;
    }

    /**
     * Returns the TelegramClient service.
     *
     * @return TelegramClient
     */
    protected function getClient(): TelegramClient
    {
        if ($this->client === null) {
            $this->client = new TelegramClient($this->httpClientHandler);
        }

        return $this->client;
    }

    /**
     * Returns the last response returned from API request.
     *
     * @return TelegramResponse
     */
    public function getLastResponse(): TelegramResponse
    {
        return $this->lastResponse;
    }

    /**
     * Returns Telegram Bot API Access Token.
     *
     * @return string
     */
    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    /**
     * Sets the bot access token to use with API requests.
     *
     * @param string $accessToken The bot access token to save.
     *
     * @return $this
     */
    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;

        return $this;
    }

    /**
     * Check if this is an asynchronous request (non-blocking).
     *
     * @return bool
     */
    public function isAsyncRequest(): bool
    {
        return $this->isAsyncRequest;
    }

    /**
     * Make this request asynchronous (non-blocking).
     *
     * @param bool $isAsyncRequest
     *
     * @return $this
     */
    public function setAsyncRequest(bool $isAsyncRequest)
    {
        $this->isAsyncRequest = $isAsyncRequest;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimeOut(): int
    {
        return $this->timeOut;
    }

    /**
     * @param int $timeOut
     *
     * @return $this
     */
    public function setTimeOut(int $timeOut)
    {
        $this->timeOut = $timeOut;

        return $this;
    }

    /**
     * @return int
     */
    public function getConnectTimeOut(): int
    {
        return $this->connectTimeOut;
    }

    /**
     * @param int $connectTimeOut
     *
     * @return $this
     */
    public function setConnectTimeOut(int $connectTimeOut)
    {
        $this->connectTimeOut = $connectTimeOut;

        return $this;
    }

    /**
     * Sends a GET request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array  $params
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    protected function get(string $endpoint, array $params = []): TelegramResponse
    {
        $params = $this->replyMarkupToString($params);

        return $this->sendRequest(
            'GET',
            $endpoint,
            $params
        );
    }

    /**
     * Sends a POST request to Telegram Bot API and returns the result.
     *
     * @param string $endpoint
     * @param array  $params
     * @param bool   $fileUpload Set true if a file is being uploaded.
     *
     * @return TelegramResponse
     */
    protected function post(string $endpoint, array $params = [], $fileUpload = false): TelegramResponse
    {
        if ($fileUpload) {
            $params = ['multipart' => $params];
        } else {

            $params = $this->replyMarkupToString($params);

            $params = ['form_params' => $params];
        }

        return $this->sendRequest(
            'POST',
            $endpoint,
            $params
        );
    }

    /**
     * Converts a reply_markup field in the $params to a string.
     *
     * @param array $params
     *
     * @return array
     */
    protected function replyMarkupToString(array $params): array
    {
        if (isset($params['reply_markup'])) {
            $params['reply_markup'] = (string)$params['reply_markup'];
        }

        return $params;
    }

    /**
     * Sends a multipart/form-data request to Telegram Bot API and returns the result.
     * Used primarily for file uploads.
     *
     * @param string $endpoint
     * @param array  $params
     * @param string $inputFileField
     *
     * @throws CouldNotUploadInputFile
     * @return TelegramResponse
     */
    protected function uploadFile(string $endpoint, array $params, $inputFileField): TelegramResponse
    {
        //Check if the field in the $params array (that is being used to send the relative file), is a file id.
        if (!isset($params[$inputFileField]) || $this->hasFileId($inputFileField, $params)) {
            return $this->post($endpoint, $params);
        }

        //Sending an actual file requires it to be sent using multipart/form-data
        return $this->post($endpoint, $this->prepareMultipartParams($params, $inputFileField), true);
    }

    /**
     * Prepare Multipart Params for File Upload.
     *
     * @param array  $params
     * @param string $inputFileField
     *
     * @throws CouldNotUploadInputFile
     * @return array
     */
    protected function prepareMultipartParams(array $params, $inputFileField): array
    {
        if (!isset($params[$inputFileField])) {
            throw CouldNotUploadInputFile::missingParam($inputFileField);
        }

        $inputFile = $params[$inputFileField];

        //All files should be provided in an InputFile object
        if (is_resource($inputFile)) {
            throw CouldNotUploadInputFile::resourceShouldBeInputFileEntity($inputFileField);
        }

        //If the user provides a path to a file, attempt to create an InputFile Object automatically for them.
        if (is_string($inputFile)) {
            $params[$inputFileField] = InputFile::create($inputFile);
        }

        //Iterate through all param options and convert to multipart/form-data.
        return collect($params)
            ->reject(function ($value) {
                return is_null($value);
            })
            ->map(function ($contents, $name) {
                return $this->generateMultipartData($contents, $name);
            })
            ->values()
            ->all();
    }

    /**
     * Generates the multipart data required when sending files to telegram.
     *
     * @param mixed  $contents
     * @param string $name
     *
     * @return array
     */
    protected function generateMultipartData($contents, $name): array
    {
        if ($this->isInputFile($contents)) {
            $filename = $contents->getFilename();
            $contents = $contents->getContents();

            return compact('name', 'contents', 'filename');
        }

        return compact('name', 'contents');
    }

    /**
     * Sends a request to Telegram Bot API and returns the result.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     *
     * @throws TelegramSDKException
     *
     * @return TelegramResponse
     */
    protected function sendRequest($method, $endpoint, array $params = []): TelegramResponse
    {
        $request = $this->request($method, $endpoint, $params);

        return $this->lastResponse = $this->getClient()->sendRequest($request);
    }

    /**
     * Instantiates a new TelegramRequest entity.
     *
     * @param string $method
     * @param string $endpoint
     * @param array  $params
     *
     * @return TelegramRequest
     */
    protected function request($method, $endpoint, array $params = []): TelegramRequest
    {
        return (new TelegramRequest(
            $this->getAccessToken(),
            $method,
            $endpoint,
            $params,
            $this->isAsyncRequest()
        ))
            ->setTimeOut($this->getTimeOut())
            ->setConnectTimeOut($this->getConnectTimeOut());
    }
}