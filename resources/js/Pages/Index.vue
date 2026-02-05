<template>
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-white py-4 px-6 shadow-sm">
            <div class="container mx-auto flex items-center justify-between">
                <div class="flex items-center">
                    <img src="/public/assets/images/logo_frame.svg" alt="SITV Logo" class="h-12" />
                </div>
                <nav class="hidden md:flex space-x-8">
                    <a href="#" class="text-amber-600 font-medium border-b-2 border-amber-600 pb-1">
                        Suivre une prestation
                    </a>
                    <a href="#" class="text-gray-800 hover:text-amber-600 font-medium">
                        Nos services
                    </a>
                    <a href="#" class="text-gray-800 hover:text-amber-600 font-medium">
                        Nous contactez
                    </a>
                    <Link :href="route('show.login')" v-if="!connectedUser">
                    <a class="text-gray-800 hover:text-amber-600 font-medium">
                        Se connecter
                    </a>
                    </Link>
                    <a v-if="connectedUser" @click="myAccount"
                        class="text-gray-800 hover:text-amber-600 font-medium cursor-pointer">
                        Dashboard
                    </a>
                    <a v-if="connectedUser" @click="handleLogout"
                        class="text-gray-800 hover:text-amber-600 font-medium cursor-pointer" title="Se déconnecter">
                        <LogOut />
                    </a>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow flex items-center justify-center py-12 px-4 bg-cover bg-center bg-no-repeat relative"
            style="background-image: url('/assets/images/login_bg.svg')">
            <!-- Dialog Overlay -->
            <!-- <div
                v-if="isDialogOpen"
                class="fixed inset-0 z-40 bg-black/50"
                @click="closeDialog"
            ></div> -->

            <Card class="m-auto w-full max-w-[819px] min-h-[70vh] sm:p-6 bg-background mx-4 relative"
                :class="isDialogOpen ? '' : 'hidden'">
                <!-- Close -->
                <button type="button"
                    class="absolute right-4 top-4 z-10 rounded-md opacity-70 hover:opacity-100 transition"
                    @click="closeDialog">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="w-4 h-4" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path d="M18 6 6 18" />
                        <path d="m6 6 12 12" />
                    </svg>
                    <span class="sr-only">Fermer</span>
                </button>

                <!-- Content -->
                <div class="w-full">
                    <TrackingPrestation v-if="trackingData" :service="trackingData.service"
                        :request-date="trackingData.requestDate" :vehicle="trackingData.vehicle"
                        :plate="trackingData.plate" :client="trackingData.clientName"
                        :start-date="trackingData.startDate" :end-date="trackingData.endDate"
                        :status-state="trackingData.statusState" :status-label="trackingData.statusLabel"
                        :status-step="trackingData.statusStep" />
                </div>
            </Card>
            <div class="container mx-auto flex flex-col md:flex-row items-center justify-center gap-10"
                v-if="!isDialogOpen">
                <!-- Login Form -->
                <div class="p-8 w-full max-w-md">
                    <!-- Login Card -->
                    <!-- <Card v-if="!clientId" class="mx-auto max-w-sm">
                        <CardHeader>
                            <h2
                                class="text-2xl font-bold text-center text-gray-800 mb-6"
                            >
                                Identifiez-vous
                            </h2>
                        </CardHeader>
                        <CardContent>
                            <form
                                @submit.prevent="handleLogin"
                                class="grid gap-4"
                            >
                                <div class="grid gap-4 pt-4">
                                    <Input
                                        id="username"
                                        v-model="loginForm.username"
                                        type="text"
                                        placeholder="Nom d'utilisateur"
                                        required
                                        :disabled="isLoading"
                                    />
                                </div>
                                <div class="grid gap-4 pt-2">
                                    <Input
                                        id="password"
                                        v-model="loginForm.password"
                                        type="password"
                                        placeholder="Mot de passe"
                                        required
                                        :disabled="isLoading"
                                    />
                                </div>
                                <div
                                    v-if="loginError"
                                    class="text-red-600 text-sm text-center"
                                >
                                    {{ loginError }}
                                </div>
                                <div class="pt-4">
                                    <Button
                                        type="submit"
                                        class="w-full bg-[#1C2434]"
                                        :disabled="isLoading"
                                    >
                                        {{
                                            isLoading
                                                ? "Chargement..."
                                                : "Valider"
                                        }}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card> -->

                    <!-- Tracking Card -->
                    <Card class="mx-auto max-w-sm">
                        <CardHeader>
                            <h2 class="text-2xl font-bold text-center text-gray-800 mb-2">
                                Suivre une prestation
                            </h2>

                        </CardHeader>
                        <CardContent>
                            <form @submit.prevent="handleTrackingSubmit" class="grid gap-4">
                                <div class="grid gap-4 pt-4">
                                    <Label for="telephone">Numéro de téléphone</Label>
                                    <Input id="telephone" v-model="trackingForm.telephone" type="text"
                                        placeholder="06123456789" required :disabled="isLoading" />
                                </div>
                                <div class="grid gap-4 pt-4">
                                    <Label for="num_chrono">Numéro chrono</Label>
                                    <Input id="num_chrono" v-model="trackingForm.numChrono" type="text"
                                        placeholder="JUD564-CI" required :disabled="isLoading" />
                                </div>

                                <div class="pt-4">
                                    <Button type="submit" class="w-full bg-[#1C2434]" :disabled="isLoading">
                                        {{
                                            isLoading
                                                ? "Recherche..."
                                                : "RECHERCHER"
                                        }}
                                    </Button>
                                </div>
                            </form>
                        </CardContent>
                    </Card>
                </div>

                <!-- Content Section -->
                <div class="text-white text-center md:text-left max-w-lg">
                    <h1 class="text-3xl md:text-4xl font-bold mb-6 text-center">
                        Effectuez le suivis de vos opérations chez EMU-CI
                    </h1>
                    <p class="text-lg mb-8 text-center">
                        La fabrication et la commercialisation des plaques
                        d'immatriculation ; ainsi que tous biens et services en
                        relation avec la pose de plaques d'immatriculation
                    </p>
                    <div class="text-center">
                        <Button type="button" class="text-center bg-[#1C2434]">
                            Tous nos services
                        </Button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-[#ca7600] text-white py-4 text-center">
            <div class="container mx-auto">
                <p>EMU CI © 2025 Réalisé par infosoluces</p>
            </div>
        </footer>
        <Toaster richColors position="top-right" />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import axios from "axios";
import { Button } from "@/components/ui/button";
import { LogOut } from "lucide-vue-next";
import { Dialog, DialogContent } from "@/components/ui/dialog";
import TrackingPrestation from "@/components/TrackingPrestation.vue";
import { Toaster, toast } from "vue-sonner";
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
} from "@/components/ui/card";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";

