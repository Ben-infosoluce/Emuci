<template>
    <div>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
            <h1 class="text-2xl font-semibold mb-6 text-center">Que souhaitez-vous faire ?</h1>
        </div>
    </div>

    <div class="flex justify-center items-center mt-20">
        <div class="flex flex-col md:flex-row gap-2">

            <!-- 🌟 Card Modification -->
            <Link :href="route('show.pdc.modification')">

            <div
                class="relative w-72 p-6 bg-white rounded-xl shadow-md hover:shadow-xl cursor-pointer transition-all border border-gray-200 text-center">
                <h2 class="text-xl font-semibold m-6">Mes Dossiers</h2>
                <!-- <p class="text-gray-600 text-sm">Ouvrir le dossier lié</p> -->
                <!-- <p>{{ dossier_lier.statut_numerisation }}</p> -->
                <!-- 🔴 Overlay si déjà numérisé -->
            </div>
            </Link>


            <!-- 🌟 Card Nouveau Dossier -->
            <Link :href="route('show.new.pdc.modification')">
            <div
                class="relative w-72 p-6 bg-white rounded-xl shadow-md hover:shadow-xl cursor-pointer transition-all border border-gray-200 text-center">

                <h2 class="text-xl font-semibold m-6">Nouveau Dossier</h2>
                <!-- <p class="text-gray-600 text-sm">Créer un nouveau dossier de modification</p> -->
                <!-- 🔴 Overlay si déjà numérisé -->
            </div>
            </Link>

        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'



</script>

<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>
