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
                            <span v-if="sessionNonFermee?.date" class="block text-xs text-orange-600 mt-1">
                                Ouverte le
                                {{ formatDate(String(sessionNonFermee.date).slice(0, 10)) }}
                            </span>
                        </div>
                    </div>

                    <!-- HEADER -->
                    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
                        <!-- Date + Filtres site / caisse -->
                        <div class="flex flex-wrap items-center gap-3">
                            <p class="text-sm font-semibold text-gray-700">
                                DATE :
                                <span class="font-bold italic text-black">{{
                                    formatDate(selectedDate)
                                }}</span>
                            </p>

                            <!-- Filtre par date -->
                            <input type="date" v-model="selectedDate" @change="onDateChange"
                                class="border rounded-md px-3 py-1 text-sm focus:ring-2 focus:ring-gray-500 focus:border-amber-500 bg-background shadow-sm h-9" />

                            <p class="text-sm font-semibold text-gray-700">
                                SITE :
                            </p>

                            <select v-model="selectedSite" @change="onSiteChange"
                                class="border rounded-md px-3 py-1 text-sm h-9">
                                <option value="">
                                    -- Tous les sites --
                                </option>
                                <option v-for="site in sites" :key="site.id" :value="String(site.id)">
                                    {{ site.nom_site }} —
                                    <span v-if="site.status === 'ouverte'">RAF Ouvert</span>
                                    <span v-else-if="site.status === 'fermee'">RAF Clôturé</span>
                                    <span v-else-if="site.status === 'partielle'">RAF Partiel</span>
                                    <span v-else-if="site.status === 'sans_caisse'">Aucune caisse</span>
                                    <span v-else>RAF Non ouvert</span>
                                </option>
                            </select>

                            <!-- Statut RAF du site sélectionné -->
                            <div v-if="selectedSite" class="flex items-center gap-2 px-3 py-1 rounded bg-gray-50">
                                <p class="text-sm font-semibold text-gray-700">
                                    Statut site :
                                </p>
                                <p :class="statusSiteClass" class="text-sm font-semibold">
                                    {{ statusSiteLabel }}
                                </p>
                            </div>

                            <p class="text-sm font-semibold text-gray-700">
                                CAISSE :
                            </p>

                            <select v-model="selectedCaisse" @change="onCaisseChange" :disabled="!selectedSite"
                                class="border rounded-md px-3 py-1 text-sm h-9 disabled:opacity-50 disabled:cursor-not-allowed">
                                <option value="">
                                    -- Toutes les caisses --
                                </option>
                                <option v-for="caisse in caisses" :key="caisse.id" :value="String(caisse.id)">
                                    {{ caisse.libelle }} ({{ caisse.code }}) —
                                    <span v-if="caisse.raf_status === 'ouverte'">RAF Ouvert</span>
                                    <span v-else-if="caisse.raf_status === 'cloturee'">RAF Clôturé</span>
                                    <span v-else>RAF Non ouvert</span>
                                </option>
                            </select>

                            <!-- Statut RAF de la caisse sélectionnée -->
                            <div v-if="selectedCaisse" class="flex items-center gap-2 px-3 py-1 rounded bg-gray-50">
                                <p class="text-sm font-semibold text-gray-700">
                                    Statut caisse :
                                </p>
                                <p :class="statusRafClass" class="text-sm font-semibold">
                                    {{ statusRafLabel }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-3 text-sm font-medium">
                            <!-- Le Trésorier dispose d'un accès en consultation uniquement -->
                            <span v-if="isTresorier"
                                class="px-3 py-1 rounded-md bg-slate-100 text-slate-600 text-xs font-semibold uppercase tracking-wide">
                                Consultation seule
                            </span>

                            <!-- Boutons Ouverture/Fermeture RAF du site -->
                            <AlertDialog v-model:open="isDialogOpen">
                                <AlertDialogTrigger as-child v-if="canOpen">
                                    <Button variant="default" :disabled="isButtonDisabled"
                                        class="transition-all duration-200" @click="pendingAction = 'open'">
                                        <span>Ouvrir session RAF</span>
                                        <ShieldCheck class="ml-2" />
                                    </Button>
                                </AlertDialogTrigger>

                                <AlertDialogTrigger as-child v-if="canClose">
                                    <Button variant="destructive" :disabled="isButtonDisabled"
                                        class="transition-all duration-200" @click="pendingAction = 'close'">
                                        <span>Fermer session RAF</span>
                                        <Shield class="ml-2" />
                                    </Button>
                                </AlertDialogTrigger>

                                <AlertDialogContent class="w-full max-w-[900px]">
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>
                                            {{ dialogTitle }}
                                        </AlertDialogTitle>

                                        <AlertDialogDescription>
                                            <div class="my-4 space-y-4">
                                                <p class="text-sm text-gray-600">
                                                    <template v-if="isActionFermeture">
                                                        Vous allez fermer la session RAF de <strong>toutes les
                                                            caisses ouvertes</strong> du site
                                                        <strong>{{ getSelectedSiteLabel }}</strong> pour le {{
                                                            formatDate(selectedDate) }}.
                                                    </template>
                                                    <template v-else>
                                                        Vous allez ouvrir la session RAF de <strong>toutes les
                                                            caisses</strong> du site
                                                        <strong>{{ getSelectedSiteLabel }}</strong> pour le {{
                                                            formatDate(selectedDate) }}.
                                                    </template>
                                                </p>

                                                <div class="border rounded-lg overflow-hidden">
                                                    <table class="w-full text-sm">
                                                        <thead class="bg-gray-50 text-[11px] uppercase text-gray-500">
                                                            <tr>
                                                                <th class="text-left px-3 py-2">Caisse</th>
                                                                <th class="text-right px-3 py-2">Ventes du jour</th>
                                                                <th class="text-left px-3 py-2">Statut RAF</th>
                                                                <th v-if="isActionFermeture" class="text-left px-3 py-2">
                                                                    Envoi X3</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr v-for="c in caisses" :key="c.id" class="border-t">
                                                                <td class="px-3 py-2">{{ c.libelle }} ({{ c.code }})</td>
                                                                <td class="px-3 py-2 text-right font-mono">
                                                                    {{ Number(c.total_paiements || 0).toLocaleString() }} F
                                                                </td>
                                                                <td class="px-3 py-2">
                                                                    <span v-if="c.raf_status === 'ouverte'"
                                                                        class="text-green-700 font-semibold">Ouverte</span>
                                                                    <span v-else-if="c.raf_status === 'cloturee'"
                                                                        class="text-blue-700 font-semibold">Clôturée</span>
                                                                    <span v-else class="text-orange-500">Non ouverte</span>
                                                                </td>
                                                                <td v-if="isActionFermeture" class="px-3 py-2">
                                                                    <span v-if="c.raf_status !== 'ouverte'"
                                                                        class="text-gray-400">—</span>
                                                                    <span v-else-if="Number(c.total_paiements || 0) > 0"
                                                                        class="text-green-700 font-semibold">
                                                                        Oui ({{ Number(c.total_paiements || 0).toLocaleString()
                                                                        }} F)
                                                                    </span>
                                                                    <span v-else class="text-gray-500 italic">Aucun montant</span>
                                                                </td>
                                                            </tr>
                                                            <tr v-if="!caisses.length">
                                                                <td colspan="4" class="px-3 py-4 text-center text-gray-400">
                                                                    Aucune caisse pour ce site.
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <p v-if="isActionFermeture" class="text-xs text-gray-500">
                                                    Les caisses sans montant seront clôturées sans envoi vers Sage X3.
                                                </p>
                                            </div>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <AlertDialogFooter class="flex flex-row gap-2">
                                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                                        <Button @click="handleValidate" v-if="!isActionFermeture"
                                            :disabled="loading"> Valider Ouverture RAF
                                            <span v-if="loading" class="ml-2 animate-spin">⏳</span> </Button>

                                        <Button v-else @click="showConfirmValidate = true" :disabled="loading">
                                            Valider Clôture RAF
                                            <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                        </Button>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>

                    <!-- RÉSUMÉ : caisse sélectionnée ou toutes les caisses -->
                    <p v-if="selectedCaisse || selectedSite || paiements.length"
                        class="mb-2 text-xs font-bold uppercase tracking-wide text-gray-500">
                        <template v-if="selectedCaisse">Caisse sélectionnée</template>
                        <template v-else-if="selectedSite">Toutes les caisses du site —
                            {{ getSelectedSiteLabel }}</template>
                        <template v-else>Toutes les caisses — tous les sites</template>
                    </p>

                    <div v-if="selectedCaisse || selectedSite || paiements.length"
                        class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                        <div class="flex flex-col bg-slate-800 p-3 rounded-xl shadow-lg shadow-slate-200">
                            <label class="text-xs font-bold text-slate-300 uppercase mb-1">
                                Ventes du jour
                            </label>
                            <div class="text-xl font-black text-white">
                                {{ totalMontantFormatted }}
                            </div>
                        </div>

                        <div class="flex flex-col bg-amber-500 p-3 rounded-xl shadow-lg shadow-amber-200">
                            <label class="text-xs font-bold text-amber-950/80 uppercase mb-1">
                                Fond de caisse
                            </label>
                            <div class="text-xl font-black text-amber-950">
                                {{ Number(caisseOuverture?.montant_ouverture || 0).toLocaleString() }} F
                            </div>
                        </div>

                        <div class="flex flex-col bg-green-600 p-3 rounded-xl shadow-lg shadow-green-100 text-white">
                            <label class="text-xs font-bold opacity-80 uppercase tracking-widest">
                                Montant Total Attendu
                            </label>
                            <div class="text-2xl font-black">
                                {{ totalAttendu.toLocaleString() }} F
                            </div>
                        </div>

                        <div v-if="perte > 0" class="flex flex-col p-3 rounded-xl shadow-sm border border-red-100 bg-red-50">
                            <label class="text-xs font-bold uppercase text-red-600">Perte (Manquant)</label>
                            <div class="text-xl font-black text-red-700">{{ perte.toLocaleString() }} F</div>
                        </div>
                        <div v-if="surplus > 0"
                            class="flex flex-col p-3 rounded-xl shadow-lg shadow-teal-100 bg-teal-700">
                            <label class="text-xs font-bold uppercase text-teal-100">Surplus (Excédent)</label>
                            <div class="text-xl font-black text-white">{{ surplus.toLocaleString() }} F</div>
                        </div>
                    </div>

                    <div v-if="commentaire"
                        class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-xl flex flex-col gap-1">
                        <div class="flex items-center gap-2 text-amber-800">
                            <i class="pi pi-comment text-sm"></i>
                            <span class="text-[10px] font-bold uppercase">Justification du Contrôleur</span>
                        </div>
                        <p class="text-sm italic text-amber-900 leading-relaxed">{{ commentaire }}</p>
                    </div>

                    <!-- CONTENT BOX: Dossier List -->
                    <DossierListTable class="mt-6" :paiements="paiements" :selectedDate="selectedDate"
                        :montantOuverture="Number(caisseOuverture?.montant_ouverture || 0)" />
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
                            <template v-if="isActionFermeture">
                                Êtes-vous sûr de vouloir fermer la session RAF de toutes les caisses ouvertes du site
                                <strong>{{ getSelectedSiteLabel }}</strong> ?
                                Les transactions seront déversées dans X3 uniquement pour les caisses dont le montant
                                est supérieur à 0.
                            </template>
                            <template v-else>
                                Êtes-vous sûr de vouloir ouvrir la session RAF de toutes les caisses du site
                                <strong>{{ getSelectedSiteLabel }}</strong> ?
                            </template>
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
import { ref, computed, onMounted, watch } from "vue";
import { usePage } from "@inertiajs/vue3";
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
import { Toaster, toast } from "vue-sonner";
import { Shield, ShieldCheck } from "lucide-vue-next";
import DossierListTable from "@/components/Caisse/DossierListTable.vue";

// ============================================================================
// REFS
// ============================================================================
const urlParams = new URLSearchParams(window.location.search);

const selectedDate = ref(urlParams.get("date") || new Date().toISOString().split("T")[0]);
const selectedSite = ref(urlParams.get("site_id") || "");
const selectedCaisse = ref(urlParams.get("caisse_id") || "");

const sites = ref([]);
const siteInfo = ref(null);
const paiements = ref([]);
const loading = ref(false);
const isDialogOpen = ref(false);
const showConfirmValidate = ref(false);
const caisseOuverture = ref(null);
const commentaire = ref("");
const perte = ref(0);
const surplus = ref(0);
const rafConfig = ref(0);
const sessionNonFermee = ref(null);

// ============================================================================
// URL SYNC
// ============================================================================
watch([selectedDate, selectedSite, selectedCaisse], () => {
    const url = new URL(window.location.href);
    if (selectedDate.value) {
        url.searchParams.set("date", selectedDate.value);
    }
    if (selectedSite.value) {
        url.searchParams.set("site_id", selectedSite.value);
    } else {
        url.searchParams.delete("site_id");
    }
    if (selectedCaisse.value) {
        url.searchParams.set("caisse_id", selectedCaisse.value);
    } else {
        url.searchParams.delete("caisse_id");
    }
    window.history.replaceState({}, '', url);
});

// ============================================================================
// COMPUTED
// ============================================================================
const caisses = computed(() => siteInfo.value?.caisses || []);

const openCaisses = computed(() => caisses.value.filter((c) => c.raf_status === "ouverte"));

// Action en cours dans le dialogue ('open' | 'close')
const pendingAction = ref("open");
const isActionFermeture = computed(() => pendingAction.value === "close");

// Le Trésorier partage l'interface RAF mais reste en lecture seule.
const isTresorier = computed(
    () => usePage().props.auth_user?.data?.r_user_role?.nom_role === "Trésorier"
);

// Caisses restant à ouvrir / encore ouvertes
const canOpen = computed(
    () => !isTresorier.value && caisses.value.some((c) => c.raf_status === "non_ouverte")
);
const canClose = computed(() => !isTresorier.value && openCaisses.value.length > 0);

const dialogTitle = computed(() => {
    return isActionFermeture.value ? "Fermer la session RAF du site" : "Ouvrir la session RAF du site";
});

const isButtonDisabled = computed(() => {
    if (!selectedSite.value) return true;

    // Si raf_config = 1, appliquer la contrainte J-5
    if (rafConfig.value === 1) {
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selected = new Date(selectedDate.value + 'T00:00:00');
        const fiveDaysAgo = new Date(today);
        fiveDaysAgo.setDate(fiveDaysAgo.getDate() - 5);

        if (selected < fiveDaysAgo || selected > today) return true;
    }

    return false;
});

const statusSiteLabel = computed(() => {
    const s = siteInfo.value?.status;
    if (s === "ouverte") return "Ouvert";
    if (s === "fermee") return "Clôturé";
    if (s === "partielle") return "Partiel";
    if (s === "sans_caisse") return "Aucune caisse";
    return "Non ouvert";
});

const statusSiteClass = computed(() => {
    const s = siteInfo.value?.status;
    if (s === "ouverte") return "text-green-600 font-bold italic";
    if (s === "fermee") return "text-blue-600 font-bold italic";
    if (s === "partielle") return "text-purple-600 font-bold italic";
    return "text-orange-500 italic";
});

const statusRafLabel = computed(() => {
    const s = caisses.value.find((c) => String(c.id) === String(selectedCaisse.value))?.raf_status;
    if (s === "ouverte") return "Ouverte";
    if (s === "cloturee") return "Clôturée";
    if (s === "non_ouverte") return "Non ouverte";
    return "Indéterminé";
});

const statusRafClass = computed(() => {
    const label = statusRafLabel.value;
    if (label === "Ouverte") return "text-green-600 font-bold italic";
    if (label === "Clôturée") return "text-blue-600 font-bold italic";
    return "text-orange-500 italic";
});

const getSelectedSiteLabel = computed(() => {
    const s = sites.value.find((x) => String(x.id) === String(selectedSite.value));
    return s ? s.nom_site : "ce site";
});

const hasSessionRafPrecedenteNonFermee = computed(() => {
    if (!sessionNonFermee.value) return false;
    return String(sessionNonFermee.value.date).slice(0, 10) !== selectedDate.value;
});

const getCaisseNonFermeeLabel = computed(() => {
    if (!sessionNonFermee.value) return "";
    const c = caisses.value.find(
        (x) => String(x.id) === String(sessionNonFermee.value.caisse_id)
    );
    return c ? `${c.libelle} (${c.code})` : "une autre caisse";
});

// ============================================================================
// DETAILS & TOTALS
// ============================================================================
const totalMontant = computed(() => {
    return paiements.value.reduce((s, p) => s + Number(p?.montant || 0), 0);
});

const totalMontantFormatted = computed(() => {
    return totalMontant.value.toLocaleString() + " F";
});

const totalAttendu = computed(() => {
    return totalMontant.value + Number(caisseOuverture.value?.montant_ouverture || 0);
});

// ============================================================================
// DATA FETCHING
// ============================================================================
const fetchSites = async () => {
    try {
        const { data } = await axios.get("/raf/site/liste", {
            params: { date: selectedDate.value },
        });

        sites.value = Array.isArray(data?.sites) ? data.sites : [];

        if (data?.raf_config !== undefined) {
            rafConfig.value = data.raf_config;
        }

        // Site sélectionné devenu invalide
        if (
            selectedSite.value &&
            !sites.value.some((s) => String(s.id) === String(selectedSite.value))
        ) {
            selectedSite.value = "";
            siteInfo.value = null;
        }
    } catch (error) {
        console.error("Erreur lors du chargement des sites :", error);
        toast.error("Impossible de charger les sites.");
    }
};

const fetchSiteInfo = async () => {
    if (!selectedSite.value) {
        siteInfo.value = null;
        sessionNonFermee.value = null;
        return;
    }

    try {
        const { data } = await axios.get("/raf/site/info", {
            params: { site_id: selectedSite.value, date: selectedDate.value },
        });

        siteInfo.value = data;
        sessionNonFermee.value = data?.session_non_fermee || null;

        if (data?.raf_config !== undefined) {
            rafConfig.value = data.raf_config;
        }

        // La caisse sélectionnée n'appartient plus au site : on la retire
        const stillExists = (data?.caisses || []).some(
            (c) => String(c.id) === String(selectedCaisse.value)
        );
        if (!stillExists) {
            selectedCaisse.value = "";
        }
    } catch (error) {
        console.error("Erreur lors du chargement du site :", error);
        toast.error("Impossible de charger les informations du site.");
    }
};

const fetchCaisseInfo = async () => {
    try {
        const params = { date: selectedDate.value };

        if (selectedCaisse.value) {
            params.caisse_id = selectedCaisse.value;
        } else if (selectedSite.value) {
            params.site_id = selectedSite.value;
        }

        const { data } = await axios.get("/raf/caisse/info", { params });

        caisseOuverture.value = data.caisse_ouverture || null;
        perte.value = Number(data.caisse_ouverture?.perte || 0);
        surplus.value = Number(data.caisse_ouverture?.surplus || 0);
        commentaire.value = data.caisse_ouverture?.commentaire || "";
    } catch (error) {
        console.error("Erreur RAF info caisse :", error);
    }
};

const fetchPaiements = async () => {
    try {
        const params = { date: selectedDate.value };

        if (selectedCaisse.value) {
            params.caisse_id = selectedCaisse.value;
        } else if (selectedSite.value) {
            params.site_id = selectedSite.value;
        }

        const { data } = await axios.get("/raf/paiement/data/stat", { params });

        paiements.value = Array.isArray(data) ? data : [];
        await fetchCaisseInfo();
    } catch (error) {
        console.error("Erreur paiements :", error);
        toast.error("Impossible de charger les paiements.");
    }
};

const onSiteChange = async () => {
    selectedCaisse.value = "";
    paiements.value = [];
    caisseOuverture.value = null;
    perte.value = 0;
    surplus.value = 0;
    commentaire.value = "";
    await fetchSiteInfo();
    await fetchPaiements();
};

const onCaisseChange = async () => {
    await fetchPaiements();
};

const onDateChange = async () => {
    await fetchSites();
    await fetchSiteInfo();
    await fetchPaiements();
};

// ============================================================================
// VALIDATION
// ============================================================================
const handleValidate = async () => {
    if (!selectedSite.value) {
        toast.error("Veuillez sélectionner un site.");
        return;
    }

    try {
        loading.value = true;

        const res = await axios.post("/raf/site/controller/validate", {
            site_id: Number(selectedSite.value),
            is_fermeture: isActionFermeture.value ? 1 : 0,
            date_operation: selectedDate.value,
        });

        toast.success(res.data?.message || "Opération RAF réussie");
        isDialogOpen.value = false;

        await fetchSites();
        await fetchSiteInfo();
        await fetchPaiements();
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
    const [y, m, d] = String(dateStr).slice(0, 10).split("-");
    const months = ["Janv", "Févr", "Mars", "Avril", "Mai", "Juin", "Juil", "Août", "Sept", "Oct", "Nov", "Déc"];
    return `${d} ${months[parseInt(m) - 1]} ${y}`;
};

onMounted(async () => {
    await fetchSites();
    if (selectedSite.value) {
        await fetchSiteInfo();
    }
    await fetchPaiements();
});
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";
export default { layout: Main };
</script>
