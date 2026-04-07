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
                        <AlertDialogContent>
                            <AlertDialogHeader>
                                <AlertDialogTitle>
                                    Font de caisse du jour</AlertDialogTitle>
                                <AlertDialogDescription>
                                    <div :class="{
                                        hidden: montantRecuCheck,
                                        'text-red-600': true,
                                        'font-bold': true,
                                    }">
                                        <label for="totalMontant">Montant total attendu</label>
                                        <Input id="totalMontant" v-model="fondDeCaisseAttendu" disabled class="my-3" />
                                    </div>

                                    <Input v-model="fondDeCaisse" class="my-8" placeholder="1 000 000" />
                                </AlertDialogDescription>
                            </AlertDialogHeader>
                            <AlertDialogFooter class="flex flex-row gap-2">
                                <AlertDialogCancel> Annuler </AlertDialogCancel>
                                <div>
                                    <Button v-if="isOpen" @click="handleClose"
                                        class="bg-red-800 p-4 rounded hover:bg-red-900 transition duration-300">
                                        Valider
                                        <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                    </Button>
                                </div>
                            </AlertDialogFooter>
                            <!-- <p v-if="error" class="text-red-600 mt-2 w-full">
                                ⚠️ {{ error }}
                            </p> -->
                        </AlertDialogContent>
                    </AlertDialog>
                </div>

                <div class="flex items-center cursor-pointer">
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Avatar>
                                <AvatarFallback>{{
                                    getInitials()
                                }}</AvatarFallback>
                            </Avatar>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent class="w-56">
                            <DropdownMenuLabel>Mon compte</DropdownMenuLabel>
                            <DropdownMenuSeparator />
                            <DropdownMenuGroup>
                                <DropdownMenuItem @click="openProfile = true">
                                    <User class="mr-2 h-4 w-4" />
                                    <span>Profile</span>
                                </DropdownMenuItem>
                                <DropdownMenuItem @click="openPasswordModal">
                                    <Settings class="mr-2 h-4 w-4" />
                                    <span>Mot de passe</span>
                                </DropdownMenuItem>
                            </DropdownMenuGroup>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem>
                                <LogOut class="mr-2 h-4 w-4" />
                                <span @click="logout">Deconnexion</span>
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                    <button data-drawer-target="sidebar-multi-level-sidebar"
                        data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar"
                        type="button"
                        class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 dark:focus:ring-gray-600">
                        <span class="sr-only">Open sidebar</span>
                        <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg">
                            <path clip-rule="evenodd" fill-rule="evenodd"
                                d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
                            </path>
                        </svg>
                    </button>
                </div>
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
        <div class="flex items-center space-x-2">
            <AlertDialog :open="openPassword">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            Modifier votre mot de passe
                        </AlertDialogTitle>

                        <AlertDialogDescription>
                            <Input v-model="oldPassword" class="my-8" placeholder="Ancien mot de passe"
                                type="password" />
                            <Input v-model="newPassword" class="my-8" placeholder="Nouveau mot de passe"
                                type="password" />
                            <Input v-model="confirmPassword" class="my-8"
                                placeholder="Confirmer le nouveau mot de passe" type="password" />
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex flex-row gap-2">
                        <AlertDialogCancel @click="openPassword = false">
                            Annuler
                        </AlertDialogCancel>
                        <div>
                            <Button @click="handlePassword">
                                Valider
                                <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                            </Button>
                        </div>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </div>

        <!-- Profile Dialog -->
        <AlertDialog :open="openProfile" @update:open="openProfile = $event">
            <AlertDialogContent class="sm:max-w-[425px] overflow-hidden p-0 rounded-xl border-none shadow-2xl">
                
                <div class="relative h-32 bg-gradient-to-r from-blue-600 to-indigo-700">
                    <div class="absolute top-4 right-4">
                        <span @click="openProfile = false">   <X class="h-4 w-4 cursor-pointer text-white" /></span>
                    </div>
                    <div class="absolute -bottom-12 left-1/2 -translate-x-1/2">
                        <Avatar class="h-24 w-24 border-4 border-white shadow-lg">
                            <AvatarFallback class="text-2xl font-bold bg-gray-100 text-blue-700">
                                {{ getInitials() }}
                            </AvatarFallback>
                        </Avatar>
                        
                    </div>
                </div>

                <div class="pt-16 pb-8 px-6 text-center">
                    <AlertDialogHeader>
                        <AlertDialogTitle class="text-2xl font-bold text-gray-900 leading-tight">
                            {{ $page.props.auth_user?.data.nom }} {{ $page.props.auth_user?.data.prenom }}
                        </AlertDialogTitle>
                        <AlertDialogDescription class="text-blue-600 font-medium flex items-center justify-center gap-1 mt-1">
                            <span class="inline-block w-2 h-2 rounded-full bg-blue-600 animate-pulse"></span>
                            {{ $page.props.auth_user?.data.r_user_role?.nom_role }}
                        </AlertDialogDescription>
                    </AlertDialogHeader>

                    <div class="mt-8 space-y-4 text-left">
                        <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 transition-colors hover:bg-gray-100">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm">
                                <User class="h-5 w-5 text-gray-500" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Email</p>
                                <p class="text-sm font-medium text-gray-700">{{ $page.props.auth_user?.data.email }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 transition-colors hover:bg-gray-100">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm">
                                <Settings class="h-5 w-5 text-gray-500" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Site & Région</p>
                                <p class="text-sm font-medium text-gray-700">
                                    {{ $page.props.auth_user?.data.r_user_site?.nom_site }} 
                                    <span class="text-gray-400 mx-1">•</span> 
                                    {{ $page.props.auth_user?.data.r_user_site?.region }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 transition-colors hover:bg-gray-100">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm">
                                <LockKeyhole class="h-5 w-5 text-gray-500" />
                            </div>
                            <div class="flex-1">
                                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Type de Site</p>
                                <p class="text-sm font-medium text-gray-700">{{ $page.props.auth_user?.data.r_user_site?.type_site }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </AlertDialogContent>
        </AlertDialog>
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
import { LogOut, Settings, User, LockKeyhole, X } from "lucide-vue-next";
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
const fondDeCaisseAttendu = ref(0);
const paiements = ref([]);
const currentCaisse = ref(null);
const montantRecuCheck = ref(true);
const montantSaisieCaisse = ref("");
const openPassword = ref(false);
const oldPassword = ref("");
const newPassword = ref("");
const confirmPassword = ref("");
const openProfile = ref(false);

const openPasswordModal = () => {
    openPassword.value = true;
};

console.log(usePage().props.auth_user)

const handlePassword = async () => {
    if (!oldPassword.value || !newPassword.value || !confirmPassword.value) {
        toast.error("Veuillez remplir tous les champs");
        return;
    }

    loading.value = true;

    try {
        const userId = usePage().props.auth_user?.data.id;

        await axios.post(`/users/change-password/${userId}`, {
            old_password: oldPassword.value,
            new_password: newPassword.value,
            new_password_confirmation: confirmPassword.value,
        });

        toast.success("Mot de passe modifié avec succès");
        openPassword.value = false;

        // Reset champs (bonne UX)
        oldPassword.value = "";
        newPassword.value = "";
        confirmPassword.value = "";
    } catch (error) {
        // Gestion erreurs Laravel (422)
        if (error.response?.status === 422) {
            const errors = error.response.data.errors;
            const firstError = Object.values(errors)[0]?.[0];
            toast.error(firstError);
        } else {
            toast.error(
                "Une erreur est survenue lors de la modification du mot de passe"
            );
        }
    } finally {
        loading.value = false;
    }
};

const handleClose = async () => {
    handleValidate();
    // await close(fondDeCaisse.value);
};

function logout() {
    axios
        .post("/logout")
        .then(() => {
            console.log("Déconnexion réussie");
            router.visit("/");
            // window.location.href = '/';
        })
        .catch((error) => {
            console.error("Erreur lors de la déconnexion :", error);
        });
}

function getInitials() {
    const user = usePage().props.auth_user?.data;
    // Vérifier si l'objet user est défini et contient les propriétés nom et prenom
    if (user && user.nom && user.prenom) {
        // Extraire la première lettre du nom et du prénom et les convertir en majuscules
        const firstInitial = user.nom.charAt(0).toUpperCase();
        const lastInitial = user.prenom.charAt(0).toUpperCase();
        // Retourner les initiales combinées
        return `${firstInitial}${lastInitial}`;
    }
    return "";
}

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

// 🔹 Fonction pour valider le montant reçu
const handleValidate = async () => {
    fondDeCaisseAttendu.value = totalMontant.value;
    try {
        if (!fondDeCaisse.value) {
            toast.error("Veuillez entrer le montant reçu avant de valider.");
            return;
        }

        if (totalMontant.value !== parseFloat(fondDeCaisse.value)) {
            montantSaisieCaisse.value = fondDeCaisse.value;
            // error.value = "Le montant reçu doit correspondre au montant total.";
            toast.error("Le montant reçu doit correspondre au montant total.");
            montantRecuCheck.value = false;
            return;
        }

        await close({
            montant_fermeture: fondDeCaisse.value,
            montant_saisie_caisse:
                montantSaisieCaisse.value !== undefined &&
                    montantSaisieCaisse.value !== null &&
                    montantSaisieCaisse.value !== ""
                    ? montantSaisieCaisse.value
                    : fondDeCaisse.value,
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
/* Add any component-specific styles here */
</style>
