<template>
    <div>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
            <h1 class="text-2xl font-semibold mb-6 text-center">Sélectionner le dossier</h1>
        </div>
    </div>

    <div class="flex justify-center items-center mt-20">
        <div class="flex flex-col md:flex-row gap-2">

            <!-- 🌟 Card Poste-Immatriculation -->
            <div
                class="relative w-72 p-6 bg-white rounded-xl shadow-md hover:shadow-xl cursor-pointer transition-all border border-gray-200 text-center">

                <Link :href="route('numerisation.dossier.documentslist.with.dossier.lier', { id: dossier_lier.id })">
                <h2 class="text-xl font-semibold mb-2">Poste-Immatriculation</h2>
                <p class="text-gray-600 text-sm">Ouvrir le dossier lié</p>
                <!-- <p>{{ dossier_lier.statut_numerisation }}</p> -->
                </Link>
            </div>

            <!-- 🌟 Card Duplicata -->
            <div
                class="relative w-72 p-6 bg-white rounded-xl shadow-md hover:shadow-xl cursor-pointer transition-all border border-gray-200 text-center">

                <Link :href="route('numerisation.dossier.documentslist.with.dossier.lier', { id: dossier.id })">

                <h2 class="text-xl font-semibold mb-2">Duplicata</h2>
                <p class="text-gray-600 text-sm">Ouvrir le dossier principal</p>
                </Link>
            </div>

        </div>
    </div>
</template>

<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed, onMounted } from 'vue'

const props = defineProps({
    dossier: Object,
    dossier_lier: Object
})




const dossier = computed(() => props.dossier)
const dossier_lier = computed(() => props.dossier_lier)


</script>

<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>
