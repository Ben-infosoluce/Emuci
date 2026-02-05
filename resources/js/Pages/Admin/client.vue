<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <h4 class="text-2xl font-bold tracking-tight">Liste des clients</h4>
            <Toaster position="top-right" />
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <div class="space-y-4 p-8">

                        <!-- Filtres -->
                        <div class="flex flex-wrap gap-4 items-center justify-between">

                            <Input_search v-model="form.search_data" @update:modelValue="onFilterChange"
                                placeholder="Rechercher un client..." class="w-full" />

                            <!-- <Select v-model="form.statut" @update:modelValue="onFilterChange">
                                <SelectTrigger class="w-40">
                                    <SelectValue placeholder="Statut" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Statut</SelectLabel>
                                        <SelectItem value="">Tous</SelectItem>
                                        <SelectItem value="1">En attente</SelectItem>
                                        <SelectItem value="2">Validé</SelectItem>
                                        <SelectItem value="3">Refusé</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select> -->
                        </div>

                        <!-- Tableau -->
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Nom du client</TableHead>
                                    <TableHead>Adresse</TableHead>
                                    <TableHead>District</TableHead>
                                    <TableHead>Date de validité</TableHead>
                                    <TableHead>Statut</TableHead>
                                </TableRow>
                            </TableHeader>

                            <TableBody>
                                <!-- Skeleton -->
                                <template v-if="loading">
                                    <TableRow v-for="i in 10" :key="i">
                                        <TableCell>
                                            <Skeleton class="h-4 w-8" />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-24" />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-20" />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-28" />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-24" />
                                        </TableCell>
                                        <TableCell>
                                            <Skeleton class="h-4 w-24" />
                                        </TableCell>
                                    </TableRow>
                                </template>

                                <!-- Lignes -->
                                <template v-else>
                                    <TableRow v-for="(client, index) in props.clients" :key="index">
                                        <TableCell>{{ client.id }}</TableCell>
                                        <TableCell>{{ client.nom }} {{ client.prenom }}</TableCell>
                                        <TableCell>{{ client.adresse || "—" }}</TableCell>
                                        <TableCell>{{ client.district_client || "—" }}</TableCell>
                                        <TableCell>{{ client.validite || "—" }}</TableCell>
                                        <TableCell>
                                            <Badge class="m-2" :variant="client.statut === 1
                                                ? 'success'
                                                : client.statut === 2
                                                    ? 'error'
                                                    : 'secondary'">
                                                {{
                                                    client.statut === 1
                                                        ? "Actif"
                                                        : client.statut === 2
                                                            ? "Inactif"
                                                            : "Inconnu"
                                                }}
                                            </Badge>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>

                        <!-- Pagination SHADCN -->
                        <!-- Pagination -->
                        <div v-if="meta" class="flex justify-end mt-6 space-x-3">
                            <Pagination v-if="meta" v-slot="{ page }" :items-per-page="meta.per_page"
                                :total="meta.total" :default-page="meta.current_page" @update:page="changePage"
                                class="mt-6 flex justify-center">
                                <PaginationContent v-slot="{ items }">
                                    <PaginationPrevious />

                                    <template v-for="(item, index) in items" :key="index">
                                        <PaginationItem v-if="item.type === 'page'" :value="item.value"
                                            :is-active="item.value === page">
                                            {{ item.value }}
                                        </PaginationItem>
                                    </template>

                                    <PaginationEllipsis />

                                    <PaginationNext />
                                </PaginationContent>
                            </Pagination>
                        </div>

                    </div>
                </Card>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";

import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from "@/components/ui/pagination";

const props = defineProps({
    clients: Array,
    meta: Object
});

const loading = ref(false);

// Pré-remplir les filtres depuis l’URL
const params = new URLSearchParams(window.location.search);

const form = useForm({
    search_data: params.get("search_data") || "",
    statut: params.get("statut") || "",
});

// Filtres
const onFilterChange = () => {
    loading.value = true;

    router.get(route("client.liste"), form, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    });
};

// Pagination
const changePage = (page) => {
    loading.value = true;

    router.get(route("client.liste"), {
        ...form.data(),
        page: page
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => loading.value = false
    });
};
</script>



<script>
import { ref, onMounted, watch } from "vue";
import { Card, CardContent } from "@/components/ui/card";
import Main from "/resources/js/Pages/Main.vue";
import DateRangePicker from "@/components/ui/DateRangePicker.vue";
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination'
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from "@/components/ui/table";
import { Input } from "@/components/ui/input";
import { Badge } from "@/components/ui/badge";
import { MoreVertical } from "lucide-vue-next";
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from "@/components/ui/select";
// import { onMounted, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
// import { Input } from "@/components/ui/input";
// import Pagination from "@/components/Pagination.vue";
import { Input_search } from "@/components/ui/Input_search";
import { Plus } from "lucide-vue-next";
import { Skeleton } from "@/components/ui/skeleton";
import { Toaster, toast } from "vue-sonner";
export default {
    layout: Main,
    // components: { Pagination },
};
</script>
