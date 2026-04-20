<template>
    <div class="flex flex-col h-screen dark:bg-gray-800 bg-[#f1f5f9]">
        <!-- Full-width white header -->
        <Toaster position="top-right" />
        <header class="bg-white shadow-md w-full z-50">
            <div class="flex mx-auto px-4 lg:py-4 md:py-4 sm:py-4 py-3 text-start justify-between">
                <div>
                    <Link :href="route('home')">
                    <img src="/public/assets/images/logo_frame.svg" alt="" />
                    </Link>
                    <!-- <h1 class="text-xl font-semibold text-gray-800 ">Your App Name</h1> -->
                </div>
                <div v-if="isOpen && $page.component.startsWith('Caisse/')" class="text-center">
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button variant="destructive">
                                ARRETER LA CAISSE
                                <LockKeyhole />
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent class="sm:max-w-[600px]">
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    Solde du jour</AlertDialogTitle>
                            <AlertDialogDescription>
                                <!-- Billetterie Module Styled -->
                                <div class="mt-4">
                                    <label class="font-bold text-gray-800 text-xs uppercase tracking-tight flex items-center gap-2 mb-3">
                                        <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                        Comptage physique des fonds
                                    </label>
                                    
                                    <div class="bg-gray-50 border rounded-xl overflow-hidden shadow-inner">
                                        <div class="grid grid-cols-2 bg-white/50 border-b text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                            <div class="px-4 py-2 border-r">Coupure</div>
                                            <div class="px-4 py-2">Quantité</div>
                                        </div>
                                        <div class="max-h-[300px] overflow-y-auto px-1 custom-scrollbar">
                                            <div v-for="item in billetterie" :key="item.valeur"
                                                class="grid grid-cols-2 items-center border-b border-gray-100 last:border-0 hover:bg-white transition-colors py-1 pl-3 pr-2">
                                                <div class="flex items-center justify-between pr-4 border-r border-gray-100">
                                                    <span class="text-sm font-black text-gray-700">{{ item.valeur.toLocaleString('fr-FR') }}</span>
                                                    <span class="text-[10px] text-gray-400 font-medium font-mono">x</span>
                                                </div>
                                                <div class="flex items-center gap-3 pl-3">
                                                    <Input type="number" v-model.number="item.quantite" min="0"
                                                        :disabled="isBilletterieValidated"
                                                        placeholder="0"
                                                        class="h-8 w-full text-right bg-white border-gray-200 focus:ring-indigo-500 font-mono text-sm pr-2 disabled:opacity-50"
                                                        @input="isBilletterieValidated = false" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6 p-4 bg-indigo-50 rounded-xl border border-indigo-100 shadow-sm">
                                    <div class="flex justify-between items-center mb-3">
                                        <span class="text-sm font-bold text-indigo-900">Total Billetterie :</span>
                                        <span class="text-lg font-black text-indigo-700">{{
                                            totalBilletterie.toLocaleString('fr-FR') }} FCFA</span>
                                    </div>
                                    <Button v-if="!isBilletterieValidated" @click="validateBilletterie"
                                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white h-9 text-sm">
                                        Valider le montant
                                    </Button>
                                    <div v-else
                                        class="flex items-center justify-center gap-2 text-green-600 font-bold bg-white p-2 rounded-lg border border-green-100 text-sm">
                                        <Check class="h-4 w-4" /> Montant physique confirmé
                                    </div>
                                </div>
                            </AlertDialogDescription>
                            </AlertDialogHeader>
                                <AlertDialogFooter class="flex flex-row items-center justify-end gap-3 mt-4">
                                    <template v-if="!confirmingClose">
                                        <AlertDialogCancel @click="resetBilletterie"> Annuler </AlertDialogCancel>
                                        <Button v-if="isOpen" @click="confirmingClose = true"
                                            :disabled="!isBilletterieValidated"
                                            class="bg-red-800 p-4 rounded hover:bg-red-900 transition duration-300 disabled:opacity-50 disabled:cursor-not-allowed">
                                            Arrêter la caisse
                                            <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                        </Button>
                                    </template>
                                    <template v-else>
                                        <div
                                            class="flex items-center gap-4 bg-red-50 p-2 rounded-lg border border-red-200 animate-in fade-in zoom-in duration-200">
                                            <span class="text-xs font-bold text-red-700 font-sans">Confirmer
                                                l'arrêt ?</span>
                                            <div class="flex gap-2">
                                                <Button size="sm" variant="outline" @click="confirmingClose = false"
                                                    class="h-8 px-3 text-xs">
                                                    Non
                                                </Button>
                                                <Button size="sm" variant="destructive" @click="handleClose"
                                                    class="h-8 px-3 text-xs bg-red-600 hover:bg-red-700">
                                                    Oui, Arrêter
                                                </Button>
                                            </div>
                                        </div>
                                    </template>
                                </AlertDialogFooter>
                            <!-- <p v-if="error" class="text-red-600 mt-2 w-full">
                                ⚠️ {{ error }}
                            </p> -->
                        </AlertDialogContent>
                    </AlertDialog>
                </div>

                <UserAccountNav :showSidebarToggle="true" />
            </div>
        </header>

        <div class="flex flex-1 overflow-hidden">
            <!-- Sidebar toggle button for mobile -->

            <!-- Sidebar -->
            <aside id="sidebar-multi-level-sidebar"
                class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full sm:translate-x-0 mt-16"
                aria-label="Sidebar">
                <Menu />
            </aside>

            <!-- Main content -->
            <div class="flex-1 p-4 sm:ml-64 mt-8 overflow-auto">
                <slot></slot>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Avatar, AvatarFallback, AvatarImage } from "@/components/ui/avatar";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
    DropdownMenuGroup,
} from "@/components/ui/dropdown-menu";
import { LogOut, Settings, User, LockKeyhole, X, Check } from "lucide-vue-next";
import UserAccountNav from "@/components/Caisse/UserAccountNav.vue";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import Menu from "./Menu.vue";
import axios from "axios";
// import { LockKeyhole } from "lucide-vue-next";
import { usePage } from "@inertiajs/vue3";
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
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from "@/components/ui/dialog";
import { Toaster, toast } from "vue-sonner";

