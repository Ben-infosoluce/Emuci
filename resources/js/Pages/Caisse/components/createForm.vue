<template>
    <div v-if="isOpen">
        <!-- Header sticky -->
        <div
            class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">Nouveau Paiement</h4>
            <div class="flex items-center space-x-2">
                <Button @click="returnBack()">
                    <MoveLeft class="w-4 h-4 mr-2" /> Retour
                </Button>
            </div>
        </div>

        <!-- Contenu scrollable -->
        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <div class="grid gap-4 md:gap-8 lg:grid-cols-2 xl:grid-cols-3">
                    <Card class="xl:col-span-2">
                        <ScrollArea class="h-full w-full rounded-md border">
                            <div class="m-8">

                                <div
                                    class=" flex flex-col space-y-4  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                                    <h3>
                                        informations du véhicule - VIN :
                                        <strong>{{
                                            dossier.r_dossier_vehicule.vin
                                            }}</strong>
                                    </h3>
                                    <h2>Statut du dossier : <Badge class="mx-2"
                                            :variant="dossier?.statut === 1 ? 'warning' : dossier?.statut === 2 ? 'success' : dossier?.statut === 3 ? 'error' : 'secondary'">
                                            {{
                                                dossier?.statut === 1
                                                    ? 'En attente'
                                                    : dossier?.statut === 2
                                                        ? 'Validé'
                                                        : dossier?.statut === 3
                                                            ? 'Refusé'
                                                            : 'Inconnu'
                                            }}
                                        </Badge>
                                    </h2>

                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                    <p>
                                        Châssis (VIN) :
                                        {{ dossier.r_dossier_vehicule.vin }}
                                    </p>
                                    <p>
                                        Marque du véhicule :
                                        {{ dossier.r_dossier_vehicule.marque }}
                                    </p>
                                    <p>
                                        Modèle du véhicule :
                                        {{ dossier.r_dossier_vehicule.modele }}
                                    </p>
                                    <p>
                                        Genre :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .genre_vehicule
                                        }}
                                    </p>
                                    <p>
                                        Poids Total en charge :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .poids_total_charge
                                        }}
                                    </p>
                                    <p>
                                        Poids Utile :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .poids_utile
                                        }}
                                    </p>
                                    <p>
                                        Sources d’énergie :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .source_energie
                                        }}
                                    </p>
                                    <p>
                                        Couleur :
                                        {{ dossier.r_dossier_vehicule.couleur }}
                                    </p>
                                    <p>
                                        Carrosserie:
                                        {{
                                            dossier.r_dossier_vehicule
                                                .carrosserie
                                        }}
                                    </p>
                                    <p>
                                        Type technique :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .type_technique
                                        }}
                                    </p>
                                    <p>
                                        Poids à Vide :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .poids_vide
                                        }}
                                    </p>
                                    <p>
                                        Puissance administrative :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .puissance_administrative
                                        }}
                                    </p>
                                    <p>
                                        Places Assises :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .places_assises
                                        }}
                                    </p>
                                    <p>
                                        Nbre d’Essieux :
                                        {{
                                            dossier.r_dossier_vehicule
                                                .nombre_essieux
                                        }}
                                    </p>

                                </div>

                                <hr />
                                <h3 class="mt-6">
                                    informations du propriétaire :
                                    <strong>{{
                                        dossier.r_dossier_client.civilite
                                        }}
                                        , {{ dossier.r_dossier_client.nom }}
                                        {{ dossier.r_dossier_client.prenom }}
                                    </strong>
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6">
                                    <p>
                                        adresse :
                                        {{ dossier.r_dossier_client.adresse }}
                                    </p>
                                    <p>
                                        email :
                                        {{ dossier.r_dossier_client.email }}
                                    </p>
                                    <p>
                                        telephone :
                                        {{ dossier.r_dossier_client.telephone }}
                                    </p>
                                    <p>
                                        date naissance :
                                        {{
                                            dossier.r_dossier_client
                                                .date_naissance
                                        }}
                                    </p>
                                    <p>
                                        ville naissance :
                                        {{
                                            dossier.r_dossier_client
                                                .ville_naissance
                                        }}
                                    </p>
                                </div>
                                <hr />
                                <!-- Informations de l'entreprise -->
                                <h3 class="mt-6" v-if="entreprise"> <strong> Informations de l'entreprise </strong>
                                </h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6 mb-6" v-if="entreprise">
                                    <p>Nom Entreprise : {{ entreprise?.nom_entreprise }}</p>
                                    <p>Numéro de compte contribuale : {{ entreprise?.compte_contribuale }}</p>
                                    <p>Registre de commerce : {{ entreprise?.registre_commerce }}</p>
                                    <p>Nom du représentant légal : {{ entreprise?.nom_representant_legal }}</p>
                                    <p>Téléphone du représentant légal : {{ entreprise?.telephone_representant_legal }}
                                    </p>
                                    <p>Profession représentant légal : {{ entreprise?.profession_representant_legal }}
                                    </p>
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
                                <h3 class="mt-6 text-center font-bold text-lg">
                                    PANIER DE PAIEMENT
                                </h3>
                                <!-- <p>{{ props.detailTypeServices }}</p> -->
                                <!-- Section des éléments à facturer -->
                                <div v-if="props.detailTypeServices.length" class="space-y-6 pt-4">
                                    <h3 class="font-semibold text-gray-700">Éléments à facturer</h3>

                                    <div v-for="(items, idType) in groupedDetailTypeServices" :key="idType"
                                        class="space-y-2">
                                        <h4 class="text-black-700 font-semibold py-2">
                                            {{ getNomTypeServiceById(parseInt(idType)) }}
                                        </h4>

                                        <div v-for="item in items" :key="item.id"
                                            class="flex justify-between px-2 py-1 border-b">
                                            <span>{{ item.element_facturation }}</span>
                                            <span class="text-right font-medium text-green-700">
                                                {{ formatMontant(item.montant) }} F CFA
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section récapitulative -->
                                <div class=" space-y-2 pt-2 mt-5">
                                    <div class="flex justify-between">
                                        <span class="font-medium">PRIX HT :</span>
                                        <span class="font-bold text-gray-700">
                                            {{ formatMontant(FinalPrice()) }} F CFA
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="font-medium">TVA (18%) :</span>
                                        <span class="font-bold text-gray-700">
                                            {{ formatMontant(getTVA()) }} F CFA
                                        </span>
                                    </div>
                                    <div class="flex justify-between font-bold text-green-800 border-t pt-2">
                                        <span class="font-medium">PRIX TTC :</span>
                                        <span class="font-bold">
                                            {{ formatMontant(getMontantTotal()) }} F CFA
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </ScrollArea>
                    </Card>

                </div>
                <div v-if="dossier?.statut_paiement == 1"
                    class="sticky top-[-20px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8 py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
                    <div>
                        <Link :href="route('show.modification.caisse.get.data', {
                            vin: dossier.num_chrono,
                        })
                            ">
                        <!-- <br> -->
                        <Button class="bg-[#ca7600]">
                            <ReceiptText class="w-4 h-4 mr-2" /> Demander une modification
                        </Button>

                        </Link>
                    </div>
                    <div class="flex items-center space-x-2">

                        <Button class="bg-[#068A06]" @click="showSummary = true">
                            <HandCoins class="w-4 h-4 mr-2" /> Payer en cash
                        </Button>
                        <Button class="bg-[#068A06]" disabled>
                            <CreditCard class="w-4 h-4 mr-2" /> Payer en ligne
                        </Button>
                    </div>
                </div>
                <div v-if="showSummary"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4 overflow-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-4xl shadow-2xl space-y-6">
                        <h3 class="text-2xl font-bold text-center mb-2">Résumé des informations de Paiement</h3>
                        <!-- Informations du propriétaire -->
                        <div class="m-8 mb-8">

                            <!-- Section des éléments à facturer -->
                            <div v-if="props.detailTypeServices.length" class="space-y-6 pt-4">
                                <h3 class="font-semibold text-gray-700">Éléments à facturer</h3>

                                <div v-for="(items, idType) in groupedDetailTypeServices" :key="idType"
                                    class="space-y-2">
                                    <h4 class="text-black-700 font-semibold py-2">
                                        {{ getNomTypeServiceById(parseInt(idType)) }}
                                    </h4>

                                    <div v-for="item in items" :key="item.id"
                                        class="flex justify-between px-2 py-1 border-b">
                                        <span>{{ item.element_facturation }}</span>
                                        <span class="text-right font-medium text-green-700">
                                            {{ formatMontant(item.montant) }} F CFA
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Section récapitulative -->
                            <div class=" space-y-2 pt-2 mt-5">
                                <div class="flex justify-between">
                                    <span class="font-medium">PRIX HT :</span>
                                    <span class="font-bold text-gray-700">
                                        {{ formatMontant(getPrixHT()) }} F CFA
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="font-medium">TVA (18%) :</span>
                                    <span class="font-bold text-gray-700">
                                        {{ formatMontant(getTVA()) }} F CFA
                                    </span>
                                </div>
                                <div class="flex justify-between font-bold text-green-800 border-t pt-2">
                                    <span class="font-medium">PRIX TTC :</span>
                                    <span class="font-bold">
                                        {{ formatMontant(getMontantTotal()) }} F CFA
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 flex justify-end gap-4 m-8">
                            <Button @click="showSummary = false" variant="outline"
                                class="px-6 py-2 rounded-lg">Retour</Button>
                            <AlertDialog>
                                <AlertDialogTrigger as-child>
                                    <Button class=" px-6 py-2 rounded-lg primary-color text-white">
                                        Continuer
                                    </Button>
                                </AlertDialogTrigger>
                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>Etes-vous sur de vouloir valider le paiement?
                                        </AlertDialogTitle>
                                        <AlertDialogDescription>
                                            <div
                                                class="max-w-xl mx-auto p-6 border rounded-xl shadow-sm bg-white text-gray-800">
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-medium">PRIX HT:</span>
                                                    <span class="font-bold">{{ formatMontant(getPrixHT()) }}F</span>
                                                </div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-medium">TVA:</span>
                                                    <span class="font-bold">{{ formatMontant(getTVA()) }}F</span>
                                                </div>
                                                <div class="flex items-center justify-between mb-6">
                                                    <span class="font-medium">PRIX TTC:</span>
                                                    <span class="font-extrabold text-lg text-black">
                                                        {{ formatMontant(getMontantTotal()) }}F
                                                    </span>
                                                </div>
                                            </div>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                                        <AlertDialogAction class="bg-green-600 hover:bg-green-700"
                                            @click="() => validerPaiement()">
                                            Valider
                                        </AlertDialogAction>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <div v-else>
        <Control />
    </div>
