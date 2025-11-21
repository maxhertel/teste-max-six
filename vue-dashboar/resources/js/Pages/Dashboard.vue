<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import TotalOrdersCard from '@/Components/TotalOrdersCard.vue'
import TotalRevenueCard from '@/Components/TotalRevenueCard.vue'
import UniqueCustomersCard from '@/Components/UniqueCustomersCard.vue'
import DeliveryRateCard from '@/Components/DeliveryRateCard.vue'
import FinancialSummaryCards from '@/Components/FinancialSummaryCards.vue'
import RefundRateCard from '@/Components/RefundRateCard.vue'


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

                <!-- Card -->
                <div class="grid grid-cols-6 gap-6 mb-6">

                    <!-- Card menor (1/3 da linha) -->
                    <div class="col-span-1 mb-6">
                        <TotalOrdersCard />
                    </div>

                    <!-- Card maior (2/3 da linha) -->
                    <div class="col-span-2">
                        <TotalRevenueCard />
                    </div>
                    <div class="col-span-2">
                        <DeliveryRateCard />
                    </div>
                    <!-- Card maior (2/3 da linha) -->
                    <div class="col-span-1">
                        <UniqueCustomersCard />
                    </div>

                </div>

                <!-- Card -->
                <div class="grid grid-cols-6 gap-6 mb-6">
                    <div class="col-span-6">
                        <FinancialSummaryCards />
                    </div>
                </div>

                <RefundRateCard/>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
