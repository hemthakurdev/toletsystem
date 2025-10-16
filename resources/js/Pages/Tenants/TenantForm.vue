<template>
    <form @submit.prevent="saveTenant" class="space-y-6">
        <!-- Personal Information -->
        <div class="bg-sky-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-sky-900 mb-4">Personal Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="Enter tenant's full name"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email Address *</label>
                    <input
                        v-model="form.email"
                        type="email"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="tenant@example.com"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number *</label>
                    <input
                        v-model="form.phone"
                        type="tel"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="+91 98765 43210"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Occupation</label>
                    <input
                        v-model="form.occupation"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="e.g., Software Engineer"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Company</label>
                    <input
                        v-model="form.company"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="e.g., Tech Corp"
                    />
                </div>
            </div>
        </div>

        <!-- Property & Lease Information -->
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-green-900 mb-4">Property & Lease Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Property *</label>
                    <select v-model="form.property_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        <option value="">Select Property</option>
                        <option v-for="property in properties" :key="property.id" :value="property.id">
                            {{ property.title }} - {{ property.locality }}, {{ property.city }}
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Monthly Rent *</label>
                    <input
                        v-model="form.monthly_rent"
                        type="number"
                        required
                        min="0"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="0"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lease Start Date *</label>
                    <input
                        v-model="form.lease_start_date"
                        type="date"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Lease End Date *</label>
                    <input
                        v-model="form.lease_end_date"
                        type="date"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
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
            </div>
        </div>

        <!-- Emergency Contact -->
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-yellow-900 mb-4">Emergency Contact</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Name</label>
                    <input
                        v-model="form.emergency_contact_name"
                        type="text"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="Emergency contact person name"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact Phone</label>
                    <input
                        v-model="form.emergency_contact_phone"
                        type="tel"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="+91 98765 43210"
                    />
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-purple-900 mb-4">Additional Information</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea
                    v-model="form.notes"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                    placeholder="Any additional notes about the tenant..."
                ></textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
            <button type="button" @click="$emit('cancelled')" class="btn-secondary">
                Cancel
            </button>
            <button type="submit" :disabled="loading" class="btn-primary">
                <span v-if="loading">Saving...</span>
                <span v-else>Save Tenant</span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    properties: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['saved', 'cancelled'])

const loading = ref(false)

const form = ref({
    name: '',
    email: '',
    phone: '',
    property_id: '',
    lease_start_date: '',
    lease_end_date: '',
    monthly_rent: '',
    security_deposit: '',
    emergency_contact_name: '',
    emergency_contact_phone: '',
    occupation: '',
    company: '',
    notes: ''
})

const saveTenant = async () => {
    loading.value = true
    
    try {
        const response = await fetch('/api/v1/tenants', {
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
            console.error('Error saving tenant:', data.message)
            alert('Error saving tenant: ' + (data.message || 'Unknown error'))
        }
    } catch (error) {
        console.error('Error saving tenant:', error)
        alert('Error saving tenant. Please try again.')
    } finally {
        loading.value = false
    }
}
</script>
