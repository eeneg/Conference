<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

</script>

<style>
.bg-hero {
    background-image: url('/storage/pics/img.jpg');
    background-size: cover;
    background-position: center;
}
</style>

<template>
    <Head title="Welcome" />

    <div
        class="relative max-h-screen sm:flex sm:justify-center sm:items-center min-h-screen bg-center bg-gray-100 dark:bg-gray-900 selection:bg-red-500 selection:text-white"
    >
        <div class="flex flex-col w-full">
            <div v-if="canLogin" class="sm:fixed sm:top-0 sm:right-0 p-6 text-right w-full">
                <Link
                    v-if="$page.props.auth.user"
                    :href="route('dashboard')"
                    class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                    >Dashboard</Link
                >

                <template v-else>
                    <Link
                        :href="route('login')"
                        class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500"
                        >Log in</Link
                    >
                </template>
            </div>

             <!-- Features Section -->
             <section class="py-15 dark:text-white light:text-black">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto text-center p-6">
                        <h1 class="text-4xl font-bold mb-6">
                            PAPERLESS CONFERENCE AND FILE MANAGEMENT SYSTEM
                        </h1>
                    </div>
                </div>
            </section>


            <!-- Features Section -->
            <section class="py-20 bg-hero light:text-white light:text-black">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto text-center bg-slate-200 p-6 rou opacity-75 rounded">
                        <h1 class="text-4xl font-bold mb-6">Go Paperless with Ease</h1>
                        <p class="text-lg mb-8">Efficiently manage your sessions and files with our minimalist platform.</p>
                    </div>
                </div>
            </section>

            <!-- Features Section -->
            <section class="py-5">
                <div class="container mx-auto px-4">
                    <div class="max-w-4xl mx-auto text-center">
                        <h2 class="text-3xl font-bold mb-10 dark:text-white light:text-black">Key Features</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <div class="p-4 border rounded-md shadow-md bg-white">
                                <h3 class="text-xl font-semibold mb-4">Efficient Session Management</h3>
                                <p class="text-gray-700">Organize and manage your sessions effortlessly.</p>
                            </div>
                            <div class="p-4 border rounded-md shadow-md bg-white">
                                <h3 class="text-xl font-semibold mb-4">Secure File Storage</h3>
                                <p class="text-gray-700">Store your files securely in the cloud.</p>
                            </div>
                            <div class="p-4 border rounded-md shadow-md bg-white">
                                <h3 class="text-xl font-semibold mb-4">Collaborative Environment</h3>
                                <p class="text-gray-700">Work together with your team in real-time.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>

    </div>
</template>

<style>
</style>
