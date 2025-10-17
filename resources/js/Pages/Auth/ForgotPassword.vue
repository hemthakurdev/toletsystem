<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        
        <div class="max-w-md w-full space-y-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center">
                <!-- Icon -->
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-r from-sky-800 to-sky-900 shadow-large mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m0 0a2 2 0 012 2m-2-2a2 2 0 00-2 2m2-2V5a2 2 0 00-2-2H9a2 2 0 00-2 2v2m0 0a2 2 0 00-2 2v6a2 2 0 002 2h6a2 2 0 002-2V9a2 2 0 00-2-2H9z"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Forgot your password?
                </h2>
                <p class="text-gray-600">
                    No worries! Enter your email address and we'll send you a link to reset your password.
                </p>
            </div>
            
            <!-- Forgot Password Form Card -->
            <div class="card">
                <div class="card-body">
                    <form class="space-y-6" @submit.prevent="sendResetLink">
                        <!-- Success Message -->
                        <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ successMessage }}
                            </div>
                        </div>
                        
                        <!-- Error Message -->
                        <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ errorMessage }}
                            </div>
                        </div>
                        
                        <!-- Email Field -->
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email address
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                    </svg>
                                </div>
                                <input
                                    id="email"
                                    v-model="form.email"
                                    name="email"
                                    type="email"
                                    autocomplete="email"
                                    required
                                    class="form-input pl-10"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.email }"
                                    placeholder="Enter your email address"
                                />
                            </div>
                            <div v-if="errors.email" class="text-sm text-red-600">
                                {{ errors.email[0] }}
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="btn btn-primary w-full"
                            >
                                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ loading ? 'Sending...' : 'Send Reset Link' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Back to Login Link -->
            <div class="text-center">
                <Link :href="route('login')" class="font-medium text-sky-800 hover:text-sky-500 transition-colors">
                    ← Back to Login
                </Link>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link } from '@inertiajs/vue3'
import axios from 'axios'

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const errors = ref({})

const form = reactive({
    email: ''
})

const sendResetLink = async () => {
    loading.value = true
    successMessage.value = ''
    errorMessage.value = ''
    errors.value = {}
    
    try {
        const response = await axios.post('/forgot-password/custom', {
            email: form.email
        })
        
        if (response.data.success) {
            successMessage.value = response.data.message
            form.email = ''
        } else {
            errorMessage.value = response.data.message || 'Failed to send reset link'
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            errorMessage.value = error.response?.data?.message || 'Failed to send reset link'
        }
    } finally {
        loading.value = false
    }
}
</script>
