<script setup>
import { ref, onMounted } from 'vue'

const gross = ref('')
const refunds = ref('')
const net = ref('')

const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/metrics/financial-summary')
        const data = await response.json()

        if (data.success) {
            gross.value = data.gross_revenue_formatted
            refunds.value = data.total_refunds_formatted
            net.value = data.net_revenue_formatted
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
    <div>
        <div v-if="loading" class="text-gray-400">
            Carregando...
        </div>

        <div v-else-if="error" class="text-red-500">
            Erro ao carregar dados financeiros
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Faturamento Bruto -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 mb-1">
                    Faturamento Bruto
                </div>
                <div class="text-2xl font-bold text-gray-900">
                    {{ gross }}
                </div>
            </div>

            <!-- Reembolsos -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 mb-1">
                    Total de Reembolsos
                </div>
                <div class="text-2xl font-bold text-red-600">
                    {{ refunds }}
                </div>
            </div>

            <!-- Receita Líquida -->
            <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-6">
                <div class="text-sm text-gray-500 mb-1">
                    Receita Líquida
                </div>
                <div
                    class="text-2xl font-bold"
                    :class="net.includes('-') ? 'text-red-600' : 'text-green-600'"
                >
                    {{ net }}
                </div>
            </div>
        </div>
    </div>
</template>
