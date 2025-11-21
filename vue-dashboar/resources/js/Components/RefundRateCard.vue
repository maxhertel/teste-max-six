<script setup>
import { ref, onMounted, computed } from 'vue'

const rate = ref(0)
const refunded = ref(0)
const total = ref(0)
const indicator = ref('green')

const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/metrics/refund-rate')
        const data = await response.json()

        if (data.success) {
            rate.value = data.refund_rate_percent
            refunded.value = data.refunded_orders_count
            total.value = data.total_orders_count
            indicator.value = data.indicator_color
        } else {
            error.value = true
        }
    } catch (e) {
        error.value = true
    } finally {
        loading.value = false
    }
})

const colorClass = computed(() => {
    switch (indicator.value) {
        case 'red':
            return 'bg-red-100 border-red-300 text-red-700'
        case 'yellow':
            return 'bg-yellow-100 border-yellow-300 text-yellow-700'
        default:
            return 'bg-green-100 border-green-300 text-green-700'
    }
})

const barClass = computed(() => {
    switch (indicator.value) {
        case 'red':
            return 'bg-red-500'
        case 'yellow':
            return 'bg-yellow-500'
        default:
            return 'bg-green-500'
    }
})
</script>

<template>
    <div
        class="border shadow-sm rounded-lg p-6 w-full max-w-sm"
        :class="colorClass"
    >
        <div class="text-sm font-medium mb-2">
            Taxa de Reembolso
        </div>

        <div v-if="loading" class="text-sm opacity-70">
            Carregando...
        </div>

        <div v-else-if="error" class="text-sm text-red-600">
            Erro ao carregar
        </div>

        <div v-else class="space-y-3">
            <div class="text-3xl font-bold">
                {{ rate }}%
            </div>

            <div class="text-sm">
                {{ refunded }} de {{ total }} pedidos reembolsados
            </div>

            <!-- Barra de indicador -->
            <div class="w-full bg-white/50 rounded-full h-3 overflow-hidden">
                <div
                    class="h-3 rounded-full transition-all duration-500"
                    :class="barClass"
                    :style="{ width: rate + '%' }"
                ></div>
            </div>
        </div>
    </div>
</template>
