<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Détails du dossier
        </h4>
        <div class="flex items-center space-x-2">
            <Button @click="returnBack()">
                <MoveLeft class="w-4 h-4 mr-2" /> Retour
            </Button>
        </div>
    </div>

    <!-- Contenu scrollable -->
    <div class="rounded-lg dark:border-gray-700 ">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <div class="grid gap-4 md:gap-8 lg:grid-cols-2 xl:grid-cols-3">
                <Card class="xl:col-span-2">
                    <ScrollArea class="h-full w-full rounded-md border">
                        <div class="m-8">
                            <h3>informations du véhicule - VIN: <strong>{{ vin }}</strong></h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                <p>Châssis (VIN) : {{ dossier.r_dossier_vehicule.vin }}</p>
                                <p>Marque du véhicule : {{ dossier.r_dossier_vehicule.marque }}</p>
                                <p>Modèle du véhicule : {{ dossier.r_dossier_vehicule.modele }}</p>
                                <p>Genre : {{ dossier.r_dossier_vehicule.genre_vehicule }}</p>
                                <p>Poids Total en charge : {{ dossier.r_dossier_vehicule.poids_total }}</p>
                                <p>Poids Utile : {{ dossier.r_dossier_vehicule.poids_utile }}</p>
                                <p>Sources d’énergie : {{ dossier.r_dossier_vehicule.source_energie }}</p>
                                <p>Couleur : {{ dossier.r_dossier_vehicule.couleur }}</p>
                                <p>Carrosserie: {{ dossier.r_dossier_vehicule.carrosserie }}</p>
                                <p>Type technique : {{ dossier.r_dossier_vehicule.type_technique }}</p>
                                <p>Poids à Vide : {{ dossier.r_dossier_vehicule.poids_vide }}</p>
                                <p>Puissance administrative : {{ dossier.r_dossier_vehicule.puissance_administrative }}
                                </p>
                                <p>Places Assises : {{ dossier.r_dossier_vehicule.places_assises }}</p>
                                <p>Nbre d’Essieux : {{ dossier.r_dossier_vehicule.nombre_essieux }}</p>
                            </div>

                            <hr />
                            <h3 class="mt-6">informations du propriétaire :
                                <strong>{{ dossier.r_dossier_client.civilite }} , {{ dossier.r_dossier_client.nom
                                    }} {{ dossier.r_dossier_client.prenom }}
                                </strong>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                <p>adresse : {{ dossier.r_dossier_client.adresse }}</p>
                                <p>email : {{ dossier.r_dossier_client.email }}</p>
                                <p>telephone : {{ dossier.r_dossier_client.telephone }}</p>
                                <p>date de naissance : {{ dossier.r_dossier_client.date_naissance }}</p>
                                <p>ville de naissance : {{ dossier.r_dossier_client.ville_naissance }}</p>
                            </div>
                            <!-- <hr /> -->
                            <!-- <h3>informations de l'entreprise : <strong>{{ dossier }}</strong>
                            </h3> -->
                            <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">-->
                            <!-- <p>Liste des documents : {{ dossier.r_dossier_documents }}</p> -->
                            <!--  <p>Marque du véhicule : {{ dossier.marque }}</p>
                                <p>Modèle du véhicule : {{ dossier.modele }}</p>
                                <p>Genre : {{ dossier.genre_dossier }}</p>
                                <p>Poids Total en charge : {{ dossier.poids_total }}</p>
                                <p>Poids Utile : {{ dossier.poids_utile }}</p>
                                <p>Sources d’énergie : {{ dossier.source_energie }}</p>
                                <p>Couleur : {{ dossier.couleur }}</p>
                                <p>Carrosserie: {{ dossier.carrosserie }}</p>
                                <p>Type technique : {{ dossier.type_technique }}</p>
                                <p>Poids à Vide : {{ dossier.poids_vide }}</p>
                                <p>Puissance administrative : {{ dossier.puissance_administrative }}</p>
                                <p>Places Assises : {{ dossier.places_assises }}</p>
                                <p>Nbre d’Essieux : {{ dossier.nombre_essieux }}</p>
                            </div> -->
                        </div>
                    </ScrollArea>
                </Card>

                <Card>
                    <!-- <ScrollArea class="h-full w-full rounded-md border"> -->
                    <div class="m-8">
                        <h3 class="mt-6 text-center">DOCUMENTATION</h3>
                        <div class="max-w-xl mx-auto bg-white rounded-lg shadow p-4 mt-6">


                            <div class="space-y-5">
                                <template v-if="filteredDocs.length">
                                    <div v-for="[key, value] in filteredDocs" :key="key"
                                        class="flex items-center justify-between border rounded px-4 py-2">
                                        <span>{{ formatLabel(key) }}</span>

                                        <AlertDialog>
                                            <AlertDialogTrigger as-child>
                                                <button
                                                    class="flex items-center space-x-1 text-sm text-gray-600 hover:text-black"
                                                    @click="selectedDoc = `/storage/${value}`">
                                                    <Eye
                                                        class="w-5 h-5 text-gray-500 transition group-hover:text-gray-900" />
                                                </button>
                                            </AlertDialogTrigger>

                                            <AlertDialogContent>
                                                <AlertDialogHeader>
                                                    <AlertDialogTitle>Prévisualisation</AlertDialogTitle>
                                                    <AlertDialogDescription>
                                                        <img :src="selectedDoc" alt="Document"
                                                            class="w-full h-auto rounded shadow mt-4 max-h-[600px] object-contain" />
                                                    </AlertDialogDescription>
                                                </AlertDialogHeader>
                                                <AlertDialogFooter>
                                                    <AlertDialogCancel>Fermer</AlertDialogCancel>
                                                    <AlertDialogAction as-child>
                                                        <a :href="selectedDoc" target="_blank"
                                                            class="text-sm text-blue-600">
                                                            Ouvrir dans un nouvel onglet
                                                        </a>
                                                    </AlertDialogAction>
                                                </AlertDialogFooter>
                                            </AlertDialogContent>
                                        </AlertDialog>
                                    </div>
                                </template>

                                <template v-else>
                                    <div class="text-center text-sm text-gray-500 m-6">
                                        Il n'y a aucun document enregistrés .
                                    </div>
                                </template>
                            </div>

                        </div>

                    </div>
                    <!-- </ScrollArea> -->
                </Card>
            </div>
            <div
                class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                <div></div>
                <div class="flex items-center space-x-2">
                    <AlertDialog>
                        <Link :href="route('show.modification.numerisation.get.data', { vin: dossier.num_chrono, })">
                        <Button class="bg-red-900">
                            <HandCoins class="w-4 h-4 mr-2" /> Demande de mofication
                        </Button>
                        </Link>
                        <!-- <Link :href="route('show.form.numerisation', { dossier: dossier.id })"> -->
                        <Button class="bg-[#068A06]">
                            <CreditCard class="w-4 h-4 mr-2" /> Valider le dossier
                        </Button>
                        <!-- </Link> -->
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <!-- <AlertDialogTitle>Are you absolutely sure?</AlertDialogTitle> -->
                                <AlertDialogDescription>
                                    <div class="max-w-md mx-auto p-6 text-center">
                                        <h2 class="text-2xl font-bold mb-6">SUGGESTIONS</h2>

                                        <textarea v-model="message" rows="5"
                                            placeholder="Changer les informations suivantes..."
                                            class="w-full p-4 rounded-xl text-left text-black resize-none mb-6 focus:outline-none "></textarea>


                                    </div>
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter>
                                <AlertDialogCancel>Annuler</AlertDialogCancel>
                                <AlertDialogAction class="bg-green-600 hover:bg-green-700 ">
                                    Envoyer</AlertDialogAction>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                    <!-- <Button @click="returnBack()">
                        <CreditCard class="w-4 h-4 mr-2" /> Payer en Ligne
                    </Button> -->
                </div>
            </div>

            <!-- Card 2 -->
            <!-- <Card class="flex-1 flex flex-col">
                <ScrollArea class="h-full w-full rounded-md border">
                    <div class="m-8">
                        laba
                    </div>
                </ScrollArea>
            </Card> -->
        </main>

    </div>
