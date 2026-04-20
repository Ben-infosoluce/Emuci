<template>
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 px-4 md:gap-8 md:px-8">
            <Card class="h-full flex flex-col">
                <div class="space-y-4 px-8">
                    <!-- Alerte session précédente non fermée -->
                    <div v-if="hasSessionPrecedenteNonFermee"
                        class="flex items-center space-x-2 p-3 bg-red-50 border border-red-400 rounded-lg">
                        <i class="pi pi-exclamation-triangle text-red-600"></i>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-red-800">
                                Session précédente non fermée
                            </span>
                            <span v-if="ouvertureNonFermee?.date" class="block text-xs text-red-600 mt-1">
                                Ouverte le
                                {{ formatDate(ouvertureNonFermee.date) }}
                            </span>
                        </div>
                    </div>

                    <!-- HEADER -->
                    <div class="flex items-center justify-between mb-6">
                        <!-- Date actuelle et filtre -->
                        <div class="flex items-center space-x-3">
                            <p class="text-sm font-semibold text-gray-700">
                                DATE :
                                <span class="font-bold italic text-black">{{
                                    formatDate(selectedDate)
                                    }}</span>
                            </p>

                            <input type="date" v-model="selectedDate" @change="fetchPaiements_Caisse"
                                class="border rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-gray-500 focus:border-amber-500 bg-background shadow-sm h-9" />

                            <p class="text-sm font-semibold text-gray-700">
                                CAISSE :
                            </p>

                            <div class="px-3 py-2 text-sm">
                                <div v-if="caisse" class="flex flex-row gap-8">
                                    <p class="font-semibold">
                                        {{ caisse.libelle }} ({{ caisse.code }})
                                    </p>
                                    <div class="flex flex-row gap-1">
                                        <p class="font-semibold">
                                            Statut de la caisse :
                                        </p>
                                        <p :class="statusCaisseClass" class="font-semibold">
                                            {{ statusCaisseLabel }}
                                        </p>
                                    </div>
                                </div>

                                <div v-else class="text-gray-500 text-sm">
                                    Chargement de la caisse…
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6 text-sm font-medium">
                            <AlertDialog v-model:open="isDialogOpen">
                                <AlertDialogTrigger as-child v-if="shouldShowActionButton">
                                    <Button :variant="buttonVariant" :disabled="isButtonDisabled"
                                        class="transition-all duration-200">
                                        <span>{{ buttonLabel }}</span>
                                        <LockKeyhole v-if="isActionFermeture" class="ml-2" />
                                        <LockOpen v-else class="ml-2" />
                                    </Button>
                                </AlertDialogTrigger>

                                <AlertDialogContent class="w-full max-w-[1200px]  ">
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>
                                            {{ dialogTitle }}
                                        </AlertDialogTitle>

                                        <AlertDialogDescription>
                                            <div v-if="!confirmingFinalClosure"
                                                class="overflow-y-auto px-1 custom-scrollbar">
                                                <div v-if="isActionFermeture" class="grid grid-cols-3 gap-4 my-4">

                                                    <!-- 🔹 VENTES -->
                                                    <div
                                                        class="flex flex-col bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                                                        <label class="text-xs font-bold text-blue-700 uppercase mb-1">
                                                            Ventes du jour
                                                        </label>
                                                        <div class="text-xl font-black text-blue-800">
                                                            {{ totalMontantFormatted }}
                                                        </div>
                                                    </div>

                                                    <!-- 🔹 FOND -->
                                                    <div
                                                        class="flex flex-col bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                                                        <label class="text-xs font-bold text-blue-700 uppercase mb-1">
                                                            Fond de caisse
                                                        </label>
                                                        <div class="text-xl font-black text-blue-800">
                                                            {{ Number(caisseOuverture?.montant_ouverture ||
                                                                0).toLocaleString() }} F
                                                        </div>
                                                    </div>

                                                    <!-- 🔹 TOTAL -->
                                                    <div
                                                        class="flex flex-col bg-green-600 p-3 rounded-xl shadow-lg shadow-indigo-100 text-white">
                                                        <label
                                                            class="text-xs font-bold opacity-80 uppercase tracking-widest">
                                                            Montant Total Attendu
                                                        </label>
                                                        <div class="text-3xl font-black">
                                                            {{ (Number(totalMontant) +
                                                                Number(caisseOuverture?.montant_ouverture ||
                                                                    0)).toLocaleString() }} F
                                                        </div>
                                                    </div>

                                                </div>

                                                <!-- Dual Billetterie Module -->
                                                <div v-if="isActionFermeture" class="mt-6 flex flex-col gap-6 ">
                                                    <div class="grid grid-cols-2 gap-8">
                                                        <!-- Caissière Side (Read Only) -->
                                                        <div class="flex flex-col gap-3">
                                                            <label
                                                                class="font-bold text-gray-400 text-xs uppercase tracking-tight flex items-center gap-2">
                                                                <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                                                Comptage Caissière (Lecture seule)
                                                            </label>
                                                            <div
                                                                class="bg-gray-50/50 border border-dashed rounded-xl overflow-hidden opacity-70">
                                                                <div
                                                                    class="grid grid-cols-2 bg-gray-100/50 border-b text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                                                    <div class="px-4 py-1.5 border-r">Coupure</div>
                                                                    <div class="px-4 py-1.5">Qté</div>
                                                                </div>
                                                                <div
                                                                    class="max-h-[300px] overflow-y-auto px-1 custom-scrollbar">
                                                                    <div v-for="item in billetterie" :key="item.valeur"
                                                                        class="grid grid-cols-2 items-center border-b border-gray-100 last:border-0 py-1 pl-3 pr-2">
                                                                        <div
                                                                            class="flex items-center justify-between pr-4 border-r border-gray-100">
                                                                            <span
                                                                                class="text-xs font-bold text-gray-500">{{
                                                                                    item.valeur.toLocaleString('fr-FR')
                                                                                }}</span>
                                                                            <span
                                                                                class="text-[9px] text-gray-300 font-mono">x</span>
                                                                        </div>
                                                                        <div
                                                                            class="pl-3 py-1 text-xs font-mono text-gray-500 text-right pr-4">
                                                                            {{ item.quantite || 0 }}
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="p-3 bg-gray-100/50 rounded-lg text-right">
                                                                <span
                                                                    class="text-[10px] font-bold text-gray-400 uppercase mr-2">Total
                                                                    Caissière:</span>
                                                                <span class="text-sm font-bold text-gray-600">{{
                                                                    totalBilletterie.toLocaleString() }} F</span>
                                                            </div>
                                                        </div>

                                                        <!-- Contrôleur Side (Editable) -->
                                                        <div class="flex flex-col gap-3">
                                                            <label
                                                                class="font-bold text-indigo-700 text-xs uppercase tracking-tight flex items-center gap-2">
                                                                <div
                                                                    class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse">
                                                                </div>
                                                                Vérification Contrôleur
                                                            </label>
                                                            <div
                                                                class="bg-white border-2 border-indigo-100 rounded-xl overflow-hidden shadow-sm shadow-indigo-50">
                                                                <div
                                                                    class="grid grid-cols-2 bg-indigo-50/50 border-b text-[10px] font-bold text-indigo-400 uppercase tracking-tighter">
                                                                    <div class="px-4 py-1.5 border-r border-indigo-100">
                                                                        Coupure</div>
                                                                    <div class="px-4 py-1.5 font-bold">Ma Quantité</div>
                                                                </div>
                                                                <div
                                                                    class="max-h-[300px] overflow-y-auto px-1 custom-scrollbar">
                                                                    <div v-for="item in billetterieControlleur"
                                                                        :key="item.valeur"
                                                                        class="grid grid-cols-2 items-center border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors py-1 pl-3 pr-2">
                                                                        <div
                                                                            class="flex items-center justify-between pr-4 border-r border-gray-100">
                                                                            <span
                                                                                class="text-xs font-black text-gray-700">{{
                                                                                    item.valeur.toLocaleString('fr-FR')
                                                                                }}</span>
                                                                            <span
                                                                                class="text-[9px] text-gray-400 font-mono">x</span>
                                                                        </div>
                                                                        <div class="flex items-center gap-3 pl-3">
                                                                            <Input type="number"
                                                                                v-model.number="item.quantite" min="0"
                                                                                @input="updateMontantRecuFromBilletterie"
                                                                                class="h-7 w-full text-right bg-white border-gray-200 focus:ring-indigo-500 font-mono text-xs pr-1" />
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="p-3 bg-indigo-600 rounded-lg text-right shadow-md">
                                                                <span
                                                                    class="text-[10px] font-bold text-indigo-100 uppercase mr-2">Mon
                                                                    Total Physique:</span>
                                                                <span class="text-sm font-black text-white">{{
                                                                    totalBilletterieControlleur.toLocaleString() }}
                                                                    F</span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Reconciliation Summary -->
                                                    <div class="grid grid-cols-3 gap-4 border-t pt-6 mt-2">
                                                        <div
                                                            class="p-4 bg-gray-50 rounded-2xl border flex flex-col gap-1 shadow-sm">
                                                            <span
                                                                class="text-[10px] font-bold text-gray-500 uppercase">Total
                                                                Théorique</span>
                                                            <span class="text-2xl font-black text-gray-800">{{
                                                                totalAttendu.toLocaleString() }} F</span>
                                                        </div>

                                                        <div class="p-4 rounded-2xl flex flex-col gap-1 shadow-sm border"
                                                            :class="perte > 0 ? 'bg-red-50 border-red-200' : 'bg-gray-50 border-gray-100'">
                                                            <span class="text-[10px] font-bold uppercase"
                                                                :class="perte > 0 ? 'text-red-600' : 'text-gray-400'">
                                                                Perte (Manquant)
                                                            </span>
                                                            <span class="text-2xl font-black"
                                                                :class="perte > 0 ? 'text-red-700' : 'text-gray-300'">
                                                                {{ perte.toLocaleString() }} F
                                                            </span>
                                                        </div>

                                                        <div class="p-4 rounded-2xl flex flex-col gap-1 shadow-sm border"
                                                            :class="surplus > 0 ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-100'">
                                                            <span class="text-[10px] font-bold uppercase"
                                                                :class="surplus > 0 ? 'text-blue-600' : 'text-gray-400'">
                                                                Surplus (Excédent)
                                                            </span>
                                                            <span class="text-2xl font-black"
                                                                :class="surplus > 0 ? 'text-blue-700' : 'text-gray-300'">
                                                                {{ surplus.toLocaleString() }} F
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div v-if="perte > 0 || surplus > 0"
                                                        class="flex items-start justify-between gap-4 bg-amber-50 border border-amber-200 p-4 rounded-xl">

                                                        <!-- ⚠️ LEFT : MESSAGE -->
                                                        <div class="flex items-start gap-3 max-w-md">
                                                            <i
                                                                class="pi pi-exclamation-triangle text-amber-600 text-xl mt-0.5"></i>
                                                            <div>
                                                                <p class="text-sm font-bold text-amber-800">
                                                                    Écart de caisse détecté !
                                                                </p>
                                                                <p class="text-xs text-amber-700 mt-1">
                                                                    Veuillez justifier l'écart ci-dessous pour valider
                                                                    la clôture.
                                                                </p>
                                                            </div>
                                                        </div>

                                                        <!-- ✍️ RIGHT : TEXTAREA -->
                                                        <div class="flex flex-col flex-1 max-w-lg">
                                                            <label
                                                                class="text-[10px] font-bold text-amber-700 uppercase tracking-wider mb-1">
                                                                Justification obligatoire
                                                            </label>

                                                            <Textarea v-model="commentaire"
                                                                placeholder="Expliquez la raison de l'écart..."
                                                                class="bg-white border-amber-200 focus-visible:ring-amber-500 min-h-[80px]" />
                                                        </div>

                                                    </div>
                                                </div>

                                                <div v-if="!isActionFermeture" class="my-4 text-center">
                                                    <p class="text-sm text-gray-600">
                                                        Vous allez ouvrir la caisse
                                                        pour le
                                                        {{
                                                            formatDate(selectedDate)
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div v-else
                                                class="py-8 text-center animate-in fade-in zoom-in duration-300">
                                                <div class="mb-4 flex justify-center text-amber-500">
                                                    <i class="pi pi-exclamation-circle text-6xl"></i>
                                                </div>
                                                <h3 class="text-xl font-bold text-gray-900 mb-2">Êtes-vous sûr ?</h3>
                                                <p class="text-gray-600">
                                                    Vous êtes sur le point de clôturer définitivement la caisse pour le
                                                    {{ formatDate(selectedDate) }}.
                                                </p>
                                                <div
                                                    class="mt-6 flex flex-col gap-3 p-4 bg-gray-50 rounded-xl text-left border border-gray-100">
                                                    <div class="flex justify-between border-b pb-2">
                                                        <span class="text-sm text-gray-500">Total Ventes :</span>
                                                        <span class="text-sm font-bold text-blue-700">{{
                                                            totalMontantFormatted }}</span>
                                                    </div>
                                                    <div class="flex justify-between border-b pb-2">
                                                        <span class="text-sm text-gray-500">Fond de Caisse :</span>
                                                        <span class="text-sm font-bold text-amber-700">{{
                                                            Number(caisseOuverture?.montant_ouverture ||
                                                                0).toLocaleString() }} F</span>
                                                    </div>
                                                    <div
                                                        class="flex justify-between border-b pb-2 bg-indigo-50/50 -mx-1 px-1">
                                                        <span class="text-sm font-bold text-indigo-900">Total Attendu
                                                            :</span>
                                                        <span class="text-sm font-black text-indigo-700">{{
                                                            (Number(totalMontant) +
                                                                Number(caisseOuverture?.montant_ouverture ||
                                                                    0)).toLocaleString() }} F</span>
                                                    </div>
                                                    <div class="flex justify-between border-b pb-2">
                                                        <span class="text-sm text-gray-500">Montant reçu :</span>
                                                        <span class="text-sm font-bold text-green-700">{{
                                                            Number(montantRecu).toLocaleString() }} F</span>
                                                    </div>
                                                    <!-- <div class="flex justify-between border-b pb-2">
                                                        <span class="text-sm text-gray-500">Montant reçu :</span>
                                                        <span class="text-sm font-bold text-green-700">{{
                                                            Number(montantRecu).toLocaleString() }} F</span>
                                                    </div> -->
                                                    <div class="flex justify-between border-b pb-2">
                                                        <span class="text-sm text-gray-500">Billetterie Contrôleur
                                                            :</span>
                                                        <span class="text-sm font-bold text-indigo-700">{{
                                                            totalBilletterieControlleur.toLocaleString() }} F</span>
                                                    </div>
                                                    <div v-if="perte > 0" class="flex justify-between border-b pb-2">
                                                        <span class="text-sm text-red-500 font-bold">Perte détectée
                                                            :</span>
                                                        <span class="text-sm font-black text-red-600">{{
                                                            perte.toLocaleString() }} F</span>
                                                    </div>
                                                    <div v-if="surplus > 0" class="flex justify-between">
                                                        <span class="text-sm text-blue-500 font-bold">Surplus détecté
                                                            :</span>
                                                        <span class="text-sm font-black text-blue-600">{{
                                                            surplus.toLocaleString() }} F</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <AlertDialogFooter class="flex flex-row gap-2 py-2">
                                        <template v-if="!confirmingFinalClosure">
                                            <AlertDialogCancel @click="confirmingFinalClosure = false">Annuler
                                            </AlertDialogCancel>
                                            <Button @click="handleValidate"
                                                class="bg-green-800 p-4 rounded hover:bg-gray-900 transition duration-300"
                                                :disabled="loading || (isActionFermeture && !montantRecu)">
                                                Valider
                                                <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                            </Button>
                                        </template>
                                        <template v-else>
                                            <Button variant="outline" @click="confirmingFinalClosure = false">
                                                Non, modifier
                                            </Button>
                                            <Button @click="handleValidate"
                                                class="bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-100 px-8">
                                                Oui, confirmer la clôture
                                                <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                            </Button>
                                        </template>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>

                    <!-- CONTENT BOX: Dossier List -->
                    <DossierListTable class="mt-6" :paiements="paiements" :selectedDate="selectedDate"
                        :montantOuverture="Number(caisseOuverture?.montant_ouverture || 0)" />
                </div>
            </Card>
        </main>

        <Toaster richColors position="top-right" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from "@/components/ui/alert-dialog";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Toaster, toast } from "vue-sonner";
import { LockKeyhole, LockOpen } from "lucide-vue-next";
import { Textarea } from "@/components/ui/textarea";
import DossierListTable from "@/components/Caisse/DossierListTable.vue";

const paiements = ref([]);
const selectedDate = ref(new Date().toISOString().split("T")[0]);
const caisse = ref(null);
const selectedCaisse = ref("");
const montantRecu = ref("");
const loading = ref(false);
const isDialogOpen = ref(false);
const ouvertureDuJour = ref(null);
const ouvertureNonFermee = ref(null);
const montantCaisse = ref(0);
const caisseOuverture = ref(null);
const confirmingFinalClosure = ref(false);
const commentaire = ref("");

const billetterie = ref([
    { valeur: 10000, quantite: 0 },
    { valeur: 5000, quantite: 0 },
    { valeur: 2000, quantite: 0 },
    { valeur: 1000, quantite: 0 },
    { valeur: 500, quantite: 0 },
    { valeur: 250, quantite: 0 },
    { valeur: 200, quantite: 0 },
    { valeur: 100, quantite: 0 },
    { valeur: 50, quantite: 0 },
    { valeur: 25, quantite: 0 },
    { valeur: 10, quantite: 0 },
    { valeur: 5, quantite: 0 },
]);

const billetterieControlleur = ref([
    { valeur: 10000, quantite: 0 },
    { valeur: 5000, quantite: 0 },
    { valeur: 2000, quantite: 0 },
    { valeur: 1000, quantite: 0 },
    { valeur: 500, quantite: 0 },
    { valeur: 250, quantite: 0 },
    { valeur: 200, quantite: 0 },
    { valeur: 100, quantite: 0 },
    { valeur: 50, quantite: 0 },
    { valeur: 25, quantite: 0 },
    { valeur: 10, quantite: 0 },
    { valeur: 5, quantite: 0 },
]);

const totalBilletterie = computed(() => {
    return billetterie.value.reduce((total, item) => {
        return total + (item.valeur * (item.quantite || 0));
    }, 0);
});

const totalBilletterieControlleur = computed(() => {
    return billetterieControlleur.value.reduce((total, item) => {
        return total + (item.valeur * (item.quantite || 0));
    }, 0);
});

const totalAttendu = computed(() => {
    return Number(totalMontant.value) + Number(caisseOuverture.value?.montant_ouverture || 0);
});

const perte = computed(() => {
    const diff = totalAttendu.value - totalBilletterieControlleur.value;
    return diff > 0 ? diff : 0;
});

const surplus = computed(() => {
    const diff = totalBilletterieControlleur.value - totalAttendu.value;
    return diff > 0 ? diff : 0;
});

const updateMontantRecuFromBilletterie = () => {
    montantRecu.value = totalBilletterieControlleur.value.toString();
};

const totalMontant = computed(() => {
    return paiements.value.reduce((sum, p) => sum + Number(p.montant || 0), 0);
});


const totalMontantFormatted = computed(
    () => `${totalMontant.value.toLocaleString()} F CFA`,
);

const today = computed(() => new Date().toISOString().split("T")[0]);
const yesterday = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() - 1);
    return date.toISOString().split("T")[0];
});

