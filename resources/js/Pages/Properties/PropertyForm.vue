<template>
    <form @submit.prevent="saveProperty" class="space-y-6">
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-yellow-900 mb-4">Location</h3>
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
                        placeholder="e.g., Bandra West"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pincode *</label>
                    <input
                        v-model="form.pincode"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="e.g., 400050"
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Address *</label>
                    <textarea
                        v-model="form.address_line"
                        rows="2"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="Complete address..."
                    ></textarea>
                </div>
            </div>
        </div>

        <!-- Property Details -->
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-purple-900 mb-4">Property Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Area (sq ft)</label>
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="parking" class="mr-2" />
                    <span class="text-sm">Parking</span>
                </label>
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="balcony" class="mr-2" />
                    <span class="text-sm">Balcony</span>
                </label>
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="garden" class="mr-2" />
                    <span class="text-sm">Garden</span>
                </label>
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="gym" class="mr-2" />
                    <span class="text-sm">Gym</span>
                </label>
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="swimming_pool" class="mr-2" />
                    <span class="text-sm">Swimming Pool</span>
                </label>
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="security" class="mr-2" />
                    <span class="text-sm">Security</span>
                </label>
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="power_backup" class="mr-2" />
                    <span class="text-sm">Power Backup</span>
                </label>
                <label class="flex items-center">
                    <input v-model="form.amenities" type="checkbox" value="lift" class="mr-2" />
                    <span class="text-sm">Lift</span>
                </label>
            </div>
        </div>

        <!-- Image Upload Section -->
        <div v-if="propertyId" class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-purple-900 mb-4">Property Images</h3>
            <ImageUpload 
                :property-id="propertyId" 
                :initial-images="propertyImages"
                @images-uploaded="onImagesUploaded"
                @images-deleted="onImagesDeleted"
            />
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
            <button type="button" @click="$emit('cancelled')" class="btn-secondary">
                Cancel
            </button>
            <button type="submit" :disabled="loading" class="btn-primary">
                <span v-if="loading">Saving...</span>
                <span v-else>Save Property</span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, computed } from 'vue'
import ImageUpload from '../../Components/ImageUpload.vue'

const props = defineProps({
    property: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['saved', 'cancelled'])

const loading = ref(false)
const propertyImages = ref([])

const propertyId = computed(() => {
    return props.property?.id || null
})

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
    floor: '',
    total_floors: '',
    amenities: []
})

const saveProperty = async () => {
    loading.value = true
    
    try {
        const response = await fetch('/api/v1/properties', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(form.value)
        })
        
        const data = await response.json()
        
        if (data.success) {
            emit('saved')
        } else {
            console.error('Error saving property:', data.message)
        }
    } catch (error) {
        console.error('Error saving property:', error)
    } finally {
        loading.value = false
    }
}

const onImagesUploaded = (uploadedImages) => {
    propertyImages.value.push(...uploadedImages)
}

const onImagesDeleted = (deletedPaths) => {
    propertyImages.value = propertyImages.value.filter(image => 
        !deletedPaths.includes(image.original)
    )
}
</script>
