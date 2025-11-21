<script setup>
import { ref, onMounted } from 'vue'

const data = ref(null)
const loading = ref(true)
const error = ref(false)

onMounted(async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/metrics/upsell-analysis')
    data.value = await res.json()
  } catch {
    error.value = true
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="bg-white border border-gray-200 shadow-sm rounded-lg p-4 w-full">
    <div class="text-sm font-medium text-gray-700 mb-3">
      Análise de Upsell
    </div>

    <div v-if="loading">Carregando...</div>
    <div v-else-if="error" class="text-red-500">Erro ao carregar</div>

    <div v-else class="space-y-2 text-sm">
      <div>Total de pedidos: <strong>{{ data.total_orders }}</strong></div>
      <div>Pedidos com upsell: <strong>{{ data.orders_with_upsell }}</strong></div>
      <div>Taxa de upsell: <strong>{{ data.upsell_rate }}%</strong></div>
      <div>Receita upsell: <strong>R{{ data.total_upsell_revenue_formatted }}</strong></div>
    </div>
  </div>
</template>
