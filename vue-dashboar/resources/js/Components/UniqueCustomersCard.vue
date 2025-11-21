<script setup>
import { ref, onMounted } from 'vue'

const uniqueCustomers = ref(null)
const avgOrders = ref(null)
const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/metrics/unique-customers')
        const data = await response.json()

        if (data.success) {
            uniqueCustomers.value = data.unique_customers
            avgOrders.value = data.avg_orders_per_customer
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
    <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-6 w-full max-w-sm">
        <div class="text-sm font-medium text-gray-700 mb-2">
            Clientes
        </div>

        <div v-if="loading" class="text-gray-400">
            Carregando...
        </div>

        <div v-else-if="error" class="text-red-500">
            Erro ao carregar
        </div>

        <div v-else class="space-y-2">
            <div class="text-2xl font-bold text-gray-900">
                {{ uniqueCustomers }} clientes únicos
            </div>

            <div class="text-sm text-gray-500">
                Média de pedidos: {{ avgOrders }}
            </div>
        </div>
    </div>
</template>