const isToday = computed(() => selectedDate.value === today.value);
const isYesterday = computed(() => selectedDate.value === yesterday.value);
const isTodayOrYesterday = computed(() => isToday.value || isYesterday.value);

// Vérifier si une session précédente (différente de celle du jour) est non fermée
const hasSessionPrecedenteNonFermee = computed(() => {
    if (!ouvertureNonFermee.value) return false;
    if (!ouvertureDuJour.value) return true;
    return ouvertureNonFermee.value.id !== ouvertureDuJour.value.id;
});

// La caisse est ouverte si ouvertureDuJour existe et status = 1
const isCaisseOuverte = computed(() => {
    return ouvertureDuJour.value?.status === 1;
});

// La caisse a été fermée aujourd'hui si ouvertureDuJour existe et status != 1
const isCaisseFermeeAujourdhui = computed(() => {
    return ouvertureDuJour.value && ouvertureDuJour.value.status !== 1;
});

// Action : fermeture si caisse ouverte, sinon ouverture
const isActionFermeture = computed(() => isCaisseOuverte.value);

// Afficher le bouton uniquement pour aujourd'hui ou hier
const shouldShowActionButton = computed(() => {
    // Cas 1 : aujourd'hui → tout est permis
    if (isToday.value) return true;

    // Cas 2 : hier → bouton visible uniquement si caisse ouverte hier et non fermée
    if (isYesterday.value) {
        if (
            ouvertureDuJour.value &&                // il y a une ouverture hier
            ouvertureDuJour.value.status === 1     // status = 1 → ouverte
        ) {
            return true; // peut afficher bouton pour fermer hier
        }
        return false; // caisse jamais ouverte hier → rien afficher
    }

    // Autres jours → jamais
    return false;
});


