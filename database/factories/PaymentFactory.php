<?php

namespace Database\Factories;

use App\Models\Payment;
use App\Models\Organization;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition()
    {
        return [
            'org_id' => Organization::factory(),
            'invoice_id' => Invoice::factory(),
            'amount' => $this->faker->randomFloat(2, 1000, 50000),
            'currency' => 'INR',
            'status' => $this->faker->randomElement(['pending', 'completed', 'failed', 'refunded']),
            'payment_method' => $this->faker->randomElement(['razorpay', 'bank_transfer', 'cash', 'cheque']),
            'razorpay_payment_id' => $this->faker->optional(0.8)->regexify('pay_[a-zA-Z0-9]{14}'),
            'razorpay_order_id' => $this->faker->optional(0.8)->regexify('order_[a-zA-Z0-9]{14}'),
            'transaction_id' => $this->faker->optional()->uuid,
            'gateway_response' => $this->faker->optional()->randomElement(['success', 'failed', 'pending']),
            'paid_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 year', 'now'),
            'refund_amount' => 0,
            'refund_reason' => null,
            'refunded_at' => null,
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'completed',
                'razorpay_payment_id' => 'pay_' . $this->faker->regexify('[a-zA-Z0-9]{14}'),
                'razorpay_order_id' => 'order_' . $this->faker->regexify('[a-zA-Z0-9]{14}'),
                'gateway_response' => 'success',
                'paid_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'pending',
                'razorpay_payment_id' => null,
                'razorpay_order_id' => 'order_' . $this->faker->regexify('[a-zA-Z0-9]{14}'),
                'gateway_response' => 'pending',
                'paid_at' => null,
            ];
        });
    }

    public function failed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'failed',
                'gateway_response' => 'failed',
                'paid_at' => null,
            ];
        });
    }

    public function refunded()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'refunded',
                'refund_amount' => $this->faker->randomFloat(2, 100, $attributes['amount']),
                'refund_reason' => $this->faker->sentence,
                'refunded_at' => $this->faker->dateTimeBetween('-6 months', 'now'),
            ];
        });
    }
}