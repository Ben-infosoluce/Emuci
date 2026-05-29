<template>
    <div class="max-w-md mx-auto p-6 space-y-6 ">
        <!-- <h1 class="text-xl font-bold">Caisse de mon site</h1> -->

        <div v-if="loading" class="text-center">⏳ Chargement...</div>

        <div v-else>
            <div class="flex flex-col items-center justify-center gap-8">
                <div class="bg-black rounded-full flex items-center justify-center w-fit p-8 text-white">
                    <Lock class="w-10 h-10" />
                </div>
                <h1 class="text-xl font-bold">Caisse fermée</h1>
                <div class="flex gap-2">
                    <AlertDialog>
                        <AlertDialogTrigger as-child>
                            <Button>
                                OUVERTURE DE LA CAISSE
                                <LockOpen class="w-20 h-20 ml-2" />
                            </Button>
                        </AlertDialogTrigger>
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    Fond de caisse du jour</AlertDialogTitle>
                                <AlertDialogDescription>
                                    <Input v-model="fondDeCaisse" class="my-8" placeholder="1 000 000" />
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter class="flex flex-row gap-2">
                                <AlertDialogCancel> Annuler </AlertDialogCancel>
                                <div>
                                    <Button @click="handleOpen"
                                        class="bg-red-800 p-4 rounded hover:bg-red-900 transition duration-300">
                                        Valider
                                        <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                    </Button>
                                    <p v-if="error" class="text-red-600 mt-2 w-full">
                                        ⚠️ {{ error }}
                                    </p>
                                </div>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>

                    <AlertDialog v-model:open="isDialogOpen" v-if="shouldShowEditButton">
                        <AlertDialogTrigger as-child>
                            <Button @click="prepareEdit">
                                Editer la billeterie
                                <Edit class="w-4 h-4 ml-2" />
                            </Button>
                        </AlertDialogTrigger>

                        <AlertDialogContent class="w-full max-w-[1200px]">
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    {{ dialogTitle }}
                                </AlertDialogTitle>

                                <AlertDialogDescription>
                                    <div v-if="!confirmingFinalClosure" class="overflow-y-auto px-1 custom-scrollbar">

                                        <!-- Dual Billetterie Module -->
                                        <div class="mt-6 flex flex-col gap-6 ">
                                            <div class="grid grid-cols-2 gap-8">
                                                <!-- Caissière Side (Read Only) -->
                                                <div class="flex flex-col gap-3">
                                                    <label class="font-bold text-gray-400 text-xs uppercase tracking-tight flex items-center gap-2">
                                                        <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                                        Comptage Précédent (Lecture seule)
                                                    </label>
                                                    <div class="bg-gray-50/50 border border-dashed rounded-xl overflow-hidden opacity-70">
                                                        <div class="grid grid-cols-2 bg-gray-100/50 border-b text-[10px] font-bold text-gray-400 uppercase tracking-tighter">
                                                            <div class="px-4 py-1.5 border-r">Coupure</div>
                                                            <div class="px-4 py-1.5">Qté</div>
                                                        </div>
                                                        <div class="max-h-[300px] overflow-y-auto px-1 custom-scrollbar">
                                                            <div v-for="item in billetterie" :key="item.valeur"
                                                                class="grid grid-cols-2 items-center border-b border-gray-100 last:border-0 py-1 pl-3 pr-2">
                                                                <div class="flex items-center justify-between pr-4 border-r border-gray-100">
                                                                    <span class="text-xs font-bold text-gray-500">{{ item.valeur.toLocaleString('fr-FR') }}</span>
                                                                    <span class="text-[9px] text-gray-300 font-mono">x</span>
                                                                </div>
                                                                <div class="pl-3 py-1 text-xs font-mono text-gray-500 text-right pr-4">
                                                                    {{ item.quantite || 0 }}
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="p-3 bg-gray-100/50 rounded-lg text-right">
                                                        <span class="text-[10px] font-bold text-gray-400 uppercase mr-2">Total Précédent:</span>
                                                        <span class="text-sm font-bold text-gray-600">{{ totalBilletterie.toLocaleString() }} F</span>
                                                    </div>
                                                </div>

                                                <!-- Contrôleur Side (Editable) - ICI UTILISÉ POUR L'ÉDITION CAISSIÈRE -->
                                                <div class="flex flex-col gap-3">
                                                    <label class="font-bold text-indigo-700 text-xs uppercase tracking-tight flex items-center gap-2">
                                                        <div class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></div>
                                                        Nouveau Comptage
                                                    </label>
                                                    <div class="bg-white border-2 border-indigo-100 rounded-xl overflow-hidden shadow-sm shadow-indigo-50">
                                                        <div class="grid grid-cols-2 bg-indigo-50/50 border-b text-[10px] font-bold text-indigo-400 uppercase tracking-tighter">
                                                            <div class="px-4 py-1.5 border-r border-indigo-100">Coupure</div>
                                                            <div class="px-4 py-1.5 font-bold">Ma Quantité</div>
                                                        </div>
                                                        <div class="max-h-[300px] overflow-y-auto px-1 custom-scrollbar">
                                                            <div v-for="item in billetterieControlleur" :key="item.valeur"
                                                                class="grid grid-cols-2 items-center border-b border-gray-50 last:border-0 hover:bg-gray-50/50 transition-colors py-1 pl-3 pr-2">
                                                                <div class="flex items-center justify-between pr-4 border-r border-gray-100">
                                                                    <span class="text-xs font-black text-gray-700">{{ item.valeur.toLocaleString('fr-FR') }}</span>
                                                                    <span class="text-[9px] text-gray-400 font-mono">x</span>
                                                                </div>
                                                                <div class="flex items-center gap-3 pl-3">
                                                                    <Input type="number" v-model.number="item.quantite" min="0"
                                                                        class="h-7 w-full text-right bg-white border-gray-200 focus:ring-indigo-500 font-mono text-xs pr-1" />
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="p-3 bg-indigo-600 rounded-lg text-right shadow-md">
                                                        <span class="text-[10px] font-bold text-indigo-100 uppercase mr-2">Nouveau Total:</span>
                                                        <span class="text-sm font-black text-white">{{ totalBilletterieControlleur.toLocaleString() }} F</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="py-8 text-center animate-in fade-in zoom-in duration-300">
                                        <div class="mb-4 flex justify-center text-amber-500">
                                            <i class="pi pi-exclamation-circle text-6xl"></i>
                                        </div>
                                        <h3 class="text-xl font-bold text-gray-900 mb-2">Confirmer les modifications ?</h3>
                                        <p class="text-gray-600">
                                            Vous allez mettre à jour définitivement le billetage de votre dernière fermeture.
                                        </p>
                                    </div>
                                </AlertDialogDescription>
                            </AlertDialogHeader>

                            <AlertDialogFooter class="flex flex-row gap-2 py-2">
                                <template v-if="!confirmingFinalClosure">
                                    <AlertDialogCancel @click="confirmingFinalClosure = false">Annuler</AlertDialogCancel>
                                    <Button @click="handleValidate" class="bg-green-800 p-4 rounded hover:bg-gray-900 transition duration-300" :disabled="loading">
                                        Valider
                                        <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                    </Button>
                                </template>
                                <template v-else>
                                    <Button variant="outline" @click="confirmingFinalClosure = false">Non, modifier</Button>
                                    <Button @click="submitUpdate" class="bg-green-600 hover:bg-green-700 text-white shadow-lg shadow-green-100 px-8">
                                        Oui, confirmer
                                        <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                    </Button>
                                </template>
                            </AlertDialogFooter>
                        </AlertDialogContent>
                    </AlertDialog>
                </div>
            </div>

            <!-- Affichage des erreurs -->
            <p v-if="error" class="text-red-600 mt-2">⚠️ {{ error }}</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from "vue";