const isButtonDisabled = computed(() => {
    if (!selectedCaisse.value) return true;

    if (isCaisseFermeeAujourdhui.value) return true;

    return false;
});

const buttonLabel = computed(() => {
    if (isButtonDisabled.value && isCaisseFermeeAujourdhui.value) {
        return "CAISSE DÉJÀ CLÔTURÉE";
    }
    return isActionFermeture.value ? "CLÔTURER LA CAISSE" : "OUVRIR LA CAISSE";
});

const buttonVariant = computed(() => {
    if (isButtonDisabled.value) return "secondary";
    return isActionFermeture.value ? "destructive" : "default";
});

const dialogTitle = computed(() => {
    const action = isActionFermeture.value ? "Clôture" : "Ouverture";
    return `${action} de la caisse - ${formatDate(selectedDate.value)}`;
});

const statusCaisseLabel = computed(() => {
    if (isCaisseOuverte.value) return "Ouverte";
    if (isCaisseFermeeAujourdhui.value) return "Fermée";
    return "Non ouverte";
});

const statusCaisseClass = computed(() => {
    if (isCaisseOuverte.value) return "text-green-600";
    if (isCaisseFermeeAujourdhui.value) return "text-gray-600";
    return "text-red-600";
});

//  Formater une date au format FR
const formatDate = (dateStr) => {
    if (!dateStr) return "";
    return new Date(dateStr + "T00:00:00").toLocaleDateString("fr-FR");
};

