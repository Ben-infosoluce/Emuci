<template>
    <div
        class="p-4 md:p-8 max-w-4xl mx-auto border rounded shadow bg-white space-y-4 text-sm"
    >
        <!-- En-tête -->
        <div class="flex justify-between items-center">
            <Button>REÇU DE PAIEMENT</Button>
            <div>Date: 22/08/2025 - 16h20</div>
        </div>

        <!-- Informations principales -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border p-3 rounded">
                <h3 class="font-semibold border-b mb-1">
                    Informations du demandeur
                </h3>
                <p><strong>Nom et prénom:</strong> Franck KOUASSI</p>
                <p><strong>Adresse du demandeur:</strong> Angré Djibi</p>
            </div>

            <div class="border p-3 rounded">
                <h3 class="font-semibold border-b mb-1">Autres informations</h3>
                <p><strong>N° Chrono:</strong> CIJJKJGHJVHV</p>
                <p><strong>N° Châssis:</strong> 7477TDDDFVUD7DGD</p>
                <p><strong>Véhicule:</strong> Mercedes C300</p>
                <p><strong>Règlement:</strong> ESPECE</p>
            </div>
        </div>

        <!-- Détails de facture -->
        <div>
            <p><strong>Facture n°:</strong> 6725</p>
        </div>

        <!-- Formalités et soldes -->
        <div class="border rounded p-3">
            <h3 class="font-semibold text-gray-600 mb-2">
                Formalités et détails de solde
            </h3>

            <div class="mb-3">
                <h4 class="font-bold uppercase text-xs text-gray-700">
                    REIMMATRICULATION ORDINAIRE
                </h4>
                <div class="grid grid-cols-2">
                    <span>Plaque</span><span class="text-right">22 600F</span>
                    <span>Carte grise</span
                    ><span class="text-right">22 000F</span> <span>Police</span
                    ><span class="text-right">2 000F</span>
                    <span>Ministère</span><span class="text-right">2 000F</span>
                </div>
            </div>

            <div>
                <h4 class="font-bold uppercase text-xs text-gray-700">
                    MUTATION
                </h4>
                <div class="grid grid-cols-2">
                    <span>Frais de mutation QUIPUX</span
                    ><span class="text-right">20 000F</span>
                    <span>Frais de mutation EMU-CI</span
                    ><span class="text-right">5 200F</span>
                </div>
            </div>
        </div>

        <!-- Totaux -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 text-sm">
            <div></div>
            <div class="md:col-span-2">
                <div class="flex justify-between">
                    <span>PRIX HT:</span>
                    <span>60 516F</span>
                </div>
                <div class="flex justify-between">
                    <span>TVA:</span>
                    <span>13 284F</span>
                </div>
                <div class="flex justify-between font-bold">
                    <span>PRIX TTC:</span>
                    <span>73 800F</span>
                </div>
            </div>
        </div>

        <!-- Infos bas de page -->
        <div
            class="flex flex-col md:flex-row justify-between items-start text-xs pt-4 border-t pt-2"
        >
            <div><strong>Nom du caissier:</strong> Joanna Koffi</div>
            <div class="pt-2 md:pt-0 text-right">
                <p class="font-semibold">Accès de connexion</p>
                <p>Nom d'utilisateur: <strong>franck028</strong></p>
                <p>Mot de passe: <strong>UH76RUYD</strong></p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Button } from "@/components/ui/button";
// Aucun JS requis pour ce composant statique
</script>
