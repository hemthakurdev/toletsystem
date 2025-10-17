<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        
        <div class="max-w-2xl w-full space-y-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center">
                <!-- Logo/Brand -->
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-r from-sky-800 to-sky-900 shadow-large mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                
                <h2 class="text-3xl font-bold text-gray-900 mb-2">
                    Create your organization
                </h2>
                <p class="text-gray-600">
                    Start your property management journey with SaleMitra
                </p>
            </div>
            <!-- Registration Form -->
            <div class="card">
                <div class="card-body">
                    <form class="space-y-8" @submit.prevent="register">
                        <!-- Organization Details Section -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 bg-sky-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-5 w-5 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Organization Details</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="space-y-2">
                                    <label for="organization_name" class="block text-sm font-medium text-gray-700">
                                        Organization Name
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        <input
                                            id="organization_name"
                                            v-model="form.organization_name"
                                            type="text"
                                            required
                                            class="form-input pl-10"
                                            placeholder="Your Company Name"
                                        />
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <label for="organization_email" class="block text-sm font-medium text-gray-700">
                                        Organization Email
                                    </label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                            </svg>
                                        </div>
                                        <input
                                            id="organization_email"
                                            v-model="form.organization_email"
                                            type="email"
                                            required
                                            class="form-input pl-10"
                                            placeholder="contact@yourcompany.com"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Admin User Details Section -->
                        <div class="space-y-6">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="flex-shrink-0">
                                    <div class="h-8 w-8 bg-green-100 rounded-lg flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <h3 class="text-lg font-semibold text-gray-900">Admin User Details</h3>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                                            placeholder="Your Full Name"
                                        />
                                    </div>
                                </div>
                                
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
                                            placeholder="your@email.com"
                                        />
                                    </div>
                                </div>
                                
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
                                </div>
                                
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
                                            placeholder="Confirm your password"
                                        />
                                    </div>
                                </div>
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
                                {{ loading ? 'Creating Organization...' : 'Create Organization' }}
                            </button>
                        </div>

                        <!-- Terms and Privacy -->
                        <div class="text-center">
                            <p class="text-sm text-gray-500">
                                By creating an account, you agree to our
                                <a href="#" class="text-sky-800 hover:text-sky-500 transition-colors">Terms of Service</a>
                                and
                                <a href="#" class="text-sky-800 hover:text-sky-500 transition-colors">Privacy Policy</a>
                            </p>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Sign In Link -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Already have an account?
                    <a href="/login" class="font-medium text-sky-800 hover:text-sky-500 transition-colors">
                        Sign in to existing account
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

const form = ref({
    organization_name: '',
    organization_email: '',
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

const loading = ref(false)
const showPassword = ref(false)

const register = async () => {
    loading.value = true
    
    try {
        const response = await fetch('/register', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                ...form.value,
                terms_accepted: true
            })
        })
        
        const data = await response.json()
        
        if (data.success) {
            // Registration successful, redirect to dashboard
            router.visit('/dashboard')
        } else {
            // Handle validation errors
            console.error('Registration failed:', data.message)
            if (data.errors) {
                console.error('Validation errors:', data.errors)
            }
            // You could show error messages to the user here
            alert('Registration failed: ' + data.message)
        }
    } catch (error) {
        console.error('Registration error:', error)
        alert('Registration failed. Please try again.')
    } finally {
        loading.value = false
    }
}
</script>
