<template>
    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 px-4 md:gap-8 md:px-8">
            <Card class="h-full flex flex-col">
                <div class="space-y-4 px-8">
                    <!-- Alerte session RAF précédente non fermée -->
                    <div v-if="hasSessionRafPrecedenteNonFermee"
                        class="flex items-center space-x-2 p-3 bg-orange-50 border border-orange-400 rounded-lg">
                        <i class="pi pi-exclamation-triangle text-orange-600"></i>
                        <div class="flex-1">
                            <span class="text-sm font-semibold text-orange-800">
                                Session RAF précédente non fermée pour
                                {{ getCaisseNonFermeeLabel }}
                            </span>
                            <span v-if="ouvertureRafNonFermee?.date" class="block text-xs text-orange-600 mt-1">
                                Ouverte le
                                {{ formatDate(ouvertureRafNonFermee.date) }}
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

                            <!-- Filtre par date -->
                            <input type="date" v-model="selectedDate" @change="fetchPaiements"
                                class="border rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-gray-500 focus:border-amber-500 bg-background shadow-sm h-9" />

                            <p class="text-sm font-semibold text-gray-700">
                                CAISSE RAF :
                            </p>

                            <select v-model="selectedCaisse" @change="onCaisseChange"
                                class="border rounded-md px-3 py-1 text-sm">
                                <option value="">
                                    -- Choisir une caisse --
                                </option>
                                <option v-for="caisse in caisses" :key="caisse.id" :value="String(caisse.id)">
                                    {{ caisse.libelle }} ({{ caisse.code }}) —
                                    <span v-if="caisse.raf_ouvert">RAF Ouvert</span>
                                    <span v-else-if="caisse.raf_ferme_aujourdhui">RAF Clôturé</span>
                                    <span v-else>RAF Non ouvert</span>
                                </option>
                            </select>

                            <!-- Statut RAF de la caisse sélectionnée -->
                            <div v-if="selectedCaisse" class="flex items-center gap-2 px-3 py-1 rounded bg-gray-50">
                                <p class="text-sm font-semibold text-gray-700">
                                    Statut RAF :
                                </p>
                                <p :class="statusRafClass" class="text-sm font-semibold">
                                    {{ statusRafLabel }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-6 text-sm font-medium">
                            <!-- Bouton Ouverture/Fermeture RAF -->
                            <AlertDialog v-model:open="isDialogOpen">
                                <AlertDialogTrigger as-child v-if="shouldShowActionButton">
                                    <Button :variant="buttonVariant" :disabled="isButtonDisabled"
                                        class="transition-all duration-200">
                                        <span>{{ buttonLabel }}</span>
                                        <Shield v-if="isActionFermeture" class="ml-2" />
                                        <ShieldCheck v-else class="ml-2" />
                                    </Button>
                                </AlertDialogTrigger>

                                <AlertDialogContent class="w-full max-w-[1200px]">
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>
                                            {{ dialogTitle }}
                                        </AlertDialogTitle>

                                        <AlertDialogDescription>
                                            <div v-if="isActionFermeture" class="overflow-y-auto px-1 custom-scrollbar">
                                                <!-- Summary Cards -->
                                                <div class="grid grid-cols-3 gap-4 my-4">
                                                    <div
                                                        class="flex flex-col bg-blue-50/50 p-3 rounded-xl border border-blue-100">
                                                        <label class="text-xs font-bold text-blue-700 uppercase mb-1">
                                                            Ventes du jour
                                                        </label>
                                                        <div class="text-xl font-black text-blue-800">
                                                            {{ totalMontantFormatted }}
                                                        </div>
                                                    </div>

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

                                                    <div
                                                        class="flex flex-col bg-green-600 p-3 rounded-xl shadow-lg shadow-green-100 text-white">
                                                        <label
                                                            class="text-xs font-bold opacity-80 uppercase tracking-widest">
                                                            Montant Total Attendu
                                                        </label>
                                                        <div class="text-3xl font-black">
                                                            {{ totalAttendu.toLocaleString() }} F
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Read-only Dual Billetterie -->
                                                <div class="grid grid-cols-2 gap-8 mt-6">
                                                    <!-- Caissière Side -->
                                                    <div class="flex flex-col gap-3">
                                                        <label
                                                            class="font-bold text-gray-400 text-xs uppercase tracking-tight flex items-center gap-2">
                                                            <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                                            Comptage Caissière
                                                        </label>
                                                        <div
                                                            class="bg-gray-50/50 border border-dashed rounded-xl overflow-hidden opacity-70">
                                                            <div
                                                                class="grid grid-cols-2 bg-gray-100/50 border-b text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                                                <div class="px-4 py-1.5 border-r">Coupure</div>
                                                                <div class="px-4 py-1.5">Qté</div>
                                                            </div>
                                                            <div
                                                                class="max-h-[250px] overflow-y-auto px-1 custom-scrollbar">
                                                                <div v-for="item in billetterie" :key="item.valeur"
                                                                    class="grid grid-cols-2 items-center border-b border-gray-100 last:border-0 py-1 pl-3 pr-2">
                                                                    <div
                                                                        class="flex items-center justify-between pr-4 border-r border-gray-100">
                                                                        <span class="text-xs font-bold text-gray-500">{{
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

                                                    <!-- Contrôleur Side -->
                                                    <div class="flex flex-col gap-3">
                                                        <label
                                                            class="font-bold text-indigo-700 text-xs uppercase tracking-tight flex items-center gap-2">
                                                            <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                                                            Validation Contrôleur
                                                        </label>
                                                        <div
                                                            class="bg-indigo-50/30 border border-indigo-100 rounded-xl overflow-hidden shadow-sm">
                                                            <div
                                                                class="grid grid-cols-2 bg-indigo-100/50 border-b text-[10px] font-bold text-indigo-400 uppercase tracking-tighter">
                                                                <div class="px-4 py-1.5 border-r border-indigo-200">
                                                                    Coupure</div>
                                                                <div class="px-4 py-1.5">Qté</div>
                                                            </div>
                                                            <div
                                                                class="max-h-[250px] overflow-y-auto px-1 custom-scrollbar">
                                                                <div v-for="item in billetterieControlleur"
                                                                    :key="item.valeur"
                                                                    class="grid grid-cols-2 items-center border-b border-indigo-100 last:border-0 py-1 pl-3 pr-2">
                                                                    <div
                                                                        class="flex items-center justify-between pr-4 border-r border-indigo-100">
                                                                        <span
                                                                            class="text-xs font-black text-indigo-700">{{
                                                                                item.valeur.toLocaleString('fr-FR')
                                                                            }}</span>
                                                                        <span
                                                                            class="text-[9px] text-indigo-300 font-mono">x</span>
                                                                    </div>
                                                                    <div
                                                                        class="pl-3 py-1 text-xs font-mono text-indigo-600 text-right pr-4">
                                                                        {{ item.quantite || 0 }}
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="p-3 bg-indigo-600 rounded-lg text-right shadow-md">
                                                            <span
                                                                class="text-[10px] font-bold text-indigo-100 uppercase mr-2">Total
                                                                Physique (Contrôleur):</span>
                                                            <span class="text-sm font-black text-white">{{
                                                                totalBilletterieControlleur.toLocaleString() }} F</span>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Reconciliation Footer -->
                                                <div class="grid grid-cols-3 gap-4 border-t pt-6 mt-6">
                                                    <div
                                                        class="p-4 rounded-2xl flex flex-col gap-1 shadow-sm border border-red-100 bg-red-50"
                                                        v-if="perte > 0">
                                                        <span class="text-[10px] font-bold uppercase text-red-600">Perte
                                                            (Manquant)</span>
                                                        <span class="text-2xl font-black text-red-700">{{
                                                            perte.toLocaleString() }} F</span>
                                                    </div>
                                                    <div
                                                        class="p-4 rounded-2xl flex flex-col gap-1 shadow-sm border border-blue-100 bg-blue-50"
                                                        v-if="surplus > 0">
                                                        <span
                                                            class="text-[10px] font-bold uppercase text-blue-600">Surplus
                                                            (Excédent)</span>
                                                        <span class="text-2xl font-black text-blue-700">{{
                                                            surplus.toLocaleString() }} F</span>
                                                    </div>
                                                    <div v-if="commentaire"
                                                        class="col-span-full p-4 bg-amber-50 border border-amber-200 rounded-xl flex flex-col gap-2">
                                                        <div class="flex items-center gap-2 text-amber-800">
                                                            <i class="pi pi-comment text-sm"></i>
                                                            <span class="text-[10px] font-bold uppercase">Justification
                                                                du Contrôleur</span>
                                                        </div>
                                                        <p class="text-sm italic text-amber-900 leading-relaxed">{{
                                                            commentaire }}</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div v-else class="my-4 text-center">
                                                <p class="text-sm text-gray-600">
                                                    Vous allez ouvrir la session RAF pour
                                                    <strong>{{ getSelectedCaisseLabel }}</strong> le {{
                                                        formatDate(selectedDate) }}
                                                </p>
                                            </div>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <AlertDialogFooter class="flex flex-row gap-2">
                                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                                        <Button @click="handleValidate" v-if="!isActionFermeture"
                                            :disabled="loading"> Valider Ouverture RAF
                                            <span v-if="loading" class="ml-2 animate-spin">⏳</span> </Button>

                                        <Button v-else @click="showConfirmValidate = true" :disabled="loading ||
                                            (isActionFermeture && (montantRecu === null || montantRecu === ''))
                                            ">
                                            Valider Clôture RAF
                                            <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                        </Button>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>

                    <!-- CONTENT BOX: Dossier List -->
                    <DossierListTable v-if="selectedCaisse" class="mt-6" :paiements="paiements"
                        :selectedDate="selectedDate"
                        :montantOuverture="Number(caisseOuverture?.montant_ouverture || 0)" />

                    <!-- Fallback -->
                    <div v-if="!selectedCaisse" class="bg-white shadow rounded-xl p-8 text-center border-2 border-dashed">
                        <p class="text-gray-500">Veuillez sélectionner une caisse pour voir les détails.</p>
                    </div>
                </div>
            </Card>
        </main>

        <Toaster richColors position="top-right" />

        <div>
            <AlertDialog v-model:open="showConfirmValidate">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Confirmation
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            Êtes-vous sûr de vouloir valider cette opération ?
                            En confirmant, vous fermez votre session et déversez les transanctions dans
                            x3.
                        </AlertDialogDescription>
                    </AlertDialogHeader>

                    <AlertDialogFooter class="flex gap-2">
                        <AlertDialogCancel @click="showConfirmValidate = false">
                            Annuler
                        </AlertDialogCancel>

                        <Button variant="destructive" :disabled="loading" @click="() => {
                            showConfirmValidate = false;
                            handleValidate();
                        }">
                            Confirmer
                            <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>
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
import { Shield, ShieldCheck, User, Settings, LockKeyhole } from "lucide-vue-next";
import DossierListTable from "@/components/Caisse/DossierListTable.vue";

// ============================================================================
// REFS
// ============================================================================
const paiements = ref([]);
const selectedDate = ref(new Date().toISOString().split("T")[0]);
const caisses = ref([]);
const selectedCaisse = ref("");
const montantRecu = ref("");
const loading = ref(false);
const isDialogOpen = ref(false);
const montantCaisse = ref(0);
const showConfirmValidate = ref(false);
const caisseRafInfo = ref({});
const ouvertureRafNonFermee = ref(null);
const caisseOuverture = ref(null);
const commentaire = ref("");
const perte = ref(0);
const surplus = ref(0);

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
    return billetterie.value.reduce((total, item) => total + (item.valeur * (item.quantite || 0)), 0);
});

