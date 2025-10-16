<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" @click="closeModal">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4" @click.stop>
            <!-- Header -->
            <div class="flex justify-between items-center p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Make Payment</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Invoice Details -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-2">Invoice Details</h4>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Invoice #</span>
                            <span class="font-medium">{{ invoice.invoice_no }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Amount</span>
                            <span class="font-medium">₹{{ formatPrice(invoice.total_amount) }}</span>
                        </div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600">Due Date</span>
                            <span class="font-medium">{{ formatDate(invoice.due_date) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Property</span>
                            <span class="font-medium">{{ invoice.property?.title }}</span>
                        </div>
                    </div>
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Payment Method</h4>
                    <div class="space-y-3">
                        <label class="flex items-center p-3 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" v-model="paymentMethod" value="razorpay" class="mr-3">
                            <div class="flex items-center">
                                <img src="/images/razorpay-logo.png" alt="Razorpay" class="w-8 h-8 mr-3" v-if="false">
                                <div>
                                    <div class="font-medium">Online Payment</div>
                                    <div class="text-sm text-gray-600">Credit/Debit Card, UPI, Net Banking</div>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Payment Button -->
                <div class="flex space-x-3">
                    <button @click="closeModal" class="flex-1 px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button @click="initiatePayment" :disabled="loading" class="flex-1 px-4 py-2 bg-sky-800 text-white rounded-lg hover:bg-sky-900 disabled:opacity-50">
                        <span v-if="loading">Processing...</span>
                        <span v-else>Pay ₹{{ formatPrice(invoice.total_amount) }}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    invoice: {
        type: Object,
        required: true
    }
})

const emit = defineEmits(['close', 'payment-success', 'payment-failed'])

const loading = ref(false)
const paymentMethod = ref('razorpay')
const razorpayConfig = ref(null)

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN').format(price)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN')
}

const loadRazorpayConfig = async () => {
    try {
        const response = await fetch('/api/v1/org/payments/config', {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
            }
        })
        const data = await response.json()
        if (data.success) {
            razorpayConfig.value = data.data
        }
    } catch (error) {
        console.error('Error loading Razorpay config:', error)
    }
}

const initiatePayment = async () => {
    if (!razorpayConfig.value) {
        alert('Payment gateway is not configured')
        return
    }

    loading.value = true

    try {
        // Create payment order
        const response = await fetch('/api/v1/org/payments/create', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                invoice_id: props.invoice.id
            })
        })

        const data = await response.json()

        if (!data.success) {
            throw new Error(data.message || 'Failed to create payment order')
        }

        // Load Razorpay script
        await loadRazorpayScript()

        // Create Razorpay options
        const options = {
            key: razorpayConfig.value.key_id,
            amount: data.data.amount,
            currency: data.data.currency,
            name: razorpayConfig.value.name,
            description: razorpayConfig.value.description,
            order_id: data.data.order_id,
            handler: async function (response) {
                await completePayment(data.data.payment_id, response)
            },
            prefill: {
                name: props.invoice.tenant?.name || '',
                email: props.invoice.tenant?.email || '',
                contact: props.invoice.tenant?.phone || '',
            },
            theme: razorpayConfig.value.theme,
            modal: {
                ondismiss: function() {
                    loading.value = false
                }
            }
        }

        // Open Razorpay checkout
        const rzp = new window.Razorpay(options)
        rzp.open()

    } catch (error) {
        console.error('Payment initiation failed:', error)
        alert('Payment initiation failed: ' + error.message)
        loading.value = false
    }
}

const completePayment = async (paymentId, razorpayResponse) => {
    try {
        const response = await fetch('/api/v1/org/payments/complete', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                payment_id: paymentId,
                razorpay_order_id: razorpayResponse.razorpay_order_id,
                razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                razorpay_signature: razorpayResponse.razorpay_signature,
            })
        })

        const data = await response.json()

        if (data.success) {
            emit('payment-success', data.data)
            closeModal()
        } else {
            throw new Error(data.message || 'Payment completion failed')
        }

    } catch (error) {
        console.error('Payment completion failed:', error)
        emit('payment-failed', error.message)
    } finally {
        loading.value = false
    }
}

const loadRazorpayScript = () => {
    return new Promise((resolve) => {
        if (window.Razorpay) {
            resolve()
            return
        }

        const script = document.createElement('script')
        script.src = 'https://checkout.razorpay.com/v1/checkout.js'
        script.onload = resolve
        document.head.appendChild(script)
    })
}

const closeModal = () => {
    emit('close')
}

onMounted(() => {
    if (props.show) {
        loadRazorpayConfig()
    }
})
</script>