//  Récupérer les informations de la caisse
const fetchCaisse = async () => {
    try {
        const { data } = await axios.get("/caisse/of/user", {
            params: { date: selectedDate.value },
        });

        // console.log("Données caisse:", data);

        caisse.value = data.caisse || null;
        ouvertureDuJour.value = data.ouverture_du_jour || null;
        ouvertureNonFermee.value = data.ouverture_non_fermee || null;
        caisseOuverture.value = data.caisse_ouverture || null;
        selectedCaisse.value = data.caisse?.id || "";

        // Pré-remplir la billetterie Caissière
        if (data.caisse_ouverture?.billetterie) {
            const savedBilletterie = typeof data.caisse_ouverture.billetterie === 'string'
                ? JSON.parse(data.caisse_ouverture.billetterie)
                : data.caisse_ouverture.billetterie;

            billetterie.value.forEach(item => {
                const saved = savedBilletterie.find(s => s.valeur === item.valeur);
                if (saved) item.quantite = saved.quantite || 0;
            });
        } else {
            billetterie.value.forEach(item => item.quantite = 0);
        }

        // Pré-remplir la billetterie Contrôleur
        if (data.caisse_ouverture?.billetterie_controlleur) {
            const savedBilletterieCtrl = typeof data.caisse_ouverture.billetterie_controlleur === 'string'
                ? JSON.parse(data.caisse_ouverture.billetterie_controlleur)
                : data.caisse_ouverture.billetterie_controlleur;

            billetterieControlleur.value.forEach(item => {
                const saved = savedBilletterieCtrl.find(s => s.valeur === item.valeur);
                if (saved) item.quantite = saved.quantite || 0;
            });
        } else {
            billetterieControlleur.value.forEach(item => item.quantite = 0);
        }
    } catch (error) {
        console.error("Erreur lors du chargement de la caisse :", error);
        toast.error("Impossible de charger la caisse.");
    }
};

