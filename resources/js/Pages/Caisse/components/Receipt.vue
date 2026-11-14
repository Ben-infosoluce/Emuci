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
            <div style="text-align: left;">
                <p style="font-size: 14px; margin: 0;">EXPRESS MULTI SERVICES</p>
                <p style="font-size: 9px; margin: 0;">CC : 22 212 24 Z</p>
                <p style="font-size: 9px; margin: 0;">RCCM : CI-ABJ-03-2022-B 13-01-452</p>
                <p style="font-size: 9px; margin: 0;">Régime Réel Normal</p>
                <p style="font-size: 9px; margin: 0;">Marcory Zone 4 rue du canal, 01 BP 12099 Abidjan 01</p>
                <p style="font-size: 9px; margin: 0;">Tel : 2721202325</p>

            </div>
            <div style="display: flex; flex-direction: column;">
                <img src="/public/assets/images/logo_simple.svg" alt="Logo" style="height: 5rem">
                <div style="display: flex; flex-direction: column;"></div>
            </div>

        </div>
        <div
            style="width: fit-content; margin-left: auto; margin-right: auto; margin-top: 1.5rem; margin-bottom: 1.5rem;">
            <qrcode-vue :value="JSON.stringify(donneeClient)" render-as="canvas" class="qrcode-print" />
        </div>
        <!-- Title -->
        <h2 style="text-align: center; font-weight: bold; font-size: 1rem; margin-top: 1rem; margin-bottom: 1rem;">
            REÇU DE PAIEMENT N°
            <span style="color: #d97706;">RPABJSA{{ formatedDate(props?.dossier?.date_paiement) }}000{{
                props?.dossier?.id }}</span>
        </h2>
        <!-- Sous-titre RELICA-PRIMO -->
        <p v-if="isRelica"
            style="text-align: center; font-size: 0.7rem; color: #1d4ed8; font-weight: 600; margin-top: 0; margin-bottom: 1rem;">
            Reliquat {{ relicaGenreLabel }} / Site {{ relicaPrefix }}
        </p>
        <!--Sous-titre PRIMO-ESPECE   -->
        <p v-if="isPrimoEspece"
            style="text-align: center; font-size: 0.7rem; color: #1d4ed8; font-weight: 600; margin-top: 0; margin-bottom: 1rem;">
            Paiement en Espèce - PRIMO
        </p>
        <!-- Sous-titre MODIFICATION -->
        <p v-if="isModification"
            style="text-align: center; font-size: 0.7rem; color: #1d4ed8; font-weight: 600; margin-top: 0; margin-bottom: 1rem;">
            Paiement - MODIFICATION ({{ props?.dossier?.status_pose_plaque == 1 ? 'Plaque non posée' : 'Plaque posée' }})
        </p>
        <!-- Info Table -->
        <div
            style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.4rem; font-size: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.8rem; margin-bottom: 0.8rem;">
            <!-- <div><span style="font-weight: 600;">Client N° :</span> {{ props?.dossier?.id }} </div> -->
            <div><span style="font-weight: 600;">Nom & Prénom :</span> {{ props?.dossier?.r_dossier_client?.nom }} {{
                props?.dossier?.r_dossier_client?.prenom }}</div>
            <div><span style="font-weight: 600;">Date de Facturation :</span> {{
                formatDateTime(props?.dossier?.date_paiement) }}</div>
            <div><span style="font-weight: 600;">N° Chassis :</span> {{ props?.dossier?.r_dossier_vehicule?.vin }}</div>
            <div><span style="font-weight: 600;">Demande N° :</span> {{ props?.dossier?.num_chrono }}</div>

            <div><span style="font-weight: 600;">Véhicule :</span> {{ props?.dossier?.r_dossier_vehicule?.marque }} {{
                props?.dossier?.r_dossier_vehicule?.modele }}</div>
            <div><span style="font-weight: 600;">Règlement :</span> ESPECE</div>
        </div>

        <!-- Informations du Demandeur -->
        <h3
            style="font-weight: bold; font-size: 0.70rem; margin-top: 1rem; margin-bottom: 0.5rem; text-decoration: underline;">
            Informations du Demandeur
        </h3>
        <div
            style="display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.4rem; font-size: 0.75rem; border: 1px solid #d1d5db; border-radius: 0.375rem; padding: 0.8rem; margin-bottom: 0.8rem; background-color: #f9fafb;">
            <div><span style="font-weight: 600;">Nom & Prénom :</span> {{ props?.dossier?.demandeur_nom }} {{
                props?.dossier?.demandeur_prenom }}</div>
            <div><span style="font-weight: 600;">Téléphone :</span> {{ props?.dossier?.demandeur_telephone }}</div>
            <div><span style="font-weight: 600;">Pièce :</span> {{ props?.dossier?.demandeur_type_piece }}</div>
            <div><span style="font-weight: 600;">N° Pièce :</span> {{ props?.dossier?.demandeur_numero_piece }}</div>
        </div>


        <!-- Table -->
        <div style="overflow-x: auto;margin-top: 1rem;">
            <table style="width: 100%; border: 1px solid #d1d5db; font-size: 0.75rem; border-collapse: collapse;">
                <thead style="background-color: #f3f4f6; ">
                    <tr>
                        <th style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: left;">Désignation
                        </th>
                        <th style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in items" :key="item.name"
                        :style="item.amount < 0 ? 'color: #dc2626;' : (item.isReliquat ? 'font-weight: 700; color: #15803d;' : '')">
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">{{ item.name }}</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">
                            <span v-if="item.amount < 0">− {{ formatAmount(Math.abs(item.amount)) }}</span>
                            <span v-else>{{ formatAmount(item.amount) }}</span>
                        </td>
                    </tr>
                </tbody>
                <tfoot style="background-color: #f9fafb; font-weight: 600;">
                    <!-- <tr>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">Total Hors Taxes (HT)</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">{{
                            formatAmount(totalHT) }}</td>
                    </tr> -->

                    <tr>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">Droit de Timbre</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">{{
                            formatAmount(timbreTotal) }}</td>
                    </tr>
                    <tr>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem;">Total Payé</td>
                        <td style="border: 1px solid #d1d5db; padding: 0.4rem 0.6rem; text-align: right;">{{
                            formatAmount(totalTTC) }}</td>
                    </tr>
                </tfoot>
            </table>

            <div
                style="display: flex; justify-content: space-between; align-items: center; width: 100%; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.8rem; font-size: 0.7rem; margin-top: 0.8rem;">
                <p></p>

                <!-- Partie droite -->
                <div style="text-align: right;">
                    <!-- <p style="font-weight: 600; text-decoration: underline;">Accès de connexion</p> -->
                    <p style="color: #111827;font-size: 9px;">Dont TVA (18%) : {{
                        totalTTC * 0.18 }} FCFA</p>
                    <!-- <p style="color: #111827;">Numéro de chrono : {{
                        props?.dossier?.num_chrono }}</p> -->
                </div>
            </div>
            <p style="font-size: 10px; margin-top: 0.5rem;">Nom caissier : {{ props?.caissier?.nom }} {{
                props?.caissier?.prenom }}</p>
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
    relicaPrimo: Object,
    paiement: Object,
    paiementLignes: { type: Array, default: () => [] },
    paiement_lier: Object,
    paiementLignes_lier: { type: Array, default: () => [] },
});
console.log('Props reçus dans Receipt.vue :', props.dossier);
const zoneImpression = ref(null);
const qrCodeRef = ref(null)