</template>

<script setup>
import { Button } from "@/components/ui/button";
import { Badge } from '@/components/ui/badge'
import { ref } from "vue";
import { returnBack } from "/resources/js/composable/fonction.js";
import Control from "./Control.vue";
import { MoveRight, MoveLeft, HandCoins, CreditCard, Pen, ReceiptText } from "lucide-vue-next";
import { Card } from "@/components/ui/card";
import { ScrollArea } from "@/components/ui/scroll-area";
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
} from "@/components/ui/alert-dialog";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";
import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner'
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3'
import { computed } from 'vue';
// import { useToast } from '@/components/ui/toast'; // si tu utilises un toast UI

const store = useCaisseStore();
const { isOpen } = storeToRefs(store);

const props = defineProps({
    vin: String,
    dossier: Object,
    dossier_lier: Object,
    detailTypeServices: Object,
    detailTypeServices_lier: Object,
    autre_facturation: Object,
    nb_plaque: Number
});

const showSummary = ref(false);

const tableauFusionne = [
    ...props.detailTypeServices,
    ...(props.detailTypeServices_lier || [])
].map(item => {

    const montant = props.nb_plaque == 1
        ? Number(item.montant_1_plaque)
        : Number(item.montant_2_plaques)

    const { montant_1_plaque, montant_2_plaques, ...rest } = item

    return {
        ...rest,
        montant: montant || 0
    }
})

