# Payment Integration Setup Guide

## 🚀 Razorpay Integration Status

✅ **Backend Integration**: Complete
✅ **API Endpoints**: Implemented
✅ **Webhook Handling**: Ready
✅ **Frontend Components**: Available
❌ **API Keys**: Need to be configured

## 📋 Setup Steps

### 1. Get Razorpay API Keys

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

### 2. Configure Environment Variables

Update your `.env` file with the Razorpay credentials:

```env
# Razorpay Configuration
RAZORPAY_KEY_ID=rzp_test_your_key_id_here
RAZORPAY_KEY_SECRET=your_key_secret_here
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret_here
```

### 3. Test the Integration

#### Test Mode
For testing, use Razorpay's test mode:
- Use test API keys (they start with `rzp_test_`)
- Use test card numbers provided by Razorpay
- No real money will be charged

#### Test Card Numbers
- **Success**: 4111 1111 1111 1111
- **Failure**: 4000 0000 0000 0002
- **CVV**: Any 3 digits
- **Expiry**: Any future date
- **Name**: Any name

## 🔧 API Endpoints

### Payment Creation
```bash
POST /api/v1/org/payments/create
Content-Type: application/json
Authorization: Bearer {token}

{
    "invoice_id": 1
}
```

### Payment Completion
```bash
POST /api/v1/org/payments/complete
Content-Type: application/json
Authorization: Bearer {token}

{
    "payment_id": 1,
    "razorpay_order_id": "order_xxx",
    "razorpay_payment_id": "pay_xxx",
    "razorpay_signature": "signature_xxx"
}
```

### Get Payment Config
```bash
GET /api/v1/org/payments/config
Authorization: Bearer {token}
```

## 🎨 Frontend Integration

The payment system includes:
- Payment modal with Razorpay checkout
- Automatic script loading
- Payment success/failure handling
- Invoice status updates

### Vue.js Component Usage

```vue
<template>
    <PaymentModal 
        :show="showPaymentModal"
        :invoice="selectedInvoice"
        @close="showPaymentModal = false"
        @success="onPaymentSuccess"
        @error="onPaymentError"
    />
</template>
```

## 🔍 Testing Checklist

- [ ] API keys configured in `.env`
- [ ] Webhook URL accessible
- [ ] Test payment creation
- [ ] Test payment completion
- [ ] Test webhook handling
- [ ] Test refund processing

## 🚨 Troubleshooting

### Common Issues

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

## 📊 Production Checklist

- [ ] Use live API keys (start with `rzp_live_`)
- [ ] Complete KYC verification
- [ ] Set up proper webhook endpoints
- [ ] Test with real payment methods
- [ ] Set up monitoring and alerts

## 🔐 Security Notes

- Never commit API keys to version control
- Use environment variables for all sensitive data
- Verify webhook signatures
- Implement proper error handling
- Log all payment activities

## 📈 Next Steps

1. **Configure API Keys**: Add your Razorpay credentials
2. **Test Integration**: Use test mode to verify functionality
3. **Deploy Webhooks**: Set up webhook endpoints
4. **Go Live**: Switch to production mode
5. **Monitor**: Set up payment monitoring and alerts