const totalBilletterieControlleur = computed(() => {
    return billetterieControlleur.value.reduce((total, item) => total + (item.valeur * (item.quantite || 0)), 0);
});

const totalAttendu = computed(() => {
    return Number(totalMontant.value) + Number(caisseOuverture.value?.montant_ouverture || 0);
});

// ============================================================================
// DETAILS & TOTALS
// ============================================================================
const details = computed(() => {
    const result = { emuci: [], quipux: [], dgtt: [], ministere: [] };
    paiements.value.forEach((p) => {
        if (!p || !p.description) return;

        const cat = p.description.toLowerCase();
        if (cat.includes("emuci")) result.emuci.push(p);
        else if (cat.includes("quipux")) result.quipux.push(p);
        else if (cat.includes("dgtt")) result.dgtt.push(p);
        else result.ministere.push(p);
    });
    return result;
});

const totals = computed(() => {
    return {
        emuci: details.value.emuci.reduce((s, x) => s + Number(x.montant), 0),
        quipux: details.value.quipux.reduce((s, x) => s + Number(x.montant), 0),
        dgtt: details.value.dgtt.reduce((s, x) => s + Number(x.montant), 0),
        ministere: details.value.ministere.reduce(
            (s, x) => s + Number(x.montant),
            0,
        ),
    };
});