</template>


<script setup>
import { Button } from '@/components/ui/button'
import { ref, computed } from 'vue'
import { returnBack } from "/resources/js/composable/fonction.js"
import { MoveRight, MoveLeft, HandCoins, CreditCard, Eye, CircleAlert } from 'lucide-vue-next'
import {
    Card,
} from '@/components/ui/card'
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



const props = defineProps({
    vin: String,
    dossier: Object,
    client: Object
});




// Exclusion des champs non pertinents
const keysToExclude = ['id', 'id_dossier', 'type_document', 'created_at', 'updated_at']

// On prend le premier élément du tableau (car un seul objet avec tous les documents)
const documentData = computed(() => {
    return props.dossier.r_dossier_documents?.[0] || {}
})

// Filtrage des documents valides
const filteredDocs = computed(() => {
    const entries = Object.entries(documentData.value)
    return entries.filter(([key, value]) => !keysToExclude.includes(key) && value)
})

// Prévisualisation
const selectedDoc = ref(null)

function formatLabel(key) {
    return key
        .replace(/_/g, ' ')
        .replace(/\b\w/g, l => l.toUpperCase())
}



</script>

<script>
import Main from '/resources/js/Pages/Main.vue';

export default {
    layout: Main,
};
</script>

<style scoped>
/* Optionnel pour responsive / animation */
</style>