// IDs des services déclencheurs
const TRIGGER_SERVICE_IDS = [4, 10, 9];

const tableauFusionne = [...props.detailTypeServices, ...(props.detailTypeServices_lier || [])];

// Vérification des services déclencheurs : on se base sur les lignes réellement
// sélectionnées (id_type_services) et non sur tous les types du service, sinon
// chaque dossier post-immatriculation — y compris une mutation seule (id 31) —
// afficherait un changement de plaque.
const hasTriggerService = [...(props.detailTypeServices || []), ...(props.detailTypeServices_lier || [])]
    .some(item =>
        TRIGGER_SERVICE_IDS.includes(Number(item?.id_type_services)) && props.dossier.id_service != 4
    );

// ── RELICA-PRIMO : table de prix ─────────────────────────────────────────────
// Prix totaux par catégorie
const RELICA_PRIX = { auto: 22600, moto: 14100, remorque: 15570 };

const isRelica = props.dossier?.type === 'RELICA-PRIMO';
const isPrimoEspece = props.dossier?.type === 'PRIMO-ESPECE';
const isModification = props.dossier?.type === 'MODIFICATION';

// ── Montants réellement encaissés ───────────────────────────────────────────
// Les lignes de paiement (paiement_lignes) sont la source de vérité : elles
// permettent à un reçu historique de conserver son montant même après un
// changement de tarif. On retombe sur l'ancien calcul si elles n'existent pas.
const paiementLignes = computed(() => props.paiementLignes || []);
const paiementLignesLier = computed(() => props.paiementLignes_lier || []);
const hasPaiementLignes = computed(() =>
    paiementLignes.value.length > 0 || paiementLignesLier.value.length > 0
);

const paiementMontant = computed(() => {
    if (!props.paiement) return null;
    return Number(props.paiement.montant || 0) + Number(props.paiement_lier?.montant || 0);
});