const totalMontant = computed(() => {
    return Object.values(totals.value).reduce((s, x) => s + x, 0);
});

const totalMontantFormatted = computed(() => {
    return totalMontant.value.toLocaleString() + " F";
});

// ============================================================================
// LOGIC: SESSIONS & VALIDATION
// ============================================================================
const isActionFermeture = computed(() => {
    if (!selectedCaisse.value) return false;
    const info = caisseRafInfo.value[selectedCaisse.value];
    return !!(info && info.ouverture_raf_du_jour);
});

const buttonLabel = computed(() => {
    return isActionFermeture.value ? "Fermer session RAF" : "Ouvrir session RAF";
});

const buttonVariant = computed(() => {
    return isActionFermeture.value ? "destructive" : "default";
});

const dialogTitle = computed(() => {
    return isActionFermeture.value ? "Fermer la session RAF" : "Ouvrir la session RAF";
});

const shouldShowActionButton = computed(() => {
    if (!selectedCaisse.value) return false;
    const info = caisseRafInfo.value[selectedCaisse.value];
    if (info?.ouverture_raf_du_jour?.date_fermeture_raf) return false;
    return true;
});

const isButtonDisabled = computed(() => {
    if (!selectedCaisse.value) return true;
    const hasActiveOther = !!ouvertureRafNonFermee.value;
    const info = caisseRafInfo.value[selectedCaisse.value];
    const isThisOpeningActive =
        info?.ouverture_raf_non_fermee &&
        String(info.ouverture_raf_non_fermee.caisse_id) === String(selectedCaisse.value);

    if (!isActionFermeture.value && hasActiveOther && !isThisOpeningActive) return true;
    return false;
});

