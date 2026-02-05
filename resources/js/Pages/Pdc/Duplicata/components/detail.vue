<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Duplicata
        </h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.new.pdc.duplicata')">
            <BoutonRetour />
            </Link>
        </div>
    </div>

    <!-- Contenu scrollable -->
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col ">
                <ScrollArea class="h-full w-full rounded-md ">
                    <div class="">
                        <div class="bg-white dark:bg-gray-800 rounded-2xl p-10 w-full shadow-2xl space-y-6 ">
                            <!-- <h3 class="text-2xl font-bold text-center mb-2">Résumé des informations</h3> -->
                            <!-- <p>{{ data }}</p> -->
                            <!-- Informations du propriétaire -->
                            <div>
                                <h4 class="text-lg font-semibold mb-4">Informations du propriétaire :
                                    <samp class="text-[#ca7600]"> {{ data.r_vehicule_client.civilite }} {{
                                        data.r_vehicule_client.nom }} {{
                                            data.r_vehicule_client.prenom }}</samp>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <p><strong>Email :</strong> {{ data.r_vehicule_client.email }}</p>
                                    <p><strong>Adresse :</strong> {{ data.r_vehicule_client.adresse }}</p>
                                    <p><strong>Ville de naissance :</strong> {{ data.r_vehicule_client.ville_naissance
                                    }}
                                    </p>
                                    <p><strong>Téléphone :</strong> {{ data.r_vehicule_client.telephone }}</p>
                                    <!-- <p><strong>Type de personne :</strong> {{ data.r_vehicule_client.typePersonne }}</p> -->
                                    <p><strong>Date de naissance :</strong> {{ data.r_vehicule_client.date_naissance }}
                                    </p>
                                    <p v-if="data.r_vehicule_client.district_client"><strong>District :</strong> {{
                                        data.r_vehicule_client.district_client }}

                                    </p>
                                </div>
                            </div>

                            <Separator class="my-4" />

                            <!-- Informations du véhicule -->
                            <div>
                                <h4 class="text-lg font-semibold mb-4">Informations du véhicule :
                                    <samp class="text-[#ca7600]">{{ data.num_immatriculation }}</samp>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <p><strong>Marque :</strong> {{ data.marque }}</p>
                                    <p><strong>Modèle :</strong> {{ data.modele }}</p>
                                    <p><strong>VIN :</strong> {{ data.vin }}</p>
                                    <p><strong>Couleur :</strong> {{ data.couleur }}</p>
                                    <p><strong>Carrosserie :</strong> {{ data.carrosserie }}</p>
                                    <p><strong>Type technique :</strong> {{ data.type_technique }}</p>
                                    <p><strong>Genre :</strong> {{ data.genre_vehicule }}</p>
                                    <p><strong>PTAC :</strong> {{ data.poids_total_charge }}</p>
                                    <p><strong>PU :</strong> {{ data.poids_utile }}</p>
                                    <p><strong>PV :</strong> {{ data.poids_vide }}</p>
                                    <p><strong>Puissance :</strong> {{ data.puissance_administrative }}</p>
                                    <p><strong>Places assises :</strong> {{ data.places_assises }}</p>
                                    <p><strong>Source d'énergie :</strong> {{ data.source_energie }}</p>
                                    <p><strong>Nombre d'essieux :</strong> {{ data.nombre_essieux }}</p>
                                    <p><strong>Date de circulation :</strong> {{ data.date_mise_circulation }}</p>
                                    <p><strong>Année de production :</strong> {{ data.annee_production }}</p>
                                    <p><strong>Date d'immatricumation :</strong> {{ data.date_immatriculation }}</p>
                                    <p><strong>Usage du véhicule :</strong> {{ data.usage_vehicule }}</p>

                                </div>
                            </div>

                            <Separator class="my-4" />

                            <!-- Informations de l'entreprise (si type Morale) -->
                            <div v-if="data.physique_morale == 'Physiques'">
                                <h4 class="text-lg font-semibold mb-4">Informations de l'entreprise :
                                    <samp class="text-[#ca7600]">{{ data.r_vehicule_entreprise.nom_entreprise }}</samp>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <p><strong>Nom entreprise :</strong> {{ data.r_vehicule_entreprise.nom_entreprise }}
                                    </p>
                                    <p><strong>Registre de commerce :</strong> {{
                                        data.r_vehicule_entreprise.registre_commerce }}</p>
                                    <p><strong>Représentant légal :</strong> {{
                                        data.r_vehicule_entreprise.nom_representant_legal }}</p>
                                    <p><strong>Téléphone :</strong> {{
                                        data.r_vehicule_entreprise.telephone_representant_legal }}</p>
                                    <p><strong>Compte contribuable :</strong> {{
                                        data.r_vehicule_entreprise.compte_contribuale }}</p>
                                    <p><strong>Préfecture :</strong> {{ data.r_vehicule_entreprise.prefecture }}</p>
                                    <p><strong>Sous-préfecture :</strong> {{ data.r_vehicule_entreprise.sous_prefecture
                                    }}</p>
                                    <p><strong>Région :</strong> {{ data.r_vehicule_entreprise.region }}</p>
                                    <p><strong>Profession représentant :</strong> {{
                                        data.r_vehicule_entreprise.profession_representant_legal }}</p>
                                    <p><strong>Date naissance représentant :</strong> {{
                                        data.r_vehicule_entreprise.date_de_naissance_representant_legal
                                    }}</p>
                                </div>
                            </div>
                            <div
                                class="flex flex-col space-y-4 px-2  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                <div></div>
                                <div class="flex items-center space-x-2">
                                    <!-- show.new.pdc.post.immatriculation -->

                                    <Link :href="route('show.new.pdc.duplicata.post', { vin: props.vin })">
                                    <Button class="bg-[#ca7600]">
                                        <Plus class="w-4 h-4 mr-2" /> Ouvrir un post
                                    </Button>
                                    </Link>
                                    <!-- <Link :href="route('show.new.service.pdc.duplicata', props.data.immatriculation.vin + '_' +
                                        props.data.immatriculation.genre.nb_plaque)"> -->
                                    <Button class="bg-[#068A06]" @click="newDuplicata()">
                                        <CircleArrowRight class="w-4 h-4 mr-2" /> Continue
                                    </Button>
                                    <!-- </Link> -->
                                </div>
                            </div>
                        </div>
                    </div>
                </ScrollArea>
            </Card>
        </main>
        <Toaster richColors position="top-right" />
    </div>
</template>

<script setup>
import { Button } from '@/components/ui/button'
import { Card } from '@/components/ui/card'
import { Toaster } from 'vue-sonner'
import { ScrollArea } from '@/components/ui/scroll-area'
import { Separator } from '@/components/ui/separator';
import { ref, onMounted, computed } from 'vue'
import { Plus, CircleArrowRight } from 'lucide-vue-next';
import { Link, router } from '@inertiajs/vue3'
import axios from 'axios'

// Props pour recevoir les données existantes
const props = defineProps({
    vin: String,
    dossier: Object,
    data: Object
});

// Accès direct aux données via props.data.immatriculation
const data = computed(() => props.data.immatriculation || {});

// Initialisation au montage du composant
onMounted(() => {
    console.log('Données reçues:', props.data.immatriculation);
});

const newDuplicata = () => {
    const vinWithGenre = `${props.data.immatriculation.vin}_${props.data.immatriculation.genre.nb_plaque}`;
    const donne = {
        vinWithGenre: vinWithGenre
    };

    router.visit(`/pdc/duplicata/new/service/${encodeURIComponent(JSON.stringify(donne))}`);
};

</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

export default {
    layout: Main,
};
</script>