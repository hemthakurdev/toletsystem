<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <Header current-path="/properties" />

        <!-- Main Content -->
        <div class="container-mobile py-8">
            <div v-if="props.property" class="max-w-6xl mx-auto">
                <!-- Property Header -->
                <div class="bg-white rounded-xl shadow-soft p-6 mb-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-4">
                        <div class="flex-1">
                            <a href="/properties" class="btn btn-secondary mb-4">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Properties
                            </a>
                        </div>
                    </div>
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ props.property.title }}</h1>
                            <div class="flex items-center text-gray-600 mb-4">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span>{{ props.property.locality }}, {{ props.property.city }} - {{ props.property.pincode }}</span>
                            </div>
                            <div class="flex items-center space-x-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    {{ formatPropertyType(props.property.property_type) }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                    {{ formatAvailabilityStatus(props.property.availability_status) }}
                                </span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                    {{ formatFurnishedStatus(props.property.furnished_status) }}
                                </span>
                            </div>
                        </div>
                        <div class="mt-4 lg:mt-0">
                            <div class="text-right">
                                <div class="text-3xl font-bold text-sky-600">₹{{ formatPrice(props.property.price) }}</div>
                                <div class="text-sm text-gray-500">per month</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Property Images -->
                <div class="bg-white rounded-xl shadow-soft p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Property Images</h2>
                    <div v-if="props.property.images && props.property.images.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="(image, index) in props.property.images" :key="index" class="relative">
                            <ImagePlaceholder 
                                :src="image"
                                :alt="`Property image ${index + 1}`"
                                size="full"
                                shape="square"
                                className="rounded-lg"
                            />
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="mt-2">No images available</p>
                    </div>
                </div>

                <!-- Property Details -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Description -->
                        <div class="bg-white rounded-xl shadow-soft p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Description</h2>
                            <div v-if="props.property.short_description" class="mb-4">
                                <h3 class="font-medium text-gray-700 mb-2">Short Description</h3>
                                <p class="text-gray-600">{{ props.property.short_description }}</p>
                            </div>
                            <div v-if="props.property.long_description">
                                <h3 class="font-medium text-gray-700 mb-2">Detailed Description</h3>
                                <p class="text-gray-600 whitespace-pre-line">{{ props.property.long_description }}</p>
                            </div>
                            <div v-if="!props.property.short_description && !props.property.long_description" class="text-gray-500">
                                No description available
                            </div>
                        </div>

                        <!-- Property Specifications -->
                        <div class="bg-white rounded-xl shadow-soft p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Property Specifications</h2>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <div v-if="props.property.bedrooms" class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-sky-600">{{ props.property.bedrooms }}</div>
                                    <div class="text-sm text-gray-600">Bedrooms</div>
                                </div>
                                <div v-if="props.property.bathrooms" class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-sky-600">{{ props.property.bathrooms }}</div>
                                    <div class="text-sm text-gray-600">Bathrooms</div>
                                </div>
                                <div v-if="props.property.area_sqft" class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-sky-600">{{ props.property.area_sqft }}</div>
                                    <div class="text-sm text-gray-600">Sq Ft</div>
                                </div>
                                <div v-if="props.property.floor" class="text-center p-4 bg-gray-50 rounded-lg">
                                    <div class="text-2xl font-bold text-sky-600">{{ props.property.floor }}</div>
                                    <div class="text-sm text-gray-600">Floor</div>
                                </div>
                            </div>
                        </div>

                        <!-- Amenities -->
                        <div v-if="props.property.amenities && props.property.amenities.length > 0" class="bg-white rounded-xl shadow-soft p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Amenities</h2>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                                <div v-for="amenity in props.property.amenities" :key="amenity" class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    <span class="text-gray-700 capitalize">{{ amenity.replace('_', ' ') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Contact Information -->
                        <div class="bg-white rounded-xl shadow-soft p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Contact Information</h2>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                    </svg>
                                    <span class="text-gray-600">+91 98765 43210</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="text-gray-600">contact@salemitra.com</span>
                                </div>
                            </div>
                            <button class="btn btn-primary w-full mt-4">
                                Contact Owner
                            </button>
                        </div>

                        <!-- Property Status -->
                        <div class="bg-white rounded-xl shadow-soft p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Property Status</h2>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Status</span>
                                    <span class="font-medium">{{ formatAvailabilityStatus(props.property.availability_status) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Published</span>
                                    <span class="font-medium">{{ props.property.published ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Featured</span>
                                    <span class="font-medium">{{ props.property.featured ? 'Yes' : 'No' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Created</span>
                                    <span class="font-medium">{{ formatDate(props.property.created_at) }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bg-white rounded-xl shadow-soft p-6">
                            <h2 class="text-xl font-semibold text-gray-900 mb-4">Actions</h2>
                            <div class="space-y-3">
                                <a :href="`/properties/${props.property.id}/edit`" class="btn btn-secondary w-full">
                                    Edit Property
                                </a>
                                <button @click="deleteProperty" class="btn btn-danger w-full">
                                    Delete Property
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Loading State -->
            <div v-else class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-sky-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Loading property details...</p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import Header from '../../Components/Header.vue'
import ImagePlaceholder from '../../Components/ImagePlaceholder.vue'

const props = defineProps({
    property: {
        type: Object,
        default: null
    }
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN').format(price)
}

const formatPropertyType = (type) => {
    const types = {
        'rent': 'For Rent',
        'sale': 'For Sale',
        'pg': 'PG',
        'commercial': 'Commercial'
    }
    return types[type] || type
}

const formatAvailabilityStatus = (status) => {
    const statuses = {
        'vacant': 'Vacant',
        'occupied': 'Occupied',
        'maintenance': 'Under Maintenance',
        'blocked': 'Blocked'
    }
    return statuses[status] || status
}

const formatFurnishedStatus = (status) => {
    const statuses = {
        'furnished': 'Furnished',
        'semi_furnished': 'Semi Furnished',
        'unfurnished': 'Unfurnished'
    }
    return statuses[status] || status
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}

const deleteProperty = async () => {
    if (confirm('Are you sure you want to delete this property? This action cannot be undone.')) {
        try {
            const response = await fetch(`/properties/${props.property.id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            
            if (response.ok) {
                window.location.href = '/properties'
            } else {
                alert('Failed to delete property. Please try again.')
            }
        } catch (error) {
            console.error('Error deleting property:', error)
            alert('Failed to delete property. Please try again.')
        }
    }
}
</script>

<style scoped>
.container-mobile {
    max-width: 80rem;
    margin: 0 auto;
    padding: 0 1rem;
}

.glass {
    background-color: rgba(255, 255, 255, 0.8);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid #e5e7eb;
}

.nav-link {
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #6b7280;
    transition: all 0.2s;
}

.nav-link:hover {
    color: #111827;
    background-color: #f3f4f6;
}

.nav-link.active {
    color: #0284c7;
    background-color: #f0f9ff;
}

.btn-primary {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    color: white;
    background-color: #0284c7;
    transition: all 0.2s;
}

.btn-primary:hover {
    background-color: #0369a1;
}

.btn-secondary {
    display: inline-flex;
    align-items: center;
    padding: 0.5rem 1rem;
    border: 1px solid #d1d5db;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    color: #374151;
    background-color: white;
    transition: all 0.2s;
}

.btn-secondary:hover {
    background-color: #f9fafb;
}

.btn-danger {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.5rem 1rem;
    border: 1px solid transparent;
    font-size: 0.875rem;
    font-weight: 500;
    border-radius: 0.375rem;
    color: white;
    background-color: #dc2626;
    transition: all 0.2s;
}

.btn-danger:hover {
    background-color: #b91c1c;
}

.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}
</style>
