<script setup>
import { ref, computed } from "vue";
import {
    Table,
    TableBody,
    TableCell,
    TableFooter,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from "@/components/ui/dialog";
import { FileDown, ListTree } from "lucide-vue-next";

const props = defineProps({
    paiements: {
        type: Array,
        required: true,
        default: () => []
    },
    selectedDate: {
        type: String,
        required: true
    },
    montantOuverture: {
        type: Number,
        default: 0
    }
});

const searchChrono = ref("");
const detailOpen = ref(false);
const selectedPaiement = ref(null);

const detailLignes = computed(() => selectedPaiement.value?.ventilation_lignes || []);
const detailTotalTtc = computed(() =>
    detailLignes.value.reduce((sum, ligne) => sum + Number(ligne.montant_ttc || 0), 0)
);
const detailTotalHt = computed(() =>
    detailLignes.value.reduce((sum, ligne) => sum + Number(ligne.montant_ht || 0), 0)
);
const detailTimbre = computed(() => Number(selectedPaiement.value?.timbre || 0));
const detailTotal = computed(() => detailTotalTtc.value + detailTimbre.value);

const openDetail = (paiement) => {
    selectedPaiement.value = paiement;
    detailOpen.value = true;
};

const filteredPaiements = computed(() => {
    if (!searchChrono.value) return props.paiements;
    const q = searchChrono.value.toLowerCase();
    return props.paiements.filter(p => 
        p.dossier?.num_chrono?.toLowerCase().includes(q)
    );
});

const totalMontant = computed(() => props.paiements.reduce((sum, p) => sum + Number(p.montant || 0), 0));
const totalTimbres = computed(() => props.paiements.reduce((sum, p) => sum + Number(p.timbre || 0), 0));
const totalDossiers = computed(() => totalMontant.value - totalTimbres.value);
const soldeCaisse = computed(() => totalMontant.value + props.montantOuverture);

const exportToExcel = async () => {
    if (!window.XLSX) {
        try {
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js';
                script.onload = resolve;
                script.onerror = reject;
                document.head.appendChild(script);
            });
        } catch (err) {
            console.error(err);
            alert("Impossible de charger la bibliothèque d'export Excel.");
            return;
        }
    }
    const XLSX = window.XLSX;

    const headers = ["#", "Client", "N° Chrono", "Référence", "Immatriculation", "Montant Dossier", "Timbre", "Heure"];
    const rows = filteredPaiements.value.map((p, i) => [
        i + 1,
        `${p.dossier?.r_dossier_client?.nom || ''} ${p.dossier?.r_dossier_client?.prenom || ''}`,
        p.dossier?.num_chrono || 'N/A',
        p.reference,
        p.dossier?.r_dossier_vehicule?.num_immatriculation || 'N/A',
        Number(p.montant) - Number(p.timbre || 0),
        Number(p.timbre || 0),
        new Date(p.created_at).toLocaleTimeString()
    ]);

    // Add totals
    rows.push([]);
    rows.push(["", "FONDS DE CAISSE", "", "", "", props.montantOuverture, "", ""]);
    rows.push(["", "RECETTE EMUCI", "", "", "", totalDossiers.value, "", ""]);
    rows.push(["", "RECETTE TIMBRES", "", "", "", "", totalTimbres.value, ""]);
    rows.push(["", "SOLDE GLOBAL", "", "", "", "", "", soldeCaisse.value]);

    const worksheet = XLSX.utils.aoa_to_sheet([headers, ...rows]);
    const workbook = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(workbook, worksheet, "Point de Caisse");

    XLSX.writeFile(workbook, `point_caisse_${props.selectedDate}.xlsx`);
};
</script>