const statusRafLabel = computed(() => {
    if (!selectedCaisse.value) return "Indéterminé";
    const info = caisseRafInfo.value[selectedCaisse.value];
    if (info?.ouverture_raf_du_jour?.date_fermeture_raf) return "Clôturée";
    if (info?.ouverture_raf_du_jour) return "Ouverte";
    return "Non ouverte";
});

const statusRafClass = computed(() => {
    const label = statusRafLabel.value;
    if (label === "Ouverte") return "text-green-600 font-bold italic";
    if (label === "Clôturée") return "text-blue-600 font-bold italic";
    return "text-orange-500 italic";
});

const getSelectedCaisseLabel = computed(() => {
    const c = caisses.value.find((x) => String(x.id) === String(selectedCaisse.value));
    return c ? `${c.libelle} (${c.code})` : "cette caisse";
});

const hasSessionRafPrecedenteNonFermee = computed(() => {
    return !!ouvertureRafNonFermee.value;
});

const getCaisseNonFermeeLabel = computed(() => {
    if (!ouvertureRafNonFermee.value) return "";
    const c = caisses.value.find(
        (x) => String(x.id) === String(ouvertureRafNonFermee.value.caisse_id)
    );
    return c ? `${c.libelle} (${c.code})` : "une autre caisse";
});

// ============================================================================
// DATA FETCHING
// ============================================================================
const fetchCaisses = async () => {
    try {
        const { data } = await axios.get("/raf/caisse/liste");
        caisses.value = data;
        await fetchAllCaissesRafInfo();
    } catch (error) {
        console.error("Erreur lors du chargement des caisses :", error);
    }
};

const fetchAllCaissesRafInfo = async () => {
    ouvertureRafNonFermee.value = null;
    for (const caisse of caisses.value) {
        await fetchRafInfoForCaisse(caisse.id);
    }
};

