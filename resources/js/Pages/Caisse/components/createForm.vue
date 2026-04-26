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
                                    <div class="flex justify-between font-bold text-green-800 border-t pt-2">
                                        <span class="font-medium">Timbre :</span>
                                        <span class="font-bold">
                                            {{ formatMontant(100) }}
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

                        <Button class="bg-[#068A06]" @click="showDemandeurModal = true">
                            <HandCoins class="w-4 h-4 mr-2" /> Payer en cash
                        </Button>
                        <Button class="bg-[#068A06]" disabled>
                            <CreditCard class="w-4 h-4 mr-2" /> Payer en ligne
                        </Button>
                    </div>
                </div>
                <!-- Modal Information Demandeur -->
                <div v-if="showDemandeurModal"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4 overflow-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-2xl shadow-2xl space-y-6">
                        <h3 class="text-2xl font-bold text-center mb-2">Informations du demandeur</h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="nom">Nom</Label>
                                <Input id="nom" v-model="demandeur.nom" placeholder="Nom du demandeur" />
                            </div>
                            <div class="space-y-2">
                                <Label for="prenom">Prénom</Label>
                                <Input id="prenom" v-model="demandeur.prenom" placeholder="Prénom du demandeur" />
                            </div>
                            <div class="space-y-2">
                                <Label for="telephone">Téléphone</Label>
                                <Input id="telephone" v-model="demandeur.telephone" placeholder="Téléphone" />
                            </div>
                            <div class="space-y-2">
                                <Label for="type_piece">Type de pièce</Label>
                                <Select v-model="demandeur.type_piece">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Sélectionner un type" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectItem value="Carte consulaire">Carte consulaire</SelectItem>
                                            <SelectItem value="CNI-Etranger">Carte d'identité (non nationaux)
                                            </SelectItem>
                                            <SelectItem value="CNI">Carte nationale d'identité (CNI)</SelectItem>
                                            <SelectItem value="Attestation d'identité">Attestation d'identité
                                            </SelectItem>
                                            <SelectItem value="Permis de conduire">Permis de conduire</SelectItem>
                                            <SelectItem value="Carte d'identité du réfugié">Carte d'identité du réfugié
                                            </SelectItem>
                                            <SelectItem value="Carte de résident">Carte de résident</SelectItem>
                                            <SelectItem value="Passeport">Passeport</SelectItem>
                                            <SelectItem value="Autre">Autre</SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2 md:col-span-2">
                                <Label for="numero_piece">Numéro de pièce</Label>
                                <Input id="numero_piece" v-model="demandeur.numero_piece"
                                    placeholder="Numéro de pièce" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-4">
                            <Button @click="showDemandeurModal = false" variant="outline">Annuler</Button>
                            <Button @click="() => {
                                if (!demandeur.nom || !demandeur.prenom || !demandeur.telephone || !demandeur.type_piece || !demandeur.numero_piece) {
                                    toast.error('Veuillez remplir tous les champs du demandeur.');
                                    return;
                                }
                                showDemandeurModal = false;
                                showSummary = true;
                            }" class="primary-color text-white">Suivant</Button>
                        </div>
                    </div>
                </div>

                <div v-if="showSummary"
                    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-60 p-4 overflow-auto">
                    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-4xl shadow-2xl space-y-6">
                        <h3 class="text-2xl font-bold text-center mb-2">Résumé des informations de Paiement</h3>

                        <!-- Informations du demandeur (Rappel) -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border mb-6">
                            <h4 class="font-bold text-sm text-gray-500 uppercase tracking-wider mb-2">Demandeur</h4>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div>
                                    <p class="text-xs text-gray-400">Nom & Prénom</p>
                                    <p class="font-medium">{{ demandeur.nom }} {{ demandeur.prenom }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Téléphone</p>
                                    <p class="font-medium">{{ demandeur.telephone }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400">Pièce ({{ demandeur.type_piece }})</p>
                                    <p class="font-medium">{{ demandeur.numero_piece }}</p>
                                </div>
                            </div>
                        </div>

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
                                        {{ formatMontant(FinalPrice()) }}
                                    </span>
                                </div>

                                <div class="flex justify-between font-bold text-green-800 border-t pt-2">
                                    <span class="font-medium">Timbre :</span>
                                    <span class="font-bold">
                                        {{ formatMontant(100) }}
                                    </span>
                                </div>
                                <div class="flex justify-between font-bold text-green-800 border-t pt-2 text-xl">
                                    <span class="font-medium uppercase">Total à payer :</span>
                                    <span class="font-bold">
                                        {{ formatMontant(getMontantTotal()) }}
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
                                                    <span class="font-medium">PRIX TTC:</span>
                                                    <span class="font-bold">{{ formatMontant(getMontantTotal() - 100)
                                                    }}</span>
                                                </div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <span class="font-medium text-red-600">Timbre:</span>
                                                    <span class="font-bold text-red-600">{{ formatMontant(100) }}</span>
                                                </div>
                                                <div class="flex items-center justify-between mb-6 border-t pt-4">
                                                    <span class="font-bold text-lg uppercase">Total à payer:</span>
                                                    <span class="font-extrabold text-2xl text-black">
                                                        {{ formatMontant(getMontantTotal()) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>
                                    <AlertDialogFooter>
                                        <AlertDialogCancel :disabled="isSubmitting">Annuler</AlertDialogCancel>
                                        <AlertDialogAction class="bg-green-600 hover:bg-green-700"
                                            :disabled="isSubmitting"
                                            @click.prevent="validerPaiement">
                                            <Loader2 v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                                            {{ isSubmitting ? 'Validation...' : 'Valider' }}
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
import { MoveRight, MoveLeft, HandCoins, CreditCard, Pen, ReceiptText, Loader2 } from "lucide-vue-next";
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
import { Input } from "@/components/ui/input";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";
import axios from 'axios';
import { Toaster, toast } from 'vue-sonner'
import { onMounted } from 'vue';
import { router } from '@inertiajs/vue3'
import { computed } from 'vue';

const store = useCaisseStore();
const { isOpen } = storeToRefs(store);

const props = defineProps({
    vin: String,
    dossier: Object,
    dossier_lier: Object,
    detailTypeServices: Object,
    detailTypeServices_lier: Object,
    autre_facturation: Object
});
console.log("Props reçus dans createForm.vue:", props.detailTypeServices_lier);
const showSummary = ref(false);
const isSubmitting = ref(false);
const showDemandeurModal = ref(false);
const demandeur = ref({
    nom: '',
    prenom: '',
    telephone: '',
    type_piece: '',
    numero_piece: ''
});

// IDs des services déclencheurs
const TRIGGER_SERVICE_IDS = [4, 10, 9]; // "Changement de Couleur", "Changement de zone (Code région)", "Usage"
//ajouter Marque 
const tableauFusionne = [
    ...props.detailTypeServices,
    ...(props.detailTypeServices_lier || [])
].map(item => {
    // we now use a single montant field regardless of nb_plaque
    const montant = Number(item.montant) || 0;

    // strip out the old plaque-specific fields if they exist
    const { ...rest } = item;
    return {
        ...rest,
        montant
    };
});

// Vérification des services déclencheurs
const serviceTypes = [
    ...(props.dossier?.r_dossier_services?.r_service_types || []),
    ...(props.dossier_lier?.r_dossier_services?.r_service_types || [])
]

// Vérification basée sur les IDs
const hasTriggerService = serviceTypes.some(item =>
    TRIGGER_SERVICE_IDS.includes(item.id) && props.dossier.id_service != 4
);

// Fonction : récupérer le nom du type de service par ID
function getNomTypeServiceById(id) {
    const serviceTypes = [
        ...props.dossier.r_dossier_services.r_service_types
    ]

    if (props.dossier_lier) {
        serviceTypes.push(...props.dossier_lier.r_dossier_services.r_service_types)
    }

    if (hasTriggerService) {
        serviceTypes.push({
            id: 20,
            nom_type_service: props.autre_facturation?.nom,
            montant: props.autre_facturation?.montant,
            id_service: 3
        })
    }

    const match = serviceTypes.find(item => item.id === id)
    return match ? match.nom_type_service : 'Type inconnu'
}


// Computed : grouper les éléments par id_type_services
const groupedDetailTypeServices = computed(() => {
    const groups = {}

    const fillGroups = (items) => {
        items?.forEach(item => {
            if (!groups[item.id_type_services]) {
                groups[item.id_type_services] = []
            }
            groups[item.id_type_services].push(item)
        })
    }

    fillGroups(tableauFusionne)

    if (hasTriggerService) {
        if (!groups[20]) {
            groups[20] = []
        }

        groups[20].push({
            id: 'generated-plaque',
            id_type_services: 20,
            element_facturation: props.autre_facturation?.nom,
            montant: props.autre_facturation?.montant,
            id_service: 3,
            isGenerated: true
        })
    }

    return groups
})

// Calcule le total brut HT
const getPrixHT = () => {
    return tableauFusionne.reduce((total, item) => {
        return total + parseInt(item.montant);
    }, 0);
};

// Ajoute le prix des plaques si nécessaire
const FinalPrice = () => {
    let plaque_price = 0;
    if (hasTriggerService) {
        plaque_price = parseInt(props.autre_facturation?.montant);
    }
    return getPrixHT() + plaque_price;
}


function formatMontant(val) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
    }).format(val);
}

// Calcule le total TTC
const getMontantTotal = () => {
    return FinalPrice() + 100;
};

const formatedMontant = (items = []) => {
    return items.map(item => {
        // always use the montant property
        const montant = Number(item.montant) || 0;
        const { ...rest } = item;
        return {
            ...rest,
            montant
        };
    });
}

const detailTypeServicesFormatted = computed(() =>
    formatedMontant(props.detailTypeServices)
)

const detailTypeServicesLierFormatted = computed(() =>
    formatedMontant(props.detailTypeServices_lier)
)

async function validerPaiement() {
    if (isSubmitting.value) return;
    isSubmitting.value = true;

    const nouveauStatut = 2;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const serviceTypes = [
        ...(props.dossier?.r_dossier_services?.r_service_types || []),
        ...(props.dossier_lier?.r_dossier_services?.r_service_types || [])
    ];

    // Vérification basée sur les IDs
    const hasTriggerService = serviceTypes.some(item =>
        TRIGGER_SERVICE_IDS.includes(item.id) && props.dossier.id_service != 4
    );


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
                // Demandeur info
                demandeur_nom: demandeur.value.nom,
                demandeur_prenom: demandeur.value.prenom,
                demandeur_telephone: demandeur.value.telephone,
                demandeur_type_piece: demandeur.value.type_piece,
                demandeur_numero_piece: demandeur.value.numero_piece,
            },
            {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
            }
        );

        toast.success(response.data.message);
        props.dossier.statut_paiement = nouveauStatut;
        router.visit('/paiement/receipt/' + props.dossier.num_chrono);
    } catch (error) {
        isSubmitting.value = false;
        const errorMessage = error.response?.data?.message || "Une erreur s'est produite lors de la mise à jour.";
        toast.error(errorMessage);
        console.error(error);
    }
}

const entreprise = ref(null);

async function fetchEntreprise(id) {
    try {
        const response = await axios.get(`/entreprises/${id}`);
        entreprise.value = response.data.data;
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
