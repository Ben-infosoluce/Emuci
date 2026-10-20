<template>
    <div>
        <div class="flex flex-col space-y-4 mx-8 sm:flex-row sm:items-center sm:justify-between sm:space-y-0">
            <!-- Titre -->
            <h4 class="text-2xl font-bold tracking-tight">
                Liste des utilisateurs
            </h4>
            <Toaster position="top-right" />

            <Button @click="openCreateModal">
                <Plus class="w-4 h-4 mr-2" /> Nouvel utilisateur
            </Button>
        </div>

        <div class="rounded-lg dark:border-gray-700">
            <main class="flex flex-1 flex-col gap-4 p-4 md:gap-8 md:p-8">
                <Card class="h-full flex flex-col">
                    <div class="space-y-4 p-8">
                        <!-- Recherche -->
                      <div class="flex justify-start w-full">
                        <div class="flex items-center gap-2">
                            <!-- Champ de recherche aligné à gauche avec focus gris et bordure fine -->
                            <input 
                            v-model="searchInput" 
                            @keyup.enter="applySearch" 
                            type="text" 
                            placeholder="Rechercher par nom ou prénom…" 
                            class="w-64 px-3 py-2 text-sm border rounded-md bg-background focus:outline-none focus:border-muted-foreground/50 focus:ring-1 focus:ring-muted-foreground/30" 
                            />
                            
                            <!-- Bouton principal avec icône -->
                            <button 
                            @click="applySearch" 
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-md bg-primary text-primary-foreground hover:bg-primary/90 transition-colors"
                            >
                            <Search class="w-4 h-4" /> 
                            Chercher 
                            </button>
                            
                            <!-- Bouton Réinitialiser -->
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
                                    <TableHead>Nom</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Rôle</TableHead>
                                    <TableHead>Site</TableHead>
                                    <TableHead>Caisse</TableHead>
                                    <TableHead>Statut</TableHead>
                                    <TableHead>Permissions</TableHead>
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
                                    <TableRow v-for="user in paginatedUsers" :key="user.id">
                                        <TableCell>{{ user.nom }}
                                            {{ user.prenom }}</TableCell>
                                        <TableCell>{{ user.email }}</TableCell>
                                        <TableCell>{{
                                            user.r_user_role?.nom_role || "—"
                                        }}</TableCell>
                                        <TableCell>{{
                                            user.r_user_site?.nom_site || "—"
                                        }}</TableCell>
                                        <TableCell>{{
                                            user.r_user_caisse?.libelle ? `${user.r_user_caisse.code} – ${user.r_user_caisse.libelle}` : "—"
                                        }}</TableCell>
                                        <TableCell>
                                            <Badge :variant="user?.statut === 1
                                                ? 'success'
                                                : user?.statut === 2
                                                    ? 'error'
                                                    : 'secondary'
                                                ">
                                                {{
                                                    user?.statut === 1
                                                        ? "Actif"
                                                        : user?.statut === 2
                                                            ? "Inactif"
                                                            : "Inconnu"
                                                }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <div>
                                                <!-- Bouton pour ouvrir le modal -->
                                                <Badge @click="openModal(user)" class="cursor-pointer">
                                                    Attribuer des Permissions
                                                </Badge>
                                            </div>
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
                                                        openEditModal(user)
                                                        ">Modifier</DropdownMenuItem>
                                                    <DropdownMenuItem class="text-red-500" @click="
                                                        openDeleteModal(
                                                            user.id
                                                        )
                                                        ">Supprimer</DropdownMenuItem>
                                                    <DropdownMenuItem @click="
                                                        openStatutModal(
                                                            user
                                                        )
                                                        ">Changer
                                                        Statut</DropdownMenuItem>
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
                                {{ (currentPage - 1) * perPage + 1 }}–{{ Math.min(currentPage * perPage, filteredUsers.length) }}
                                sur {{ filteredUsers.length }} utilisateur(s)
                            </p>
                            <div class="flex items-center gap-1">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    :disabled="currentPage === 1"
                                    @click="currentPage = 1"
                                    title="Première page"
                                >
                                    <ChevronsLeft class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    :disabled="currentPage === 1"
                                    @click="currentPage--"
                                    title="Page précédente"
                                >
                                    <ChevronLeft class="w-4 h-4" />
                                </Button>
                                <template v-for="page in pageNumbers" :key="page">
                                    <span v-if="page === '...'" class="px-2 text-muted-foreground">…</span>
                                    <Button
                                        v-else
                                        :variant="page === currentPage ? 'default' : 'outline'"
                                        size="icon"
                                        @click="currentPage = page"
                                    >
                                        {{ page }}
                                    </Button>
                                </template>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    :disabled="currentPage === totalPages"
                                    @click="currentPage++"
                                    title="Page suivante"
                                >
                                    <ChevronRight class="w-4 h-4" />
                                </Button>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    :disabled="currentPage === totalPages"
                                    @click="currentPage = totalPages"
                                    title="Dernière page"
                                >
                                    <ChevronsRight class="w-4 h-4" />
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>
            </main>

            <!-- Modale de création / modification -->
            <AlertDialog :open="createModal || editModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>{{ editModal ? "Modifier" : "Créer" }} un utilisateur</AlertDialogTitle>
                        <AlertDialogDescription>
                            <div class="space-y-4 my-4">
                                <!-- Nom -->
                                <div class="space-y-1">
                                    <Label for="user-nom">Nom <span class="text-red-500">*</span></Label>
                                    <Input id="user-nom" v-model="form.nom" placeholder="Ex: Dupont" />
                                </div>
                                <!-- Prénom -->
                                <div class="space-y-1">
                                    <Label for="user-prenom">Prénom <span class="text-red-500">*</span></Label>
                                    <Input id="user-prenom" v-model="form.prenom" placeholder="Ex: Jean" />
                                </div>
                                <!-- Email -->
                                <div class="space-y-1">
                                    <Label for="user-email">Adresse e-mail <span class="text-red-500">*</span></Label>
                                    <Input id="user-email" v-model="form.email" placeholder="Ex: jean.dupont@email.com" />
                                </div>
                                <!-- Mot de passe (création uniquement) -->
                                <div v-if="!editModal" class="space-y-1">
                                    <Label for="user-password">Mot de passe <span class="text-red-500">*</span></Label>
                                    <Input id="user-password" v-model="form.password" type="password" placeholder="Minimum 6 caractères" />
                                </div>
                                <!-- Rôle -->
                                <div class="space-y-1">
                                    <Label>Rôle <span class="text-red-500">*</span></Label>
                                    <Select v-model="form.id_role">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionner un rôle" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="role in props.roles" :key="role.id" :value="role.id">{{ role.nom_role }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <!-- Site -->
                                <div class="space-y-1">
                                    <Label>Site <span class="text-red-500">*</span></Label>
                                    <Select v-model="form.id_site">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Sélectionner un site" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem v-for="site in props.sites" :key="site.id" :value="site.id">{{ site.nom_site }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <!-- Caisse (rôles Caisse/CaisseFinancier uniquement) -->
                                <template v-if="isCaisseRole">
                                    <div class="space-y-1">
                                        <Label>Caisse <span class="text-red-500">*</span></Label>
                                        <Select v-model="form.caisse_id" :disabled="!form.id_site">
                                            <SelectTrigger>
                                                <SelectValue placeholder="Sélectionner une caisse" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem
                                                    v-for="caisse in filteredCaisses"
                                                    :key="caisse.id"
                                                    :value="caisse.id"
                                                >
                                                    {{ caisse.code }} – {{ caisse.libelle }}
                                                </SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <p v-if="!form.id_site" class="text-xs text-muted-foreground">
                                            Veuillez d'abord sélectionner un site.
                                        </p>
                                        <p v-else-if="filteredCaisses.length === 0" class="text-xs text-amber-500">
                                            Aucune caisse active pour ce site.
                                        </p>
                                    </div>
                                </template>
                            </div>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex gap-2">
                        <AlertDialogCancel @click="resetModals">Annuler</AlertDialogCancel>
                        <Button @click="editModal ? handleEdit() : handleCreate()">
                            Valider
                            <span v-if="loading" class="ml-2 animate-spin">⏳</span>
                        </Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <!-- Modale suppression -->
            <AlertDialog :open="deleteModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Supprimer l'utilisateur ?</AlertDialogTitle>
                        <AlertDialogDescription>
                            Cette action est irréversible.
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex gap-2">
                        <AlertDialogCancel @click="resetModals">Annuler</AlertDialogCancel>
                        <Button variant="destructive" @click="handleDelete">Supprimer</Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <!-- Modale changement de statut -->
            <AlertDialog :open="statutModal">
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>Changer le statut de
                            l'utilisateur</AlertDialogTitle>
                        <AlertDialogDescription>
                            <Select v-model="form.statut">
                                <SelectTrigger>
                                    <SelectValue placeholder="Statut" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="1">Actif</SelectItem>
                                    <SelectItem :value="2">Inactif</SelectItem>
                                </SelectContent>
                            </Select>
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter class="flex gap-2">
                        <AlertDialogCancel @click="resetModals">Annuler</AlertDialogCancel>
                        <Button @click="handleStatut">Valider</Button>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>

            <!-- Modal Permissions -->
            <Dialog v-model:open="isModalOpen">
                <DialogContent class="sm:max-w-[90%] w-full max-h-[90vh] flex flex-col p-0">
                    <!-- HEADER -->
                    <DialogHeader class="p-6 border-b">
                        <DialogTitle>Sélectionner les Permissions</DialogTitle>
                        <DialogDescription>
                            Cochez les permissions que vous souhaitez attribuer à cet utilisateur.
                        </DialogDescription>
                    </DialogHeader>

                    <!-- CONTENU SCROLLABLE -->
                    <ScrollArea class="flex-1 px-6 py-4 overflow-y-auto">
                        <div v-if="isLoading" class="space-y-6">
                            <div v-for="i in 6" :key="i" class="space-y-3">
                                <Skeleton class="h-5 w-1/3" />
                                <Skeleton class="h-4 w-full" />
                                <Skeleton class="h-4 w-5/6" />
                            </div>
                        </div>


                        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div v-for="(group, index) in groupedPermissions" :key="index" class="mb-6">
                                <h3 class="text-lg font-semibold mb-2">
                                    {{ group.service.nom_service }}
                                </h3>

                                <!-- Permissions du service -->
                                <div v-if="group.permissions.length > 0" class="mb-4">
                                    <h4 class="font-medium mb-2">Permissions du service :</h4>

                                    <div v-for="permission in group.permissions" :key="permission.id"
                                        class="flex items-center space-x-2">
                                        <Checkbox :id="'service-perm-' + permission.id"
                                            :checked="isPermissionSelected(permission.id)"
                                            @update:checked="togglePermission(permission.id, $event)" />
                                        <Label :for="'service-perm-' + permission.id">
                                            {{ permission.Nom_Permission }}
                                        </Label>
                                    </div>
                                </div>

                                <!-- Permissions par type de service -->
                                <div v-for="typeService in group.type_services" :key="typeService.id" class="ml-4 mb-4">
                                    <h4 class="font-medium mb-2">
                                        {{ typeService.nom_type_service }} :
                                    </h4>

                                    <div v-for="permission in group.type_service_permissions.filter(
                                        p => p.type_service_id === typeService.id
                                    )" :key="permission.id" class="flex items-center space-x-2">
                                        <Checkbox :id="'type-perm-' + permission.id"
                                            :checked="isPermissionSelected(permission.id)"
                                            @update:checked="togglePermission(permission.id, $event)" />
                                        <Label :for="'type-perm-' + permission.id">
                                            {{ permission.Nom_Permission }}
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </ScrollArea>

                    <!-- FOOTER FIXE -->
                    <DialogFooter class="p-6 border-t flex flex-col sm:flex-row gap-2">
                        <Button type="button" variant="outline" class="w-full sm:w-auto" @click="closeModal">
                            Fermer
                        </Button>

                        <Button type="button" class="w-full sm:w-auto" @click="savePermissions" :disabled="isLoading">
                            {{ isLoading ? "Enregistrement..." : "Enregistrer" }}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed, watch, nextTick } from "vue";
