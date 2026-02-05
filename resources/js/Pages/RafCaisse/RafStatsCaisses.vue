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
                            <!-- <button class="flex items-center space-x-1 text-gray-700 hover:text-black">
                                <i class="pi pi-print"></i>
                                <span>Déverser dans X3</span>
                            </button>
                            <button class="flex items-center space-x-1 text-gray-700 hover:text-black">
                                <i class="pi pi-upload"></i>
                                <span>Exporter</span>
                            </button> -->

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

                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>
                                            {{ dialogTitle }}
                                        </AlertDialogTitle>

                                        <AlertDialogDescription>
                                            <div class="flex items-center space-x-6 my-8" v-if="isActionFermeture">
                                                <!-- Montant à verser -->
                                                <div class="flex flex-col w-1/2">
                                                    <label class="text-sm font-semibold text-gray-700 mb-1">
                                                        Montant total à valider
                                                    </label>
                                                    <Input class="my-2 text-xl font-bold" :placeholder="totalMontantFormatted
                                                        " disabled />
                                                </div>

                                                <!-- Montant RAF reçu -->
                                                <div class="flex flex-col w-1/2">
                                                    <label class="text-sm font-semibold text-gray-700 mb-1">
                                                        Montant RAF validé
                                                    </label>
                                                    <Input v-model="montantRecu" class="my-2" placeholder="1 000 000"
                                                        inputmode="numeric" />
                                                </div>
                                            </div>

                                            <div v-else class="my-4 text-center">
                                                <p class="text-sm text-gray-600">
                                                    Vous allez ouvrir la session
                                                    RAF pour
                                                    <strong>{{
                                                        getSelectedCaisseLabel
                                                    }}</strong>
                                                    le
                                                    {{
                                                        formatDate(selectedDate)
                                                    }}
                                                </p>
                                            </div>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <AlertDialogFooter class="flex flex-row gap-2">
                                        <AlertDialogCancel>Annuler</AlertDialogCancel>
                                        <Button @click="handleValidate" v-if="!isActionFermeture"
                                            :disabled="loading || (isActionFermeture && !montantRecu)"> Valider RAF
                                            <span v-if="loading" class="ml-2 animate-spin">⏳</span> </Button>

                                        <Button v-else @click="showConfirmValidate = true" :disabled="loading ||
                                            (isActionFermeture &&
                                                !montantRecu)
                                            ">
                                            Valider RAF
                                            <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                        </Button>
                                    </AlertDialogFooter>
                                </AlertDialogContent>
                            </AlertDialog>
                        </div>
                    </div>

                    <!-- CONTENT BOX -->
                    <div class="bg-white shadow rounded-xl p-4">
                        <div class="grid grid-cols-4 divide-x divide-gray-200">
                            <!-- EMUCI -->
                            <div class="p-4">
                                <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                                    {{ totals.emuci.toLocaleString() }} F
                                </h2>
                                <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                                    Total EMUCI
                                </p>

                                <div class="space-y-3">
                                    <div v-for="(item, i) in details.emuci" :key="i"
                                        class="border rounded-md p-2 hover:shadow transition">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ item.element_facturation }}
                                        </p>
                                        <p class="text-amber-600 font-semibold">
                                            {{
                                                Number(
                                                    item.montant,
                                                ).toLocaleString()
                                            }}
                                            F
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- QUIPUX -->
                            <div class="p-4">
                                <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                                    {{ totals.quipux.toLocaleString() }} F
                                </h2>
                                <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                                    Total QUIPUX
                                </p>

                                <div class="space-y-3">
                                    <div v-for="(item, i) in details.quipux" :key="i"
                                        class="border rounded-md p-2 hover:shadow transition">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ item.element_facturation }}
                                        </p>
                                        <p class="text-amber-600 font-semibold">
                                            {{
                                                Number(
                                                    item.montant,
                                                ).toLocaleString()
                                            }}
                                            F
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- DGTT -->
                            <div class="p-4">
                                <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                                    {{ totals.dgtt.toLocaleString() }} F
                                </h2>
                                <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                                    Total DGTT
                                </p>

                                <div class="space-y-3">
                                    <div v-for="(item, i) in details.dgtt" :key="i"
                                        class="border rounded-md p-2 hover:shadow transition">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ item.element_facturation }}
                                        </p>
                                        <p class="text-amber-600 font-semibold">
                                            {{
                                                Number(
                                                    item.montant,
                                                ).toLocaleString()
                                            }}
                                            F
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- MINISTÈRE -->
                            <div class="p-4">
                                <h2 class="text-gray-800 text-xl font-extrabold mb-2 text-center">
                                    {{ totals.ministere.toLocaleString() }} F
                                </h2>
                                <p class="text-gray-500 text-xs text-center uppercase tracking-wide mb-4">
                                    Total MINISTÈRE
                                </p>

                                <div class="space-y-3">
                                    <div v-for="(item, i) in details.ministere" :key="i"
                                        class="border rounded-md p-2 hover:shadow transition">
                                        <p class="text-sm font-medium text-gray-800">
                                            {{ item.element_facturation }}
                                        </p>
                                        <p class="text-amber-600 font-semibold">
                                            {{
                                                Number(
                                                    item.montant,
                                                ).toLocaleString()
                                            }}
                                            F
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
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
import { Shield, ShieldCheck } from "lucide-vue-next";

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

