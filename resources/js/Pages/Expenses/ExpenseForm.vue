<template>
    <form @submit.prevent="saveExpense" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Property *</label>
                <select v-model="form.property_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    <option value="">Select Property</option>
                    <option v-for="property in properties" :key="property.id" :value="property.id">
                        {{ property.title }}
                    </option>
                </select>
                <div v-if="errors.property_id" class="text-red-500 text-sm mt-1">{{ errors.property_id[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select v-model="form.category" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    <option value="">Select Category</option>
                    <option v-for="category in categories" :key="category" :value="category">
                        {{ category }}
                    </option>
                </select>
                <div v-if="errors.category" class="text-red-500 text-sm mt-1">{{ errors.category[0] }}</div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
            <textarea v-model="form.description" required rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800" placeholder="Enter expense description"></textarea>
            <div v-if="errors.description" class="text-red-500 text-sm mt-1">{{ errors.description[0] }}</div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount *</label>
                <input v-model="form.amount" type="number" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800" placeholder="0.00">
                <div v-if="errors.amount" class="text-red-500 text-sm mt-1">{{ errors.amount[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expense Date *</label>
                <input v-model="form.expense_date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                <div v-if="errors.expense_date" class="text-red-500 text-sm mt-1">{{ errors.expense_date[0] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                <input v-model="form.vendor" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800" placeholder="Vendor name">
                <div v-if="errors.vendor" class="text-red-500 text-sm mt-1">{{ errors.vendor[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt Number</label>
                <input v-model="form.receipt_number" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800" placeholder="Receipt number">
                <div v-if="errors.receipt_number" class="text-red-500 text-sm mt-1">{{ errors.receipt_number[0] }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
                <select v-model="form.payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                    <option value="">Select Payment Method</option>
                    <option value="cash">Cash</option>
                    <option value="bank_transfer">Bank Transfer</option>
                    <option value="cheque">Cheque</option>
                    <option value="credit_card">Credit Card</option>
                    <option value="debit_card">Debit Card</option>
                    <option value="upi">UPI</option>
                    <option value="other">Other</option>
                </select>
                <div v-if="errors.payment_method" class="text-red-500 text-sm mt-1">{{ errors.payment_method[0] }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Receipt File</label>
                <input @change="handleFileUpload" type="file" accept=".pdf,.jpg,.jpeg,.png" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800">
                <div v-if="errors.receipt_file" class="text-red-500 text-sm mt-1">{{ errors.receipt_file[0] }}</div>
                <div v-if="form.receipt_file" class="text-sm text-gray-600 mt-1">
                    Selected: {{ form.receipt_file.name }}
                </div>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea v-model="form.notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-sky-800" placeholder="Additional notes"></textarea>
            <div v-if="errors.notes" class="text-red-500 text-sm mt-1">{{ errors.notes[0] }}</div>
        </div>

        <div class="flex justify-end space-x-3 pt-4">
            <button type="button" @click="$emit('cancelled')" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-sky-800">
                Cancel
            </button>
            <button type="submit" :disabled="saving" class="px-4 py-2 bg-sky-800 border border-transparent rounded-md text-sm font-medium text-white hover:bg-sky-900 focus:outline-none focus:ring-2 focus:ring-sky-800 disabled:opacity-50">
                <span v-if="saving">Saving...</span>
                <span v-else>{{ expense ? 'Update' : 'Save' }} Expense</span>
            </button>
        </div>
    </form>
</template>

<script setup>
import { ref, reactive, watch } from 'vue'
import axios from 'axios'

const props = defineProps({
    expense: {
        type: Object,
        default: null
    },
    properties: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Array,
        default: () => []
    }
})

const emit = defineEmits(['saved', 'cancelled'])

const saving = ref(false)
const errors = ref({})

const form = reactive({
    property_id: '',
    category: '',
    description: '',
    amount: '',
    expense_date: '',
    vendor: '',
    receipt_number: '',
    payment_method: '',
    notes: '',
    receipt_file: null
})

// Watch for expense prop changes to populate form
watch(() => props.expense, (newExpense) => {
    if (newExpense) {
        form.property_id = newExpense.property_id || ''
        form.category = newExpense.category || ''
        form.description = newExpense.description || ''
        form.amount = newExpense.amount || ''
        form.expense_date = newExpense.expense_date || ''
        form.vendor = newExpense.vendor || ''
        form.receipt_number = newExpense.receipt_number || ''
        form.payment_method = newExpense.payment_method || ''
        form.notes = newExpense.notes || ''
        form.receipt_file = null
    } else {
        // Reset form for new expense
        Object.keys(form).forEach(key => {
            if (key === 'receipt_file') {
                form[key] = null
            } else {
                form[key] = ''
            }
        })
    }
}, { immediate: true })

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Validate file size (10MB max)
        if (file.size > 10 * 1024 * 1024) {
            alert('File size must be less than 10MB')
            return
        }
        
        // Validate file type
        const allowedTypes = ['application/pdf', 'image/jpeg', 'image/jpg', 'image/png']
        if (!allowedTypes.includes(file.type)) {
            alert('Only PDF, JPEG, and PNG files are allowed')
            return
        }
        
        form.receipt_file = file
    }
}

const saveExpense = async () => {
    saving.value = true
    errors.value = {}
    
    try {
        const formData = new FormData()
        
        // Add form fields
        Object.keys(form).forEach(key => {
            if (form[key] !== null && form[key] !== '') {
                formData.append(key, form[key])
            }
        })
        
        let response
        if (props.expense) {
            // Update existing expense
            response = await axios.post(`/api/v1/org/expenses/${props.expense.id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
        } else {
            // Create new expense
            response = await axios.post('/api/v1/org/expenses', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
        }
        
        if (response.data.success) {
            emit('saved', response.data.data)
        }
    } catch (error) {
        if (error.response && error.response.status === 422) {
            errors.value = error.response.data.errors
        } else {
            console.error('Error saving expense:', error)
            alert('Error saving expense. Please try again.')
        }
    } finally {
        saving.value = false
    }
}
</script>

