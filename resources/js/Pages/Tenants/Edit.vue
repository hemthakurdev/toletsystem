<template>
    <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100">
        <Header current-path="/tenants" />
        <div class="container-mobile py-6">
            <div class="px-4 py-6 sm:px-0">
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Edit Tenant</h1>
                        <p class="text-gray-600">Update tenant details</p>
                    </div>
                    <button class="btn btn-secondary" @click="goBack">Back</button>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <TenantForm
                        :initial-tenant="tenant"
                        :properties="properties"
                        mode="edit"
                        @saved="onSaved"
                        @cancelled="goBack"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import Header from '../../Components/Header.vue'
import TenantForm from './TenantForm.vue'

const props = defineProps({
    tenant: {
        type: Object,
        default: null
    },
    properties: {
        type: Array,
        default: () => []
    }
})

const goBack = () => {
    window.history.back()
}

const onSaved = () => {
    window.location.href = `/tenants/${props.tenant?.id}`
}
</script>


