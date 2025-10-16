<template>
    <form @submit.prevent="saveUser" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
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
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                <div v-if="errors.email" class="mt-1 text-sm text-red-600">{{ errors.email[0] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
                <input
                    id="phone"
                    v-model="form.phone"
                    type="tel"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                <div v-if="errors.phone" class="mt-1 text-sm text-red-600">{{ errors.phone[0] }}</div>
            </div>

            <div>
                <label for="org_id" class="block text-sm font-medium text-gray-700">Organization</label>
                <select
                    id="org_id"
                    v-model="form.org_id"
                    required
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                    <option value="">Select Organization</option>
                    <option v-for="org in organizations" :key="org.id" :value="org.id">
                        {{ org.name }}
                    </option>
                </select>
                <div v-if="errors.org_id" class="mt-1 text-sm text-red-600">{{ errors.org_id[0] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    {{ user ? 'New Password (leave blank to keep current)' : 'Password' }}
                </label>
                <input
                    id="password"
                    v-model="form.password"
                    type="password"
                    :required="!user"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                <div v-if="errors.password" class="mt-1 text-sm text-red-600">{{ errors.password[0] }}</div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-sky-800 focus:border-sky-500"
                >
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                    <option value="suspended">Suspended</option>
                </select>
                <div v-if="errors.status" class="mt-1 text-sm text-red-600">{{ errors.status[0] }}</div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Roles</label>
            <div class="space-y-2">
                <div v-for="role in roles" :key="role.id" class="flex items-center">
                    <input
                        :id="`role-${role.id}`"
                        v-model="form.roles"
                        :value="role.name"
                        type="checkbox"
                        class="h-4 w-4 text-sky-800 focus:ring-sky-800 border-gray-300 rounded"
                    >
                    <label :for="`role-${role.id}`" class="ml-2 block text-sm text-gray-900">
                        {{ role.name }}
                    </label>
                </div>
            </div>
            <div v-if="errors.roles" class="mt-1 text-sm text-red-600">{{ errors.roles[0] }}</div>
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
                {{ loading ? 'Saving...' : (user ? 'Update User' : 'Create User') }}
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    user: {
        type: Object,
        default: null
    },
    organizations: {
        type: Array,
        default: () => []
    },
    roles: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['saved', 'cancelled'])

const loading = ref(false)
const errors = ref({})

const form = reactive({
    name: '',
    email: '',
    phone: '',
    password: '',
    org_id: '',
    status: 'active',
    roles: []
})

const initializeForm = () => {
    if (props.user) {
        form.name = props.user.name || ''
        form.email = props.user.email || ''
        form.phone = props.user.phone || ''
        form.org_id = props.user.org_id || ''
        form.status = props.user.status || 'active'
        form.roles = props.user.roles ? props.user.roles.map(role => role.name) : []
    }
}

const saveUser = async () => {
    loading.value = true
    errors.value = {}

    try {
        const url = props.user 
            ? `/api/v1/admin/users/${props.user.id}`
            : '/api/v1/admin/users'
        
        const method = props.user ? 'put' : 'post'
        
        const data = { ...form }
        if (!data.password && props.user) {
            delete data.password
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
            console.error('Error saving user:', error)
        }
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    initializeForm()
})
</script>