// ============================================================================
// DETAILS & TOTALS
// ============================================================================
const details = computed(() => {
    const result = { emuci: [], quipux: [], dgtt: [], ministere: [] };
    paiements.value.forEach((p) => {
        if (!p || !p.description) return;
        try {
            const lignes =
                typeof p.description === "string"
                    ? JSON.parse(p.description)
                    : p.description;
            lignes.forEach((item) => {
                switch (item.id_entite) {
                    case 1: result.emuci.push(item); break;
                    case 2: result.quipux.push(item); break;
                    case 3: result.dgtt.push(item); break;
                    default: result.ministere.push(item);
                }
            });
        } catch (e) {
            console.warn("Description JSON invalide pour paiement id:", p.id);
        }
    });
    return result;
});

const totals = computed(() => ({
    emuci: details.value.emuci.reduce((s, i) => s + Number(i.montant || 0), 0),
    quipux: details.value.quipux.reduce((s, i) => s + Number(i.montant || 0), 0),
    dgtt: details.value.dgtt.reduce((s, i) => s + Number(i.montant || 0), 0),
    ministere: details.value.ministere.reduce((s, i) => s + Number(i.montant || 0), 0),
}));

const totalMontant = computed(
    () =>
        totals.value.emuci +
        totals.value.quipux +
        totals.value.dgtt +
        totals.value.ministere,
);

const totalMontantFormatted = computed(
    () => `${totalMontant.value.toLocaleString()} F CFA`,
);

// ============================================================================
// DATES
// ============================================================================
const today = computed(() => new Date().toISOString().split("T")[0]);
const yesterday = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() - 1);
    return date.toISOString().split("T")[0];
});

const isToday = computed(() => selectedDate.value === today.value);
const isYesterday = computed(() => selectedDate.value === yesterday.value);

// 🔥 Permettre la fermeture RAF jusqu’à 5 jours en arrière
const isWithin5Days = computed(() => {
    const d = new Date(selectedDate.value);
    const todayDate = new Date();

    const diffTime = todayDate - d;
    const diffDays = diffTime / (1000 * 60 * 60 * 24);

    return diffDays >= 0 && diffDays <= 5;
});

// ============================================================================
// RAF STATUS
// ============================================================================
const selectedCaisseRafInfo = computed(() => {
    if (!selectedCaisse.value) return null;
    return caisseRafInfo.value[selectedCaisse.value] || null;
});

const ouvertureRafDuJour = computed(() => {
    return selectedCaisseRafInfo.value?.ouverture_raf_du_jour || null;
});

const hasSessionRafPrecedenteNonFermee = computed(() => {
    if (!ouvertureRafNonFermee.value) return false;
    if (!selectedCaisse.value) return false;

    if (String(ouvertureRafNonFermee.value.caisse_id) !== String(selectedCaisse.value))
        return false;

    if (!ouvertureRafDuJour.value) return true;

    return ouvertureRafNonFermee.value.id !== ouvertureRafDuJour.value.id;
});

const isRafOuverte = computed(() => {
    return ouvertureRafDuJour.value?.status_raf === 1;
});

