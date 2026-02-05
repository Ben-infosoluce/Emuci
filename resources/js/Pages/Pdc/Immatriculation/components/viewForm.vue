<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Immatriculation Spéciale
        </h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.pdc.immatriculation')">
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
                            <h3 class="text-2xl font-bold text-center mb-2">Résumé des informations</h3>
                            <!-- <p>{{ data }}</p> -->

                            <!-- Type de Service & motif de rejet -->
                            <div
                                class=" flex flex-col space-y-4  py-2 sm:flex-row sm:items-center justify-between sm:space-y-0">
                                <div>
                                    <h2>
                                        <strong>Type de Service : </strong>
                                        <template v-if="dossier.detail && Array.isArray(dossier.detail)">
                                            {{ dossier.detail.join(', ') }}
                                        </template>
                                        <template v-else-if="dossier.detail && typeof dossier.detail === 'string'">
                                            {{ JSON.parse(dossier.detail).join(', ') }}
                                        </template>
                                    </h2>
                                </div>
                                <!-- Motif de rejet -->
                                <div v-if="dossier.motif_rejet">
                                    <div class="">
                                        <p class="border border-red-300 rounded-md p-3">
                                            <strong>Motif de rejet : </strong>
                                            <AlertDialog>
                                                <AlertDialogTrigger>

                                                    <Badge @click="fetchMotifsRejets()" variant="error"
                                                        class="mx-2 flex items-center cursor-pointer ">
                                                        <span>Voir les motifs</span>
                                                        <MoveRight class="w-4 h-4 ml-2" />
                                                    </Badge>

                                                </AlertDialogTrigger>
                                                <AlertDialogContent class="max-w-4xl w-full">
                                                    <AlertDialogHeader>
                                                        <AlertDialogTitle>Détails du rejet du dossier</AlertDialogTitle>
                                                        <AlertDialogDescription>
                                                            <ScrollArea class="h-[400px] w-full rounded-md border p-4">
                                                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4 mb-6"
                                                                    v-if="motifDossierRejeter.length">
                                                                    <div v-for="motif in motifDossierRejeter"
                                                                        :key="motif.id"
                                                                        class="flex items-center space-x-2">
                                                                        <Checkbox checked="true" class="mr-2"
                                                                            :id="'motif-' + motif.id" />
                                                                        <Label :for="'motif-' + motif.id">{{ motif.motif
                                                                            }}</Label>
                                                                    </div>
                                                                </div>
                                                                <p v-else class="text-gray-500 italic">
                                                                    Chargement des données...
                                                                </p>
                                                            </ScrollArea>
                                                        </AlertDialogDescription>
                                                    </AlertDialogHeader>
                                                    <AlertDialogFooter class="flex justify-center gap-4 mt-6">
                                                        <AlertDialogCancel
                                                            class="bg-gray-200 text-gray-800 hover:bg-gray-300 px-4 py-2 rounded-md">
                                                            Fermer
                                                        </AlertDialogCancel>
                                                    </AlertDialogFooter>
                                                </AlertDialogContent>

                                            </AlertDialog>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <!-- Informations du propriétaire -->
                            <div>
                                <h4 class="text-lg font-semibold mb-4">Informations du propriétaire :
                                    <samp class="text-[#ca7600]"> {{ data.civilite }} {{ data.firstname }} {{
                                        data.lastname }}</samp>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <p><strong>Email :</strong> {{ data.email }}</p>
                                    <p><strong>Adresse :</strong> {{ data.adresse }}</p>
                                    <p><strong>District :</strong> {{ data.district_client }}</p>
                                    <p><strong>Ville de naissance :</strong> {{ data.villeNaissance }}</p>
                                    <p><strong>Téléphone :</strong> {{ data.phone }}</p>
                                    <p><strong>Type de personne :</strong> {{ data.type_personne }}</p>
                                    <p><strong>Date de naissance :</strong> {{ data.DateNaissance }}</p>
                                </div>
                            </div>

                            <Separator class="my-4" />

                            <!-- Informations du véhicule -->
                            <div>
                                <h4 class="text-lg font-semibold mb-4">Informations du véhicule :
                                    <samp class="text-[#ca7600]">{{ data.marqueVehicule?.nom }}</samp>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <p><strong>Marque :</strong> {{ data.marqueVehicule?.nom }}</p>
                                    <p><strong>Modèle :</strong> {{ data.modelVehicule?.nom }}</p>
                                    <p><strong>VIN :</strong> {{ data.vin }}</p>
                                    <p><strong>Couleur :</strong> {{ data.couleurVehicule }}</p>
                                    <p><strong>Carrosserie :</strong> {{ data.carrosserie }}</p>
                                    <p><strong>Type technique :</strong> {{ data.typeTechnique }}</p>
                                    <p><strong>Genre :</strong> {{ data.genre }}</p>
                                    <p><strong>PTAC :</strong> {{ data.ptac }}</p>
                                    <p><strong>PU :</strong> {{ data.pu }}</p>
                                    <p><strong>PV :</strong> {{ data.pv }}</p>
                                    <p><strong>Puissance :</strong> {{ data.puissance }}</p>
                                    <p><strong>Places assises :</strong> {{ data.placesAssises }}</p>
                                    <p><strong>Source d'énergie :</strong> {{ data.sourcesEnergie }}</p>
                                    <p><strong>Nombre d'essieux :</strong> {{ data.nombreEssieux }}</p>
                                    <p><strong>Date de circulation :</strong> {{ data.DateCirculation }}</p>
                                    <p><strong>Année de production :</strong> {{ data.AnneeProduction }}</p>
                                    <p><strong>Nombre de plaques :</strong> {{ dossier.r_dossier_vehicule.nb_plaque }}
                                    </p>
                                    <p><strong>Code de région :</strong> {{ data.codeRegion }}</p>
                                    <p><strong>Cylindrée :</strong> {{ dossier.r_dossier_vehicule.cylindree }}</p>
                                </div>
                            </div>

                            <Separator class="my-4" />

                            <!-- Informations de l'entreprise (si type Morale) -->
                            <div v-if="data.type != 'Physique'">
                                <h4 class="text-lg font-semibold mb-4">Informations de l'entreprise :
                                    <samp class="text-[#ca7600]">{{ data.nomEntreprise }}</samp>
                                </h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                    <p><strong>Nom entreprise :</strong> {{ data.nomEntreprise }}</p>
                                    <p><strong>Registre de commerce :</strong> {{ data.registreCommerce }}</p>
                                    <p><strong>Représentant légal :</strong> {{ data.representantLegal }}</p>
                                    <p><strong>Téléphone :</strong> {{ data.numeroTelephone }}</p>
                                    <p><strong>Compte contribuable :</strong> {{ data.compteContribuable }}</p>
                                    <p><strong>Préfecture :</strong> {{ data.prefecture }}</p>
                                    <p><strong>Sous-préfecture :</strong> {{ data.sousPrefecture }}</p>
                                    <p><strong>Région :</strong> {{ data.region }}</p>
                                    <p><strong>Profession représentant :</strong> {{ data.ProfessionRepresantant }}</p>
                                    <p><strong>Date naissance représentant :</strong> {{ data.DateNaissanceRepresantant
                                        }}</p>
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
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { Badge } from '@/components/ui/badge'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

// Props pour recevoir les données existantes
const props = defineProps({
    immatriculation: Object,
    marques: Array,
    modeles: Array,
    vin: String,
    dossier: Object,
    entreprise: Object,
    data: Object
});

// Accès direct aux données via props.data
const data = computed(() => props.data?.immatriculation || {});



const motifDossierRejeter = ref('');

const fetchMotifsRejets = async () => {
    if (!motifDossierRejeter.value) {
        try {
            const response = await axios.get(`/minister/mt1/get/motifs/rejets/${props.dossier.id}`);
            motifDossierRejeter.value = response.data.motifs;
        } catch (err) {
            console.error("Erreur de chargement des motifs sélectionnés:", err);
        }
    }
};
// Initialisation au montage du composant
onMounted(() => {
    console.log('Données reçues:', props.data);
});
</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";

export default {
    layout: Main,
};
</script>