import { router } from "@inertiajs/vue3";
import { Input } from "@/components/ui/input";
import { Plus, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Search, X } from "lucide-vue-next";
import { Skeleton } from "@/components/ui/skeleton";
import { Toaster, toast } from "vue-sonner";
import { ScrollArea } from '@/components/ui/scroll-area'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter,
} from "@/components/ui/dialog";
import { Button } from "@/components/ui/button";
import { Checkbox } from "@/components/ui/checkbox";
import { Label } from "@/components/ui/label";

const loading = ref(false);
const props = defineProps({
    users: Array,
    roles: Array,
    sites: Array,
    caisses: Array,
});

// Rôles nécessitant une caisse (le contrôleur est rattaché au site, pas à une caisse)
const CAISSE_ROLE_NAMES = ['Caisse', 'CaisseFinancier'];

// ── Recherche ────────────────────────────────────────────────
const searchInput = ref('');   // valeur saisie dans le champ
const activeSearch = ref('');  // valeur appliquée au clic

const applySearch = () => {
    activeSearch.value = searchInput.value.trim();
    currentPage.value = 1;
};

const clearSearch = () => {
    searchInput.value = '';
    activeSearch.value = '';
    currentPage.value = 1;
};
// ────────────────────────────────────────────────────────────

// ── Pagination ──────────────────────────────────────────────
const perPage = 10;
const currentPage = ref(1);