const isRafFermeeAujourdhui = computed(() => {
    return ouvertureRafDuJour.value && ouvertureRafDuJour.value.status_raf !== 1;
});

const isActionFermeture = computed(() => isRafOuverte.value);

// ============================================================================
// BUTTON LOGIC
// ============================================================================
const shouldShowActionButton = computed(() => {
    if (!selectedCaisse.value) return false;

    // 🔥 Nouveau comportement : fermeture possible jusqu’à 5 jours en arrière
    if (isActionFermeture.value) {
        return isWithin5Days.value;
    }

    // Ouverture = uniquement pour aujourd'hui
    return isToday.value;
});

const isButtonDisabled = computed(() => {
    if (!selectedCaisse.value) return true;

    if (isRafFermeeAujourdhui.value) return true;

    if (!isActionFermeture.value && hasSessionRafPrecedenteNonFermee.value)
        return true;

    // 🔥 interdire fermeture si plus vieux que 5 jours
    if (isActionFermeture.value && !isWithin5Days.value) return true;

    return false;
});

const buttonLabel = computed(() => {
    if (isButtonDisabled.value) {
        if (isRafFermeeAujourdhui.value) return "RAF DÉJÀ CLÔTURÉ";
        if (hasSessionRafPrecedenteNonFermee.value) return "SESSION PRÉCÉDENTE OUVERTE";
    }
    return isActionFermeture.value ? "CLÔTURER RAF" : "OUVRIR RAF";
});

const buttonVariant = computed(() => {
    if (isButtonDisabled.value) return "secondary";
    return isActionFermeture.value ? "destructive" : "default";
});

const dialogTitle = computed(() => {
    const action = isActionFermeture.value ? "Clôture RAF" : "Ouverture RAF";
    const caisseLabel = getSelectedCaisseLabel.value;
    return `${action} - ${caisseLabel} - ${formatDate(selectedDate.value)}`;
});

// ============================================================================
// LABELS
// ============================================================================
const statusRafLabel = computed(() => {
    if (!selectedCaisse.value) return "Aucune caisse sélectionnée";
    if (isRafOuverte.value) return "Ouverte";
    if (isRafFermeeAujourdhui.value) return "Fermée";
    return "Non ouverte";
});

const statusRafClass = computed(() => {
    if (isRafOuverte.value) return "text-blue-600";
    if (isRafFermeeAujourdhui.value) return "text-gray-600";
    return "text-red-600";
});

const getSelectedCaisseLabel = computed(() => {
    const caisse = caisses.value.find(
        (c) => String(c.id) === String(selectedCaisse.value),
    );
    return caisse ? `${caisse.libelle} (${caisse.code})` : "Caisse sélectionnée";
});

const getCaisseNonFermeeLabel = computed(() => {
    if (!ouvertureRafNonFermee.value) return "";
    const caisse = caisses.value.find(
        (c) => c.id === ouvertureRafNonFermee.value.caisse_id,
    );
    return caisse ? `${caisse.libelle} (${caisse.code})` : "la caisse";
});



// ============================================================================
// RAF
// ============================================================================

//   Formater une date au format FR
const formatDate = (dateStr) => {
    if (!dateStr) return "";
    return new Date(dateStr + "T00:00:00").toLocaleDateString("fr-FR");
};

//   Récupérer la liste des caisses liées au RAF
const fetchCaisses = async () => {
    try {
        const { data } = await axios.get("/raf/caisse/liste");

        if (!Array.isArray(data)) {
            caisses.value = [];
            return;
        }

        caisses.value = data.map((c) => ({
            ...c,
            id: c.id,
        }));

        // Charger les infos RAF pour chaque caisse
        await fetchAllCaissesRafInfo();
    } catch (error) {
        console.error("Erreur lors du chargement des caisses :", error);
        toast.error("Impossible de charger les caisses.");
    }
};

//   Récupérer les infos RAF pour toutes les caisses
const fetchAllCaissesRafInfo = async () => {
    for (const caisse of caisses.value) {
        await fetchRafInfoForCaisse(caisse.id);
    }

    // Mettre à jour les propriétés des caisses pour l'affichage
    caisses.value = caisses.value.map((caisse) => {
        const info = caisseRafInfo.value[caisse.id];
        return {
            ...caisse,
            raf_ouvert: info?.ouverture_raf_du_jour?.status_raf === 1,
            raf_ferme_aujourdhui:
                info?.ouverture_raf_du_jour &&
                info?.ouverture_raf_du_jour?.status_raf !== 1,
        };
    });
};

