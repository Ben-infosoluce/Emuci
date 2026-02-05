<template>
    <div
        class="sticky top-[-30px] z-10 bg-[#f1f5f9] dark:bg-gray-900 flex flex-col space-y-4 px-8  py-4 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <h4 class="text-2xl font-bold tracking-tight">
            Ré-immatriculation
        </h4>
        <div class="flex items-center space-x-2">
            <Link :href="route('show.pdc.re.immatriculation')">
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
                    <h2 class="text-xl font-semibold mb-3">Service : <span class="font-bold">Re-immatriculation
                        </span>
                    </h2>

                    <p class="w-fit mx-auto">Date de la demande : <strong>{{ props.dossier.date_creation }}</strong></p>

                    <p class="w-fit mx-auto">Nom du véhicule: <strong>{{ props.dossier.r_dossier_vehicule.marque
                            }} {{ props.dossier.r_dossier_vehicule.modele }}</strong>
                    </p>

                    <p class="w-fit mx-auto">N° d’immatriculation précédente : <strong>{{
                        props.dossier.r_dossier_vehicule.num_immatriculation_precedant }}</strong></p>
                    <p class="text-lg font-bold w-fit mx-auto">{{ props.dossier.r_dossier_client.nom }} {{
                        props.dossier.r_dossier_client.prenom }}.
                    </p>
                </div>

                <div class="w-fit mx-auto bg-[#CA7600] text-white mt-6 rounded-lg"
                    style="background-color: #CA7600;border-radius: 10px;">
                    <div class="" style=" display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: space-between;
                        padding-left: 2rem;
                        padding-right: 2rem;
                        padding-top: 0.75rem;
                        padding-bottom: 0.75rem;">
                        <div class="" style="display: flex;
                        align-items: center;
                        gap: 0.5rem;">
                            <KeyRound class="w-5 h-5" />
                            <span style="font-size: 1.25rem;
                        font-weight: 700;
                        ">N° CHRONO</span>
                        </div>
                        <div class="" style="display: flex; align-items: center; margin-top: 0.5rem;">
                            <div class="" style="font-size: 1.25rem;
                                font-weight: 700;
                                letter-spacing: 0.1em;">{{
                                    props.dossier.num_chrono }}
                            </div>
                            <!-- <EyeOff class="w-5 h-5" /> -->
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

                <div class="flex items-center mt-6 space-x-3 text-gray-700 w-fit mx-auto mb-14"
                    v-if="dossier?.statut == 1 && dossier?.statut == 4">
                    <CheckCircle class="text-green-500 w-6 h-6" />
                    <p v-if="dossier?.statut_paiement != 1">Le processus est en cours</p>
                    <p v-if="dossier?.statut_paiement === 1">Le processus est en cours, veuillez passer à la caisse pour
                        terminer
                    </p>
                </div>

                <!-- QR-code ici qui contien le numéro chrono -->
                <div class="w-fit mx-auto space-y-2 mt-8">
                    <qrcode-vue :value="dossier?.num_chrono" render-as="img" :size="128" />
                </div>
            </div>
        </Card>
    </div>
</template>

<script setup>
import { Printer, KeyRound, EyeOff, CheckCircle } from 'lucide-vue-next'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import QrcodeVue from 'qrcode.vue';
import { nextTick } from 'vue';

// Props pour recevoir les données existantes
const props = defineProps({
    dossier: Object,
});

console.log('Données reçues:', props.dossier);

async function imprimer() {
    // Wait for the next DOM update cycle
    await nextTick();

    // Add a small delay to ensure QR code canvas is fully rendered
    await new Promise(resolve => setTimeout(resolve, 100));

    // Find the QR code canvas
    const canvas = document.querySelector('#receipt canvas');
    if (canvas) {
        if (canvas.width > 0 && canvas.height > 0) {
            const img = document.createElement('img');
            img.src = canvas.toDataURL('image/png');
            img.style.width = canvas.style.width || '128px';
            img.style.height = canvas.style.height || '128px';
            canvas.parentNode.replaceChild(img, canvas);
        } else {
            console.warn('QR code canvas is not fully rendered yet.');
        }
    } else {
        console.warn('QR code canvas not found.');
    }

    // Force re-render of dynamic content by cloning the element
    const receiptElement = document.getElementById('receipt');
    if (receiptElement) {
        // Create a temporary container to force Vue to render the content
        const tempContainer = document.createElement('div');
        tempContainer.innerHTML = receiptElement.outerHTML;
        document.body.appendChild(tempContainer);

        // Wait for Vue to update the DOM
        await nextTick();

        // Select and remove the elements to exclude from printing
        const elementsToExclude = tempContainer.querySelectorAll('.w-fit.mx-auto.items-center.mt-6, .flex.items-center.mt-6.space-x-3.text-gray-700.w-fit.mx-auto.mb-14');
        elementsToExclude.forEach(element => element.remove());

        // Get the rendered HTML from the temporary container
        const printContent = tempContainer.querySelector('#receipt').innerHTML;
        document.body.removeChild(tempContainer);

        // Open a new window
        const printWindow = window.open('', '_blank');
        if (!printWindow) {
            console.error('Failed to open new window. Please allow pop-ups.');
            return;
        }

        // Write the content to the new window with centered styling
        printWindow.document.write(`
            <html>
            <head>
                <title></title>
                <style>
                    body {
                        margin: 50px ;
                        padding: 20px;
                        display: flex;
                        flex-direction: column;
                        align-items: center;
                        justify-content: center;
                       
                        background: white;
                        color: black;
                          min-height: 0; 
                            max-height: 100vh
                             -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                    }
                    .centered-content {
                        text-align: center;
                    }
                         .separator {
                        width: 100%;
                        border-top: 1px dashed #555;
                        margin: 20px 0;
                    }
                    @media print {
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
                        canvas, img {
                            display: block !important;
                            -webkit-print-color-adjust: exact;
                            print-color-adjust: exact;
                            text-align: center;
                            margin: 0 auto;
                            margin-top: 30px;
                        }
                        .centered-content {
                            text-align: center;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="centered-content receipt-copy">
                    ${printContent}
                </div>

                <hr class="separator">

                <div class="centered-content receipt-copy">
                    ${printContent}
                </div>
            </body>
            </html>
        `);

        // Ensure the document is fully written
        printWindow.document.close();

        // Wait for the new window to load content before printing
        printWindow.onload = () => {
            printWindow.print();
            printWindow.onafterprint = () => {
                printWindow.close();
            };
        };
    } else {
        console.error('Receipt element not found.');
    }
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