const filteredUsers = computed(() => {
    const all = props.users ?? [];
    if (!activeSearch.value) return all;
    const q = activeSearch.value.toLowerCase();
    return all.filter(u =>
        (u.nom ?? '').toLowerCase().includes(q) ||
        (u.prenom ?? '').toLowerCase().includes(q)
    );
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredUsers.value.length / perPage)));

const paginatedUsers = computed(() => {
    const start = (currentPage.value - 1) * perPage;
    return filteredUsers.value.slice(start, start + perPage);
});

const pageNumbers = computed(() => {
    const total = totalPages.value;
    const current = currentPage.value;
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);
    const pages = [];
    pages.push(1);
    if (current > 3) pages.push('...');
    for (let i = Math.max(2, current - 1); i <= Math.min(total - 1, current + 1); i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
    return pages;
});

// Revenir à la page 1 quand les données changent
watch(() => props.users, () => { currentPage.value = 1; });
// ────────────────────────────────────────────────────────────

const form = ref({
    nom: "",
    prenom: "",
    email: "",
    password: "",
    id_role: "",
    id_site: "",
    caisse_id: null,
    statut: 1,
});

// Nom du rôle sélectionné (on se base sur le nom, pas sur l'ID qui peut varier)
const selectedRoleName = computed(() => {
    const roleId = Number(form.value.id_role);
    return (props.roles ?? []).find((role) => Number(role.id) === roleId)?.nom_role ?? "";
});

