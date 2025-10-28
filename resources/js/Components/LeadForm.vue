<template>
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6 relative">
            <button @click="closeModal" class="absolute top-3 right-3 text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>

            <h2 class="text-2xl font-bold text-gray-800 mb-6">Contact Property Owner</h2>

            <div v-if="loading" class="text-center py-8">
                <p class="text-lg text-gray-600">Sending your inquiry...</p>
                <div class="mt-4 flex justify-center">
                    <svg class="animate-spin h-8 w-8 text-sky-800" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            <form v-else @submit.prevent="submitLead" class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                    <input
                        id="name"
                        v-model="form.name"
                        type="text"
                        required
                        class="form-input"
                        placeholder="Enter your full name"
                    />
                </div>

                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number *</label>
                    <input
                        id="phone"
                        v-model="form.phone"
                        type="tel"
                        required
                        class="form-input"
                        placeholder="Enter your phone number"
                    />
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        class="form-input"
                        placeholder="Enter your email address"
                    />
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">Message *</label>
                    <textarea
                        id="message"
                        v-model="form.message"
                        required
                        rows="4"
                        class="form-textarea"
                        placeholder="Tell the property owner about your interest..."
                    ></textarea>
                </div>

                <div v-if="error" class="text-red-600 text-sm">
                    {{ error }}
                </div>

                <div class="flex space-x-3 pt-4">
                    <button type="button" @click="closeModal" class="btn btn-secondary flex-1">
                        Cancel
                    </button>
                    <button type="submit" :disabled="loading" class="btn btn-primary flex-1">
                        Send Inquiry
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    property: Object,
    show: Boolean
})

const emit = defineEmits(['close', 'success', 'error'])

const form = ref({
    name: '',
    phone: '',
    email: '',
    message: ''
})

const loading = ref(false)
const error = ref(null)

const submitLead = async () => {
    loading.value = true
    error.value = null
    try {
        const response = await fetch(`/marketplace/properties/${props.property.id}/contact`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(form.value)
        })
        const data = await response.json()
        if (!response.ok) {
            error.value = data.message || 'Failed to send inquiry'
            emit('error', error.value)
            return
        }
        emit('success', data)
        closeModal()
    } catch (err) {
        console.error('Error submitting lead:', err)
        error.value = 'An unexpected error occurred'
        emit('error', error.value)
    } finally {
        loading.value = false
    }
}

const closeModal = () => {
    emit('close')
    // Reset form
    form.value = {
        name: '',
        phone: '',
        email: '',
        message: ''
    }
    error.value = null
}
</script>
