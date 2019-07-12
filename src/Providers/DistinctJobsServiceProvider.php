<?php

namespace Expanse\Providers;

use Expanse\Traits\DistinctJobTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class DistinctJobsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Event::listen(\Illuminate\Queue\Events\JobProcessing::class, function ($event) {
            if (! $this->jobUsesTrait($event->job)) {
                return;
            }

            $distinctKey = $this->buildDistinctKey($event);

            if (Cache::get($distinctKey, 0) > 1) {
                $event->job->delete();
                return;
            }
        });

        Event::listen(\Illuminate\Queue\Events\JobDeleted::class, function ($event) {
            if (! $this->jobUsesTrait($event->job)) {
                return;
            }

            $distinctKey = $this->buildDistinctKey($event);

            Cache::decrement($distinctKey, 1);
        });

        Event::listen(\Illuminate\Queue\Events\JobProcessed::class, function ($event) {
            if (! $this->jobUsesTrait($event->job)) {
                return;
            }

            $distinctKey = $this->buildDistinctKey($event);

            Cache::decrement($distinctKey, 1);
        });
    }

    public function register()
    {
    }

    protected function jobUsesTrait($job) : bool
    {
        if (array_key_exists(
            DistinctJobTrait::class,
            class_uses($job->resolveName()))
        ) {
            return true;
        }

        return false;
    }

    protected function buildDistinctKey($event) : string
    {
        $connection = $event->connectionName;
        $payload = $event->job->payload();

        $job = $event->job->resolveName();

        $command = unserialize($payload['data']['command']);

        $distinctKey = $job::buildDistinctKey($connection, $command);

        return $distinctKey;
    }
}