// Vérifie si le rôle actuellement sélectionné nécessite une caisse
const isCaisseRole = computed(() => CAISSE_ROLE_NAMES.includes(selectedRoleName.value));

// Filtre les caisses selon le site sélectionné
const filteredCaisses = computed(() => {
    if (!form.value.id_site || !props.caisses) return [];
    return props.caisses.filter(c => c.site_id === Number(form.value.id_site));
});

// Réinitialise caisse_id quand le site change (sauf lors du pré-remplissage en édition)
const skipSiteReset = ref(false);
watch(() => form.value.id_site, () => {
    if (skipSiteReset.value) return;
    form.value.caisse_id = null;
});

// Réinitialise caisse_id quand le rôle change et n'est plus un rôle caisse
watch(() => form.value.id_role, () => {
    if (!isCaisseRole.value) {
        form.value.caisse_id = null;
    }
});

const createModal = ref(false);
const editModal = ref(false);
const deleteModal = ref(false);
const statutModal = ref(false);
const currentUserId = ref(null);

const resetModals = () => {
    createModal.value =
        editModal.value =
        deleteModal.value =
        statutModal.value =
        false;
    currentUserId.value = null;
    form.value = {
        nom: "",
        prenom: "",
        email: "",
        password: "",
        id_role: "",
        id_site: "",
        caisse_id: null,
        statut: 1,
    };
};

// Modales
const openCreateModal = () => {
    resetModals();
    createModal.value = true;
};

const openEditModal = (user) => {
    resetModals();
    currentUserId.value = user.id;
    skipSiteReset.value = true;
    Object.assign(form.value, { ...user, password: "" });
    nextTick(() => { skipSiteReset.value = false; });
    editModal.value = true;
};

const openDeleteModal = (id) => {
    resetModals();
    currentUserId.value = id;
    deleteModal.value = true;
};

const openStatutModal = (user) => {
    resetModals();
    currentUserId.value = user.id;
    form.value.statut = user.statut;
    statutModal.value = true;
};

// Actions
// Récupère le message d'erreur renvoyé par Laravel (validation ou autre)
const getErrorMessage = (error, fallback) => {
    const data = error?.response?.data;
    const validationError = data?.errors
        ? Object.values(data.errors).flat().find(Boolean)
        : null;
    return validationError || data?.message || fallback;
};

const handleCreate = async () => {
    loading.value = true;
    try {
        await axios.post("/users", form.value);
        toast.success("Utilisateur créé.");
        resetModals();
        router.reload();
    } catch (e) {
        console.error(e);
        toast.error(getErrorMessage(e, "Erreur lors de la création."));
    } finally {
        loading.value = false;
    }
};

const handleEdit = async () => {
    loading.value = true;
    try {
        await axios.put(`/users/${currentUserId.value}`, form.value);
        toast.success("Utilisateur modifié.");
        resetModals();
        router.reload();
    } catch (e) {
        console.error(e);
        toast.error(getErrorMessage(e, "Erreur lors de la modification."));
    } finally {
        loading.value = false;
    }
};

