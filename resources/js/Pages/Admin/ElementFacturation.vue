<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-2xl font-bold tracking-tight">
                Liste des éléments de facturation
            </h4>
            <Toaster position="top-right" />

            <!-- Bouton Nouveau -->
            <div class="flex items-center space-x-2">
                <AlertDialog :open="openCreateElementModal">
                    <AlertDialogTrigger as-child>
                        <Button @click="openCreateElement">
                            <Plus class="w-4 h-4 mr-2" /> Nouvel élément
                        </Button>
                    </AlertDialogTrigger>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle> Nouvel élément</AlertDialogTitle>
                            <AlertDialogDescription>
                                <div class="space-y-8 my-8">
                                    <Input v-model="element_facturation" placeholder="Nom de l'élément" />
                                    <Input v-model="montant" placeholder="Montant" />
                                </div>
                                <div class="flex gap-4">
                                    <Select v-model="id_site" class="my-8" placeholder="Type de site">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Type de site" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Site</SelectLabel>
                                                <SelectItem v-for="site in sites" :key="site.id" :value="site.id">
                                                    {{ site.nom_site }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <Select v-model="id_service" class="my-8" placeholder="Type de site">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Type de site" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Type de
                                                    service</SelectLabel>
                                                <SelectItem v-for="service in services" :key="service.id"
                                                    :value="service.id">
                                                    {{ service.nom_service }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="flex gap-4 my-8">
                                    <Select v-model="id_entite" class="my-8" placeholder="Choisir une entité">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Choisir une entité" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Entité</SelectLabel>
                                                <SelectItem v-for="entite in entites" :key="entite.id"
                                                    :value="entite.id">
                                                    {{ entite.nom }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <Select v-model="id_type_services" class="my-8"
                                        placeholder="Choisir le type de service">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Choisir le type de service" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Type de
                                                    service</SelectLabel>
                                                <SelectItem v-for="type_service in type_services" :key="type_service.id"
                                                    :value="type_service.id">
                                                    {{
                                                        type_service.nom_type_service
                                                    }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter class="flex flex-row gap-2">
                            <AlertDialogCancel @click="openCreateElementModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleCreateSite">
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
                        <!-- Recherche -->
                        <div class="flex justify-start w-full">
                            <div class="flex items-center gap-2">
                                <input
                                    v-model="searchInput"
                                    @keyup.enter="applySearch"
                                    type="text"
                                    placeholder="Rechercher par nom d'élément…"
                                    class="w-64 px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30"
                                />
                                <button
                                    @click="applySearch"
                                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                                >
                                    <Search class="w-4 h-4" />
                                    Chercher
                                </button>
                                <button
                                    v-if="activeSearch"
                                    @click="clearSearch"
                                    title="Réinitialiser"
                                    class="inline-flex items-center gap-1 px-3 py-2 text-sm rounded-md border hover:bg-muted transition-colors text-muted-foreground"
                                >
                                    <X class="w-4 h-4" />
                                    Réinitialiser
                                </button>
                            </div>
                        </div>

                        <!-- Tableau -->
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>ID</TableHead>
                                    <TableHead>Nom</TableHead>
                                    <TableHead>Service</TableHead>
                                    <!-- <TableHead>Type de service</TableHead> -->
                                    <TableHead> Site </TableHead>
                                    <TableHead>Entité</TableHead>
                                    <TableHead>Montant</TableHead>
                                    <TableHead>Status</TableHead>

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
                                        <TableCell>
                                            <Skeleton class="h-4 w-20" />
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
                                    <TableRow v-for="(element, index) in paginatedElements" :key="index">
                                        <TableCell>{{ index + 1 }}</TableCell>
                                        <TableCell>{{
                                            element?.element_facturation || "—"
                                        }}</TableCell>
                                        <TableCell>{{
                                            element
                                                ?.r_details_type_service_service
                                                .nom_service || "—"
                                        }}</TableCell>
                                        <!-- <TableCell>
                                            {{
                                                element
                                                    ?.r_details_type_service_type_service
                                                    .nom_type_service || "—"
                                            }}
                                        </TableCell> -->
                                        <TableCell>{{
                                            element?.r_details_type_service_site?.nom_site
                                            || "—"
                                        }}</TableCell>
                                        <TableCell>{{
                                            element.r_details_type_service_entite
                                                .nom || "—"

                                        }}</TableCell>
                                        <TableCell>{{
                                            element?.montant || "—"
                                        }}</TableCell>

                                        <TableCell>
                                            <Badge :variant="element?.statut === 1
                                                ? 'success'
                                                : element?.statut === 2
                                                    ? 'error'
                                                    : 'secondary'
                                                ">
                                                {{
                                                    element?.statut === 1
                                                        ? "Actif"
                                                        : element?.statut === 2
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
                                                        openEditElement(
                                                            element.id
                                                        )
                                                        ">
                                                        Modifier
                                                    </DropdownMenuItem>

                                                    <DropdownMenuItem class="text-red-500" @click="
                                                        openDeleteElement(
                                                            element.id
                                                        )
                                                        ">Supprimer</DropdownMenuItem>
                                                    <DropdownMenuItem @click="
                                                        openStatutElement(
                                                            element.statut,
                                                            element.id
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
                        <div class="flex items-center justify-between pt-4 border-t">
                            <p class="text-sm text-muted-foreground">
                                {{ filteredElements.length === 0 ? 0 : (elemCurrentPage - 1) * elemPerPage + 1 }}–{{ Math.min(elemCurrentPage * elemPerPage, filteredElements.length) }}
                                sur {{ filteredElements.length }} élément(s)
                            </p>
                            <div class="flex items-center gap-1">
                                <button
                                    :disabled="elemCurrentPage === 1"
                                    @click="elemCurrentPage = 1"
                                    title="Première page"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md border hover:bg-muted disabled:opacity-40 transition-colors"
                                >
                                    <ChevronsLeft class="w-4 h-4" />
                                </button>
                                <button
                                    :disabled="elemCurrentPage === 1"
                                    @click="elemCurrentPage--"
                                    title="Page précédente"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md border hover:bg-muted disabled:opacity-40 transition-colors"
                                >
                                    <ChevronLeft class="w-4 h-4" />
                                </button>
                                <template v-for="page in pageNumbers" :key="page">
                                    <span v-if="page === '...'" class="px-2 text-muted-foreground">…</span>
                                    <button
                                        v-else
                                        @click="elemCurrentPage = page"
                                        :class="[
                                            'inline-flex items-center justify-center w-8 h-8 rounded-md border text-sm transition-colors',
                                            page === elemCurrentPage ? 'bg-primary text-primary-foreground border-primary' : 'hover:bg-muted'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                </template>
                                <button
                                    :disabled="elemCurrentPage === totalPages"
                                    @click="elemCurrentPage++"
                                    title="Page suivante"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md border hover:bg-muted disabled:opacity-40 transition-colors"
                                >
                                    <ChevronRight class="w-4 h-4" />
                                </button>
                                <button
                                    :disabled="elemCurrentPage === totalPages"
                                    @click="elemCurrentPage = totalPages"
                                    title="Dernière page"
                                    class="inline-flex items-center justify-center w-8 h-8 rounded-md border hover:bg-muted disabled:opacity-40 transition-colors"
                                >
                                    <ChevronsRight class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                </Card>
            </main>
            <div class="flex items-center space-x-2">
                <AlertDialog :open="openEditModal">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Modifier l'élément</AlertDialogTitle>
                            <AlertDialogDescription>
                                <div class="space-y-8 my-8">
                                    <Input v-model="element_facturation" placeholder="Nom de l'élément" />
                                    <Input v-model="montant" placeholder="Montant" />
                                </div>
                                <div class="flex gap-4">
                                    <Select v-model="id_site" class="my-8" placeholder="Type de site">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Type de site" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Site</SelectLabel>
                                                <SelectItem v-for="site in sites" :key="site.id" :value="site.id">
                                                    {{ site.nom_site }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <Select v-model="id_service" class="my-8" placeholder="Type de site">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Type de site" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Type de
                                                    service</SelectLabel>
                                                <SelectItem v-for="service in services" :key="service.id"
                                                    :value="service.id">
                                                    {{ service.nom_service }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div class="flex gap-4 my-8">
                                    <Select v-model="id_entite" class="my-8" placeholder="Choisir une entité">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Choisir une entité" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Entité</SelectLabel>
                                                <SelectItem v-for="entite in entites" :key="entite.id"
                                                    :value="entite.id">
                                                    {{ entite.nom }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                    <Select v-model="id_type_services" class="my-8"
                                        placeholder="Choisir le type de service">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Choisir le type de service" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectGroup>
                                                <SelectLabel>Type de
                                                    service</SelectLabel>
                                                <SelectItem v-for="type_service in type_services" :key="type_service.id"
                                                    :value="type_service.id">
                                                    {{
                                                        type_service.nom_type_service
                                                    }}
                                                </SelectItem>
                                            </SelectGroup>
                                        </SelectContent>
                                    </Select>
                                </div>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter class="flex flex-row gap-2">
                            <AlertDialogCancel @click="openEditModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleEditSite">
                                    Valider
                                    <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                </Button>
                            </div>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
            <div class="flex items-center space-x-2">
                <AlertDialog :open="openDeleteModal">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Voulez-vous vraiment supprimer cet élément de
                                facturation ?
                            </AlertDialogTitle>

                            <AlertDialogDescription>
                                <p>Cette action est irréversible.</p>
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter class="flex flex-row gap-2">
                            <AlertDialogCancel @click="openDeleteModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleDeleteElement" variant="destructive">
                                    Supprimer
                                    <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                                </Button>
                            </div>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </div>
            <div class="flex items-center space-x-2">
                <AlertDialog :open="openStatutModal">
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                Voulez-vous vraiment changer le statut de cet
                                élément de facturation ?
                            </AlertDialogTitle>

                            <AlertDialogDescription>
                                <Select v-model="newStatut" class="my-8" placeholder="Type de site">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Type de site" />
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
                            <AlertDialogCancel @click="openStatutModal = false">
                                Annuler
                            </AlertDialogCancel>
                            <div>
                                <Button @click="handleStatutElement">
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
import { onMounted, ref, computed, watch } from "vue";
import { router, useForm } from "@inertiajs/vue3";
import { Input } from "@/components/ui/input";
import { Plus, Search, X, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from "lucide-vue-next";
import { Skeleton } from "@/components/ui/skeleton";
import { Toaster, toast } from "vue-sonner";

const loading = ref(false);

const openCreateElementModal = ref(false);
const openEditModal = ref(false);
const openDeleteModal = ref(false);
const openStatutModal = ref(false);
const siteId = ref(null);
const newStatut = ref(null);

const element_facturation = ref("");
const id_type_services = ref("");
const id_site = ref("");
const id_service = ref("");
const id_entite = ref("");
const montant = ref("");

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
    elementFacturation: Array,
    sites: Array,
    services: Array,
    entites: Array,
    type_services: Array,
});

// ── Recherche ──────────────────────────────────────────────
const searchInput  = ref('');
const activeSearch = ref('');

const applySearch = () => {
    activeSearch.value   = searchInput.value.trim();
    elemCurrentPage.value = 1;
};

const clearSearch = () => {
    searchInput.value    = '';
    activeSearch.value   = '';
    elemCurrentPage.value = 1;
};

// ── Pagination ──────────────────────────────────────────────
const elemPerPage     = 10;
const elemCurrentPage = ref(1);

const filteredElements = computed(() => {
    const all = props.elementFacturation ?? [];
    if (!activeSearch.value) return all;
    const q = activeSearch.value.toLowerCase();
    return all.filter(e =>
        (e.element_facturation ?? '').toLowerCase().includes(q)
    );
});

const totalPages = computed(() =>
    Math.max(1, Math.ceil(filteredElements.value.length / elemPerPage))
);

const paginatedElements = computed(() => {
    const start = (elemCurrentPage.value - 1) * elemPerPage;
    return filteredElements.value.slice(start, start + elemPerPage);
});

const pageNumbers = computed(() => {
    const total   = totalPages.value;
    const current = elemCurrentPage.value;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [1];
    if (current > 3)          pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
    if (current < total - 2)  pages.push('...');
    pages.push(total);
    return pages;
});

watch(() => props.elementFacturation, () => { elemCurrentPage.value = 1; });
// ────────────────────────────────────────────────────────────

// const refreshData = (el) => {
//   router.reload({
//     only: ['elementFacturation'] // si tu veux recharger uniquement une prop
//   })
// }

const handleDeleteElement = async () => {
    if (siteId.value) {
        loading.value = true;
        try {
            await axios.delete(`/element-facturation/delete/${siteId.value}`);
            toast.success("Élément supprimé avec succès");
        } catch (error) {
            toast.error(
                "Une erreur est survenue lors de la suppression de l'élément"
            );
            return;
        } finally {
            loading.value = false;
            openDeleteModal.value = false;
        }

        // router.reload();
    }
};

const openDeleteElement = async (id) => {
    if (id) {
        openDeleteModal.value = true;
        siteId.value = id;
    }
};

const openStatutElement = async (statut, id) => {
    if (id) {
        openStatutModal.value = true;
        newStatut.value = statut;
        siteId.value = id;
    }
};

const handleStatutElement = async () => {
    if (siteId.value) {
        loading.value = true;
        try {
            await axios.put(`/element-facturation/statut/${siteId.value}`, {
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
            openStatutModal.value = false;
            loading.value = false;
        }
    }
};

const openEditElement = async (id) => {
    if (id) {
        const res = await axios.get(`/element-facturation/edit/${id}`);
        element_facturation.value = res.data.element_facturation;
        id_type_services.value = res.data.id_type_services;
        id_site.value = res.data.id_site;
        id_service.value = res.data.id_service;
        id_entite.value = res.data.id_entite;
        montant.value = res.data.montant;
        openEditModal.value = true;
        siteId.value = id;
    }
};
const openCreateElement = () => {
    element_facturation.value = "";
    id_type_services.value = "";
    id_site.value = "";
    id_service.value = "";
    id_entite.value = "";
    montant.value = "";
    openCreateElementModal.value = true;
};

const handleCreateSite = async () => {
    if (
        element_facturation.value === "" ||
        id_type_services.value === "" ||
        id_site.value === "" ||
        id_service.value === "" ||
        id_entite.value === "" ||
        montant.value === ""
    ) {
        toast.error("Veuillez remplir tous les champs");
        return;
    }
    loading.value = true;
    try {
        await axios.post("/element-facturation/create", {
            element_facturation: element_facturation.value,
            id_type_services: id_type_services.value,
            id_site: id_site.value,
            id_service: id_service.value,
            id_entite: id_entite.value,
            montant: montant.value,
        });
        toast.success("Élément créé avec succès");
        router.reload();
    } catch (error) {
        toast.error("Une erreur est survenue lors de la création de l'élément");
        return;
    } finally {
        openCreateElementModal.value = false;
        loading.value = false;
    }
};

const handleEditSite = async () => {
    if (
        element_facturation.value === "" ||
        id_type_services.value === "" ||
        id_site.value === "" ||
        id_service.value === "" ||
        id_entite.value === "" ||
        montant.value === ""
    ) {
        toast.error("Veuillez remplir tous les champs");
        return;
    }
    loading.value = true;
    try {
        await axios.put(`/element-facturation/update/${siteId.value}`, {
            element_facturation: element_facturation.value,
            id_type_services: id_type_services.value,
            id_site: id_site.value,
            id_service: id_service.value,
            id_entite: id_entite.value,
            montant: montant.value,
        });
        toast.success("Élément modifié avec succès");
        router.reload();
    } catch (error) {
        toast.error(
            "Une erreur est survenue lors de la modification de l'élément"
        );
        return;
    } finally {
        openEditModal.value = false;
        loading.value = false;
    }
};

// Pagination
const dossiers = ref([]);
const meta = ref(null);
const page = ref(1);
const perPage = ref(10);

// Initialisation du formulaire avec les paramètres d'URL
const params = new URLSearchParams(window.location.search);
const form = useForm({
    search_data: params.get("search_data") || "",
    statut: params.get("statut") || "",
    date_start: params.get("date_start") || "",
    date_end: params.get("date_end") || "",
});

// URL dans l'historique
function updateURL() {
    const params = new URLSearchParams();
    if (form.search_data) params.set("search_data", form.search_data);
    if (form.statut) params.set("statut", form.statut);
    if (form.date_start) params.set("date_start", form.date_start);
    if (form.date_end) params.set("date_end", form.date_end);
    if (page.value) params.set("page", page.value);
    // history.pushState(null, '', `?${params.toString()}`)
}

// Récupération des données
async function fetchDossiers() {
    loading.value = true;
    const url = new URL("/caisse/get/data", window.location.origin);
    url.searchParams.set("filtre_per_page", perPage.value);
    url.searchParams.set("page", page.value);
    if (form.search_data) url.searchParams.set("search_data", form.search_data);
    if (form.statut) url.searchParams.set("statut", form.statut);
    if (form.date_start) url.searchParams.set("date_start", form.date_start);
    if (form.date_end) url.searchParams.set("date_end", form.date_end);

    const res = await fetch(url.toString());
    const json = await res.json();
    dossiers.value = json.dossiers.data;
    meta.value = json.dossiers;
    updateURL();
    loading.value = false;
}

// Filtres
function onFilterChange() {
    page.value = 1;
    fetchDossiers();
}

function changePage(p) {
    page.value = p;
    fetchDossiers();
}

// Gestion navigation arrière
window.addEventListener("popstate", () => {
    const params = new URLSearchParams(window.location.search);
    // page.value = parseInt(params.get('page')) || 1
    form.search_data = params.get("search_data") || "";
    form.statut = params.get("statut") || "";
    form.date_start = params.get("date_start") || "";
    form.date_end = params.get("date_end") || "";
    fetchDossiers();
});

// Initial fetch
onMounted(() => {
    // page.value = parseInt(params.get('page')) || 1
    fetchDossiers();
});
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
