<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-indigo-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-grid-pattern opacity-5"></div>
        
        <div class="max-w-4xl w-full space-y-8 relative z-10">
            <!-- Header Section -->
            <div class="text-center">
                <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-r from-sky-800 to-sky-900 shadow-large mb-6">
                    <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                
                <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 mb-4">
                    Choose Your Account Type
                </h1>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Select the type of account that best fits your needs. You can always change your preferences later.
                </p>
            </div>
            
            <!-- Account Type Cards -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Individual User Card -->
                <div class="card hover:shadow-xl transition-all duration-300 group">
                    <div class="card-body text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-sky-100 rounded-2xl mx-auto mb-6 group-hover:bg-sky-200 transition-colors">
                            <svg class="w-8 h-8 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Individual User</h2>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Perfect for individuals looking to find and rent properties. Browse thousands of properties, save favorites, and contact property owners directly.
                        </p>
                        
                        <!-- Features List -->
                        <div class="text-left mb-8">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">What you can do:</h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Browse and search properties
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Save favorite properties
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Contact property owners
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Get property recommendations
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Track your inquiries
                                </li>
                            </ul>
                        </div>
                        
                        <a href="/user/register" class="btn-primary w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                            Create Individual Account
                        </a>
                        
                        <p class="text-xs text-gray-500 mt-3">
                            Already have an account? <a href="/user/login" class="text-sky-800 hover:text-sky-700">Sign in</a>
                        </p>
                    </div>
                </div>
                
                <!-- Organization Card -->
                <div class="card hover:shadow-xl transition-all duration-300 group">
                    <div class="card-body text-center">
                        <div class="flex items-center justify-center w-16 h-16 bg-red-100 rounded-2xl mx-auto mb-6 group-hover:bg-red-200 transition-colors">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        
                        <h2 class="text-2xl font-bold text-gray-900 mb-4">Organization</h2>
                        <p class="text-gray-600 mb-6 leading-relaxed">
                            Ideal for property managers, real estate companies, and landlords. Manage multiple properties, tenants, and finances all in one place.
                        </p>
                        
                        <!-- Features List -->
                        <div class="text-left mb-8">
                            <h3 class="text-sm font-semibold text-gray-900 mb-3">What you can do:</h3>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    List and manage properties
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Manage tenants and leases
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Track rent payments
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Generate invoices and reports
                                </li>
                                <li class="flex items-center">
                                    <svg class="w-4 h-4 text-green-500 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Handle maintenance requests
                                </li>
                            </ul>
                        </div>
                        
                        <a href="/register" class="btn-danger w-full">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            Create Organization Account
                        </a>
                        
                        <p class="text-xs text-gray-500 mt-3">
                            Already have an account? <a href="/login" class="text-red-600 hover:text-red-500">Sign in</a>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Additional Info -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Not sure which account type is right for you? 
                    <a href="/contact" class="text-sky-800 hover:text-sky-700 font-medium">Contact our support team</a> for assistance.
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
// No JavaScript needed for this static page
</script>