const handleDelete = async () => {
    try {
        await axios.delete(`/users/${currentUserId.value}`);
        toast.success("Utilisateur supprimé.");
        resetModals();
        router.reload();
    } catch (e) {
        console.error(e);
        toast.error(getErrorMessage(e, "Erreur lors de la suppression"));
    }
};

const handleStatut = async () => {
    try {
        await axios.put(`/users/${currentUserId.value}/statut`, {
            statut: form.value.statut,
        });

        toast.success("Statut mis à jour.");
        resetModals();
        router.reload();
    } catch (e) {
        console.error(e, form.value.statut);
        toast.error(getErrorMessage(e, "Erreur lors du changement de statut"));
    }
};

// ============= GESTION DES PERMISSIONS =============

const isModalOpen = ref(false);
const isLoading = ref(false);
const selectedPermissions = ref([]);
const groupedPermissions = ref([]);
const currentUserForPermissions = ref(null);

// Vérifier si une permission est sélectionnée
const isPermissionSelected = (permissionId) => {
    return selectedPermissions.value.includes(permissionId);
};

// Ajouter ou retirer une permission du tableau
const togglePermission = (permissionId, isChecked) => {
    if (isChecked) {
        // Ajouter la permission si elle n'est pas déjà présente
        if (!selectedPermissions.value.includes(permissionId)) {
            selectedPermissions.value.push(permissionId);
        }
    } else {
        // Retirer la permission
        const index = selectedPermissions.value.indexOf(permissionId);
        if (index > -1) {
            selectedPermissions.value.splice(index, 1);
        }
    }
    console.log("Permissions sélectionnées:", selectedPermissions.value);
};

// Ouvrir le modal et récupérer les permissions
const openModal = async (user) => {
    currentUserForPermissions.value = user;
    isModalOpen.value = true;
    isLoading.value = true;

    try {
        // Récupérer toutes les permissions disponibles
        await fetchPermissions();

        // Charger les permissions de l'utilisateur
        await loadUserPermissions(user.id);
    } catch (error) {
        toast.error("Erreur lors du chargement des permissions");
    } finally {
        isLoading.value = false;
    }
};

// Fermer le modal
const closeModal = () => {
    isModalOpen.value = false;
    selectedPermissions.value = [];
    currentUserForPermissions.value = null;
};

// Récupérer les permissions depuis l'API
const fetchPermissions = async () => {
    try {
        const response = await axios.get("/permissions/list");
        groupedPermissions.value = response.data;
    } catch (error) {
        console.error(
            "Erreur lors de la récupération des permissions :",
            error
        );
    }
};

// Charger les permissions existantes de l'utilisateur
const loadUserPermissions = async (userId) => {
    try {
        const response = await axios.get(`/users/permissions/${userId}`);
        // Supposons que l'API renvoie un tableau d'IDs de permissions
        selectedPermissions.value = response.data.permissions || [];
    } catch (error) {
        console.error(
            "Erreur lors du chargement des permissions utilisateur :",
            error
        );
        selectedPermissions.value = [];
    }
};

// Enregistrer les permissions sélectionnées
const savePermissions = async () => {
    if (!currentUserForPermissions.value) {
        toast.error("Aucun utilisateur sélectionné");
        return;
    }

    isLoading.value = true;
    try {
        const response = await axios.post(
            `/users/permissions/${currentUserForPermissions.value.id}`,
            {
                permissions: selectedPermissions.value,
            }
        );
        console.log(response.data);

        toast.success(
            response.data.message || "Permissions enregistrées avec succès"
        );
        closeModal();
        router.reload();
    } catch (error) {
        console.error("Erreur lors de l'attribution des permissions :", error);
        toast.error(getErrorMessage(error, "Erreur lors de l'enregistrement des permissions"));
    } finally {
        isLoading.value = false;
    }
};

// Initial fetch des permissions au montage
onMounted(() => {
    fetchPermissions();
});
</script>

<script>
import { Card } from "@/components/ui/card";
import Main from "/resources/js/Pages/Main.vue";
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from "@/components/ui/dropdown-menu";
import {
    Table,
    TableHeader,
    TableBody,
    TableRow,
    TableHead,
    TableCell,
} from "@/components/ui/table";
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
import {
    AlertDialog,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from "@/components/ui/alert-dialog";

export default {
    layout: Main,
};
</script>
