<template>
    <!-- Header sticky -->
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
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
    <div class="rounded-lg dark:border-gray-700 ">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col ">
                <ScrollArea class="h-full w-full rounded-md ">



                    <!-- Informations du véhicule -->
                    <div class="m-5">

                        <h2 class="col-span-2 text-lg font-semibold mb-4 text-center mt-10">Récapitulatif des
                            informations de : {{
                                data.marque }}</h2>
                        <div class="flex flex-col md:flex-row gap-8 p-6 bg-white rounded ">
                            <!-- Colonne gauche -->
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-2 flex-1">


                                <p class="my-1"><span class="text-gray-600">Châssis (VIN): </span><strong>{{ data.vin
                                        }}</strong></p>
                                <p class="my-1"><span class="text-gray-600">Couleur: </span><strong>{{ data.couleur
                                        }}</strong></p>

                                <p class="my-1"><span class="text-gray-600">Marque du véhicule: </span><strong>{{
                                    data.marque
                                        }}</strong></p>
                                <p class="my-1"><span class="text-gray-600">Carrosserie: </span><strong>{{
                                    data.carrosserie
                                        }}</strong></p>

                                <p class="my-1"><span class="text-gray-600">Modèle: </span><strong>{{ data.modele
                                        }}</strong></p>
                                <p class="my-1"><span class="text-gray-600">Type technique: </span><strong>{{
                                    data.type_technique
                                        }}</strong></p>

                                <p class="my-1"><span class="text-gray-600">Genre: </span><strong>{{ data.genre_vehicule
                                        }}</strong>
                                </p>
                                <p class="my-1"><span class="text-gray-600">Poids à Vide: </span><strong>{{
                                    data.poids_vide
                                        }}</strong></p>

                                <p class="my-1"><span class="text-gray-600">Poids Total en charge: </span><strong>{{
                                    data.poids_total_charge }}</strong></p>
                                <p class="my-1"><span class="text-gray-600">Puissance administrative: </span><strong>{{
                                    data.puissance_administrative }}</strong></p>

                                <p class="my-1"><span class="text-gray-600">Poids Utile: </span><strong>{{
                                    data.poids_utile
                                        }}</strong></p>
                                <p class="my-1"><span class="text-gray-600">Places Assises: </span><strong>{{
                                    data.places_assises
                                        }}</strong></p>

                                <p class="my-1"><span class="text-gray-600">Sources d’énergie: </span><strong>{{
                                    data.sourcesEnergie
                                        }}</strong></p>
                                <p class="my-1"><span class="text-gray-600">Nbre d’Essieux: </span><strong>{{
                                    data.nombre_essieux
                                        }}</strong></p>

                                <p class="my-1"><span class="text-gray-600">Date de mise en circulation :
                                    </span><strong> {{
                                        data.date_mise_circulation }}</strong></p>
                                <p class="my-1"><span class="text-gray-600">Année de production : </span><strong> {{
                                    data.annee_production }}</strong></p>
                            </div>

                            <!-- Colonne droite -->
                            <div
                                class="bg-white rounded-lg shadow p-6 w-full md:max-w-sm flex flex-col items-center justify-center">
                                <div class="flex items-center space-x-2">
                                    <div class="w-4 h-4 bg-black rounded-full flex items-center justify-center">
                                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor"
                                            stroke-width="2" viewBox="0 0 24 24">
                                            <path d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <span class="text-xl">{{ props.selected[5].element_facturation }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-end">

                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button class="bg-[#ca7600]">
                                        Valider
                                        <MoveRight />

                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Etes-vous sur de vouloir continuer?
                                        </AlertDialogTitle>
                                        <AlertDialogDescription>
                                            <p v-if="props.postImtData">
                                                En continuant, vous acceptez de valider les demmandes de
                                                post-immatriculation et du duplicata.
                                            </p>

                                            <p v-else>En continuant, vous valider la demande de duplicata.</p>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                                        <AlertDialogAction @click="SendData()">Continuer
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>
                </ScrollArea>
                <!-- Modal Résumé -->
            </Card>
        </main>
        <Toaster richColors position="top-right" />
    </div>



</template>



<script setup>
import { Button } from '@/components/ui/button'
import {
    Card,
} from '@/components/ui/card'
import { Toaster, toast } from 'vue-sonner'
import { ScrollArea } from '@/components/ui/scroll-area'
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { MoveRight, EyeOff, Printer, KeyRound, CheckCircle, MoveLeft } from 'lucide-vue-next'
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
    vehicule: Object,
    selected: Object,
    postImtData: Object
})
const data = computed(() => props.vehicule || {});
console.log(props.vehicule)


onMounted(() => {
    console.log("postImtData : ", props.postImtData)
    console.log("selected : ", props.selected)
    console.log("iici : ")
})


function SendData() {
    const payload = {
        vehicule_id: data.value.id,
        detail: [props.selected[0].id],
        client_id: data.value.id_client
    }

    // 🔥 On regroupe tout dans un seul objet
    const finalData = {
        payload: payload,
        postImtData: props.postImtData,
        selected: props.selected
    }

    console.log("finalData : ", finalData)

    axios.post('/pdc/duplicata/save/data', finalData)
        .then(response => {
            const res = response.data;
            console.log('Réponse serveur :', res);

            if (res.success) {
                toast.success(res.message || 'Information enregistrée avec succès !');

                setTimeout(() => {
                    window.location.href = `/pdc/duplicata/receipt/${res.data.id}`;
                }, 1500);
            } else {
                toast.error(res.message || "Une erreur est survenue lors de l'enregistrement.");
            }
        })
        .catch(error => {
            console.error('Erreur :', error);
            const errMsg = error.response?.data?.message || 'Erreur lors de l’enregistrement des données.';
            toast.error(errMsg);
        });
}


</script>


<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
export default {
    layout: Main,
};
</script>