//  Récupérer les paiements

const fetchPaiements = async () => {
    try {
        const { data } = await axios.get("/paiement/data/stat", {
            params: { date: selectedDate.value },
        });

        paiements.value = Array.isArray(data) ? data : [];
    } catch (error) {
        console.error("Erreur lors du chargement des paiements :", error);
        toast.error("Impossible de charger les paiements.");
    }
};

//  Recharger caisse + paiements
const fetchPaiements_Caisse = async () => {
    await fetchCaisse();
    await fetchPaiements();
};

//  Validation de l'ouverture/fermeture
const handleValidate = async () => {
    if (!selectedCaisse.value) {
        toast.error("Veuillez sélectionner une caisse.");
        return;
    }
    montantCaisse.value = montantRecu.value;

    // Validation du montant en cas de fermeture
    if (isActionFermeture.value && !montantRecu.value) {
        toast.error("Veuillez saisir le montant reçu.");
        return;
    }

    // Validation du commentaire si écart
    if (isActionFermeture.value && (perte.value > 0 || surplus.value > 0) && !commentaire.value.trim()) {
        toast.error("Veuillez saisir un commentaire pour justifier l'écart de caisse.");
        return;
    }

    // Première étape : Demander confirmation (toujours ou si écart)
    if (isActionFermeture.value && !confirmingFinalClosure.value) {
        confirmingFinalClosure.value = true;
        return;
    }

    const selectedInfo = caisse.value;

    const payload = {
        montant_caisse: isActionFermeture.value ? totalBilletterieControlleur.value : 0,
        montant_controlleur: isActionFermeture.value
            ? parseFloat(
                String(montantRecu.value)
                    .replace(/\s+/g, "")
                    .replace(/,/g, "."),
            )
            : 0,
        id_caisse: Number(selectedCaisse.value),
        code_caisse: selectedInfo?.code ?? "",
        id_site: selectedInfo?.site_id ?? null,
        is_fermeture: isActionFermeture.value ? 1 : 0,
        date_operation: selectedDate.value,
        billetterie: billetterie.value,
        billetterie_controlleur: billetterieControlleur.value,
        perte: perte.value,
        surplus: surplus.value,
        commentaire: commentaire.value,
    };

    try {
        loading.value = true;
        // L'URL semble être différente dans votre environnement, je garde la vôtre
        const res = await axios.post("/verification/validate/caisse/controller/montant", payload);

        toast.success(res.data?.message || "Opération réussie");
        isDialogOpen.value = false;
        confirmingFinalClosure.value = false;

        await fetchCaisse();
        await fetchPaiements();

        if (isActionFermeture.value) {
            montantRecu.value = "";
            commentaire.value = "";
        }
    } catch (error) {
        console.error("Erreur lors de la validation :", error);
        const msg =
            error?.response?.data?.message ||
            (error?.response?.data?.errors
                ? formatErrors(error.response.data.errors)
                : "Une erreur est survenue lors de la validation.");
        toast.error(msg);
    } finally {
        loading.value = false;
    }
};

//   Formater les erreurs Laravel
function formatErrors(errs) {
    if (!errs) return "Erreur serveur";
    if (typeof errs === "string") return errs;
    const messages = [];
    for (const k of Object.keys(errs)) {
        if (Array.isArray(errs[k])) messages.push(...errs[k]);
        else messages.push(String(errs[k]));
    }
    return messages.join(" — ");
}

onMounted(async () => {
    await fetchCaisse();
    if (selectedCaisse.value) {
        await fetchPaiements();
    }
});
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";

export default {
    layout: Main,
};
</script>