<template>
    <div class="rounded-xl border bg-card text-card-foreground shadow">
        <!-- Header Style Shadcn -->
        <div class="flex flex-col space-y-1.5 p-6 border-b">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold leading-none tracking-tight">
                        Point de Caisse du : {{ selectedDate }}
                    </h3>
                  
                </div>
                
                <div class="flex items-center gap-4">
                    <!-- Export Actions -->
                    <div class="flex items-center border rounded-md overflow-hidden bg-background">
                        <Button variant="ghost" size="sm" class="h-9 px-3 rounded-none" title="Exporter en Excel" @click="exportToExcel">
                            <FileDown />
                            <span class="text-xs font-medium"> Exporter en Excel</span>
                        </Button>
                    </div>

                    <div class="relative w-64">
                        <i class="pi pi-search absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground"></i>
                        <Input 
                            v-model="searchChrono" 
                            placeholder="N° Chrono..." 
                            class="pl-8 h-9"
                        />
                        <button 
                            v-if="searchChrono" 
                            @click="searchChrono = ''" 
                            class="absolute right-2.5 top-2.5 text-muted-foreground hover:text-foreground"
                        >
                            <i class="pi pi-times "></i>
                        </button>
                    </div>
                    <Badge variant="secondary" class="h-9 px-4 rounded-md">
                        {{ filteredPaiements.length }} Dossier(s)
                    </Badge>
                </div>
            </div>
        </div>
        
        <!-- Table Responsive Container -->
        <div class="relative w-full overflow-auto max-h-[650px]">
            <Table>
                <TableHeader class="bg-muted/50 sticky top-0 z-10 ">
                    <TableRow>
                        <TableHead class="w-[50px] text-center">#</TableHead>
                        <TableHead>Client </TableHead>
                        <TableHead>N° Chrono</TableHead>
                        <TableHead>Référence</TableHead>
                        <TableHead class="text-center">Immatriculation</TableHead>
                        <TableHead class="text-right">Dossier</TableHead>
                        <TableHead class="text-right">Timbre</TableHead>
                        <TableHead class="text-right pr-6">Heure</TableHead>
                        <TableHead class="text-center pr-6">Détail</TableHead>
                    </TableRow>
                </TableHeader>

                <TableBody>
                    <TableRow v-for="(p, index) in filteredPaiements" :key="p.id" class="group">
                        <TableCell class=" text-muted-foreground text-center ">{{ index + 1 }}</TableCell>
                        <TableCell>
                            <div class="py-3">
                                {{ p.dossier?.r_dossier_client?.nom }} {{ p.dossier?.r_dossier_client?.prenom }}
                            </div>
                        </TableCell>
                        <TableCell>
                                               <Badge variant="warning"  class=" uppercase tracking-tighter ">
                                {{ p.dossier?.num_chrono || '--------' }}
                            </Badge>
                         
                        </TableCell>
                        <TableCell class="  text-muted-foreground">{{ p.reference }}</TableCell>
                        <TableCell class="text-center">
                           
                            <Badge variant="success"  class=" uppercase tracking-tighter ">
                                {{ p.dossier?.r_dossier_vehicule?.num_immatriculation || '--------' }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right font-medium">
                            {{ (Number(p.montant) - Number(p.timbre || 0)).toLocaleString() }} F
                        </TableCell>
                        <TableCell class="text-right text-orange-500 font-medium">
                            {{ Number(p.timbre || 0).toLocaleString() }} F
                        </TableCell>
                        <TableCell class="text-right text-muted-foreground  pr-6">
                            {{ new Date(p.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                        </TableCell>
                        <TableCell class="text-center pr-6">
                            <Button variant="outline" size="sm" class="h-8 gap-1"
                                :disabled="!(p.ventilation_lignes || []).length" @click="openDetail(p)">
                                <ListTree class="w-3.5 h-3.5" />
                                Détail
                            </Button>
                        </TableCell>
                    </TableRow>

                    <!-- Empty State -->
                    <TableRow v-if="filteredPaiements.length === 0">
                        <TableCell colspan="9" class="h-48 text-center">
                            <div class="flex flex-col items-center justify-center text-muted-foreground">
                                <i class="pi pi-inbox text-3xl mb-2"></i>
                                <p>Aucun résultat pour cette recherche</p>
                                <Button variant="link" @click="searchChrono = ''" class="mt-2 ">
                                    Réinitialiser les filtres
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>

                <!-- <TableFooter class="bg-muted/50 font-bold sticky bottom-0">
                    <TableRow>
                        <TableCell colspan="5" class="text-right uppercase  tracking-wider">Totaux</TableCell>
                        <TableCell class="text-right">{{ totalDossiers.toLocaleString() }} F</TableCell>
                        <TableCell class="text-right text-orange-500">{{ totalTimbres.toLocaleString() }} F</TableCell>
                        <TableCell class="text-right pr-6 text-primary">{{ totalMontant.toLocaleString() }} F</TableCell>
                    </TableRow>
                </TableFooter> -->
            </Table>
        </div>

        <!-- Section Solde Caisse -->
        <div class="flex items-center p-6 bg-primary text-primary-foreground rounded-b-xl">
            <div class="flex-1 grid grid-cols-3 gap-4">
                <div class="space-y-1">
                    <p class=" uppercase opacity-70 tracking-widest">Fond de Caisse</p>
                    <p class="text-xl font-bold">{{ montantOuverture.toLocaleString() }} F</p>
                </div>
                <div class="space-y-1 border-l border-primary-foreground/20 pl-4">
                    <p class=" uppercase opacity-70 tracking-widest">Recettes EMUCI</p>
                    <p class="text-xl font-bold">+ {{ totalDossiers.toLocaleString() }} F</p>
                </div>
                 <div class="space-y-1 border-l border-primary-foreground/20 pl-4">
                    <p class=" uppercase opacity-70 tracking-widest">Recettes Timbre</p>
                    <p class="text-xl font-bold">+ {{ totalTimbres.toLocaleString() }} F</p>
                </div>
            </div>
            <div class="text-right">
                <p class=" uppercase opacity-70 tracking-widest">Solde Finale</p>
                <p class="text-3xl font-black">{{ soldeCaisse.toLocaleString() }} F</p>
            </div>
        </div>

        <!-- Modal : détail ventilé du dossier -->
        <Dialog v-model:open="detailOpen">
            <DialogContent class="max-w-3xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <ListTree class="w-4 h-4" />
                        Détail ventilé
                        <Badge variant="warning" class="uppercase tracking-tighter">
                            {{ selectedPaiement?.dossier?.num_chrono || '--------' }}
                        </Badge>
                    </DialogTitle>
                    <DialogDescription>
                        {{ selectedPaiement?.dossier?.r_dossier_client?.nom }}
                        {{ selectedPaiement?.dossier?.r_dossier_client?.prenom }}
                        <span v-if="selectedPaiement?.dossier?.r_dossier_vehicule?.num_immatriculation"
                            class="ml-2 text-muted-foreground">
                            — {{ selectedPaiement.dossier.r_dossier_vehicule.num_immatriculation }}
                        </span>
                    </DialogDescription>
                </DialogHeader>

                <div class="rounded-lg border overflow-hidden">
                    <Table>
                        <TableHeader class="bg-muted/50">
                            <TableRow>
                                <TableHead>Article</TableHead>
                                <TableHead>Libellé</TableHead>
                                <TableHead>Fait générateur</TableHead>
                                <TableHead class="text-right">HT</TableHead>
                                <TableHead class="text-right pr-4">TTC</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(ligne, index) in detailLignes" :key="index">
                                <TableCell>
                                    <Badge variant="secondary" class="font-mono tracking-tighter">
                                        {{ ligne.article_code }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-sm">{{ ligne.libelle || '—' }}</TableCell>
                                <TableCell class="text-xs text-muted-foreground">
                                    {{ ligne.fait_generateur || '—' }}
                                </TableCell>
                                <TableCell class="text-right font-mono text-sm">
                                    {{ Number(ligne.montant_ht || 0).toLocaleString() }} F
                                </TableCell>
                                <TableCell class="text-right font-mono text-sm pr-4">
                                    {{ Number(ligne.montant_ttc || 0).toLocaleString() }} F
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="!detailLignes.length">
                                <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                                    Aucune ligne de ventilation pour ce paiement.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>

                <div class="flex flex-col gap-1 text-sm">
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Total HT</span>
                        <span class="font-mono">{{ detailTotalHt.toLocaleString() }} F</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Total TTC services</span>
                        <span class="font-mono">{{ detailTotalTtc.toLocaleString() }} F</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-muted-foreground">Timbre</span>
                        <span class="font-mono text-orange-500">{{ detailTimbre.toLocaleString() }} F</span>
                    </div>
                    <div class="flex justify-between border-t pt-2 font-semibold">
                        <span>Total encaissé</span>
                        <span class="font-mono">{{ detailTotal.toLocaleString() }} F</span>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>


