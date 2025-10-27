<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Frontend Header -->
        <FrontendHeader :current-path="$page.url" />

        <!-- Main Content -->
        <div class="container-mobile py-8">
            <div class="max-w-7xl mx-auto">
                <!-- Back Button -->
                <div class="mb-6">
                    <a :href="'/marketplace/properties/' + props.property.id" class="inline-flex items-center text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                        </svg>
                        Back to Property
                    </a>
                </div>

                <!-- Alert Messages -->
                <div v-if="errorMessage" class="mb-6">
                    <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700" v-html="errorMessage"></p>
                            </div>
                            <div class="ml-auto pl-3">
                                <div class="-mx-1.5 -my-1.5">
                                    <button @click="errorMessage = ''" class="inline-flex text-red-400 hover:text-red-500">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="successMessage" class="mb-6">
                    <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ successMessage }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column - Contact Form -->
                    <div class="bg-white rounded-xl shadow-soft overflow-hidden">
                        <div class="p-6">
                            <h1 class="text-2xl font-bold text-gray-900 mb-2">Contact Property Owner</h1>
                            <p class="text-gray-600 mb-6">Send a message regarding "{{ props.property.title }}"</p>

                            <!-- Organization Info -->
                            <div class="flex items-center p-4 bg-gray-50 rounded-lg mb-6">
                                <div class="flex-shrink-0">
                                    <div v-if="props.property.organization.logo" class="h-12 w-12 rounded-full overflow-hidden">
                                        <img :src="props.property.organization.logo" :alt="props.property.organization.name" class="h-full w-full object-cover">
                                    </div>
                                    <div v-else class="h-12 w-12 rounded-full bg-sky-100 flex items-center justify-center">
                                        <svg class="h-6 w-6 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-900">{{ props.property.organization.name }}</h3>
                                    <p class="text-sm text-gray-600">Property Owner</p>
                                </div>
                            </div>

                            <!-- Contact Form -->
                            <form @submit.prevent="submitForm" class="space-y-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700">Your Name</label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        :class="[
                                            'mt-1 block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-sky-500 sm:text-sm',
                                            formErrors.name 
                                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                                : 'border-gray-300 focus:border-sky-500'
                                        ]"
                                    />
                                    <p v-if="formErrors.name" class="mt-1 text-sm text-red-600">{{ formErrors.name }}</p>
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        type="email"
                                        :class="[
                                            'mt-1 block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-sky-500 sm:text-sm',
                                            formErrors.email 
                                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                                : 'border-gray-300 focus:border-sky-500'
                                        ]"
                                    />
                                    <p v-if="formErrors.email" class="mt-1 text-sm text-red-600">{{ formErrors.email }}</p>
                                </div>

                                <div>
                                    <label for="phone" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                    <input
                                        id="phone"
                                        v-model="form.phone"
                                        type="tel"
                                        :class="[
                                            'mt-1 block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-sky-500 sm:text-sm',
                                            formErrors.phone 
                                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                                : 'border-gray-300 focus:border-sky-500'
                                        ]"
                                    />
                                    <p v-if="formErrors.phone" class="mt-1 text-sm text-red-600">{{ formErrors.phone }}</p>
                                </div>

                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700">Message</label>
                                    <textarea
                                        id="message"
                                        v-model="form.message"
                                        rows="4"
                                        :class="[
                                            'mt-1 block w-full rounded-md border px-3 py-2 focus:outline-none focus:ring-sky-500 sm:text-sm',
                                            formErrors.message 
                                                ? 'border-red-300 focus:border-red-500 focus:ring-red-500' 
                                                : 'border-gray-300 focus:border-sky-500'
                                        ]"
                                        :placeholder="'I am interested in ' + props.property.title + '...'"
                                    ></textarea>
                                    <p v-if="formErrors.message" class="mt-1 text-sm text-red-600">{{ formErrors.message }}</p>
                                </div>

                                <div class="pt-4">
                                    <button
                                        type="submit"
                                        :disabled="processing"
                                        class="w-full btn btn-primary flex items-center justify-center"
                                    >
                                        <svg v-if="processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span v-if="processing">Sending Message...</span>
                                        <span v-else>Send Message</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column - Property Details -->
                    <div class="space-y-6">
                        <!-- Property Image Gallery -->
                        <div class="bg-white rounded-xl shadow-soft overflow-hidden">
                            <div v-if="props.property.images && props.property.images.length > 0" class="aspect-w-16 aspect-h-9">
                                <img :src="'/storage/' + props.property.images[0]" :alt="props.property.title" class="w-full h-full object-cover">
                            </div>
                            <div v-else class="aspect-w-16 aspect-h-9 bg-gray-100 flex items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Property Info -->
                        <div class="bg-white rounded-xl shadow-soft p-6">
                            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ props.property.title }}</h2>
                            
                            <div class="flex items-baseline justify-between mb-4">
                                <div class="text-2xl font-bold text-sky-600">₹{{ formatPrice(props.property.price) }}</div>
                                <div class="text-sm text-gray-500">{{ props.property.property_type }}</div>
                            </div>

                            <div class="flex items-center text-gray-500 mb-4">
                                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ props.property.locality }}, {{ props.property.city }}
                            </div>

                            <!-- Property Features -->
                            <div class="grid grid-cols-2 gap-4 mb-6">
                                <div v-if="props.property.bedrooms" class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                                    </svg>
                                    <span>{{ props.property.bedrooms }} Bedrooms</span>
                                </div>
                                <div v-if="props.property.bathrooms" class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v4H3zm0 5h18m-9 3v7m0 0v3m0-3h3m-3 0H9"></path>
                                    </svg>
                                    <span>{{ props.property.bathrooms }} Bathrooms</span>
                                </div>
                                <div v-if="props.property.area_sqft" class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"></path>
                                    </svg>
                                    <span>{{ props.property.area_sqft }} sq ft</span>
                                </div>
                                <div v-if="props.property.furnished_status" class="flex items-center">
                                    <svg class="h-5 w-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                    </svg>
                                    <span class="capitalize">{{ props.property.furnished_status }}</span>
                                </div>
                            </div>

                            <!-- Property Description -->
                            <div v-if="props.property.description" class="border-t pt-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">About this property</h3>
                                <p class="text-gray-600 whitespace-pre-line">{{ props.property.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import FrontendHeader from '../../Components/FrontendHeader.vue'

const props = defineProps({
    property: {
        type: Object,
        required: true
    }
})

const processing = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const form = ref({
    name: '',
    email: '',
    phone: '',
    message: ''
})
const formErrors = ref({})

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN', {
        style: 'decimal',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(price)
}

const validateForm = () => {
    const errors = {}
    
    if (!form.value.name) {
        errors.name = 'Name is required'
    }
    
    if (!form.value.email) {
        errors.email = 'Email is required'
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.value.email)) {
        errors.email = 'Please enter a valid email address'
    }
    
    if (!form.value.phone) {
        errors.phone = 'Phone number is required'
    } else if (!/^([0-9\s\-\+\(\)]*)$/.test(form.value.phone)) {
        errors.phone = 'Please enter a valid phone number'
    }
    
    if (!form.value.message) {
        errors.message = 'Message is required'
    } else if (form.value.message.length < 10) {
        errors.message = 'Message must be at least 10 characters long'
    }
    
    formErrors.value = errors
    return Object.keys(errors).length === 0
}

