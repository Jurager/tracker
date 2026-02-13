<?php

namespace Jurager\Tracker\Traits;

use Jurager\Tracker\Events\IpLookupFailed;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

trait MakesHttpCalls
{
    protected Client $httpClient;
    protected ?Collection $result = null;

    /**
     * Initialize HTTP client and make the API call.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function __construct()
    {
        $timeout = config('tracker.lookup.timeout', 1.0);

        $this->httpClient = new Client([
            'connect_timeout' => $timeout,
            'timeout' => $timeout,
        ]);

        $this->result = $this->makeApiCall();
    }

    /**
     * Make the API call and get the response as a Laravel collection.
     *
     * @return Collection|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function makeApiCall(): ?Collection
    {
        try {
            $response = $this->httpClient->send($this->getRequest());

            $data = json_decode($response->getBody()->getContents(), true);

            return $data ? collect($data) : null;

        } catch (TransferException $e) {
            Event::dispatch(new IpLookupFailed($e));

            return null;
        }
    }

    /**
     * Get the result of the API call as a Laravel collection.
     *
     * @return Collection|null
     */
    public function getResult(): ?Collection
    {
        return $this->result;
    }
}
