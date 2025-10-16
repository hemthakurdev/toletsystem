<template>
    <div class="min-h-screen bg-gradient-to-br from-slate-50 via-sky-50 to-indigo-100">
        <!-- Navigation -->
        <nav class="glass sticky top-0 z-50 backdrop-blur-md">
            <div class="container-mobile">
                <div class="flex justify-between items-center h-16">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-sky-800 to-sky-900 bg-clip-text text-transparent">
                                SaleMitra
                            </h1>
                        </div>
                    </div>
                        <div class="hidden sm:flex items-center space-x-4">
                            <a href="/user/login" class="text-gray-600 hover:text-gray-900 transition-colors duration-200">Login</a>
                            <div class="relative group">
                                <a href="/user/register" class="btn-primary">Get Started</a>
                                <!-- Dropdown Menu -->
                                <div class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="p-4">
                                    <h3 class="text-sm font-semibold text-gray-900 mb-3">Choose Account Type</h3>
                                    <div class="mb-3">
                                        <a href="/choose-account-type" class="text-xs text-sky-800 hover:text-sky-700 underline">
                                            Not sure? Compare account types →
                                        </a>
                                    </div>
                                    <div class="space-y-3">
                                            <a href="/user/register" class="block p-3 rounded-lg border border-gray-200 hover:border-sky-400 hover:bg-sky-50 transition-colors">
                                                <div class="flex items-start space-x-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 bg-sky-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-medium text-gray-900">Individual User</h4>
                                                        <p class="text-xs text-gray-600">Find and rent properties</p>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="/register" class="block p-3 rounded-lg border border-gray-200 hover:border-red-300 hover:bg-red-50 transition-colors">
                                                <div class="flex items-start space-x-3">
                                                    <div class="flex-shrink-0">
                                                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                                                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h4 class="text-sm font-medium text-gray-900">Organization</h4>
                                                        <p class="text-xs text-gray-600">Manage properties & tenants</p>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <!-- Mobile Menu Button -->
                    <button 
                        @click="toggleMobileMenu"
                        class="sm:hidden p-2 text-gray-600 hover:text-gray-900 transition-colors"
                    >
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
                <!-- Mobile Menu -->
                    <div v-if="showMobileMenu" class="sm:hidden py-4 border-t border-gray-200">
                        <div class="flex flex-col space-y-3">
                            <a href="/user/login" class="text-gray-600 hover:text-gray-900 transition-colors">Login</a>
                            <div class="space-y-2">
                                <p class="text-sm font-medium text-gray-700">Choose Account Type:</p>
                                <a href="/user/register" class="block w-full p-3 rounded-lg border border-sky-200 bg-sky-50 text-sky-700 hover:bg-sky-100 transition-colors text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Individual User</span>
                                    </div>
                                    <p class="text-xs text-sky-800 mt-1">Find and rent properties</p>
                                </a>
                                <a href="/register" class="block w-full p-3 rounded-lg border border-red-200 bg-red-50 text-red-700 hover:bg-red-100 transition-colors text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                        </svg>
                                        <span class="text-sm font-medium">Organization</span>
                                    </div>
                                    <p class="text-xs text-red-600 mt-1">Manage properties & tenants</p>
                                </a>
                            </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="container-mobile py-12 sm:py-20">
            <div class="text-center fade-in">
                <h1 class="text-3xl sm:text-4xl md:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                    Complete Property Management
                    <span class="bg-gradient-to-r from-sky-800 to-sky-900 bg-clip-text text-transparent">Made Simple</span>
                </h1>
                <p class="text-lg sm:text-xl text-gray-600 mb-8 max-w-3xl mx-auto leading-relaxed">
                    Manage your rental properties, tenants, and finances all in one place. 
                    Plus, list your properties on our marketplace to reach more tenants.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/user/register" class="btn-primary shadow-glow">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Find Properties
                    </a>
                    <a href="/register" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        List Properties
                    </a>
                    <a href="#features" class="btn-secondary">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Learn More
                    </a>
                </div>
            </div>
        </div>

        <!-- Features Section -->
        <div id="features" class="bg-white py-12 sm:py-20">
            <div class="container-mobile">
                <div class="text-center mb-12 sm:mb-16">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">
                        Everything You Need to Manage Properties
                    </h2>
                    <p class="text-lg sm:text-xl text-gray-600 max-w-2xl mx-auto">
                        From tenant management to financial tracking, we've got you covered.
                    </p>
                </div>

                <div class="grid-responsive">
                    <!-- Feature 1 -->
                    <div class="card text-center">
                        <div class="w-12 h-12 bg-sky-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-sky-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Property Management</h3>
                        <p class="text-gray-600">
                            Add, edit, and manage all your properties with detailed information and photos.
                        </p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="card text-center">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Tenant Management</h3>
                        <p class="text-gray-600">
                            Keep track of tenant information, lease agreements, and rent collection.
                        </p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="card text-center">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Financial Tracking</h3>
                        <p class="text-gray-600">
                            Generate invoices, track payments, and manage expenses with detailed reports.
                        </p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="card text-center">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Property Marketplace</h3>
                        <p class="text-gray-600">
                            List your properties on our marketplace to reach more potential tenants.
                        </p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="card text-center">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Analytics & Reports</h3>
                        <p class="text-gray-600">
                            Get insights into your property performance with detailed analytics.
                        </p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="card text-center">
                        <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center mx-auto mb-4">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Mobile Ready</h3>
                        <p class="text-gray-600">
                            Access your property management dashboard from anywhere with our mobile-friendly interface.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="bg-sky-800 py-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold text-white mb-4">
                    Ready to Simplify Your Property Management?
                </h2>
                <p class="text-xl text-sky-100 mb-8">
                    Join thousands of property owners who trust SaleMitra to manage their rentals.
                </p>
                <a href="/register" class="bg-white text-sky-800 hover:bg-gray-100 font-medium py-4 px-8 rounded-lg text-lg transition-colors duration-200 shadow-lg hover:shadow-xl flex items-center justify-center mx-auto w-fit">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Start Your Free Trial
                </a>
            </div>
        </div>

        <!-- Footer -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                    <div>
                        <h3 class="text-xl font-bold mb-4">SaleMitra</h3>
                        <p class="text-gray-400">
                            Complete property management solution for modern landlords.
                        </p>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Product</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white">Features</a></li>
                            <li><a href="#" class="hover:text-white">Pricing</a></li>
                            <li><a href="#" class="hover:text-white">API</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Support</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white">Help Center</a></li>
                            <li><a href="#" class="hover:text-white">Contact Us</a></li>
                            <li><a href="#" class="hover:text-white">Status</a></li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold mb-4">Legal</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#" class="hover:text-white">Privacy Policy</a></li>
                            <li><a href="#" class="hover:text-white">Terms of Service</a></li>
                            <li><a href="#" class="hover:text-white">Cookie Policy</a></li>
                        </ul>
                    </div>
                </div>
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                    <p>&copy; 2024 SaleMitra. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const showMobileMenu = ref(false)

const toggleMobileMenu = () => {
    showMobileMenu.value = !showMobileMenu.value
}
</script>
