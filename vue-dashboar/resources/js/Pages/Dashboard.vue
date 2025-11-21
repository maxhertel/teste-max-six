<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TotalOrdersCard from '@/Components/TotalOrdersCard.vue'
import TotalRevenueCard from '@/Components/TotalRevenueCard.vue'
import UniqueCustomersCard from '@/Components/UniqueCustomersCard.vue'
import DeliveryRateCard from '@/Components/DeliveryRateCard.vue'
import FinancialSummaryCards from '@/Components/FinancialSummaryCards.vue'
import RefundRateCard from '@/Components/RefundRateCard.vue'
import BestSellingProductCard from '@/Components/BestSellingProductCard.vue'
import OrdersTable from '@/Components/OrdersTable.vue'
</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="grid grid-cols-6 gap-6 mb-6">
                    <div class="col-span-1 mb-6">
                        <TotalOrdersCard />
                    </div>
                    <div class="col-span-2">
                        <TotalRevenueCard />
                    </div>
                    <div class="col-span-2">
                        <DeliveryRateCard />
                    </div>
                    <div class="col-span-1">
                        <UniqueCustomersCard />
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-6 mb-6">
                    <div class="col-span-6">
                        <FinancialSummaryCards />
                    </div>
                </div>
                <div class="grid grid-cols-6 gap-6 mb-6">
                    <div class="col-span-2">
                        <RefundRateCard />
                    </div>
                    <div class="col-span-2">
                        <BestSellingProductCard />
                    </div>
                </div>
                <div class="mt-8">
                    <OrdersTable />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
