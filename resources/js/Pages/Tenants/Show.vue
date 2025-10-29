<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <Header current-path="/tenants" />
        <div class="container-mobile py-6">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Tenant Details</h1>
                        <p class="text-gray-600">View information for this tenant</p>
                    </div>
                    <button class="btn btn-secondary" @click="goBack">Back</button>
                </div>

                <div v-if="tenant" class="bg-white rounded-lg shadow-sm p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">Basic Info</h2>
                            <p class="text-gray-700"><span class="font-medium">Name:</span> {{ tenant.name }}</p>
                            <p class="text-gray-700"><span class="font-medium">Email:</span> {{ tenant.email || '—' }}</p>
                            <p class="text-gray-700"><span class="font-medium">Phone:</span> {{ tenant.phone }}</p>
                            <p class="text-gray-700"><span class="font-medium">Occupation:</span> {{ tenant.occupation || '—' }}</p>
                            <p class="text-gray-700"><span class="font-medium">Company:</span> {{ tenant.company || '—' }}</p>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900 mb-2">Lease</h2>
                            <p class="text-gray-700"><span class="font-medium">Property:</span> {{ tenant.property?.title || '—' }}</p>
                            <p class="text-gray-700"><span class="font-medium">Location:</span> {{ tenant.property?.locality }}, {{ tenant.property?.city }}</p>
                            <p class="text-gray-700"><span class="font-medium">Start:</span> {{ formatDate(tenant.lease_start) }}</p>
                            <p class="text-gray-700"><span class="font-medium">End:</span> {{ formatDate(tenant.lease_end) }}</p>
                            <p class="text-gray-700"><span class="font-medium">Rent:</span> ₹{{ formatPrice(tenant.rent_amount) }}</p>
                            <p class="text-gray-700" v-if="tenant.security_deposit"><span class="font-medium">Deposit:</span> ₹{{ formatPrice(tenant.security_deposit) }}</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-2">Status</h2>
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full" :class="getStatusBadgeClass(tenant.status)">{{ tenant.status }}</span>
                    </div>
                </div>

                <div v-else class="text-center text-gray-600">Loading...</div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '../../Components/Header.vue'

const props = defineProps({
    tenant: {
        type: Object,
        default: null
    }
})

const goBack = () => {
    window.history.back()
}

const formatPrice = (price) => {
    if (!price && price !== 0) return '0'
    return new Intl.NumberFormat('en-IN').format(price)
}

const formatDate = (date) => {
    if (!date) return '—'
    return new Date(date).toLocaleDateString('en-IN')
}

const getStatusBadgeClass = (status) => {
    const classes = {
        'active': 'bg-green-100 text-green-800',
        'inactive': 'bg-gray-100 text-gray-800',
        'terminated': 'bg-red-100 text-red-800'
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>


