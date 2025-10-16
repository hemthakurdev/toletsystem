<template>
    <form @submit.prevent="generateBulkInvoices" class="space-y-6">
        <div class="bg-sky-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-sky-900 mb-4">Bulk Invoice Generation</h3>
            <p class="text-sm text-gray-600 mb-4">
                Generate monthly rent invoices for all active tenants. This will create invoices for tenants who don't already have an invoice for the specified month.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Month *</label>
                    <input
                        v-model="form.month"
                        type="month"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                    />
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Due Date *</label>
                    <input
                        v-model="form.due_date"
                        type="date"
                        required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                    />
                </div>
            </div>
        </div>

        <!-- Preview Section -->
        <div v-if="previewData.length > 0" class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-green-900 mb-4">Preview</h3>
            <p class="text-sm text-gray-600 mb-4">
                The following invoices will be generated:
            </p>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tenant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Property</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="item in previewData" :key="item.tenant.id">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ item.tenant.name }}</div>
                                <div class="text-sm text-gray-500">{{ item.tenant.email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ item.property.title }}</div>
                                <div class="text-sm text-gray-500">{{ item.property.locality }}, {{ item.property.city }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">₹{{ formatPrice(item.tenant.rent_amount) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="item.status === 'new' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full">
                                    {{ item.status === 'new' ? 'New Invoice' : 'Already Exists' }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-between">
            <button type="button" @click="previewInvoices" :disabled="loading" class="btn-secondary">
                <span v-if="loading">Loading...</span>
                <span v-else>Preview</span>
            </button>
            
            <div class="flex space-x-4">
                <button type="button" @click="$emit('cancelled')" class="btn-secondary">
                    Cancel
                </button>
                <button type="submit" :disabled="loading || previewData.length === 0" class="btn-primary">
                    <span v-if="loading">Generating...</span>
                    <span v-else>Generate Invoices</span>
                </button>
            </div>
        </div>
    </form>
</template>

<script setup>
import { ref } from 'vue'

const emit = defineEmits(['saved', 'cancelled'])

const loading = ref(false)
const previewData = ref([])

const form = ref({
    month: '',
    due_date: ''
})

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-IN').format(price)
}

const previewInvoices = async () => {
    if (!form.value.month || !form.value.due_date) {
        alert('Please fill in all required fields')
        return
    }

    loading.value = true
    
    try {
        // Get all active tenants
        const tenantsResponse = await fetch('/api/v1/org/tenants?status=active')
        const tenantsData = await tenantsResponse.json()
        
        if (tenantsData.success) {
            const tenants = tenantsData.data.data
            
            // Get existing invoices for the month
            const invoicesResponse = await fetch(`/api/v1/org/invoices?type=rent&description=${form.value.month}`)
            const invoicesData = await invoicesResponse.json()
            
            const existingInvoices = invoicesData.success ? invoicesData.data.data : []
            const existingTenantIds = existingInvoices.map(inv => inv.tenant_id)
            
            // Prepare preview data
            previewData.value = tenants.map(tenant => ({
                tenant,
                property: tenant.property,
                status: existingTenantIds.includes(tenant.id) ? 'exists' : 'new'
            }))
        }
    } catch (error) {
        console.error('Error previewing invoices:', error)
        alert('Error loading preview data')
    } finally {
        loading.value = false
    }
}

const generateBulkInvoices = async () => {
    if (!form.value.month || !form.value.due_date) {
        alert('Please fill in all required fields')
        return
    }

    loading.value = true
    
    try {
        const response = await fetch('/api/v1/org/invoices/generate-monthly-rent', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(form.value)
        })
        
        const data = await response.json()
        
        if (data.success) {
            alert(`Successfully generated ${data.data.generated_count} invoices!`)
            emit('saved')
        } else {
            console.error('Error generating invoices:', data.message)
            alert('Error generating invoices: ' + (data.message || 'Unknown error'))
        }
    } catch (error) {
        console.error('Error generating invoices:', error)
        alert('Error generating invoices. Please try again.')
    } finally {
        loading.value = false
    }
}
</script>