//   Récupérer les infos RAF pour une caisse spécifique
const fetchRafInfoForCaisse = async (caisseId) => {
    try {
        const { data } = await axios.get("/raf/caisse/info", {
            params: {
                date: selectedDate.value,
                caisse_id: caisseId,
            },
        });

        // Stocker les infos pour cette caisse
        caisseRafInfo.value[caisseId] = {
            ouverture_raf_du_jour: data.ouverture_raf_du_jour || null,
            ouverture_raf_non_fermee: data.ouverture_raf_non_fermee || null,
        };

        // Si cette caisse a une session non fermée, la stocker globalement
        if (data.ouverture_raf_non_fermee) {
            ouvertureRafNonFermee.value = data.ouverture_raf_non_fermee;
        }
    } catch (error) {
        console.error(
            `Erreur lors du chargement des infos RAF pour caisse ${caisseId} :`,
            error,
        );
    }
};

//   Récupérer les paiements pour la caisse sélectionnée
const fetchPaiements = async () => {
    // if (!selectedCaisse.value) {
    //     paiements.value = [];
    //     return;
    // }

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
        console.error("Erreur lors du chargement des paiements :", error);
        toast.error("Impossible de charger les paiements.");
    }
};

//   Handler when user changes caisse select
const onCaisseChange = async () => {
    await fetchPaiements();
};

//  * Validation de l'ouverture/fermeture RAF
const handleValidate = async () => {
    if (!selectedCaisse.value) {
        toast.error("Veuillez sélectionner une caisse.");
        return;
    }

    // Convertir correctement le montant reçu en nombre
    const parseMoney = (v) =>
        Number(String(v).replace(/\s+/g, "").replace(/,/g, "."));

    // Si fermeture → contrôler montant reçu obligatoire
    if (isActionFermeture.value && !montantRecu.value) {
        toast.error("Veuillez saisir le montant RAF validé.");
        return;
    }

    // Comparaison des montants : convertir en nombre
    if (isActionFermeture.value) {
        const montantRecuNum = parseMoney(montantRecu.value);
        const totalMontantNum = Number(totalMontant.value);

        if (montantRecuNum !== totalMontantNum) {
            toast.error(
                "Le montant reçu doit correspondre au montant total des paiements."
            );
            return;
        }

        montantCaisse.value = montantRecuNum;
    }

    // Récupérer info de la caisse sélectionnée
    const selectedInfo = caisses.value.find(
        (c) => String(c.id) === String(selectedCaisse.value)
    );

    // Payload envoyé à l’API
    const payload = {
        montant_controlleur: isActionFermeture.value
            ? parseMoney(montantRecu.value)
            : 0,

        id_caisse: Number(selectedCaisse.value),
        code_caisse: selectedInfo?.code ?? "",
        id_site: selectedInfo?.site_id ?? null,
        is_fermeture: isActionFermeture.value ? 1 : 0,

        // IMPORTANT : tu l’envoies toujours !
        date_operation: selectedDate.value,
    };

    try {
        loading.value = true;

        const res = await axios.post(
            "/raf/caisse/controller/validate",
            payload
        );

        toast.success(res.data?.message || "Opération RAF réussie");

        // Fermer la modal
        isDialogOpen.value = false;

        // Rafraîchir données
        await fetchCaisses();
        await fetchPaiements();

        // Reset montant reçu si fermeture
        if (isActionFermeture.value) {
            montantRecu.value = "";
        }

    } catch (error) {
        console.error("Erreur lors de la validation RAF :", error);

        const msg =
            error?.response?.data?.message ||
            (error?.response?.data?.errors
                ? formatErrors(error.response.data.errors)
                : "Une erreur est survenue lors de la validation RAF.");

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
    await fetchCaisses();
    await fetchPaiements();
});
</script>

<script>
import Main from "/resources/js/Pages/Main.vue";

export default {
    layout: Main,
};
</script>
