<script setup>
import { ref, onMounted } from 'vue'

const name = ref('')
const quantity = ref(0)
const revenue = ref('')

const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/metrics/best-selling-product')
        const data = await response.json()

        if (data.success) {
            name.value = data.product_name
            quantity.value = data.total_quantity
            revenue.value = data.total_revenue_formatted
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
    <div
        class="bg-gradient-to-br from-indigo-50 to-indigo-100 border border-indigo-200 shadow-md rounded-xl p-6 w-full max-w-sm"
    >
        <div class="text-xs font-semibold uppercase tracking-wide text-indigo-600 mb-2">
            Produto Mais Vendido
        </div>

        <div v-if="loading" class="text-indigo-400 text-sm">
            Carregando...
        </div>

        <div v-else-if="error" class="text-red-600 text-sm">
            Erro ao carregar
        </div>

        <div v-else class="space-y-3">
            <div class="text-lg font-bold text-indigo-900">
                {{ name }}
            </div>

            <div class="text-sm text-indigo-700">
                Quantidade vendida: <strong>{{ quantity }}</strong>
            </div>

            <div class="text-2xl font-bold text-indigo-800">
                Valor Un. R{{ revenue }}
            </div>
        </div>
    </div>
</template>
