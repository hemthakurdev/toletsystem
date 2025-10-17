<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        
        <div class="max-w-md w-full space-y-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center">
                <!-- Logo/Brand -->
                    <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-r from-sky-800 to-sky-900 shadow-large mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Join SaleMitra
                </h2>
                <p class="text-gray-600">
                    Create your personal account to find and manage properties
                </p>
            </div>
            
            <!-- Registration Form Card -->
            <div class="card">
                <div class="card-body">
                    <form class="space-y-6" @submit.prevent="register">
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
                        
                        <!-- Name Field -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-gray-700">
                                Full Name
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    class="form-input pl-10"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.name }"
                                    placeholder="Enter your full name"
                                />
                            </div>
                            <div v-if="errors.name" class="text-sm text-red-600">
                                {{ errors.name[0] }}
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="space-y-2">
                            <label for="email" class="block text-sm font-medium text-gray-700">
                                Email Address
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
                                    type="email"
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

                        <!-- Phone Field -->
                        <div class="space-y-2">
                            <label for="phone" class="block text-sm font-medium text-gray-700">
                                Phone Number (Optional)
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                </div>
                                <input
                                    id="phone"
                                    v-model="form.phone"
                                    type="tel"
                                    class="form-input pl-10"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.phone }"
                                    placeholder="Enter your phone number"
                                />
                            </div>
                            <div v-if="errors.phone" class="text-sm text-red-600">
                                {{ errors.phone[0] }}
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="space-y-2">
                            <label for="password" class="block text-sm font-medium text-gray-700">
                                Password
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
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    class="form-input pl-10 pr-10"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.password }"
                                    placeholder="Choose a strong password"
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
                                Confirm Password
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
                                    :type="showPassword ? 'text' : 'password'"
                                    required
                                    class="form-input pl-10"
                                    :class="{ 'border-red-500 focus:ring-red-500 focus:border-red-500': errors.password_confirmation }"
                                    placeholder="Confirm your password"
                                />
                            </div>
                            <div v-if="errors.password_confirmation" class="text-sm text-red-600">
                                {{ errors.password_confirmation[0] }}
                            </div>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="flex items-center">
                            <input
                                id="terms_accepted"
                                v-model="form.terms_accepted"
                                type="checkbox"
                                required
                                class="h-4 w-4 text-sky-800 focus:ring-sky-800 border-gray-300 rounded"
                                :class="{ 'border-red-500': errors.terms_accepted }"
                            />
                            <label for="terms_accepted" class="ml-2 block text-sm text-gray-700">
                                I agree to the
                                <a href="#" class="text-sky-800 hover:text-sky-500 transition-colors">Terms of Service</a>
                                and
                                <a href="#" class="text-sky-800 hover:text-sky-500 transition-colors">Privacy Policy</a>
                            </label>
                        </div>
                        <div v-if="errors.terms_accepted" class="text-sm text-red-600">
                            {{ errors.terms_accepted[0] }}
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
                                {{ loading ? 'Creating Account...' : 'Create Account' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sign In Link -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                        <a href="/user/login" class="font-medium text-sky-800 hover:text-sky-700 transition-colors">
                        Sign in here
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const errors = ref({})
const showPassword = ref(false)

const form = reactive({
    name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    terms_accepted: false,
})

const register = async () => {
    loading.value = true
    successMessage.value = ''
    errorMessage.value = ''
    errors.value = {}
    
    try {
        const response = await axios.post('/user/register', form)
        
        if (response.data.success) {
            successMessage.value = response.data.message
            // Redirect to user dashboard
            setTimeout(() => {
                router.visit('/user/dashboard')
            }, 1500)
        } else {
            errorMessage.value = response.data.message || 'Registration failed'
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            errorMessage.value = error.response?.data?.message || 'Registration failed. Please try again.'
        }
    } finally {
        loading.value = false
    }
}
</script>
