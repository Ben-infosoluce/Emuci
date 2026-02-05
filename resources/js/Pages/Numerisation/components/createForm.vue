<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Nouvelle Numerisation
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
                            <!-- <p>dossier : {{ dossier.r_dossier_vehicule.physique_morale }}</p> -->
                            <h3>informations du véhicule </h3>
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
                            <!-- Informations de l'entreprise -->
                            <h3 class="mt-6" v-if="entreprise"> <strong> Informations de l'entreprise </strong> </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6" v-if="entreprise">
                                <p>Nom Entreprise : {{ entreprise?.nom_entreprise }}</p>
                                <p>Numéro de compte contribuale : {{ entreprise?.compte_contribuale }}</p>
                                <p>Registre de commerce : {{ entreprise?.registre_commerce }}</p>
                                <p>Nom du représentant légal : {{ entreprise?.nom_representant_legal }}</p>
                                <p>Téléphone du représentant légal : {{ entreprise?.telephone_representant_legal }}</p>
                                <p>Profession représentant légal : {{ entreprise?.profession_representant_legal }}</p>
                                <p>Date de naissance du représentant : {{
                                    entreprise.date_de_naissance_representant_legal
                                    }}</p>

                            </div>
                        </div>
                    </ScrollArea>
                </Card>

                <Card>
                    <ScrollArea class="h-full w-full rounded-md border">
                        <div class="m-8">
                            <h3 class="mt-6 text-center">SERVICE</h3>
                            <p class="mt-6 text-center">{{ dossier.r_dossier_services.nom_service }}</p>
                            <!-- <p class="mt-6 text-center">{{ dossier.id }}</p>
                            <p class="mt-6 text-center">{{ dossier.r_dossier_vehicule.physique_morale }}</p> -->


                            <!-- <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                <p>adresse : {{ dossier.r_dossier_client.adresse }}</p>
                                <p>email : {{ dossier.r_dossier_client.email }}</p>
                                <p>telephone : {{ dossier.r_dossier_client.telephone }}</p>
                                <p>date_naissance : {{ dossier.r_dossier_client.date_naissance }}</p>
                                <p>ville_naissance : {{ dossier.r_dossier_client.ville_naissance }}</p>
                            </div> -->
                            <!-- {{ dossier }} -->
                        </div>
                    </ScrollArea>
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
                        <Link
                            :href="route('show.form.numerisation', { dossier: dossier.id, service: dossier.r_dossier_services.nom_service, detail: dossier.detail, physique_morale: dossier.r_dossier_vehicule.physique_morale })">
                        <Button class="bg-[#068A06]">
                            <CreditCard class="w-4 h-4 mr-2" /> Ouvrir la numérisation
                        </Button>
                        </Link>
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
import { ref } from 'vue'
import { returnBack } from "/resources/js/composable/fonction.js"
import { MoveRight, MoveLeft, HandCoins, CreditCard, } from 'lucide-vue-next'
import axios from 'axios'
import { onMounted } from 'vue'
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

const entreprise = ref(null);

// ✅ Fonction pour charger les données
async function fetchEntreprise(id) {
    try {
        const response = await axios.get(`/entreprises/${id}`);
        entreprise.value = response.data.data; // On stocke uniquement la partie data
        console.log("Données de l'entreprise :", entreprise.value);
    } catch (error) {
        console.error("Erreur :", error.response?.data || error);
        entreprise.value = null;
    }
}
onMounted(() => {
    console.log("Dossier reçu en tant que prop :", props.dossier);
    if (props.dossier.r_dossier_vehicule.entreprise_id) {
        fetchEntreprise(props.dossier.r_dossier_vehicule.entreprise_id);
    }
})

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