console.log('autre_facturation:', props.autre_facturation);
console.log('nb_plaque:', props.nb_plaque);


// Vérification des services déclencheurs
const serviceTypes = [
    ...(props.dossier?.r_dossier_services?.r_service_types || []),
    ...(props.dossier_lier?.r_dossier_services?.r_service_types || [])
]
console.log('serviceTypes:', serviceTypes)
const triggerServices = [
    "Changement de Couleur",
    "Changement de zone (Code région)",
    "Usage"
]

const hasTriggerService = tableauFusionne.some(item =>
    triggerServices.includes(item.element_facturation)
)
console.log('tableauFusionne:', tableauFusionne);
console.log('hasTriggerService', hasTriggerService);



// Fonction : récupérer le nom du type de service par ID
function getNomTypeServiceById(id) {
    // Créer une copie pour éviter de muter les props réactives (Vue Proxy)
    const serviceTypes = [
        ...props.dossier.r_dossier_services.r_service_types
    ]

    if (props.dossier_lier) {
        serviceTypes.push(...props.dossier_lier.r_dossier_services.r_service_types)
    }

    // Liste des services déclencheurs
    const triggerServices = [
        "Changement de Couleur",
        "Changement de zone (Code région)",
        "Usage"
    ]

    // Vérifier si un des services déclencheurs est présent
    const hasTriggerService = serviceTypes.some(item =>
        triggerServices.includes(item.nom_type_service)
    )

    // Ajouter "Changement de Plaque" si condition remplie
    if (hasTriggerService) {
        serviceTypes.push({
            id: 20,
            nom_type_service: props.autre_facturation.nom,
            montant: props.autre_facturation.montant,
            id_service: 3
        })
    }

    console.log('serviceTypes:', serviceTypes)
    const match = serviceTypes.find(item => item.id === id)
    console.log('match:', match)
    return match ? match.nom_type_service : 'Type inconnu'
}

