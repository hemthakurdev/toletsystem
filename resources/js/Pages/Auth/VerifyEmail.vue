<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="mx-auto h-12 w-12 flex items-center justify-center rounded-full bg-yellow-100">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Verify your email address
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    We've sent a verification link to your email address
                </p>
            </div>
            
            <div class="bg-white shadow rounded-lg p-6">
                <div v-if="successMessage" class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md mb-4">
                    {{ successMessage }}
                </div>
                
                <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-4">
                    {{ errorMessage }}
                </div>
                
                <div class="text-center">
                    <div class="mb-4">
                        <svg class="mx-auto h-16 w-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        Check your email
                    </h3>
                    
                    <p class="text-sm text-gray-600 mb-6">
                        We've sent a verification link to <strong>{{ userEmail }}</strong>. 
                        Please check your email and click the link to verify your account.
                    </p>
                    
                    <div class="space-y-4">
                        <button
                            @click="resendVerification"
                            :disabled="loading || resendCooldown > 0"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-800 hover:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-800 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span v-if="loading" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Sending...
                            </span>
                            <span v-else-if="resendCooldown > 0">
                                Resend in {{ resendCooldown }}s
                            </span>
                            <span v-else>
                                Resend Verification Email
                            </span>
                        </button>
                        
                        <button
                            @click="logout"
                            class="w-full flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-800"
                        >
                            Sign Out
                        </button>
                    </div>
                </div>
                
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <div class="text-center">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Didn't receive the email?</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li>• Check your spam/junk folder</li>
                            <li>• Make sure you entered the correct email address</li>
                            <li>• Wait a few minutes and try again</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { usePage, router } from '@inertiajs/vue3'
import axios from 'axios'

const page = usePage()
const loading = ref(false)
const successMessage = ref('')
const errorMessage = ref('')
const resendCooldown = ref(0)
let cooldownTimer = null

const userEmail = ref('')

onMounted(() => {
    // Get user email from props or auth user
    userEmail.value = page.props.auth?.user?.email || 'your email address'
})

onUnmounted(() => {
    if (cooldownTimer) {
        clearInterval(cooldownTimer)
    }
})

const resendVerification = async () => {
    loading.value = true
    successMessage.value = ''
    errorMessage.value = ''
    
    try {
        const response = await axios.post('/email/verification-notification')
        
        if (response.data.success) {
            successMessage.value = response.data.message
            startCooldown()
        } else {
            errorMessage.value = response.data.message || 'Failed to send verification email'
        }
    } catch (error) {
        errorMessage.value = error.response?.data?.message || 'Failed to send verification email'
    } finally {
        loading.value = false
    }
}

const startCooldown = () => {
    resendCooldown.value = 60 // 60 seconds cooldown
    cooldownTimer = setInterval(() => {
        resendCooldown.value--
        if (resendCooldown.value <= 0) {
            clearInterval(cooldownTimer)
            cooldownTimer = null
        }
    }, 1000)
}

const logout = () => {
    router.post('/logout')
}
</script>
