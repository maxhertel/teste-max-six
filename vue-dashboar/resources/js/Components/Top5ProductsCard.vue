<script setup>
import { ref, onMounted } from 'vue'

const products = ref([])
const loading = ref(true)
const error = ref(false)

onMounted(async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/metrics/top-5-products')
    const data = await res.json()
    products.value = data.top_products || []
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
      Top 5 Produtos
    </div>

    <div v-if="loading">Carregando...</div>
    <div v-else-if="error" class="text-red-500">Erro ao carregar</div>

    <ul v-else class="space-y-2 text-sm">
      <li v-for="(p, i) in products" :key="i" class="flex justify-between">
        <span class="truncate max-w-[70%]">{{ p.name }}</span>
        <span class="font-medium">R{{ p.total_revenue_formatted }}</span>
      </li>
    </ul>
  </div>
</template>
