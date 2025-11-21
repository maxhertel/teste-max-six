<script setup>
import { ref, onMounted } from 'vue'

const delivered = ref(null)
const total = ref(null)
const rate = ref(0)

const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/metrics/delivery')
        const data = await response.json()

        if (data.delivered_orders_count !== undefined) {
            delivered.value = data.delivered_orders_count
            total.value = data.total_orders_count
            rate.value = data.delivery_rate_percent
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
            Pedidos Entregues
        </div>

        <div v-if="loading" class="text-gray-400">
            Carregando...
        </div>

        <div v-else-if="error" class="text-red-500">
            Erro ao carregar
        </div>

        <div v-else>
            <div class="text-sm text-gray-600">
                {{ delivered }} entregues de {{ total }} pedidos
            </div>

            <div class="flex items-center gap-3">
                <div class="text-2xl font-bold text-gray-900">
                    {{ rate }}%
                </div>

                <div class="flex-1 w-full bg-gray-100 rounded-full h-3 overflow-hidden">
                    <div class="bg-blue-600 h-3 rounded-full transition-all duration-500"
                        :style="{ width: rate + '%' }"></div>
                </div>
            </div>


            <!-- Barra percentual -->

        </div>
    </div>
</template>
