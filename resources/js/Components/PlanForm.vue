<template>
    <form @submit.prevent="savePlan" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Plan Name</label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                <div v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name[0] }}</div>
            </div>

            <div>
                <label for="price" class="block text-sm font-medium text-gray-700">Price (₹)</label>
                <input
                    id="price"
                    v-model="form.price"
                    type="number"
                    step="0.01"
                    min="0"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                <div v-if="errors.price" class="mt-1 text-sm text-red-600">{{ errors.price[0] }}</div>
            </div>
        </div>

        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea
                id="description"
                v-model="form.description"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
            ></textarea>
            <div v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="billing_cycle" class="block text-sm font-medium text-gray-700">Billing Cycle</label>
                <select
                    id="billing_cycle"
                    v-model="form.billing_cycle"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                    <option value="monthly">Monthly</option>
                    <option value="yearly">Yearly</option>
                </select>
                <div v-if="errors.billing_cycle" class="mt-1 text-sm text-red-600">{{ errors.billing_cycle[0] }}</div>
            </div>

            <div>
                <label for="max_properties" class="block text-sm font-medium text-gray-700">Max Properties</label>
                <input
                    id="max_properties"
                    v-model="form.max_properties"
                    type="number"
                    min="1"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                <div v-if="errors.max_properties" class="mt-1 text-sm text-red-600">{{ errors.max_properties[0] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="max_users" class="block text-sm font-medium text-gray-700">Max Users</label>
                <input
                    id="max_users"
                    v-model="form.max_users"
                    type="number"
                    min="1"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                <div v-if="errors.max_users" class="mt-1 text-sm text-red-600">{{ errors.max_users[0] }}</div>
            </div>

            <div class="flex items-center">
                <input
                    id="is_active"
                    v-model="form.is_active"
                    type="checkbox"
                    class="h-4 w-4 text-sky-800 focus:ring-sky-800 border-gray-300 rounded"
                >
                <label for="is_active" class="ml-2 block text-sm text-gray-900">
                    Active Plan
                </label>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Features</label>
            <div class="space-y-2">
                <div v-for="(feature, index) in form.features" :key="index" class="flex items-center space-x-2">
                    <input
                        v-model="feature.name"
                        type="text"
                        placeholder="Feature name"
                        class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                    >
                    <button
                        type="button"
                        @click="removeFeature(index)"
                        class="px-3 py-1 text-sm bg-red-600 text-white rounded hover:bg-red-700"
                    >
                        Remove
                    </button>
                </div>
                <button
                    type="button"
                    @click="addFeature"
                    class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700"
                >
                    Add Feature
                </button>
            </div>
            <div v-if="errors.features" class="mt-1 text-sm text-red-600">{{ errors.features[0] }}</div>
        </div>

        <div class="flex justify-end space-x-3">
            <button
                type="button"
                @click="$emit('cancelled')"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-800"
            >
                Cancel
            </button>
            <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-sky-800 hover:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-sky-800 disabled:opacity-50"
            >
                {{ loading ? 'Saving...' : (plan ? 'Update Plan' : 'Create Plan') }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    plan: {
        type: Object,
        default: null
    }
})

const emit = defineEmits(['saved', 'cancelled'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: '',
    description: '',
    price: 0,
    billing_cycle: 'monthly',
    max_properties: 1,
    max_users: 1,
    features: [],
    is_active: true
})

const initializeForm = () => {
    if (props.plan) {
        form.name = props.plan.name || ''
        form.description = props.plan.description || ''
        form.price = props.plan.price || 0
        form.billing_cycle = props.plan.billing_cycle || 'monthly'
        form.max_properties = props.plan.max_properties || 1
        form.max_users = props.plan.max_users || 1
        form.features = props.plan.features ? [...props.plan.features] : []
        form.is_active = props.plan.is_active !== undefined ? props.plan.is_active : true
    }
}

const addFeature = () => {
    form.features.push({ name: '' })
}

const removeFeature = (index) => {
    form.features.splice(index, 1)
}

const savePlan = async () => {
    loading.value = true
    errors.value = {}

    try {
        const url = props.plan 
            ? `/api/v1/admin/plans/${props.plan.id}`
            : '/api/v1/admin/plans'
        
        const method = props.plan ? 'put' : 'post'
        
        const data = {
            ...form,
            features: form.features.filter(feature => feature.name.trim() !== '')
        }

        const response = await axios[method](url, data, {
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token')
            }
        })

        if (response.data.success) {
            emit('saved', response.data.data)
        }
    } catch (error) {
        if (error.response?.status === 422) {
            errors.value = error.response.data.errors
        } else {
            console.error('Error saving plan:', error)
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    initializeForm()
    if (form.features.length === 0) {
        addFeature()
    }
})
</script>
