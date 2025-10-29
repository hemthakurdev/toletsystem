<template>
    <form @submit.prevent="saveInvoice" class="space-y-6">
        <!-- Basic Information -->
        <div class="bg-sky-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-sky-900 mb-4">Invoice Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tenant *</label>
                    <select v-model="form.tenant_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        <option value="">Select Tenant</option>
                        <option v-for="tenant in tenants" :key="tenant.id" :value="tenant.id">
                            {{ tenant.name }} - {{ tenant.property?.title || tenant.property_title || ('#'+tenant.property_id) }}
                        </option>
                    </select>
                </div>
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">Invoice Type *</label>
                    <select v-model="form.type" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500">
                        <option value="">Select Type</option>
                        <option value="rent">Rent</option>
                        <option value="maintenance">Maintenance</option>
                        <option value="penalty">Penalty</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Amount *</label>
                    <input
                        v-model="form.amount"
                        type="number"
                        required
                        min="0"
                        step="0.01"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                        placeholder="0.00"
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

        <!-- Description -->
        <div class="bg-green-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-green-900 mb-4">Description</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                <textarea
                    v-model="form.description"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                    placeholder="Enter invoice description..."
                ></textarea>
            </div>
        </div>

        <!-- Invoice Items -->
        <div class="bg-yellow-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-yellow-900 mb-4">Invoice Items (Optional)</h3>
            <div class="space-y-4">
                <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <input
                            v-model="item.description"
                            type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                            placeholder="Item description"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                        <input
                            v-model="item.quantity"
                            type="number"
                            min="0"
                            step="0.01"
                            @input="calculateItemAmount(index)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                            placeholder="1"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Rate</label>
                        <input
                            v-model="item.rate"
                            type="number"
                            min="0"
                            step="0.01"
                            @input="calculateItemAmount(index)"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                            placeholder="0.00"
                        />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Amount</label>
                        <input
                            v-model="item.amount"
                            type="number"
                            min="0"
                            step="0.01"
                            readonly
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
                            placeholder="0.00"
                        />
                    </div>
                    <div>
                        <button
                            type="button"
                            @click="removeItem(index)"
                            class="w-full px-3 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                        >
                            Remove
                        </button>
                    </div>
                </div>
                
                <button
                    type="button"
                    @click="addItem"
                    class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-md text-gray-600 hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-800"
                >
                    + Add Item
                </button>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-purple-50 p-4 rounded-lg">
            <h3 class="text-lg font-medium text-purple-900 mb-4">Additional Information</h3>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                <textarea
                    v-model="form.notes"
                    rows="3"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-sky-800 focus:border-sky-500"
                    placeholder="Any additional notes..."
                ></textarea>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
            <button type="button" @click="$emit('cancelled')" class="btn btn-secondary">
                Cancel
            </button>
            <button type="submit" :disabled="loading" class="btn btn-primary">
                <span v-if="loading">Creating...</span>
                <span v-else>Create Invoice</span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    tenants: {
        type: Array,
        default: () => []
    },
    properties: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['saved', 'cancelled'])

const loading = ref(false)

const form = ref({
    tenant_id: '',
    property_id: '',
    type: '',
    amount: '',
    due_date: '',
    description: '',
    items: [],
    notes: ''
})

// Watch for tenant selection to auto-fill property
watch(() => form.value.tenant_id, (newTenantId) => {
    if (newTenantId) {
        const selectedTenant = props.tenants.find(tenant => tenant.id == newTenantId)
        if (selectedTenant && selectedTenant.property_id) {
            form.value.property_id = selectedTenant.property_id
            form.value.amount = selectedTenant.rent_amount || ''
        }
    }
})

// Watch for property selection to auto-select tenant if unique
watch(() => form.value.property_id, (newPropertyId) => {
    if (!newPropertyId) return
    const candidates = props.tenants.filter(t => t.property_id == newPropertyId)
    if (candidates.length === 1) {
        form.value.tenant_id = candidates[0].id
        if (form.value.type === 'rent' || !form.value.type) {
            form.value.amount = candidates[0].rent_amount || form.value.amount
        }
    }
})

// Watch for type to prefill amount for rent
watch(() => form.value.type, (newType) => {
    if (newType === 'rent' && form.value.tenant_id) {
        const t = props.tenants.find(tenant => tenant.id == form.value.tenant_id)
        if (t && t.rent_amount) {
            form.value.amount = t.rent_amount
        }
    }
})

const addItem = () => {
    form.value.items.push({
        description: '',
        quantity: 1,
        rate: 0,
        amount: 0
    })
}

const removeItem = (index) => {
    form.value.items.splice(index, 1)
}

const calculateItemAmount = (index) => {
    const item = form.value.items[index]
    item.amount = (parseFloat(item.quantity) || 0) * (parseFloat(item.rate) || 0)
}

const saveInvoice = async () => {
    loading.value = true
    try {
        // Build payload safely
        const payload = {
            tenant_id: form.value.tenant_id || '',
            property_id: form.value.property_id || '',
            type: form.value.type || 'rent',
            amount: form.value.amount || 0,
            due_date: form.value.due_date || '',
            description: form.value.description || '',
            items: (form.value.items || [])
                .filter(it => (it && typeof it.description === 'string' && it.description.trim() !== ''))
                .map(it => ({
                    description: String(it.description || ''),
                    quantity: Number(it.quantity || 0),
                    rate: Number(it.rate || 0),
                    amount: Number(it.amount || 0)
                })),
            notes: form.value.notes || ''
        }

        const token = localStorage.getItem('token')
        const init = {
            method: 'POST',
            headers: token ? {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            } : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') },
            body: JSON.stringify(payload),
            credentials: token ? undefined : 'include'
        }

        // Prefer API when token available; else use web JSON endpoint with session
        const url = token ? '/api/v1/org/invoices' : '/org/invoices'
        const res = await fetch(url, init)
        const ct = res.headers.get('content-type') || ''
        let data = null
        if (ct.includes('application/json')) {
            try { data = await res.json() } catch (_) { data = null }
        }

        // Consider 200 OK without JSON or without explicit success flag as success
        if (res.ok && (!data || data?.success === true || typeof data?.success === 'undefined')) {
            emit('saved')
        } else {
            const message = data?.message || `Request failed (${res.status})`
            console.error('Error saving invoice:', message)
            alert('Error saving invoice: ' + message)
        }
    } catch (error) {
        console.error('Error saving invoice:', error)
        alert('Error saving invoice. Please try again.')
    } finally {
        loading.value = false
    }
}
</script>
