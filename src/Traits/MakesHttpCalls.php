<?php

namespace Jurager\Tracker\Traits;

use Jurager\Tracker\Events\LookupFailed;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;

trait MakesHttpCalls
{
    protected Client $httpClient;
    protected ?Collection $result = null;

    /**
     * Make the API call and get the response as a Laravel collection.
     *
     * @return Collection|null
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    protected function makeApiCall(): ?Collection
    {
        $retries = config('tracker.lookup.retries', 2);
        $attempt = 0;
        $exception = null;

        while ($attempt < $retries) {
            try {
                $response = $this->httpClient->send($this->getRequest());

                $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

                return $data ? collect($data) : null;

            } catch (TransferException $e) {
                $exception = $e;
                $attempt++;

                // If not the last attempt, wait before retrying with exponential backoff
                if ($attempt < $retries) {
                    usleep(100000 * (2 ** ($attempt - 1))); // 100ms, 200ms, 400ms, etc.
                }
            } catch (\JsonException $e) {
                Event::dispatch(new LookupFailed($exception, $this->ip ?? null));
            }
        }

        // All attempts failed, dispatch event with the last exception
        if ($exception) {
            Event::dispatch(new LookupFailed($exception, $this->ip ?? null));
        }

        return null;
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