const trackingForm = reactive({
    numChrono: "",
    telephone: "",
});

const isLoading = ref(false);

const isDialogOpen = ref(false);
const trackingData = ref(null);

const connectedUser = ref(null);

// Lifecycle hooks
onMounted(() => {
    const authUser = usePage().props.auth_user;
    if (authUser?.data?.r_user_role?.nom_role) {
        connectedUser.value = authUser.data.r_user_role.nom_role;
    }
});

const handleTrackingSubmit = async () => {
    if (isLoading.value) return;

    isLoading.value = true;

    try {
        const data = await fetchTrackingData();
        if (data) {
            trackingData.value = data;
            isDialogOpen.value = true;
        } else {
            toast.error("Aucune donnée trouvée pour ce numéro chrono.");
        }
    } catch (error) {
        console.error("Erreur de recherche:", error);

        if (error.response?.data?.message) {
            toast.error(error.response.data.message);
        } else if (error.response?.status === 404) {
            toast.error("Numéro chrono introuvable. Veuillez vérifier.");
        } else if (error.response?.status >= 500) {
            toast.error("Erreur serveur. Veuillez réessayer plus tard.");
        } else {
            toast.error("Une erreur est survenue lors de la recherche.");
        }
    } finally {
        isLoading.value = false;
    }
};

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString("fr-FR");
};

const fetchTrackingData = async () => {
    if (!trackingForm.numChrono || !trackingForm.telephone) {
        throw new Error("Données manquantes");
    }

    try {
        const { data } = await axios.post("/client/chrono", {
            num_chrono: trackingForm.numChrono.trim(),
            telephone: trackingForm.telephone.trim(),
        });

        if (!data?.client || !data?.dossier) {
            return null;
        }

        const { client, dossier } = data;

        const tracking = computeTrackingState(dossier);

        // Construction sécurisée des données
        const vehiculeInfo = dossier?.r_dossier_vehicule;
        const vehicleDisplay =
            vehiculeInfo?.marque && vehiculeInfo?.modele
                ? `${vehiculeInfo.marque} ${vehiculeInfo.modele}`
                : "—";

        console.log(tracking);

        return {
            clientName:
                `${client.prenom || ""} ${client.nom || ""}`.trim() || "—",
            service: dossier?.r_dossier_services?.nom_service || "—",
            requestDate: formatDateTime(dossier?.date_creation) || "—",
            vehicle: vehicleDisplay,
            plate: vehiculeInfo?.num_immatriculation || "—",
            startDate: formatDateTime(dossier?.date_creation) || "—",
            endDate: formatDateTime(dossier?.date_validation) || "—",
            statusStep: tracking.statusStep,
            statusLabel: tracking.libelle,
            statusState: tracking.statusState,
        };
    } catch (error) {
        throw error;
    }
};

