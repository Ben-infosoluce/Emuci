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
import { FileDown }  from "lucide-vue-next";

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

const exportToCSV = () => {
    const headers = ["#", "Client", "N° Chrono", "Référence", "Immatriculation", "Montant Dossier", "Timbre", "Heure"];
    const rows = filteredPaiements.value.map((p, i) => [
        i + 1,
        `"${p.dossier?.r_dossier_client?.nom || ''} ${p.dossier?.r_dossier_client?.prenom || ''}"`,
        `"${p.dossier?.num_chrono || 'N/A'}"`,
        `"${p.reference}"`,
        `"${p.dossier?.r_dossier_vehicule?.num_immatriculation || 'N/A'}"`,
        Number(p.montant) - Number(p.timbre || 0),
        Number(p.timbre || 0),
        new Date(p.created_at).toLocaleTimeString()
    ]);

    // Add totals
    rows.push([]);
    rows.push(["", "TOTAL DOSSIERS", "", "", "", totalDossiers.value, "", ""]);
    rows.push(["", "TOTAL TIMBRES", "", "", "", "", totalTimbres.value, ""]);
    rows.push(["", "SOLDE GLOBAL", "", "", "", "", "", soldeCaisse.value]);

    const csvContent = [headers.join(","), ...rows.map(r => r.join(","))].join("\n");
    const blob = new Blob(["\ufeff" + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.setAttribute("href", url);
    link.setAttribute("download", `point_caisse_${props.selectedDate}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
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
                        <Button variant="ghost" size="sm" class="h-9 px-3 rounded-none" title="Exporter en CSV" @click="exportToCSV">
                            <FileDown />
                            <span class="text-xs font-medium"> Exporter en CSV</span>
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
                    </TableRow>

                    <!-- Empty State -->
                    <TableRow v-if="filteredPaiements.length === 0">
                        <TableCell colspan="8" class="h-48 text-center">
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
                    <p class=" uppercase opacity-70 tracking-widest">Total Recettes</p>
                    <p class="text-xl font-bold">+ {{ totalMontant.toLocaleString() }} F</p>
                </div>
                 <div class="space-y-1 border-l border-primary-foreground/20 pl-4">
                    <p class=" uppercase opacity-70 tracking-widest">Total Timbre</p>
                    <p class="text-xl font-bold">+ {{ totalTimbres.toLocaleString() }} F</p>
                </div>
            </div>
            <div class="text-right">
                <p class=" uppercase opacity-70 tracking-widest">Solde Finale</p>
                <p class="text-3xl font-black">{{ soldeCaisse.toLocaleString() }} F</p>
            </div>
        </div>
    </div>
</template>


