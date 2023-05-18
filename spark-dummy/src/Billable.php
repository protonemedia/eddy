<?php

namespace Spark;

use Illuminate\Support\Collection;
use Illuminate\Support\Fluent;
use Illuminate\Support\Str;
use Laravel\Paddle\Concerns\ManagesCustomer;
use Laravel\Paddle\Concerns\ManagesSubscriptions;

trait Billable
{
    use ManagesSubscriptions;
    use ManagesCustomer;

    /**
     * Boot the billable model.
     *
     * @return void
     */
    public static function bootBillable()
    {
        static::created(function ($model) {
            $type = Str::of(get_class($model))->classBasename()->snake();

            $trialDays = config('spark.billables.'.$type.'.trial_days');

            $model->customer()->create([
                'trial_ends_at' => $trialDays ? now()->addDays($trialDays) : null,
            ]);
        });
    }

    /**
     * A simplified version of the Laravel Spark's `sparkPlan` method.
     *
     * @return \Illuminate\Support\Fluent|null
     */
    public function sparkPlan()
    {
        $subscription = $this->subscription();

        if ($subscription?->valid()) {
            $type = Str::of(get_class($this))->classBasename()->snake();

            $plans = Collection::make(config('spark.billables.'.$type.'.plans'));

            $paddlePlan = $subscription->paddle_plan;

            $plan = $plans->firstWhere(
                fn ($plan) => $plan['monthly_id'] == $paddlePlan || $plan['yearly_id'] == $paddlePlan
            );

            return $plan ? new Fluent($plan) : null;
        }
    }
}
