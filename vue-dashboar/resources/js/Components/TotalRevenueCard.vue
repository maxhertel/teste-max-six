<script setup>
import { ref, onMounted } from 'vue'

const usd = ref('')
const brl = ref('')
const loading = ref(true)
const error = ref(false)

onMounted(async () => {
    try {
        const response = await fetch('http://127.0.0.1:8000/metrics/total-revenue')
        const data = await response.json()

        if (data.success) {
            usd.value = data.total_usd_formatted
            brl.value = data.total_brl_formatted
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
        class="bg-green-50 border border-green-200 shadow-sm rounded-lg p-6 w-full max-w-sm"
    >
        <div class="text-sm font-medium text-green-700 mb-2">
            Receita Total
        </div>

        <div v-if="loading" class="text-green-400 text-lg">
            Carregando...
        </div>

        <div v-else-if="error" class="text-red-500 text-lg">
            Erro ao carregar
        </div>

        <div v-else class="space-y-2">
            <div class="text-2xl font-bold text-green-800">
                {{ usd }} | {{ brl }}
            </div>
            <div class="text-lg font-semibold text-green-600">
                
            </div>
        </div>
    </div>
</template>