import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";
import { Button } from "@/components/ui/button";
import { Lock, LockOpen, Edit } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
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
import { toast } from "vue-sonner";
import axios from "axios";

const store = useCaisseStore();
const { error, loading, lastClosure } = storeToRefs(store);
const { fetchCurrent, open, fetchLastClosure, updateBilletterie } = store;

const fondDeCaisse = ref("");
const isDialogOpen = ref(false);
const confirmingFinalClosure = ref(false);
const paiements = ref([]);

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

const caisseOuverture = computed(() => lastClosure.value);

const shouldShowEditButton = computed(() => {
    if (!lastClosure.value) return false;
    
    // Vérifier si c'est le même jour
    const closureDate = new Date(lastClosure.value.date_fermeture).toDateString();
    const today = new Date().toDateString();
    
    return closureDate === today && lastClosure.value.edit_billetterie === 1;
});

const dialogTitle = computed(() => {
    return `Édition du billetage - ${formatDate(lastClosure.value?.date_fermeture)}`;
});

const totalBilletterie = computed(() => {
    return billetterie.value.reduce((total, item) => total + (item.valeur * (item.quantite || 0)), 0);
});

const totalBilletterieControlleur = computed(() => {
    return billetterieControlleur.value.reduce((total, item) => total + (item.valeur * (item.quantite || 0)), 0);
});

