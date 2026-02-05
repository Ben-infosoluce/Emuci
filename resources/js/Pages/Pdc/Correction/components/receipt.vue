<template>
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Immatriculation Spéciale
        </h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.pdc.immatriculation')">
            <BoutonRetour />
            </Link>
        </div>
    </div>

    <div class="p-6  ">
        <Card class="h-full flex flex-col p-10 mx-auto">
            <div class="flex justify-between items-center mb-4">
                <div></div>
                <div class="flex items-center space-x-2 cursor-pointer" @click="imprimer">
                    <Printer class="w-5 h-5" />
                    <span class="font-semibold">Imprimer</span>
                </div>
            </div>
            <div id="receipt">

                <div class="w-fit mx-auto space-y-2 text-gray-800 mx-auto mt-12">
                    <h2 class="text-xl font-semibold mb-3">Service: <span class="font-bold">Immatriculation
                            Spéciale</span>
                    </h2>

                    <p class="w-fit mx-auto">Date de la demande : <strong> {{ props.dossier.date_creation }}</strong>
                    </p>

                    <p class="w-fit mx-auto">Nom du véhicule : <strong> {{ props.dossier.r_dossier_vehicule.marque
                    }} {{ props.dossier.r_dossier_vehicule.modele }}</strong>
                    </p>

                    <!-- <p class="w-fit mx-auto">N° d’immatriculation: <strong>{{
                        props.dossier.r_dossier_vehicule.num_immatriculation }}</strong></p> -->
                    <p class="text-lg  w-fit mx-auto">Client : <strong>{{
                        props.dossier.r_dossier_client.civilite
                            }} {{
                                props.dossier.r_dossier_client.nom }} {{
                                props.dossier.r_dossier_client.prenom }}.
                        </strong>
                    </p>
                </div>

                <div class="w-fit mx-auto bg-[#CA7600] text-white mt-6 rounded-lg">
                    <div class="flex flex-col items-center justify-between px-8 py-3">
                        <div class="flex items-center gap-2">
                            <KeyRound class="w-5 h-5" />
                            <span class="font-semibold">N° CHRONO</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <div class="text-xl font-bold tracking-widest">{{
                                props.dossier.num_chrono }}</div>
                            <EyeOff class="w-5 h-5" />
                        </div>
                    </div>
                </div>
                <div class="w-fit mx-auto items-center">
                    <div class="w-fit mx-auto items-center mt-6 ">
                        <Badge
                            :variant="dossier?.statut_paiement === 1 ? 'warning' : dossier?.statut_paiement === 2 ? 'success' : dossier?.statut_paiement === 3 ? 'error' : 'secondary'">
                            {{
                                dossier?.statut_paiement === 1
                                    ? 'EN ATTENTE DE PAIEMENT'
                                    : dossier?.statut_paiement === 2
                                        ? 'PAYER'
                                        : dossier?.statut_paiement === 3
                                            ? 'ANNULER'
                                            : 'Inconnu'
                            }}
                        </Badge>

                    </div>

                </div>


                <div class="flex items-center mt-6 space-x-3 text-gray-700 w-fit mx-auto mb-14">
                    <CheckCircle class="text-green-500 w-6 h-6" />
                    <p>Le processus est en cours, veuillez passer à la caisse pour terminer</p>
                </div>
            </div>

        </Card>
    </div>
</template>

<script setup>
import { Printer, KeyRound, EyeOff, CheckCircle } from 'lucide-vue-next'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'


// Props pour recevoir les données existantes
const props = defineProps({
    dossier: Object,
});

console.log('Données reçues:', props.dossier);

function imprimer() {
    // Ouvrir une nouvelle fenêtre
    const printContent = document.getElementById("receipt").innerHTML;

    const originalContent = document.body.innerHTML;
    document.body.innerHTML = printContent;
    window.print();
    document.body.innerHTML = originalContent;
    window.location.reload(); // recharge pour remettre Vue dans l’état normal
}

</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
export default {
    layout: Main,
};

</script>
<style scoped>
@media print {
    body {
        background: white !important;
        color: black !important;
    }

    .bg-\[\#CA7600\] {
        background-color: #CA7600 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .text-white {
        color: white !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .text-green-500 {
        color: #22c55e !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