const clearMessages = () => {
    errorMessage.value = ''
    successMessage.value = ''
    formErrors.value = {}
}

const submitForm = async () => {
    clearMessages()
    
    if (!validateForm()) {
        errorMessage.value = 'Please correct the errors in the form'
        return
    }
    
    processing.value = true
    
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
            if (response.status === 422) {
                // Validation errors
                formErrors.value = data.errors
                errorMessage.value = 'Please correct the errors in the form.'
                return
            } else if (response.status === 429) {
                // Rate limit error
                errorMessage.value = data.message
                return
            } else if (response.status === 400) {
                // Bad request (e.g., property no longer available)
                errorMessage.value = data.message
                setTimeout(() => {
                    window.location.href = '/marketplace/search'
                }, 3000)
                return
            }
            throw new Error(data.message || 'Failed to send message')
        }

        // Show success message and clear form
        successMessage.value = data.message
        form.value = {
            name: '',
            email: '',
            phone: '',
            message: ''
        }

        // Redirect after showing success message
        setTimeout(() => {
            router.visit(`/marketplace/properties/${props.property.id}`, {
                preserveState: true,
                preserveScroll: true
            })
        }, 3000)
    } catch (error) {
        console.error('Error sending message:', error)
        alert('Failed to send message. Please try again.')
    } finally {
        processing.value = false
    }
}
</script>

<style scoped>
.container-mobile {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;
}

@media (min-width: 640px) {
    .container-mobile {
        padding: 0 2rem;
    }
}
</style>