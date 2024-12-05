<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3'; // Pastikan Link diimpor

</script>

<template>

    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                Dashboard
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <!-- Tambahkan Button di atas tulisan -->
                <div class="mb-4">
                    <Link href="/rekomendasi-obat"
                        class="px-4 py-2 text-sm font-medium text-white bg-[#28AE96] rounded-md hover:bg-[#28AE96] focus:outline-none focus:ring focus:ring-[#28AE96]">
                    Rekomendasi Obat
                    </Link>
                </div>
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg dark:bg-gray-800">


                    <div class="p-6 text-gray-900 dark:text-gray-100">



                        <!-- Tulisan You're logged in! -->
                        You're logged in!
                    </div>

                </div>
            </div>
        </div>


    </AuthenticatedLayout>
</template>
