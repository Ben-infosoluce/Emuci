<template>



    <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
        <!-- Titre -->
        <h4 class="text-2xl font-bold tracking-tight">
            Immatriculations
        </h4>

        <!-- Bouton Nouveau -->
        <div class="flex items-center space-x-2">
            <Link :href="route('show.new.pdc.immatriculation')">
            <BoutonNouveau />
            </Link>
        </div>
    </div>

    <div class="rounded-lg dark:border-gray-700">
        <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
            <Card class="h-full flex flex-col">
                <div class="space-y-4 p-8">
                    <!-- Filtres -->
                    <div class="flex flex-wrap gap-4 items-center justify-between">
                        <!-- Recherche -->
                        <Input_search v-model="form.search_data" placeholder="Rechercher un véhicule" class="w-full "
                            @input="search" />
                        <!-- Statut -->
                        <FormField v-slot="{ componentField }" name="filterstatut">
                            <FormItem>
                                <FormControl>
                                    <Select v-model="form.statut" @update:modelValue="search">
                                        <SelectTrigger class="w-40">
                                            <SelectValue placeholder="statut" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Statut</SelectLabel>
                                                <SelectItem value="0">Tous </SelectItem>
                                                <SelectItem value="1">En attente</SelectItem>
                                                <SelectItem value="2">Validé</SelectItem>
                                                <SelectItem value="3">Refusé</SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </FormControl>
                                <FormMessage />
                            </FormItem>
                        </FormField>

                        <!-- Dates -->
                        <!-- <DateRangePicker /> -->
                        <DateRangePicker v-model="form.dateRange"
                            @update:start="val => { form.date_start = val; search(); }"
                            @update:end="val => { form.date_end = val; search(); }" />
                    </div>

                    <!-- Tableau -->
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Vin</TableHead>
                                <TableHead>Véhicule</TableHead>
                                <TableHead>Type de service</TableHead>
                                <TableHead>Statut</TableHead>
                                <TableHead class="text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="(data, index) in props.dossiers.data" :key="index">
                                <TableCell>{{ index + 1 + (currentPage - 1) * perPage }}</TableCell>
                                <TableCell>{{ data?.r_dossier_vehicule?.vin || '—' }}</TableCell>
                                <TableCell>{{ data?.r_dossier_vehicule?.marque || '—' }}</TableCell>
                                <TableCell>
                                    {{ data?.r_dossier_services?.r_service_types?.[0]?.nom_type_service || '—'
                                    }}
                                </TableCell>
                                <TableCell>
                                    <Badge
                                        :variant="data?.statut === 1 ? 'warning' : data?.statut === 2 ? 'success' : data?.statut === 3 ? 'destructive' : 'secondary'">
                                        {{
                                            data?.statut === 1
                                                ? 'En attente'
                                                : data?.statut === 2
                                                    ? 'Validé'
                                                    : data?.statut === 3
                                                        ? 'Refusé'
                                                        : 'Inconnu'
                                        }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <DropdownMenu>
                                        <DropdownMenuTrigger as-child>
                                            <Button variant="ghost" size="icon">
                                                <MoreVertical class="w-4 h-4" />
                                            </Button>
                                        </DropdownMenuTrigger>
                                        <DropdownMenuContent>
                                            <DropdownMenuItem>Modifier</DropdownMenuItem>
                                            <DropdownMenuItem class="text-red-500">Annuler</DropdownMenuItem>
                                            <DropdownMenuItem>Lorem</DropdownMenuItem>
                                        </DropdownMenuContent>
                                    </DropdownMenu>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                    <!-- pagination -->
                    <Pagination :links="props.dossiers.links" :next="props.dossiers.next_page_url"
                        :prev="props.dossiers.prev_page_url" />
                </div>
            </Card>
        </main>
    </div>


</template>

<script setup>

import { ref, computed, watch } from 'vue';
import { router, useForm } from "@inertiajs/vue3";
import { Input_search } from '@/components/ui/Input_search';
import { Search } from 'lucide-vue-next'
import { Deferred } from '@inertiajs/vue3';
import { useThrottleFn } from "@vueuse/core";
const props = defineProps({
    dossiers: Object,
    filtres: Object,
});

const currentPage = ref(1);
const perPage = ref(10);

const form = useForm({
    search_data: props.filtres?.search_data ?? '',
    statut: props.filtres?.statut ?? '',
    date_start: props.filtres?.date_start ?? '',
    date_end: props.filtres?.date_end ?? '',
});

const search = useThrottleFn(() => {
    currentPage.value = 1;
    router.get(route("show.pdc.immatriculation"), {
        search_data: form.search_data,
        statut: form.statut,
        date_start: form.date_start,
        date_end: form.date_end,
        page: currentPage.value
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}, 1000);

// Auto-recherche sur input texte
watch(() => form.search_data, () => {
    search();
});

</script>

<script>
import Main from '/resources/js/Pages/Main.vue';
import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Card } from '@/components/ui/card'
import DateRangePicker from '@/components/ui/DateRangePicker.vue'
import Pagination from '@/components/Pagination.vue';
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { MoreVertical } from 'lucide-vue-next'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import {
    FormControl,
    FormField,
    FormItem,
    FormMessage,
} from '@/components/ui/form'
import BoutonNouveau from "/resources/js/components/BoutonNouveau.vue";

export default {
    layout: Main,
    components: { Pagination },
};
</script>
