<script setup>
import { ref, onMounted } from 'vue'

const totalOrders = ref(null)
const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/metrics/total-orders')
        const data = await response.json()

        if (data.success) {
            totalOrders.value = data.total_orders
        } else {
            error.value = true
        }
    } catch (e) {
        error.value = true
    } finally {
        loading.value = false
    }
})
</script>

<template>
    <div class="bg-white shadow-sm rounded-lg p-6 border border-gray-100 w-full max-w-sm">
        <div class="text-sm text-gray-500 mb-2">
            Total de Pedidos
        </div>

        <div v-if="loading" class="text-gray-400 text-lg">
            Carregando...
        </div>

        <div v-else-if="error" class="text-red-500 text-lg">
            Erro ao carregar
        </div>

        <div v-else class="text-3xl font-bold text-gray-800">
            {{ totalOrders }}
        </div>
    </div>
</template>
