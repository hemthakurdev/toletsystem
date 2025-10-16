<template>
    <button
        :type="type"
        :disabled="disabled || loading"
        :class="[
            'btn',
            variantClasses,
            sizeClasses,
            {
                'opacity-50 cursor-not-allowed': disabled || loading,
                'animate-pulse': loading
            },
            className
        ]"
        @click="handleClick"
    >
        <!-- Loading Spinner -->
        <svg 
            v-if="loading" 
            class="animate-spin -ml-1 mr-2 h-4 w-4" 
            fill="none" 
            viewBox="0 0 24 24"
        >
            <circle 
                class="opacity-25" 
                cx="12" 
                cy="12" 
                r="10" 
                stroke="currentColor" 
                stroke-width="4"
            ></circle>
            <path 
                class="opacity-75" 
                fill="currentColor" 
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
            ></path>
        </svg>

        <!-- Icon (left) -->
        <component 
            v-if="icon && !loading" 
            :is="icon" 
            :class="['w-4 h-4', iconPosition === 'left' ? 'mr-2' : 'ml-2']"
        />

        <!-- Button Text -->
        <span v-if="!loading">{{ text }}</span>
        <span v-else>{{ loadingText }}</span>

        <!-- Icon (right) -->
        <component 
            v-if="icon && iconPosition === 'right' && !loading" 
            :is="icon" 
            class="w-4 h-4 ml-2"
        />
    </button>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    type: {
        type: String,
        default: 'button'
    },
    variant: {
        type: String,
        default: 'primary', // primary, secondary, success, danger, ghost, outline
        validator: (value) => ['primary', 'secondary', 'success', 'danger', 'ghost', 'outline'].includes(value)
    },
    size: {
        type: String,
        default: 'medium', // xs, sm, medium, lg, xl
        validator: (value) => ['xs', 'sm', 'medium', 'lg', 'xl'].includes(value)
    },
    text: {
        type: String,
        default: 'Button'
    },
    loading: {
        type: Boolean,
        default: false
    },
    loadingText: {
        type: String,
        default: 'Loading...'
    },
    disabled: {
        type: Boolean,
        default: false
    },
    icon: {
        type: [String, Object],
        default: null
    },
    iconPosition: {
        type: String,
        default: 'left', // left, right
        validator: (value) => ['left', 'right'].includes(value)
    },
    className: {
        type: String,
        default: ''
    }
})

const emit = defineEmits(['click'])

const variantClasses = computed(() => {
    const variants = {
        primary: 'btn-primary',
        secondary: 'btn-secondary',
        success: 'btn-success',
        danger: 'btn-danger',
        ghost: 'btn-ghost',
        outline: 'btn-outline'
    }
    return variants[props.variant] || variants.primary
})

const sizeClasses = computed(() => {
    const sizes = {
        xs: 'px-2 py-1 text-xs',
        sm: 'px-3 py-1.5 text-sm',
        medium: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base',
        xl: 'px-8 py-4 text-lg'
    }
    return sizes[props.size] || sizes.medium
})

const handleClick = (event) => {
    if (!props.disabled && !props.loading) {
        emit('click', event)
    }
}
</script>
