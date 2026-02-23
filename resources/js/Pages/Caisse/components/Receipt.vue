<template>
    <div class=" max-w-3xl mx-auto  p-2">
        <div class="flex justify-between items-center mb-6">
            <!-- <div></div> -->
            <div class="">
                <Link :href="route('show.caisse.data')">
                <BoutonRetour />
                </Link>
            </div>
            <div class="text-right">
                <div class="flex items-center space-x-2 cursor-pointer" @click="imprimer">
                    <Printer class="w-5 h-5" />
                    <span class="font-semibold">Imprimer</span>
                </div>
            </div>
        </div>
        <div class="flex items-center space-x-2">
        </div>
    </div>
    <!-- je veux imprimer toute cette div -->
    <div ref="zoneImpression" class="bg-white max-w-3xl mx-auto border rounded-xl shadow p-6"
        style="width: 148mm; min-height: 210mm;">
        <!-- Header -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div style="display: flex; flex-direction: column;">
                <img src="/public/assets/images/logo_frame.svg" alt="Logo" style="height: 2rem">
                <div style="display: flex; flex-direction: column;"></div>
            </div>
            <div style="text-align: right;">
                <p style="font-size: 14px; margin: 0;">
                    <span style="font-weight: 500;">EMUCI</span>
                </p>
            </div>
        </div>
        <div
            style="width: fit-content; margin-left: auto; margin-right: auto; margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <qrcode-vue :value="JSON.stringify(donneeClient)" render-as="canvas" class="qrcode-print" />
        </div>
        <!-- Title -->
        <h2 style="text-align: center; font-weight: bold; font-size: 1rem; margin-top: 1rem; margin-bottom: 1rem;">
            REÇU DE PAIEMENT N°
            <span style="color: #d97706;">{{ props?.dossier?.num_chrono }}</span>
        </h2>
        <!-- Info Table -->
        <div
            style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.4rem; font-size: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.8rem; margin-bottom: 0.8rem;">
            <div><span style="font-weight: 600;">Client N° :</span> {{ props?.dossier?.id }} </div>
            <div><span style="font-weight: 600;">Date de Facturation :</span> {{
                formatDateTime(props?.dossier?.date_paiement) }}</div>
            <div><span style="font-weight: 600;">Nom & Prénom :</span> {{ props?.dossier?.r_dossier_client?.nom }} {{
                props?.dossier?.r_dossier_client?.prenom }}</div>
            <div><span style="font-weight: 600;">N° Chassis :</span> {{ props?.dossier?.r_dossier_vehicule?.vin }}</div>
            <div><span style="font-weight: 600;">Véhicule :</span> {{ props?.dossier?.r_dossier_vehicule?.marque }} {{
                props?.dossier?.r_dossier_vehicule?.modele }}</div>
            <div><span style="font-weight: 600;">Règlement :</span> ESPECE</div>
        </div>


        <!-- Table -->
        <div style="overflow-x: auto;">
            <table style="width: 100%; border: 1px solid #d1d5db; font-size: 0.75rem; border-collapse: collapse;">
                <thead style="background-color: #f3f4f6;">
                    <tr>
                        <th style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: left;">Désignation
                        </th>
                        <th style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.name">
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">{{ item.name }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">{{
                            formatAmount(item.amount) }}</td>
                    </tr>
                </tbody>
                <tfoot style="background-color: #f9fafb; font-weight: 600;">
                    <tr>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">Total Hors Taxes (HT)</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">{{
                            formatAmount(totalHT) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">TVA (18%)</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">{{
                            formatAmount(tva) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">Total TTC</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">{{
                            formatAmount(totalTTC) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div
                style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-top: 1px solid #e5e7eb; padding-top: 0.8rem; font-size: 0.7rem; margin-top: 0.8rem;">
                <!-- Partie gauche -->
                <!-- <p style="color: #9ca3af; font-weight: bold;">
                    Nom du caissier : {{ props?.caissier?.nom }} {{ props?.caissier?.prenom }}
                </p> -->
                <p></p>

                <!-- Partie droite -->
                <div style="text-align: right;">
                    <p style="font-weight: 600; text-decoration: underline;">Accès de connexion</p>
                    <p style="color: #111827;">Numéro de téléphone : {{
                        donneeClient.Telephone }}</p>
                    <p style="color: #111827;">Numéro de chrono : {{
                        props?.dossier?.num_chrono }}</p>
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import BoutonRetour from "/resources/js/components/BoutonRetour.vue";
import { Printer, KeyRound, EyeOff, CheckCircle } from 'lucide-vue-next'

import { computed, ref } from 'vue';
import dayjs from 'dayjs'
import QrcodeVue from 'qrcode.vue';
import { nextTick } from 'vue'

const props = defineProps({
    dossier: Object,
    detailTypeServices: Object,
    caissier: Object,
    dossier_lier: Object,
    detailTypeServices_lier: Object,
    autre_facturation: Object,

});
console.log('dossier_lier:', props.dossier);

const zoneImpression = ref(null);

const qrCodeRef = ref(null)

async function imprimer() {
    await nextTick(); // s'assure que tout est rendu

    // ✅ 1) On clone le contenu de la div
    let contenu = zoneImpression.value.innerHTML;

    // ✅ 2) On remplace le canvas par une image base64
    const canvas = zoneImpression.value.querySelector('.qrcode-print');
    if (canvas && canvas.toDataURL) {
        const qrImg = `<img src="${canvas.toDataURL()}" alt="QR Code" style="width: 80px; height: 80px;" />`;
        contenu = contenu.replace(/<canvas[^>]*class="qrcode-print"[^>]*><\/canvas>/, qrImg);
    }

    // ✅ 3) Séparateur pointillé entre les deux exemplaires
    const separateur = `
        <div style="
            width: 100%; 
            height: 3px; 
            border-top: 3px dashed #6b7280; 
            margin: 15px 0; 
            position: relative;
        ">
            <span style="
                position: absolute; 
                top: -10px; 
                left: 50%; 
                transform: translateX(-50%); 
                background: white; 
                padding: 0 10px; 
                font-size: 10px; 
                color: #6b7280;
                font-weight: bold;
            ">
                - - - COUPER ICI - - -
            </span>
        </div>
    `;

    // ✅ 4) On crée le HTML avec 2 exemplaires (réduits à 48% pour tenir sur une page A4)
    const htmlContent = `
        <html>
            <head>
                <title>Reçu - 2 Exemplaires</title>
                <style>
                    @media print {
                        @page {
                            size: A4 landscape;
                            margin: 5mm;
                        }
                        body { 
                            font-family: Arial, sans-serif; 
                            margin: 0; 
                            padding: 0; 
                            background: white;
                        }
                        .exemplaire {
                            width: 48%;
                            display: inline-block;
                            vertical-align: top;
                            box-sizing: border-box;
                            padding: 5mm;
                            border: 1px solid #d1d5db;
                            border-radius: 8px;
                        }
                        .exemplaire:first-child {
                            margin-right: 2%;
                        }
                        .exemplaire:last-child {
                            margin-left: 2%;
                        }
                        .contenu-recu {
                            transform: scale(0.95);
                            transform-origin: top left;
                        }
                    }
                    body { 
                        font-family: Arial, sans-serif; 
                        margin: 0; 
                        padding: 5mm;
                        background: white;
                    }
                    .page-container {
                        width: 100%;
                        display: flex;
                        justify-content: space-between;
                        align-items: flex-start;
                    }
                    .exemplaire {
                        width: 48%;
                        box-sizing: border-box;
                        padding: 5mm;
                        border: 1px solid #d1d5db;
                        border-radius: 8px;
                        background: white;
                    }
                    .exemplaire:first-child {
                        margin-right: 1%;
                    }
                    .exemplaire:last-child {
                        margin-left: 1%;
                    }
                    .badge-exemplaire {
                        text-align: center;
                        font-weight: bold;
                        font-size: 11px;
                        color: #d97706;
                        margin-bottom: 5px;
                        border-bottom: 1px solid #e5e7eb;
                        padding-bottom: 3px;
                    }
                    table { 
                        border-collapse: collapse; 
                        width: 100%; 
                        font-size: 0.7rem;
                    }
                    th, td { 
                        border: 1px solid #ccc; 
                        padding: 0.3rem 0.4rem; 
                        text-align: left; 
                    }
                    h2, p { 
                        margin: 0; 
                        padding: 0; 
                    }
                    .qrcode-print { 
                        width: 60px !important; 
                        height: 60px !important; 
                    }
                    img[src*="data:image"] {
                        width: 60px !important;
                        height: 60px !important;
                    }
                </style>
            </head>
            <body>
                <div class="page-container">
                    <!-- Exemplaire 1 -->
                    <div class="exemplaire">
                        <div class="badge-exemplaire">CLIENT</div>
                        <div class="contenu-recu">
                            ${contenu}
                        </div>
                    </div>
                    
                    <!-- Exemplaire 2 -->
                    <div class="exemplaire">
                        <div class="badge-exemplaire">CAISSE / ARCHIVE</div>
                        <div class="contenu-recu">
                            ${contenu}
                        </div>
                    </div>
                </div>
                
                <script>
                    window.onload = function() {
                        window.print();
                        setTimeout(() => window.close(), 1000);
                    }
                <\/script>
            </body>
        </html>
    `;

    // ✅ 5) On ouvre la fenêtre d'impression
    const printWindow = window.open('', '', 'width=1000,height=800');
    printWindow.document.write(htmlContent);
    printWindow.document.close();
    printWindow.focus();
}

const tableauFusionne = [...props.detailTypeServices, ...(props.detailTypeServices_lier || [])];

console.log('autre_facturation:', props.autre_facturation);

// Vérification des services déclencheurs
const serviceTypes = [
    ...(props.dossier?.r_dossier_services?.r_service_types || []),
    ...(props.dossier_lier?.r_dossier_services?.r_service_types || [])
]
console.log('serviceTypes:', serviceTypes)
const triggerServices = [
    "Changement de Couleur",
    "Changement de zone (Code région)",
    "Usage"
]

const hasTriggerService = tableauFusionne.some(item =>
    triggerServices.includes(item.element_facturation)
)
console.log('tableauFusionne:', tableauFusionne);
console.log('hasTriggerService', hasTriggerService);



const items = computed(() => {
    const result = []

    if (hasTriggerService) {
        result.push({
            name: props.autre_facturation.nom,
            amount: parseFloat(props.autre_facturation.montant),
            // props.autre_facturation.montant,
        })
    }

    result.push(
        ...tableauFusionne.map(item => ({
            name: item.element_facturation,
            amount: parseFloat(props.dossier.r_dossier_vehicule.nb_plaque == 1
                ? item?.montant_1_plaque
                : item?.montant_2_plaques),
        }))
    )

    return result
})


// Total Hors Taxes (HT)
const totalHT = computed(() =>
    items.value.reduce((acc, item) => acc + item.amount, 0)
);

// TVA à 18%
const tva = computed(() => totalHT.value * 0.18);

// 💰 Total TTC (HT + TVA)
const totalTTC = computed(() => totalHT.value + tva.value);

// 💸 Formatage
function formatAmount(val) {
    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'XOF',
        minimumFractionDigits: 0
    }).format(val);
}

function formatDateTime(dateString) {
    return dayjs(dateString).format('DD/MM/YYYY HH:mm');
}

const donneeClient = {
    Client_Numero: props.dossier.id,
    Numero_chrono: props.dossier.num_chrono,
    civilite: props.dossier.r_dossier_client.civilite,
    Nom: props.dossier.r_dossier_client.nom,
    Prenom: props.dossier.r_dossier_client.prenom,
    Adresse: props.dossier.r_dossier_client.adresse,
    Telephone: props.dossier.r_dossier_client.telephone,
    Date_de_Naissance: props.dossier.r_dossier_client.date_naissance,
    ville_de_Naissance: props.dossier.r_dossier_client.ville_naissance,
    email: props.dossier.r_dossier_client.email,
    Statut_Paiement: props.dossier.statut === 1 ? 'Payé' : 'Non Payé',
    Date_de_Facturation: props.dossier.date_paiement,
    Caissier: props.dossier.paiement_validated_by,
    //
};
const donneeClientString = JSON.stringify(donneeClient.value);
console.log("Données Client :", donneeClient);

// const donneesclient = computed(() => props.dossier);
</script>

<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};
</script>