const timbreTotal = computed(() => {
    if (!props.paiement) return 100;
    return Number(props.paiement.timbre || 0) + Number(props.paiement_lier?.timbre || 0);
});

const paiementNet = computed(() => {
    if (paiementMontant.value === null) return null;
    return Math.max(0, paiementMontant.value - timbreTotal.value);
});

const lignesVersItems = (lignes) => lignes.map(ligne => ({
    name: `${ligne.libelle || ligne.article_code} (${ligne.article_code})`,
    amount: Number(ligne.montant_ttc) || 0,
}));

// Détail historique du paiement (avant ventilation) : repli pour les reçus
// antérieurs à la création de paiement_lignes. Les montants y sont ceux
// réellement encaissés.
const paiementDescription = computed(() =>
    Array.isArray(props.paiement?.description) ? props.paiement.description : []
);
const paiementDescriptionLier = computed(() =>
    Array.isArray(props.paiement_lier?.description) ? props.paiement_lier.description : []
);

const descriptionVersItems = (items) => (items || [])
    .filter(item => item && Number(item.montant) !== 0)
    .map(item => ({
        name: item.element_facturation || 'Service',
        amount: Number(item.montant) || 0,
    }));

const modificationMontantHT = computed(() => {
    return props.dossier?.status_pose_plaque == 1 ? 15000 : 25000;
});

const relicaPrefix = computed(() => {
    const chrono = props.dossier?.num_chrono || '';
    if (chrono.startsWith('ABJ')) return 'ABJ';
    if (chrono.startsWith('BKE')) return 'BKE';
    if (chrono.startsWith('KGO')) return 'KGO';
    return chrono.substring(0, 3) || '???';
});
const relicaGenreCategory = computed(() => {
    const genre = (props.dossier?.r_dossier_vehicule?.genre_vehicule || '').toUpperCase();
    const nbPlaque = props.dossier?.r_dossier_vehicule?.nb_plaque;
    if (genre.includes('REMORQUE')) return 'remorque';
    if (genre.includes('MOTO') || nbPlaque == 1) return 'moto';
    return 'auto';
});
const relicaGenreLabel = computed(() =>
    ({ auto: 'Auto', moto: 'Motos', remorque: 'Semi-remorques' })[relicaGenreCategory.value]
);
const relicaPrixTotal = computed(() => RELICA_PRIX[relicaGenreCategory.value]);

// Montant déjà réglé récupéré depuis la table relica_primo (via le backend)
const relicaDejaRegle = computed(() => {
    if (props.relicaPrimo && props.relicaPrimo.mt_total_cil != null) {
        return Number(props.relicaPrimo.mt_total_cil);
    }
    return 0;
});

// Reliquat = Prix total − Déjà réglé (ou montant fixe 7 800 F CFA pour les motos RELICA-PRIMO uniquement)
// Pour PRIMO-ESPECE, on facture toujours le prix total (pas de reliquat)
const relicaReliquat = computed(() => {
    if (relicaGenreCategory.value === 'moto' && !isPrimoEspece) {
        // RELICA-PRIMO moto : reliquat fixe de 7 800 F CFA
        return 7800;
    }
    // PRIMO-ESPECE (toute catégorie) ou RELICA-PRIMO non-moto : prix total − déjà réglé
    return Math.max(0, RELICA_PRIX[relicaGenreCategory.value] - relicaDejaRegle.value);
});

