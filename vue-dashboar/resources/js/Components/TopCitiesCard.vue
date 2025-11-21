<script setup>
import { ref, onMounted } from 'vue'

const cities = ref([])
const loading = ref(true)
const error = ref(false)

onMounted(async () => {
  try {
    const res = await fetch('http://127.0.0.1:8000/metrics/top-10-cities')
    const data = await res.json()
    cities.value = data.top_cities || []
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
      Top 10 Cidades
    </div>

    <div v-if="loading">Carregando...</div>
    <div v-else-if="error" class="text-red-500">Erro ao carregar</div>

    <div v-else class="overflow-x-auto">
      <table class="w-full text-sm border-collapse">
        <thead>
          <tr class="border-b text-left">
            <th class="py-1">Cidade</th>
            <th class="py-1">Pedidos</th>
            <th class="py-1">Receita</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(c, i) in cities" :key="i" class="border-b">
            <td class="py-1">{{ c.city }} - {{ c.state }}</td>
            <td class="py-1">{{ c.order_count }}</td>
            <td class="py-1 font-medium">R{{ c.total_revenue_formatted }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