const fetchRafInfoForCaisse = async (caisseId) => {
    try {
        const { data } = await axios.get("/raf/caisse/info", {
            params: { date: selectedDate.value, caisse_id: caisseId },
        });

        caisseRafInfo.value[caisseId] = {
            ouverture_raf_du_jour: data.ouverture_raf_du_jour || null,
            ouverture_raf_non_fermee: data.ouverture_raf_non_fermee || null,
        };

        if (String(caisseId) === String(selectedCaisse.value)) {
            caisseOuverture.value = data.caisse_ouverture || null;
            perte.value = data.caisse_ouverture?.perte || 0;
            surplus.value = data.caisse_ouverture?.surplus || 0;
            commentaire.value = data.caisse_ouverture?.commentaire || "";
            montantRecu.value = data.caisse_ouverture?.montant_controlleur || "";

            // Hydrater billetteries
            const hydrate = (dest, sourceStr) => {
                if (sourceStr) {
                    const source = typeof sourceStr === 'string' ? JSON.parse(sourceStr) : sourceStr;
                    dest.value.forEach(item => {
                        const saved = source.find(s => s.valeur === item.valeur);
                        item.quantite = saved ? (saved.quantite || 0) : 0;
                    });
                } else {
                    dest.value.forEach(item => item.quantite = 0);
                }
            };
            hydrate(billetterie, data.caisse_ouverture?.billetterie);
            hydrate(billetterieControlleur, data.caisse_ouverture?.billetterie_controlleur);
        }

        if (data.ouverture_raf_non_fermee) {
            ouvertureRafNonFermee.value = data.ouverture_raf_non_fermee;
        }
    } catch (error) {
        console.error(`Erreur RAF info caisse ${caisseId}:`, error);
    }
};

const fetchPaiements = async () => {
    try {
        const { data } = await axios.get("/raf/paiement/data/stat", {
            params: {
                date: selectedDate.value,
                caisse_id: selectedCaisse.value,
            },
        });
        paiements.value = Array.isArray(data) ? data : [];
        await fetchRafInfoForCaisse(selectedCaisse.value);
        await fetchAllCaissesRafInfo();
    } catch (error) {
        console.error("Erreur paiements :", error);
        toast.error("Impossible de charger les paiements.");
    }
};

const onCaisseChange = async () => {
    await fetchPaiements();
};

// ============================================================================
// VALIDATION
// ============================================================================
const handleValidate = async () => {
    if (!selectedCaisse.value) {
        toast.error("Veuillez sélectionner une caisse.");
        return;
    }

    const parseMoney = (v) => Number(String(v).replace(/\s+/g, "").replace(/,/g, "."));

    if (isActionFermeture.value && (montantRecu.value === null || montantRecu.value === "")) {
        toast.error("Le montant de la session n'est pas encore validé par le contrôleur.");
        return;
    }

    if (isActionFermeture.value) {
        montantCaisse.value = parseMoney(montantRecu.value);
    }

    const selectedInfo = caisses.value.find((c) => String(c.id) === String(selectedCaisse.value));
    const payload = {
        montant_controlleur: isActionFermeture.value ? parseMoney(montantRecu.value) : 0,
        id_caisse: Number(selectedCaisse.value),
        code_caisse: selectedInfo?.code ?? "",
        id_site: selectedInfo?.site_id ?? null,
        is_fermeture: isActionFermeture.value ? 1 : 0,
        date_operation: selectedDate.value,
    };

    try {
        loading.value = true;
        const res = await axios.post("/raf/caisse/controller/validate", payload);
        toast.success(res.data?.message || "Opération RAF réussie");
        isDialogOpen.value = false;
        await fetchCaisses();
        await fetchPaiements();
        if (isActionFermeture.value) montantRecu.value = "";
    } catch (error) {
        console.error("Erreur validation RAF :", error);
        const msg = error?.response?.data?.message || "Une erreur est survenue lors de la validation RAF.";
        toast.error(msg);
    } finally {
        loading.value = false;
    }
};

// ============================================================================
// UTILS
// ============================================================================
const formatDate = (dateStr) => {
    if (!dateStr) return "";
    const [y, m, d] = dateStr.split("-");
    const months = ["Janv", "Févr", "Mars", "Avril", "Mai", "Juin", "Juil", "Août", "Sept", "Oct", "Nov", "Déc"];
    return `${d} ${months[parseInt(m) - 1]} ${y}`;
};

onMounted(async () => {
    await fetchCaisses();
    await fetchPaiements();
});
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";
export default { layout: Main };
</script>
