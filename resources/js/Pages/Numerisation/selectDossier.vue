<template>
    <div>
        <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8">
            <h1 class="text-2xl font-semibold mb-6 text-center">Sélectionner le dossier à numériser</h1>
        </div>
    </div>

    <div class="flex justify-center items-center mt-20">
        <div class="flex flex-col md:flex-row gap-2">

            <!-- 🌟 Card Poste-Immatriculation -->
            <div class="relative w-72 p-6 bg-white rounded-xl shadow-md hover:shadow-xl cursor-pointer transition-all border border-gray-200 text-center"
                :class="dossier_lier.statut_numerisation == 2 ? 'opacity-60' : ''">

                <Link
                    :href="route('show.dupli.post.form.numerisation', { dossier: dossier_lier.id, service: dossier_lier.r_dossier_services.nom_service, detail: dossier_lier.detail, physique_morale: dossier_lier.r_dossier_vehicule.physique_morale })"
                    :target="dossier_lier.statut_numerisation == 2 ? '_blank' : '_self'">
                <h2 class="text-xl font-semibold mb-2">Poste-Immatriculation</h2>
                <p class="text-gray-600 text-sm">Ouvrir le dossier lié</p>
                <!-- <p>{{ dossier_lier.statut_numerisation }}</p> -->
                </Link>

                <!-- 🔴 Overlay si déjà numérisé -->
                <div v-if="dossier_lier.statut_numerisation == 2"
                    class="absolute inset-0 bg-black bg-opacity-40 rounded-xl flex items-center justify-center text-white font-semibold text-lg">
                    Déjà numérisé
                </div>
            </div>

            <!-- 🌟 Card Duplicata -->
            <div class="relative w-72 p-6 bg-white rounded-xl shadow-md hover:shadow-xl cursor-pointer transition-all border border-gray-200 text-center"
                :class="dossier.statut_numerisation == 2 ? 'opacity-60' : ''">

                <Link
                    :href="route('show.dupli.post.form.numerisation', { dossier: dossier.id, service: dossier.r_dossier_services.nom_service, detail: dossier.detail, physique_morale: dossier.r_dossier_vehicule.physique_morale })"
                    :target="dossier.statut_numerisation == 2 ? '_blank' : '_self'">
                <h2 class="text-xl font-semibold mb-2">Duplicata</h2>
                <p class="text-gray-600 text-sm">Ouvrir le dossier principal</p>
                </Link>

                <!-- 🔴 Overlay si déjà numérisé -->
                <div>
                    <!-- <Link :href="route('numerisation.dossier.documentslist', { id: dossier.id })"> -->
                    <div v-if="dossier.statut_numerisation == 2"
                        class="absolute inset-0 bg-black bg-opacity-40 rounded-xl flex items-center justify-center text-white font-semibold text-lg">
                        Déjà numérisé
                    </div>
                    <!-- </Link> -->

                </div>

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



onMounted(() => {
    if (
        dossier.value?.statut_numerisation === 2 &&
        dossier_lier.value?.statut_numerisation === 2
    ) {
        router.visit('/numerisation/list')
    }
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
