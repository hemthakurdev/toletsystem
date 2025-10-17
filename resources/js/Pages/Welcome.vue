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
                                <a href="/user/register" class="btn btn-primary">Get Started</a>
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
        <div class="container-mobile py-12 sm:py-20 relative">
            <!-- Floating Elements -->
            <div class="absolute top-20 left-10 w-20 h-20 bg-sky-200/30 rounded-full blur-xl animate-float"></div>
            <div class="absolute top-40 right-20 w-32 h-32 bg-indigo-200/30 rounded-full blur-xl animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-20 left-1/4 w-16 h-16 bg-purple-200/30 rounded-full blur-xl animate-float" style="animation-delay: 4s;"></div>
            
            <div class="text-center fade-in-scale relative z-10">
                <div class="inline-flex items-center px-4 py-2 bg-white/80 backdrop-blur-sm rounded-full border border-white/20 shadow-lg mb-8">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse mr-2"></span>
                    <span class="text-sm font-medium text-gray-700">Trusted by 10,000+ property owners</span>
                </div>
                
                <h1 class="text-4xl sm:text-5xl md:text-7xl font-bold text-gray-900 mb-6 leading-tight">
                    Complete Property Management
                    <span class="bg-gradient-to-r from-sky-600 via-sky-700 to-sky-800 bg-clip-text text-transparent animate-gradient-shift">Made Simple</span>
                </h1>
                <p class="text-xl sm:text-2xl text-gray-600 mb-8 max-w-4xl mx-auto leading-relaxed">
                    Manage your rental properties, tenants, and finances all in one place. 
                    Plus, list your properties on our marketplace to reach more tenants.
                </p>
                <div class="flex flex-col sm:flex-row gap-6 justify-center items-center">
                    <a href="/user/register" class="btn btn-primary shadow-glow-hover group">
                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Find Properties
                    </a>
                    <a href="/register" class="btn btn-secondary group">
                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        List Properties
                    </a>
                    <a href="#features" class="btn btn-ghost group">
                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Learn More
                    </a>
                </div>
                
                <!-- Trust Indicators -->
                <div class="mt-12 flex flex-wrap justify-center items-center gap-8 opacity-60">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-600">SSL Secured</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-600">24/7 Support</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium text-gray-600">Mobile Ready</span>
                    </div>
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
                    <div class="card-premium text-center group cursor-pointer">
                        <div class="icon-container icon-sky mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Property Management</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Add, edit, and manage all your properties with detailed information and photos.
                        </p>
                        <div class="mt-4 text-sm text-sky-600 font-medium">
                            Learn more →
                        </div>
                    </div>

                    <!-- Feature 2 -->
                    <div class="card-success text-center group cursor-pointer">
                        <div class="icon-container icon-emerald mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Tenant Management</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Keep track of tenant information, lease agreements, and rent collection.
                        </p>
                        <div class="mt-4 text-sm text-emerald-600 font-medium">
                            Learn more →
                        </div>
                    </div>

                    <!-- Feature 3 -->
                    <div class="card text-center group cursor-pointer">
                        <div class="icon-container icon-amber mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Financial Tracking</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Generate invoices, track payments, and manage expenses with detailed reports.
                        </p>
                        <div class="mt-4 text-sm text-amber-600 font-medium">
                            Learn more →
                        </div>
                    </div>

                    <!-- Feature 4 -->
                    <div class="card text-center group cursor-pointer">
                        <div class="icon-container bg-purple-100/80 text-purple-600 border border-purple-200/50 mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Property Marketplace</h3>
                        <p class="text-gray-600 leading-relaxed">
                            List your properties on our marketplace to reach more potential tenants.
                        </p>
                        <div class="mt-4 text-sm text-purple-600 font-medium">
                            Learn more →
                        </div>
                    </div>

                    <!-- Feature 5 -->
                    <div class="card text-center group cursor-pointer">
                        <div class="icon-container icon-red mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Analytics & Reports</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Get insights into your property performance with detailed analytics.
                        </p>
                        <div class="mt-4 text-sm text-red-600 font-medium">
                            Learn more →
                        </div>
                    </div>

                    <!-- Feature 6 -->
                    <div class="card text-center group cursor-pointer">
                        <div class="icon-container bg-indigo-100/80 text-indigo-600 border border-indigo-200/50 mx-auto mb-4 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Mobile Ready</h3>
                        <p class="text-gray-600 leading-relaxed">
                            Access your property management dashboard from anywhere with our mobile-friendly interface.
                        </p>
                        <div class="mt-4 text-sm text-indigo-600 font-medium">
                            Learn more →
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="relative bg-gradient-to-br from-sky-800 via-sky-900 to-indigo-900 py-20 overflow-hidden">
            <!-- Background Pattern -->
            <div class="absolute inset-0 bg-grid-pattern opacity-10"></div>
            <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-sky-600/20 to-transparent"></div>
            
            <!-- Floating Elements -->
            <div class="absolute top-10 left-10 w-32 h-32 bg-white/10 rounded-full blur-2xl animate-float"></div>
            <div class="absolute bottom-10 right-10 w-40 h-40 bg-indigo-400/20 rounded-full blur-2xl animate-float" style="animation-delay: 3s;"></div>
            
            <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full border border-white/20 mb-8">
                    <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse mr-2"></span>
                    <span class="text-sm font-medium text-white/90">Join 10,000+ property owners</span>
                </div>
                
                <h2 class="text-4xl sm:text-5xl font-bold text-white mb-6 leading-tight">
                    Ready to Simplify Your
                    <span class="bg-gradient-to-r from-emerald-400 to-cyan-400 bg-clip-text text-transparent">Property Management?</span>
                </h2>
                <p class="text-xl text-sky-100 mb-10 max-w-3xl mx-auto leading-relaxed">
                    Join thousands of property owners who trust SaleMitra to manage their rentals. 
                    Start your free trial today and experience the difference.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center items-center">
                    <a href="/register" class="bg-white text-sky-800 hover:bg-gray-50 font-semibold py-4 px-8 rounded-xl text-lg transition-all duration-300 shadow-2xl hover:shadow-3xl flex items-center justify-center group transform hover:-translate-y-1">
                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                        Start Your Free Trial
                    </a>
                    <a href="#features" class="bg-white/10 text-white hover:bg-white/20 font-semibold py-4 px-8 rounded-xl text-lg transition-all duration-300 backdrop-blur-sm border border-white/20 flex items-center justify-center group">
                        <svg class="w-5 h-5 mr-2 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Watch Demo
                    </a>
                </div>
                
                <!-- Trust Badges -->
                <div class="mt-12 flex flex-wrap justify-center items-center gap-8 opacity-80">
                    <div class="flex items-center space-x-2 text-white/80">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium">No Credit Card Required</span>
                    </div>
                    <div class="flex items-center space-x-2 text-white/80">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium">14-Day Free Trial</span>
                    </div>
                    <div class="flex items-center space-x-2 text-white/80">
                        <svg class="w-5 h-5 text-emerald-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-sm font-medium">Cancel Anytime</span>
                    </div>
                </div>
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
