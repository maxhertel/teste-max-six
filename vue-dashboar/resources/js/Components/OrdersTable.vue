<script setup>
import { ref, onMounted, watch } from 'vue'

const orders = ref([])
const loading = ref(true)
const error = ref(false)

const search = ref('')

const currentPage = ref(1)
const lastPage = ref(1)
const perPage = ref(10)
const total = ref(0)

const fetchOrders = async () => {
    loading.value = true

    try {
        const url = `http://127.0.0.1:8000/metrics/orders-table?page=${currentPage.value}&search=${search.value}`
        const response = await fetch(url)
        const data = await response.json()

        if (data.success) {
            orders.value = data.orders

            currentPage.value = data.pagination.current_page
            lastPage.value = data.pagination.last_page
            perPage.value = data.pagination.per_page
            total.value = data.pagination.total

            error.value = false
        } else {
            error.value = true
        }
    } catch (e) {
        error.value = true
    } finally {
        loading.value = false
    }
}

// Atualizar automaticamente ao pesquisar
watch(search, () => {
    currentPage.value = 1
    fetchOrders()
})

onMounted(() => {
    fetchOrders()
})
</script>

<template>
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-4 gap-3">
            <h2 class="text-lg font-semibold text-gray-800">
                Pedidos
            </h2>

            <input
                v-model="search"
                type="text"
                placeholder="Buscar por nome, email ou pedido..."
                class="border rounded-lg px-4 py-2 text-sm w-full sm:w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
        </div>

        <div v-if="loading" class="text-gray-500 text-sm">
            Carregando pedidos...
        </div>

        <div v-else-if="error" class="text-red-600 text-sm">
            Erro ao carregar pedidos
        </div>

        <div v-else class="overflow-x-auto">
            <table class="min-w-full border border-gray-100 text-sm">
                <thead class="bg-gray-50 text-gray-600 uppercase tracking-wide text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Pedido</th>
                        <th class="px-4 py-3 text-left">Cliente</th>
                        <th class="px-4 py-3 text-left">Email</th>
                        <th class="px-4 py-3 text-left">Pagamento</th>
                        <th class="px-4 py-3 text-left">Entrega</th>
                        <th class="px-4 py-3 text-left">Valor</th>
                        <th class="px-4 py-3 text-left">Data</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="order in orders"
                        :key="order.id"
                        class="hover:bg-gray-50"
                    >
                        <td class="px-4 py-3 font-medium text-gray-900">
                            #{{ order.order_number }}
                        </td>

                        <td class="px-4 py-3">
                            {{ order.customer_name }}
                        </td>

                        <td class="px-4 py-3 text-gray-500">
                            {{ order.customer_email }}
                        </td>

                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium"
                                :class="order.financial_status === 'paid'
                                    ? 'bg-green-100 text-green-700'
                                    : 'bg-yellow-100 text-yellow-700'"
                            >
                                {{ order.financial_status }}
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium"
                                :class="order.fulfillment_status === 'Fully Fulfilled'
                                    ? 'bg-green-100 text-green-700'
                                    : order.fulfillment_status === 'Partially Fulfilled'
                                        ? 'bg-yellow-100 text-yellow-700'
                                        : 'bg-red-100 text-red-700'"
                            >
                                {{ order.fulfillment_status }}
                            </span>
                        </td>

                        <td class="px-4 py-3 font-semibold">
                            R$ {{ Number(order.amount_float).toFixed(2) }}
                        </td>

                        <td class="px-4 py-3 text-gray-500">
                            {{ order.formatted_date }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Paginação -->
            <div class="flex flex-col sm:flex-row items-center justify-between mt-4 gap-3">
                <div class="text-sm text-gray-500">
                    Mostrando {{ orders.length }} de {{ total }} pedidos
                </div>

                <div class="flex items-center gap-2">
                    <button
                        class="px-3 py-1 border rounded text-sm disabled:opacity-50"
                        :disabled="currentPage === 1"
                        @click="currentPage--; fetchOrders()"
                    >
                        Anterior
                    </button>

                    <span class="text-sm text-gray-600">
                        Página {{ currentPage }} de {{ lastPage }}
                    </span>

                    <button
                        class="px-3 py-1 border rounded text-sm disabled:opacity-50"
                        :disabled="currentPage === lastPage"
                        @click="currentPage++; fetchOrders()"
                    >
                        Próxima
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