const totalMontant = computed(() => {
    return paiements.value.reduce((sum, p) => sum + Number(p.montant || 0), 0);
});

const totalMontantFormatted = computed(() => `${totalMontant.value.toLocaleString()} F CFA`);

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

const formatDate = (dateStr) => {
    if (!dateStr) return "";
    return new Date(dateStr).toLocaleDateString("fr-FR", { day: 'numeric', month: 'long', year: 'numeric' });
};

const fetchPaiements = async (date) => {
    if (!date) return;
    try {
        const d = date.split(' ')[0]; // YYYY-MM-DD
        const { data } = await axios.get("/paiement/data/stat", { params: { date: d } });
        paiements.value = Array.isArray(data) ? data : [];
    } catch (err) {
        console.error("Erreur paiements:", err);
    }
};

const prepareEdit = async () => {
    await fetchLastClosure();
    if (!lastClosure.value) {
        toast.error("Aucune fermeture récente trouvée.");
        isDialogOpen.value = false;
        return;
    }
    if (lastClosure.value.edit_billetterie === 0) {
        toast.error("Veuillez demander au contrôleur de valider l'édition.");
        isDialogOpen.value = false;
        return;
    }

    // Charger les paiements pour la date de cette fermeture
    await fetchPaiements(lastClosure.value.date_ouverture);

    // Charger les billetteries
    if (lastClosure.value.billetterie) {
        const saved = JSON.parse(lastClosure.value.billetterie);
        billetterie.value.forEach(item => {
            const found = saved.find(s => s.valeur === item.valeur);
            item.quantite = found ? found.quantite : 0;
        });
        // Pour l'édition, on copie dans billetterieControlleur (qui est l'editable dans le template copié)
        billetterieControlleur.value.forEach(item => {
            const found = saved.find(s => s.valeur === item.valeur);
            item.quantite = found ? found.quantite : 0;
        });
    }
    
    isDialogOpen.value = true;
};

const handleOpen = async () => {
    await open(Number(fondDeCaisse.value));
};

const handleValidate = () => {
    confirmingFinalClosure.value = true;
};

const submitUpdate = async () => {
    try {
        await updateBilletterie(lastClosure.value.id, {
            billetterie: billetterieControlleur.value,
            montant_fermeture: totalBilletterieControlleur.value
        });
        toast.success("Billetage mis à jour avec succès.");
        isDialogOpen.value = false;
        confirmingFinalClosure.value = false;
    } catch (err) {
        toast.error(err.response?.data?.message || "Erreur lors de la mise à jour.");
    }
};

onMounted(() => {
    fetchCurrent();
    fetchLastClosure();
});
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