const items = computed(() => {
    // ── Cas RELICA-PRIMO : afficher le détail reliquat ──
    if (isRelica) {
        // Le montant encaissé prime sur le tarif courant.
        const reliquat = paiementNet.value ?? relicaReliquat.value;

        if (relicaGenreCategory.value === 'moto') {
            return [
                { name: `Reliquat à encaisser (${relicaGenreLabel.value} / ${relicaPrefix.value})`, amount: reliquat, isReliquat: true },
            ];
        }
        return [
            { name: `Prix total TTC — ${relicaGenreLabel.value}`, amount: relicaPrixTotal.value, isReliquat: false },
            { name: `Déjà réglé (Dossiers ${relicaPrefix.value})`, amount: -relicaDejaRegle.value, isReliquat: false },
            { name: `Reliquat à encaisser (${relicaGenreLabel.value} / ${relicaPrefix.value})`, amount: reliquat, isReliquat: true },
        ];
    }

    // ── Cas PRIMO-ESPECE : ventilation PRIM1 / PRIM2 / PRIM3 ──
    // Même logique que RELICA-PRIMO mais rien n'a été déjà réglé, on facture la totalité.
    if (isPrimoEspece) {
        // Montants réellement encaissés (reçus historiques inclus).
        if (hasPaiementLignes.value) {
            return [
                ...lignesVersItems(paiementLignes.value),
                ...lignesVersItems(paiementLignesLier.value),
            ];
        }

        if (paiementDescription.value.length) {
            return descriptionVersItems(paiementDescription.value);
        }

        const primoItem = (props.detailTypeServices || [])
            .find(item => Array.isArray(item?.ventilation) && item.ventilation.length);

        if (primoItem) {
            return primoItem.ventilation.map(ligne => ({
                name: `${ligne.libelle} (${ligne.code})`,
                amount: Number(ligne.montant_ttc) || 0,
            }));
        }

        return [
            { name: `Prix total TTC — ${relicaGenreLabel.value}`, amount: relicaPrixTotal.value, isReliquat: true }
        ];
    }

    // ── Cas MODIFICATION : 15 000 (status_pose_plaque = 1) ou 25 000 (status_pose_plaque = 2) ──
    if (isModification) {
        const plaqueLabel = props.dossier?.status_pose_plaque == 1 ? 'Plaque non posée' : 'Plaque posée';
        return [
            { name: `Modification (${plaqueLabel})`, amount: paiementNet.value ?? modificationMontantHT.value, isReliquat: true }
        ];
    }

    // ── Cas standard ──
    // Montants réellement encaissés (reçus historiques inclus).
    if (hasPaiementLignes.value) {
        return [
            ...lignesVersItems(paiementLignes.value),
            ...lignesVersItems(paiementLignesLier.value),
        ];
    }

    // Repli : détail du paiement (avant ventilation), montants encaissés.
    if (paiementDescription.value.length || paiementDescriptionLier.value.length) {
        return descriptionVersItems([
            ...paiementDescription.value,
            ...paiementDescriptionLier.value,
        ]);
    }

    const result = [];
    if (hasTriggerService && props.autre_facturation) {
        result.push({
            name: props.autre_facturation.nom,
            amount: parseFloat(props.autre_facturation.montant),
        });
    }
    result.push(
        ...tableauFusionne.flatMap(item => {
            if (Array.isArray(item?.ventilation) && item.ventilation.length) {
                return item.ventilation.map(ligne => ({
                    name: `${ligne.libelle} (${ligne.code})`,
                    amount: Number(ligne.montant_ttc) || 0,
                }));
            }

            return [{
                name: item.element_facturation,
                amount: parseFloat(item?.montant || 0),
            }];
        })
    );
    return result;
});

// Total HT
const totalHT = computed(() =>
    (items.value || []).reduce((acc, item) => acc + item.amount, 0)
);

// Total TTC :
// - RELICA-PRIMO : uniquement reliquat + timbre (les lignes Prix total et Déjà réglé sont informatives)
// - PRIMO-ESPECE : total HT + timbre
// - MODIFICATION : total HT + timbre (15 000 + 100 = 15 100 ou 25 000 + 100 = 25 100)
// - Standard     : somme HT + timbre
const totalTTC = computed(() => {
    // Montant réellement encaissé (principal + dossier lié) : prioritaire.
    if (paiementMontant.value !== null) return paiementMontant.value;

    if (isRelica) return relicaReliquat.value + 100;
    if (isPrimoEspece) return totalHT.value + 100;
    if (isModification) return totalHT.value + 100;
    return totalHT.value + 100;
});

// Formatage des montants
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

function formatedDate(dateStr) {
    const date = new Date(dateStr);

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');

    return `${day}${month}`;
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
    Demandeur_Nom: props.dossier.demandeur_nom,
    Demandeur_Prenom: props.dossier.demandeur_prenom,
    Demandeur_Telephone: props.dossier.demandeur_telephone,
    Demandeur_Piece_Type: props.dossier.demandeur_type_piece,
    Demandeur_Piece_Numero: props.dossier.demandeur_numero_piece,
};

async function imprimer() {
    await nextTick();

    let contenu = zoneImpression.value.innerHTML;

    const canvas = zoneImpression.value.querySelector('.qrcode-print');
    if (canvas && canvas.toDataURL) {
        const qrImg = `<img src="${canvas.toDataURL()}" alt="QR Code" style="width: 80px; height: 80px;" />`;
        contenu = contenu.replace(/<canvas[^>]*class="qrcode-print"[^>]*><\/canvas>/, qrImg);
    }

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

    const printWindow = window.open('', '', 'width=1000,height=800');
    printWindow.document.write(htmlContent);
    printWindow.document.close();
    printWindow.focus();
}
</script>



<script>

import Main from '/resources/js/Pages/Main.vue';
export default {
    layout: Main,
};
</script>
