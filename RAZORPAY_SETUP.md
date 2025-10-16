# Razorpay Payment Gateway Setup

## Configuration

Add the following environment variables to your `.env` file:

```env
# Razorpay Configuration
RAZORPAY_KEY_ID=your_razorpay_key_id_here
RAZORPAY_KEY_SECRET=your_razorpay_key_secret_here
RAZORPAY_WEBHOOK_SECRET=your_razorpay_webhook_secret_here
```

## Getting Razorpay Credentials

1. **Sign up for Razorpay Account**
   - Go to [https://razorpay.com](https://razorpay.com)
   - Create a new account or sign in

2. **Get API Keys**
   - Go to Dashboard → Settings → API Keys
   - Generate new API keys
   - Copy the Key ID and Key Secret

3. **Set up Webhooks**
   - Go to Dashboard → Settings → Webhooks
   - Add webhook URL: `https://yourdomain.com/api/v1/webhooks/razorpay`
   - Select events: `payment.captured`, `payment.failed`, `refund.created`
   - Copy the webhook secret

## Test Mode

For testing, use Razorpay's test mode:
- Use test API keys (they start with `rzp_test_`)
- Use test card numbers provided by Razorpay
- No real money will be charged

## Production Mode

For production:
- Use live API keys (they start with `rzp_live_`)
- Complete KYC verification
- Set up proper webhook endpoints

## Test Card Numbers

Use these test card numbers for testing:

- **Success**: 4111 1111 1111 1111
- **Failure**: 4000 0000 0000 0002
- **CVV**: Any 3 digits
- **Expiry**: Any future date
- **Name**: Any name

## Features Implemented

✅ **Payment Processing**
- Create payment orders
- Process payments via Razorpay
- Verify payment signatures
- Handle payment success/failure

✅ **Invoice Integration**
- Pay invoices directly
- Update invoice status after payment
- Track payment history

✅ **Webhook Handling**
- Automatic payment status updates
- Refund processing
- Payment failure handling

✅ **Security**
- Payment signature verification
- Webhook signature validation
- Secure API key storage

## API Endpoints

- `POST /api/v1/org/payments/create` - Create payment order
- `POST /api/v1/org/payments/complete` - Complete payment
- `POST /api/v1/org/payments/{payment}/refund` - Process refund
- `GET /api/v1/org/payments/config` - Get Razorpay config
- `POST /api/v1/webhooks/razorpay` - Webhook handler

## Frontend Integration

The payment system includes:
- Payment modal with Razorpay checkout
- Automatic script loading
- Payment success/failure handling
- Invoice status updates

## Troubleshooting

1. **Payment not working**
   - Check API keys are correct
   - Verify webhook URL is accessible
   - Check browser console for errors

2. **Webhook not receiving events**
   - Verify webhook URL is correct
   - Check webhook secret matches
   - Ensure server is accessible from internet

3. **Test payments failing**
   - Use correct test card numbers
   - Check if test mode is enabled
   - Verify API keys are test keys
