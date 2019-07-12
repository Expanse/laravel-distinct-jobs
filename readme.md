# Laravel Distinct Jobs

Allows a user to specify that this job (with these specific parameters)
should not run if there's another job (with the same set of parameters)
already queued to run.

## Requirements

* Laravel 5.8.29 or above

## Install

```
composer require expanse/laravel-distinct-jobs
```

## Use

Modify your jobs to use `Expanse\Traits\DistinctJobTrait` as a trait.
If multiple jobs are then queued before the `queue:work` command gets
to running them, any duplicated jobs will be immediately ended until
there is only a single job to run.
