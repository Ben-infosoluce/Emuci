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
                            <!-- <button class="flex items-center space-x-1 text-gray-700 hover:text-black">
                                <i class="pi pi-print"></i>
                                <span>Imprimer</span>
                            </button>
                            <button class="flex items-center space-x-1 text-gray-700 hover:text-black">
                                <i class="pi pi-upload"></i>
                                <span>Exporter</span>
                            </button> -->

                            <AlertDialog v-model:open="isDialogOpen">
                                <AlertDialogTrigger as-child v-if="shouldShowActionButton">
                                    <Button :variant="buttonVariant" :disabled="isButtonDisabled"
                                        class="transition-all duration-200">
                                        <span>{{ buttonLabel }}</span>
                                        <LockKeyhole v-if="isActionFermeture" class="ml-2" />
                                        <LockOpen v-else class="ml-2" />
                                    </Button>
                                </AlertDialogTrigger>

                                <AlertDialogContent>
                                    <AlertDialogHeader>
                                        <AlertDialogTitle>
                                            {{ dialogTitle }}
                                        </AlertDialogTitle>

                                        <AlertDialogDescription>
                                            <div class="flex items-center space-x-6 my-8" v-if="isActionFermeture">
                                                <div class="flex flex-col w-1/2">
                                                    <label class="text-sm font-semibold text-gray-700 mb-1">
                                                        Montant à verser
                                                    </label>
                                                    <Input class="my-2 text-xl font-bold text-green-600" :placeholder="totalMontantFormatted
                                                        " disabled />
                                                </div>

                                                <div class="flex flex-col w-1/2">
                                                    <label class="text-sm font-semibold text-gray-700 mb-1">
                                                        Montant reçu
                                                    </label>
                                                    <Input v-model="montantRecu" class="my-2" placeholder="1 000 000"
                                                        inputmode="numeric" />
                                                </div>
                                            </div>

                                            <div v-else class="my-4 text-center">
                                                <p class="text-sm text-gray-600">
                                                    Vous allez ouvrir la caisse
                                                    pour le
                                                    {{
                                                        formatDate(selectedDate)
                                                    }}
                                                </p>
                                            </div>
                                        </AlertDialogDescription>
                                    </AlertDialogHeader>

                                    <AlertDialogFooter class="flex flex-row gap-2">
                                        <AlertDialogCancel>Annuler</AlertDialogCancel>

                                        <Button @click="handleValidate"
                                            class="bg-green-800 p-4 rounded hover:bg-gray-900 transition duration-300"
                                            :disabled="loading ||
                                                (isActionFermeture &&
                                                    !montantRecu)
                                                ">
                                            Valider
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
                    case 1:
                        result.emuci.push(item);
                        break;
                    case 2:
                        result.quipux.push(item);
                        break;
                    case 3:
                        result.dgtt.push(item);
                        break;
                    default:
                        result.ministere.push(item);
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
    quipux: details.value.quipux.reduce(
        (s, i) => s + Number(i.montant || 0),
        0,
    ),
    dgtt: details.value.dgtt.reduce((s, i) => s + Number(i.montant || 0), 0),
    ministere: details.value.ministere.reduce(
        (s, i) => s + Number(i.montant || 0),
        0,
    ),
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
        selectedCaisse.value = data.caisse?.id || "";
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
    if (
        isActionFermeture.value &&
        Number(montantCaisse.value) !== Number(totalMontant.value)
    ) {
        console.log(
            "Montant caisse:", Number(montantCaisse.value),
            "Total montant:", Number(totalMontant.value)
        );

        toast.error("Le montant reçu doit correspondre au montant total des paiements.");
        return;
    }


    const selectedInfo = caisse.value;

    const payload = {
        montant_caisse: isActionFermeture.value ? montantCaisse.value : 0,
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
    };

    try {
        loading.value = true;
        const res = await axios.post("/verification/validate/caisse/controller/montant", payload);
        // console.log("Réponse validation:", res);

        toast.success(res.data?.message || "Opération réussie");
        isDialogOpen.value = false;

        await fetchCaisse();
        await fetchPaiements();

        if (isActionFermeture.value) montantRecu.value = "";
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