// Fonction : formater le montant
function formatMontant(montant) {
    return Number(montant).toLocaleString()
}

// Computed : grouper les éléments par id_type_services
const groupedDetailTypeServices = computed(() => {
    const groups = {}

    // Helper pour remplir les groupes sans répétition
    const fillGroups = (items) => { items?.forEach(item => { if (!groups[item.id_type_services]) { groups[item.id_type_services] = [] } groups[item.id_type_services].push(item) }) }


    // Remplissage avec les données existantes
    fillGroups(tableauFusionne)
    // fillGroups(props.detailTypeServices_lier)



    // Ajout conditionnel du groupe "Changement de Plaque" (id: 20)
    if (hasTriggerService) {
        if (!groups[20]) {
            groups[20] = []
        }

        // Ajout d'un item représentatif dans le groupe
        groups[20].push({
            id: 'generated-plaque',
            id_type_services: 13,
            element_facturation: props.autre_facturation.nom,
            montant: props.autre_facturation.montant,
            id_service: 3,
            isGenerated: true // flag pour identifier l'item ajouté
        })
    }
    console.log('groupsa:', groups)
    return groups
})

// Calcule le total brut HT
const getPrixHT = () => {
    return tableauFusionne.reduce((total, item) => {
        return total + parseInt(item.montant);
    }, 0);

};


const FinalPrice = () => {
    var palque_price = 0
    if (hasTriggerService) {
        palque_price = parseInt(props.autre_facturation.montant)
    }
    return getPrixHT() + palque_price
}

// Calcule la TVA (par défaut à 18 %)
const getTVA = () => {
    return FinalPrice() * 0.18;
};

// Calcule le total TTC
const getMontantTotal = () => {
    return FinalPrice() + getTVA();
};

const formatedMontant = (items = []) => {
    return items.map(item => {
        const montant = props.nb_plaque == 1
            ? Number(item.montant_1_plaque)
            : Number(item.montant_2_plaques)

        const { montant_1_plaque, montant_2_plaques, ...rest } = item

        return {
            ...rest,
            montant: montant || 0
        }
    })
}

const detailTypeServicesFormatted = computed(() =>
    formatedMontant(props.detailTypeServices)
)

const detailTypeServicesLierFormatted = computed(() =>
    formatedMontant(props.detailTypeServices_lier)
)


async function validerPaiement() {
    const nouveauStatut = 2;
    // Récupérer le token CSRF depuis le <meta>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    // Vérification des services déclencheurs
    const serviceTypes = [
        ...(props.dossier?.r_dossier_services?.r_service_types || []),
        ...(props.dossier_lier?.r_dossier_services?.r_service_types || [])
    ]
    const triggerServices = [
        "Changement de Couleur",
        "Changement de zone (Code région)",
        "Usage"
    ]
    const hasTriggerService = serviceTypes.some(item =>
        triggerServices.includes(item.nom_type_service)
    )
    try {
        const response = await axios.post(
            `/statut/paiement`,
            {
                statut_paiement: nouveauStatut,
                chrono: props.dossier.num_chrono,
                chrono_lier: props.dossier_lier?.num_chrono,
                detailTypeServices: detailTypeServicesFormatted.value,
                detailTypeServices_lier: detailTypeServicesLierFormatted.value,
                montant_total: getMontantTotal(),
                caisse_ouverture_id: store.caisseOpened.caisse_id,
                id_site: props.dossier.id_site,
                has_changement_plaque: hasTriggerService,
            },
            {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            }
        );
        // Succès
        toast.success(response.data.message);
        // console.log(response.data);
        props.dossier.statut_paiement = nouveauStatut;
        // Rediriger vers le reçu
        router.visit('/paiement/receipt/' + props.dossier.num_chrono);
    } catch (error) {
        toast.error("Une erreur s'est produite lors de la mise à jour.");
        console.error(error);
    }
}

const entreprise = ref(null);
// Gestion de l'entreprise liée au dossier
async function fetchEntreprise(id) {
    try {
        const response = await axios.get(`/entreprises/${id}`);
        entreprise.value = response.data.data; // On stocke uniquement la partie data
        // console.log("Données de l'entreprise :", entreprise.value);
    } catch (error) {
        console.error("Erreur :", error.response?.data || error);
        entreprise.value = null;
    }
}

onMounted(() => {
    if (props.dossier.r_dossier_vehicule.entreprise_id) {
        fetchEntreprise(props.dossier.r_dossier_vehicule.entreprise_id);
    }
})
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";
import { data } from "autoprefixer";

export default {
    layout: Main,
};
</script>

<style scoped>
/* Optionnel pour responsive / animation */
</style>
