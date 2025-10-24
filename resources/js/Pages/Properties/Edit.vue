<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <!-- Navigation -->
        <Header current-path="/properties" />

        <!-- Main Content -->
        <div class="container-mobile py-8">
            <div v-if="props.property" class="max-w-4xl mx-auto">
                <!-- Header -->
                <div class="bg-white rounded-xl shadow-soft p-6 mb-6">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">Edit Property</h1>
                        </div>
                        <div class="mt-4 sm:mt-0">
                            <a :href="`/properties/${props.property.id}`" class="btn btn-secondary">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Back to Property
                            </a>
                        </div>
                    </div>
                    <p class="text-gray-600">Update the property information below</p>
                </div>

                <!-- Edit Form -->
                <form @submit.prevent="updateProperty" class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-sky-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-sky-900 mb-4">Basic Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Property Title *</label>
                                <input
                                    v-model="form.title"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="e.g., Beautiful 2BHK Apartment"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Property Type *</label>
                                <select v-model="form.property_type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                    <option value="">Select Type</option>
                                    <option value="rent">Rent</option>
                                    <option value="sale">Sale</option>
                                    <option value="pg">PG</option>
                                    <option value="commercial">Commercial</option>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                                <textarea
                                    v-model="form.short_description"
                                    rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="Brief description of the property..."
                                ></textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Long Description</label>
                                <textarea
                                    v-model="form.long_description"
                                    rows="4"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="Detailed description of the property..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-green-900 mb-4">Pricing</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price *</label>
                                <input
                                    v-model="form.price"
                                    type="number"
                                    required
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Security Deposit</label>
                                <input
                                    v-model="form.security_deposit"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Maintenance Charges</label>
                                <input
                                    v-model="form.maintenance_charges"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-purple-900 mb-4">Location</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">City *</label>
                                <input
                                    v-model="form.city"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="e.g., Mumbai"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Locality *</label>
                                <input
                                    v-model="form.locality"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="e.g., Andheri West"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pincode *</label>
                                <input
                                    v-model="form.pincode"
                                    type="text"
                                    required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="e.g., 400058"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
                                <input
                                    v-model="form.category"
                                    type="text"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="e.g., Apartment, Villa"
                                />
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Address Line *</label>
                                <textarea
                                    v-model="form.address_line"
                                    required
                                    rows="2"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="Complete address..."
                                ></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Property Details -->
                    <div class="bg-orange-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-orange-900 mb-4">Property Details</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bedrooms</label>
                                <input
                                    v-model="form.bedrooms"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bathrooms</label>
                                <input
                                    v-model="form.bathrooms"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Area (Sq Ft)</label>
                                <input
                                    v-model="form.area_sqft"
                                    type="number"
                                    min="0"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Furnished Status *</label>
                                <select v-model="form.furnished_status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                    <option value="">Select Status</option>
                                    <option value="furnished">Furnished</option>
                                    <option value="semi_furnished">Semi Furnished</option>
                                    <option value="unfurnished">Unfurnished</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Availability Status *</label>
                                <select v-model="form.availability_status" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                                    <option value="">Select Availability</option>
                                    <option value="vacant">Vacant</option>
                                    <option value="occupied">Occupied</option>
                                    <option value="maintenance">Under Maintenance</option>
                                    <option value="blocked">Blocked</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Floor</label>
                                <input
                                    v-model="form.floor"
                                    type="number"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Total Floors</label>
                                <input
                                    v-model="form.total_floors"
                                    type="number"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                                    placeholder="0"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- Amenities -->
                    <div class="bg-indigo-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-indigo-900 mb-4">Amenities</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="parking" class="mr-2" />
                                <span class="text-sm text-gray-700">Parking</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="balcony" class="mr-2" />
                                <span class="text-sm text-gray-700">Balcony</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="garden" class="mr-2" />
                                <span class="text-sm text-gray-700">Garden</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="gym" class="mr-2" />
                                <span class="text-sm text-gray-700">Gym</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="swimming_pool" class="mr-2" />
                                <span class="text-sm text-gray-700">Swimming Pool</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="security" class="mr-2" />
                                <span class="text-sm text-gray-700">Security</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="power_backup" class="mr-2" />
                                <span class="text-sm text-gray-700">Power Backup</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.amenities" type="checkbox" value="lift" class="mr-2" />
                                <span class="text-sm text-gray-700">Lift</span>
                            </label>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Status</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <label class="flex items-center">
                                <input v-model="form.published" type="checkbox" class="mr-2" />
                                <span class="text-sm text-gray-700">Published</span>
                            </label>
                            <label class="flex items-center">
                                <input v-model="form.featured" type="checkbox" class="mr-2" />
                                <span class="text-sm text-gray-700">Featured</span>
                            </label>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex justify-end space-x-4">
                        <a :href="`/properties/${props.property.id}`" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" :disabled="loading" class="btn btn-primary">
                            <span v-if="loading" class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></span>
                            {{ loading ? 'Updating...' : 'Update Property' }}
                        </button>
                    </div>
                </form>
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
import { ref, onMounted } from 'vue'
import Header from '../../Components/Header.vue'
import ImagePlaceholder from '../../Components/ImagePlaceholder.vue'

const props = defineProps({
    property: {
        type: Object,
        default: null
    }
})

const loading = ref(false)

const form = ref({
    title: '',
    short_description: '',
    long_description: '',
    property_type: '',
    category: '',
    price: '',
    security_deposit: '',
    maintenance_charges: '',
    city: '',
    locality: '',
    pincode: '',
    address_line: '',
    bedrooms: '',
    bathrooms: '',
    area_sqft: '',
    furnished_status: '',
    availability_status: '',
    floor: '',
    total_floors: '',
    amenities: [],
    published: false,
    featured: false
})

// Populate form with existing property data
onMounted(() => {
    if (props.property) {
        form.value = {
            title: props.property.title || '',
            short_description: props.property.short_description || '',
            long_description: props.property.long_description || '',
            property_type: props.property.property_type || '',
            category: props.property.category || '',
            price: props.property.price || '',
            security_deposit: props.property.security_deposit || '',
            maintenance_charges: props.property.maintenance_charges || '',
            city: props.property.city || '',
            locality: props.property.locality || '',
            pincode: props.property.pincode || '',
            address_line: props.property.address_line || '',
            bedrooms: props.property.bedrooms || '',
            bathrooms: props.property.bathrooms || '',
            area_sqft: props.property.area_sqft || '',
            furnished_status: props.property.furnished_status || '',
            availability_status: props.property.availability_status || '',
            floor: props.property.floor || '',
            total_floors: props.property.total_floors || '',
            amenities: props.property.amenities || [],
            published: props.property.published || false,
            featured: props.property.featured || false
        }
    }
})

const updateProperty = async () => {
    loading.value = true
    
    try {
        const response = await fetch(`/properties/${props.property.id}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify(form.value)
        })
        
        const data = await response.json()
        
        if (data.success) {
            // Redirect to property show page
            window.location.href = `/properties/${props.property.id}`
        } else {
            console.error('Error updating property:', data.message)
            alert('Failed to update property: ' + data.message)
        }
    } catch (error) {
        console.error('Error updating property:', error)
        alert('Failed to update property. Please try again.')
    } finally {
        loading.value = false
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

.btn-primary:hover:not(:disabled) {
    background-color: #0369a1;
}

.btn-primary:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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
    text-decoration: none;
}

.btn-secondary:hover {
    background-color: #f9fafb;
}

.shadow-soft {
    box-shadow: 0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04);
}
</style>
