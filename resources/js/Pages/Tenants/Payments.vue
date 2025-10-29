<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <Header current-path="/tenants" />
        <div class="container-mobile py-6">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Tenant Payments</h1>
                        <p class="text-gray-600">Payment history and invoices</p>
                    </div>
                    <button class="btn btn-secondary" @click="goBack">Back</button>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <p class="text-gray-700">Tenant ID: <span class="font-medium">{{ id }}</span></p>
                    <p class="text-gray-500 mt-2">Payments UI placeholder. Integrate invoices and payments list here.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '../../Components/Header.vue'

const props = defineProps({
    id: {
        type: [String, Number],
        required: true
    }
})

const goBack = () => {
    window.history.back()
}
</script>


