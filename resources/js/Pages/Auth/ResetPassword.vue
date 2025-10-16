<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        
        <div class="max-w-md w-full space-y-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center">
                <!-- Icon -->
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-r from-green-600 to-green-700 shadow-large mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Reset your password
                </h2>
                <p class="text-gray-600">
                    Enter your new password below
                </p>
            </div>
            
            <!-- Reset Password Form Card -->
            <div class="card">
                <div class="card-body">
                    <form class="space-y-6" @submit.prevent="resetPassword">
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
                        
                        <!-- Email Field (Read-only) -->
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
                                    readonly
                                    class="form-input pl-10 bg-gray-50 text-gray-500 cursor-not-allowed"
                                />
                            </div>
                        </div>

                        <!-- New Password Field -->
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                New Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="password"
                                    v-model="form.password"
                                    name="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    required
                                    class="form-input pl-10 pr-10"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.password }"
                                    placeholder="Enter your new password"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                >
                                    <svg v-if="showPassword" class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L3 3m6.878 6.878L21 21"></path>
                                    </svg>
                                    <svg v-else class="h-5 w-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                    </svg>
                                </button>
                            </div>
                            <div v-if="errors.password" class="text-sm text-red-600">
                                {{ errors.password[0] }}
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-2">
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                                Confirm New Password
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="password_confirmation"
                                    v-model="form.password_confirmation"
                                    name="password_confirmation"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="new-password"
                                    required
                                    class="form-input pl-10"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.password_confirmation }"
                                    placeholder="Confirm your new password"
                                />
                            </div>
                            <div v-if="errors.password_confirmation" class="text-sm text-red-600">
                                {{ errors.password_confirmation[0] }}
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div>
                            <button
                                type="submit"
                                :disabled="loading"
                                class="btn-success w-full"
                            >
                                <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ loading ? 'Resetting...' : 'Reset Password' }}
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
import { ref, reactive, onMounted } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const errors = ref({})
const showPassword = ref(false)

const form = reactive({
    email: '',
    password: '',
    password_confirmation: '',
    token: ''
})

onMounted(() => {
    // Get token and email from URL parameters
    const urlParams = new URLSearchParams(window.location.search)
    form.token = urlParams.get('token') || page.props.token
    form.email = urlParams.get('email') || page.props.email
})

const resetPassword = async () => {
    loading.value = true
    successMessage.value = ''
    errorMessage.value = ''
    errors.value = {}
    
    try {
        const response = await axios.post('/reset-password/custom', {
            email: form.email,
            password: form.password,
            password_confirmation: form.password_confirmation,
            token: form.token
        })
        
        if (response.data.success) {
            successMessage.value = response.data.message
            // Redirect to login after 3 seconds
            setTimeout(() => {
                window.location.href = '/login'
            }, 3000)
        } else {
            errorMessage.value = response.data.message || 'Failed to reset password'
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            errorMessage.value = error.response?.data?.message || 'Failed to reset password'
        }
    } finally {
        loading.value = false
    }
}
</script>
