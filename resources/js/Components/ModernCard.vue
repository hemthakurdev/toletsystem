<template>
    <div 
        :class="[
            'card group transition-all duration-300',
            hoverEffect ? 'hover:shadow-large hover:-translate-y-1' : '',
            className
        ]"
    >
        <!-- Card Header -->
        <div v-if="$slots.header || title" class="card-header">
            <div class="flex items-center justify-between">
                <div>
                    <h3 v-if="title" class="text-lg font-semibold text-gray-900">{{ title }}</h3>
                    <p v-if="subtitle" class="text-sm text-gray-600 mt-1">{{ subtitle }}</p>
                </div>
                <div class="flex items-center space-x-2">
                    <slot name="header-actions"></slot>
                    <button 
                        v-if="collapsible" 
                        @click="toggleCollapse"
                        class="p-1 text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        <svg 
                            :class="['w-5 h-5 transition-transform duration-200', isCollapsed ? 'rotate-180' : '']"
                            fill="none" 
                            stroke="currentColor" 
                            viewBox="0 0 24 24"
                        >
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                </div>
            </div>
            <slot name="header"></slot>
        </div>

        <!-- Card Body -->
        <div v-show="!isCollapsed" class="card-body">
            <slot></slot>
        </div>

        <!-- Card Footer -->
        <div v-if="$slots.footer" class="card-footer">
            <slot name="footer"></slot>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
    title: {
        type: String,
        default: null
    },
    subtitle: {
        type: String,
        default: null
    },
    hoverEffect: {
        type: Boolean,
        default: true
    },
    collapsible: {
        type: Boolean,
        default: false
    },
    defaultCollapsed: {
        type: Boolean,
        default: false
    },
    className: {
        type: String,
        default: ''
    }
})

const isCollapsed = ref(props.defaultCollapsed)

const toggleCollapse = () => {
    isCollapsed.value = !isCollapsed.value
}
</script>
