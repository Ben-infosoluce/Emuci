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
                <AlertDialog>
                    <AlertDialogTrigger as-child>
                        <!-- @click="handleOpen" -->
                        <Button>
                            OVERTURE DE LA CAISSE
                            <LockOpen class="w-20 h-20" />
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Font de caisse du jour</AlertDialogTitle>
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

            </div>

            <!-- Affichage des erreurs -->
            <p v-if="error" class="text-red-600 mt-2">⚠️ {{ error }}</p>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { storeToRefs } from "pinia";
import { useCaisseStore } from "@/stores/mainStore";
import { Button } from "@/components/ui/button";
import { Lock, LockOpen } from "lucide-vue-next";
import { LogOut, Settings, User, LockKeyhole } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
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

const store = useCaisseStore();
const { error, loading } = storeToRefs(store);
const { fetchCurrent, open } = store;
const fondDeCaisse = ref("");
// Ouvrir la caisse
const handleOpen = async () => {
    // await open();
    console.log("Ouverture de la caisse avec le fond :", fondDeCaisse.value);
    await open(Number(fondDeCaisse.value));
};

// Au montage : charger l’état actuel
onMounted(() => {
    fetchCurrent();
});
</script>