const computeTrackingState = (dossier) => {
    const hasPlateStep = dossier.status_pose_plaque !== null;
    let step = 1; // Step de base

    // Statut 1 : En attente de validation
    if (dossier.statut === 1) {
        return {
            statusStep: 1,
            statusState: "pending",
            libelle: "En attente de validation",
        };
    }

    // Statut 2 : Validé → Dossier clos
    if (dossier.statut === 2) {
        return {
            statusStep: hasPlateStep ? 4 : 3,
            statusState: "success",
            libelle: "Dossier validé et clos",
        };
    }

    // Statut 3 : Refusé
    if (dossier.statut === 3) {
        return {
            statusStep: 1,
            statusState: "error",
            libelle: "Dossier refusé",
        };
    }

    // Statut 4 : En cours → Vérifier les sous-étapes
    if (dossier.statut === 4) {
        // ÉTAPE 1 : Paiement
        if (dossier.statut_paiement === 1) {
            return {
                statusStep: step,
                statusState: "pending",
                libelle: "En attente de paiement",
            };
        }
        if (dossier.statut_paiement === 3) {
            return {
                statusStep: step,
                statusState: "error",
                libelle: "Paiement annulé",
            };
        }

        // Si paiement OK (= 2), on passe à l'étape suivante
        if (dossier.statut_paiement === 2) {
            step++; // step = 2

            // ÉTAPE 2 : Numérisation
            if (dossier.statut_numerisation === 1) {
                return {
                    statusStep: step,
                    statusState: "pending",
                    libelle: "En attente de numérisation",
                };
            }
            if (dossier.statut_numerisation === 3) {
                return {
                    statusStep: step,
                    statusState: "error",
                    libelle: "Numérisation annulée",
                };
            }

            // Si numérisation OK (= 2), on passe à l'étape suivante
            if (dossier.statut_numerisation === 2) {
                step++; // step = 3

                // ÉTAPE 3 : Pose plaque (si applicable)
                if (!hasPlateStep) {
                    // Pas de pose plaque → en attente validation finale
                    return {
                        statusStep: step,
                        statusState: "pending",
                        libelle: "En attente de validation finale",
                    };
                }

                // Avec pose plaque
                if (dossier.status_pose_plaque === 1) {
                    return {
                        statusStep: step,
                        statusState: "pending",
                        libelle: "En attente de pose de plaque",
                    };
                }
                if (dossier.status_pose_plaque === 3) {
                    return {
                        statusStep: step,
                        statusState: "error",
                        libelle: "Pose plaque annulée",
                    };
                }

                // Si pose plaque OK (= 2), on passe à l'étape suivante
                if (dossier.status_pose_plaque === 2) {
                    step++; // step = 4
                    return {
                        statusStep: step,
                        statusState: "pending",
                        libelle: "En attente de validation finale",
                    };
                }
            }
        }
    }

    // Fallback
    return {
        statusStep: 1,
        statusState: "pending",
        libelle: "Statut inconnu",
    };
};

const closeDialog = () => {
    isDialogOpen.value = false;
    trackingForm.numChrono = "";
    trackingForm.telephone = "";
    // trackingData.value = null;
    // trackingError.value = "";
};

const handleLogout = async () => {
    try {
        await axios.post("/logout");
        router.visit("/");
    } catch (error) {
        console.error("Erreur lors de la déconnexion:", error);
        // Forcer la redirection même en cas d'erreur
        router.visit("/");
    }
};

const myAccount = () => {
    const user_type = usePage().props.auth_user.data.r_user_role.nom_role;
    let linnk = "";
    if (user_type == "PoolControle") {
        linnk = "pdc";
    } else if (user_type == "Caisse") {
        linnk = "caisse/data";
    } else if (user_type == "Numerisation") {
        linnk = "numerisation";
    } else if (user_type == "MT1") {
        linnk = "minister/mt1";
    } else if (user_type == "MT2") {
        linnk = "minister/mt2";
    } else if (user_type == "Admin") {
        linnk = "admin";
    } else if (user_type == "Boss") {
        linnk = "boss";
    } else if (user_type == "Raf") {
        linnk = "raf";
    } else if (user_type == "Gestionnaire") {
        linnk = "gestionnaire/dashboard";
    } else if (user_type == "CaisseController") {
        linnk = "caisse/controller";
    }

    router.visit(linnk);
    console.log(user_type);
    console.log("link", linnk);
};
</script>
