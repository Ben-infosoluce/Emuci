<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-2xl font-bold tracking-tight">Liste des entités</h4>
            <Toaster position="top-right" />

            <!-- Bouton Nouveau -->
            <div class="flex items-center space-x-2">
                <AlertDialog :open="createModal">
                    <AlertDialogTrigger as-child>
                        <Button @click="openCreateModal">
                            <Plus class="w-4 h-4 mr-2" /> Nouvelle entités
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Nouvelle entité</AlertDialogTitle>
                            <AlertDialogDescription>
                                <div class="space-y-8 my-8">
                                    <Input v-model="nom" placeholder="Nom de l'entité" />
                                </div>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter class="flex flex-row gap-2">
                            <AlertDialogCancel @click="createModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleCreate">
                                    Valider
                                    <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                </Button>
                            </div>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <div class="space-y-4 p-8">
                        <!-- Filtres -->
                        <div class="flex flex-wrap gap-4 items-center justify-between">
                            <!-- Recherche -->
                            <Input_search v-model="form.search_data" @update:modelValue="onFilterChange"
                                placeholder="Rechercher par VIN..." class="w-full" />
                            <!-- Statut -->
                            <Select v-model="form.statut" @update:modelValue="onFilterChange">
                                <SelectTrigger class="w-40">
                                    <SelectValue placeholder="Statut" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectGroup>
                                        <SelectLabel>Statut</SelectLabel>
                                        <SelectItem value="0">Tous</SelectItem>
                                        <SelectItem value="1">En attente</SelectItem>
                                        <SelectItem value="2">Validé</SelectItem>
                                        <SelectItem value="3">Refusé</SelectItem>
                                    </SelectGroup>
                                </SelectContent>
                            </Select>

                            <!-- Dates -->
                            <!-- <DateRangePicker /> -->
                            <DateRangePicker v-model="form.dateRange" @update:start="
                                (val) => {
                                    form.date_start = val;
                                    onFilterChange();
                                }
                            " @update:end="
                                (val) => {
                                    form.date_end = val;
                                    onFilterChange();
                                }
                            " />
                        </div>

                        <!-- Tableau -->
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Nom de l'entité</TableHead>
                                    <TableHead>Date de création</TableHead>
                                    <TableHead>Date de modification</TableHead>
                                    <TableHead>Statut</TableHead>
                                    <TableHead class="text-right">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <!-- Skeleton -->
                                <template v-if="loading">
                                    <TableRow v-for="i in 10" :key="i">
                                        <TableCell class="p-4">
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
                                        <TableCell>
                                            <Skeleton class="h-4 w-20" />
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <Skeleton class="h-4 w-10 ml-auto" />
                                        </TableCell>
                                    </TableRow>
                                </template>
                                <template v-else>
                                    <TableRow v-for="(entite, index) in props.entites" :key="index">
                                        <TableCell>{{ entite.id }}</TableCell>
                                        <TableCell>{{
                                            entite?.nom || "—"
                                        }}</TableCell>
                                        <TableCell>{{
                                            entite?.created_at || "—"
                                        }}</TableCell>
                                        <TableCell>
                                            {{ entite?.updated_at || "—" }}
                                        </TableCell>

                                        <TableCell>
                                            <Badge :variant="entite?.statut === 1
                                                ? 'success'
                                                : entite?.statut === 2
                                                    ? 'error'
                                                    : 'secondary'
                                                ">
                                                {{
                                                    entite?.statut === 1
                                                        ? "Actif"
                                                        : entite?.statut === 2
                                                            ? "Inactif"
                                                            : "Inconnu"
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
                                                    <DropdownMenuItem @click="
                                                        openEditModal(
                                                            entite.id
                                                        )
                                                        ">
                                                        Modifier
                                                    </DropdownMenuItem>

                                                    <DropdownMenuItem class="text-red-500" @click="
                                                        openDeleteModal(
                                                            entite.id
                                                        )
                                                        ">Supprimer</DropdownMenuItem>
                                                    <DropdownMenuItem @click="
                                                        openStatutModal(
                                                            entite.statut,
                                                            entite.id
                                                        )
                                                        ">Statut</DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                </template>
                            </TableBody>
                        </Table>
                        <!-- Pagination -->
                        <Pagination v-if="meta" :meta="meta" @page-change="changePage" />
                    </div>
                </Card>
            </main>
            <!-- Edit Modal -->
            <div class="flex items-center space-x-2">
                <AlertDialog :open="editModal">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Modifier l'entité</AlertDialogTitle>
                            <AlertDialogDescription>
                                <div class="space-y-8 my-8">
                                    <Input v-model="nom" placeholder="Nom de l'entité" />
                                </div>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter class="flex flex-row gap-2">
                            <AlertDialogCancel @click="editModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleEdit">
                                    Valider
                                    <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                </Button>
                            </div>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>

            <!-- Delete Modal -->
            <div class="flex items-center space-x-2">
                <AlertDialog :open="deleteModal">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Voulez-vous vraiment supprimer cette entité ?
                            </AlertDialogTitle>

                            <AlertDialogDescription>
                                <p>Cette action est irréversible.</p>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter class="flex flex-row gap-2">
                            <AlertDialogCancel @click="deleteModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleDelete" variant="destructive">
                                    Supprimer
                                    <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                </Button>
                            </div>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>

            <!-- Statut Modal -->
            <div class="flex items-center space-x-2">
                <AlertDialog :open="statutModal">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Voulez-vous vraiment changer le statut de cette
                                entité ?
                            </AlertDialogTitle>

                            <AlertDialogDescription>
                                <Select v-model="newStatut" class="my-8" placeholder="Statut">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Statut" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectGroup>
                                            <SelectLabel>Site</SelectLabel>
                                            <SelectItem v-for="statut in statuts" :key="statut.id" :value="statut.id">
                                                {{ statut.nom }}
                                            </SelectItem>
                                        </SelectGroup>
                                    </SelectContent>
                                </Select>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter class="flex flex-row gap-2">
                            <AlertDialogCancel @click="statutModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleStatut">
                                    Changer le statut
                                    <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                </Button>
                            </div>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { Input } from "@/components/ui/input";
import Pagination from "@/components/Pagination.vue";
import { Input_search } from "@/components/ui/Input_search";
import { Plus } from "lucide-vue-next";
import { Skeleton } from "@/components/ui/skeleton";
import { Toaster, toast } from "vue-sonner";

const loading = ref(false);

const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const statutModal = ref(false);
const siteId = ref(null);
const newStatut = ref(null);

const nom = ref("");

const statuts = [
    {
        id: 1,
        nom: "Actif",
    },
    {
        id: 2,
        nom: "Inactif",
    },
];

const props = defineProps({
    entites: Array,
    meta: Object,
});
// console.log(props.elementFacturation);

const openDeleteModal = async (id) => {
    if (id) {
        deleteModal.value = true;
        siteId.value = id;
    }
};

const openStatutModal = async (statut, id) => {
    if (id) {
        statutModal.value = true;
        newStatut.value = statut;
        siteId.value = id;
    }
};

const openEditModal = async (id) => {
    if (id) {
        const res = await axios.get(`/entite/edit/${id}`);
        nom.value = res.data.nom;
        editModal.value = true;
        siteId.value = id;
    }
};

const openCreateModal = () => {
    nom.value = "";
    createModal.value = true;
};

const handleStatut = async () => {
    if (siteId.value) {
        loading.value = true;
        try {
            await axios.put(`/entite/statut/${siteId.value}`, {
                statut: newStatut.value,
            });
            toast.success("Statut mis à jour avec succès");
            router.reload();
        } catch (error) {
            toast.error(
                "Une erreur est survenue lors de la mise à jour du statut"
            );
            return;
        } finally {
            statutModal.value = false;
            loading.value = false;
        }
    }
};

const handleDelete = async () => {
    if (siteId.value) {
        loading.value = true;
        try {
            await axios.delete(`/entite/delete/${siteId.value}`);
            toast.success("Entité supprimé avec succès");
            router.reload();
        } catch (error) {
            toast.error(
                "Une erreur est survenue lors de la suppression de l'entité"
            );
            return;
        } finally {
            loading.value = false;
            deleteModal.value = false;
        }
    }
};

const handleCreate = async () => {
    if (nom.value === "") {
        toast.error("Veuillez remplir le nom de l'entité");
        return;
    }
    loading.value = true;
    try {
        await axios.post("/entite/create", {
            nom: nom.value,
        });
        toast.success("Entité créé avec succès");
        router.reload();
    } catch (error) {
        toast.error("Une erreur est survenue lors de la création de l'entité");
        return;
    } finally {
        createModal.value = false;
        loading.value = false;
    }
};

const handleEdit = async () => {
    if (nom.value === "") {
        toast.error("Veuillez remplir le nom de l'entité");
        return;
    }
    loading.value = true;
    try {
        await axios.put(`/entite/update/${siteId.value}`, {
            nom: nom.value,
        });
        toast.success("Entité modifié avec succès");
        router.reload();
    } catch (error) {
        toast.error(
            "Une erreur est survenue lors de la modification de l'entité"
        );
        return;
    } finally {
        editModal.value = false;
        loading.value = false;
    }
};

// Pagination

const form = useForm({
    search_data: "",
    statut: "",
    date_start: "",
    date_end: "",
});

// Filtres
const onFilterChange = () => {
    loading.value = true;
    router.get(route("entite.liste"), form, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => (loading.value = false),
    });
};

// Pagination
const changePage = (page) => {
    loading.value = true;
    router.get(route("entite.liste"), { ...form.data(), page }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => (loading.value = false),
    });
};
// Initial fetch

</script>

<script>
import { ref, onMounted, watch } from "vue";
import { Card, CardContent } from "@/components/ui/card";

import Main from "/resources/js/Pages/Main.vue";
import { Button } from "@/components/ui/button";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import DateRangePicker from "@/components/ui/DateRangePicker.vue";
import Pagination from "@/components/Pagination.vue";
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
import BoutonNouveau from "/resources/js/components/BoutonNouveau.vue";
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

export default {
    layout: Main,
    components: { Pagination },
};
</script>