import { useCaisseStore } from "@/stores/mainStore";
import { storeToRefs } from "pinia";
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
// import Input from "@/components/ui/input/Input.vue";

const store = useCaisseStore();
const { isOpen, error, loading } = storeToRefs(store);
const { close } = store;
const fondDeCaisse = ref("");
const confirmingClose = ref(false);
const paiements = ref([]);
const currentCaisse = ref(null);

const billetterie = ref([
    { valeur: 10000, quantite: null },
    { valeur: 5000, quantite: null },
    { valeur: 2000, quantite: null },
    { valeur: 1000, quantite: null },
    { valeur: 500, quantite: null },
    { valeur: 250, quantite: null },
    { valeur: 200, quantite: null },
    { valeur: 100, quantite: null },
    { valeur: 50, quantite: null },
    { valeur: 25, quantite: null },
    { valeur: 10, quantite: null },
    { valeur: 5, quantite: null },
]);

const totalBilletterie = computed(() => {
    return billetterie.value.reduce((total, item) => {
        return total + item.valeur * (item.quantite || 0);
    }, 0);
});

const isBilletterieValidated = ref(false);

const resetBilletterie = () => {
    billetterie.value.forEach(item => item.quantite = null);
    isBilletterieValidated.value = false;
    confirmingClose.value = false;
};

const validateBilletterie = () => {
    isBilletterieValidated.value = true;
};

const handleClose = async () => {
    fondDeCaisse.value = totalBilletterie.value;
    await handleValidate();
};

// 🔹 Charger la liste des caisses
const fetchCaisses = async () => {
    const data = await store.fetchCurrent();
    currentCaisse.value = data;
};

// 🔹 Charger les paiements
const fetchPaiements = async () => {
    try {
        const { data } = await axios.get("/paiement/data/stat", {
            params: {
                date: new Date().toISOString().split("T")[0],
                caisse_id: currentCaisse.value?.caisse_id,
            },
        });
        paiements.value = data;
        // console.log("paiements", paiements.value);
    } catch (error) {
        console.error("Erreur lors du chargement des paiements :", error);
    }
};

onMounted(() => {
    fetchPaiements();
    fetchCaisses();
});
// onMounted(fetchPaiements)

// 🔹 Regrouper les données par entité
const details = computed(() => {
    const result = { emuci: [], quipux: [], dgtt: [], ministere: [] };

    paiements.value.forEach((p) => {
        if (p.description) {
            const lignes = JSON.parse(p.description);
            lignes.forEach((item) => {
                switch (item.id_entite) {
                    case 2:
                        result.emuci.push(item);
                        break;
                    case 3:
                        result.quipux.push(item);
                        break;
                    case 4:
                        result.dgtt.push(item);
                        break;
                    default:
                        result.ministere.push(item);
                }
            });
        }
    });
    return result;
});

// 🔹 Totaux
const totals = computed(() => ({
    emuci: details.value.emuci.reduce((s, i) => s + Number(i.montant), 0),
    quipux: details.value.quipux.reduce((s, i) => s + Number(i.montant), 0),
    dgtt: details.value.dgtt.reduce((s, i) => s + Number(i.montant), 0),
    ministere: details.value.ministere.reduce(
        (s, i) => s + Number(i.montant),
        0
    ),
}));

// 🔹 Total général
const totalMontant = computed(
    () =>
        totals.value.emuci +
        totals.value.quipux +
        totals.value.dgtt +
        totals.value.ministere
);
const currentDate = new Date().toLocaleDateString("fr-FR");

// 🔹 Fonction pour valider le montant reçu et fermer la caisse
const handleValidate = async () => {
    try {
        if (!fondDeCaisse.value) {
            toast.error("Veuillez entrer le montant reçu avant de valider.");
            return;
        }

        await close({
            montant_fermeture: fondDeCaisse.value,
            montant_saisie_caisse: fondDeCaisse.value,
            billetterie: billetterie.value,
        });

        error.value = null;
        await fetchPaiements(); // Recharger les données si besoin
    } catch (error) {
        console.error("Erreur lors de la validation :", error);
        error.value =
            error.response?.data?.message ||
            "Une erreur est survenue lors de la validation.";
    }
};
</script>

<script>
export default {
    name: "SidebarWithHeader",
    // Add any necessary component logic here
};
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
