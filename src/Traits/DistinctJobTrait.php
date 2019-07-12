<?php

namespace Expanse\Traits;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

trait DistinctJobTrait {
    /**
     * Used to set the distinct counter for our jobs
     */
    public function queue($queue, $command)
    {
        $distinctKey = self::buildDistinctKey($queue->getConnectionName(), $command);

        // set incrementer for $payload
        Cache::increment($distinctKey, 1);

        if (isset($command->queue, $command->delay)) {
            return $queue->laterOn($command->queue, $command->delay, $command);
        }

        if (isset($command->queue)) {
            return $queue->pushOn($command->queue, $command);
        }

        if (isset($command->delay)) {
            return $queue->later($command->delay, $command);
        }

        return $queue->push($command);
    }

    /**
     * @param $connectionName The named connection
     * @param $command The actual job class with all its parameters
     */
    public static function buildDistinctKey($connectionName, $command) {
        $sortedCommandOptions = Arr::sortRecursive(array_filter(get_object_vars($command)));
        $dots = Arr::dot($sortedCommandOptions);

        return md5(implode(';', [
            $connectionName,
            implode(';', array_keys($dots)),
            implode(';', array_values($dots))
        ]));
    }
}

