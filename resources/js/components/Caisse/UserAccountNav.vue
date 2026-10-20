<template>
  <div class="flex items-center cursor-pointer">
    <DropdownMenu>
      <DropdownMenuTrigger as-child>
        <Avatar class="h-10 w-10 border border-gray-100 hover:ring-2 hover:ring-amber-500/20 transition-all">
          <AvatarFallback class="bg-amber-100 text-amber-700 font-bold">
            {{ getInitials() }}
          </AvatarFallback>
        </Avatar>
      </DropdownMenuTrigger>
      <DropdownMenuContent class="w-56" align="end">
        <DropdownMenuLabel class="font-normal">
          <div class="flex flex-col space-y-1">
            <p class="text-sm font-medium leading-none">{{ user?.nom }} {{ user?.prenom }}</p>
            <p class="text-xs leading-none text-muted-foreground">{{ user?.email }}</p>
          </div>
        </DropdownMenuLabel>
        <DropdownMenuSeparator />
        <DropdownMenuGroup>
          <DropdownMenuItem @click="openProfile = true" class="cursor-pointer">
            <User class="mr-2 h-4 w-4" />
            <span>Profile</span>
          </DropdownMenuItem>
          <DropdownMenuItem @click="openPassword = true" class="cursor-pointer">
            <Settings class="mr-2 h-4 w-4" />
            <span>Mot de passe</span>
          </DropdownMenuItem>
        </DropdownMenuGroup>
        <DropdownMenuSeparator />
        <DropdownMenuItem @click="logout" class="cursor-pointer text-red-600 focus:text-red-600">
          <LogOut class="mr-2 h-4 w-4" />
          <span>Déconnexion</span>
        </DropdownMenuItem>
      </DropdownMenuContent>
    </DropdownMenu>

    <!-- Optional Sidebar Toggle (passed via prop or used only in layouts) -->
    <button v-if="showSidebarToggle" data-drawer-target="sidebar-multi-level-sidebar"
      data-drawer-toggle="sidebar-multi-level-sidebar" aria-controls="sidebar-multi-level-sidebar" type="button"
      class="inline-flex items-center p-2 mt-2 ms-3 text-sm text-gray-500 rounded-lg sm:hidden hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-200">
      <span class="sr-only">Open sidebar</span>
      <svg class="w-6 h-6" aria-hidden="true" fill="currentColor" viewBox="0 0 20 20"
        xmlns="http://www.w3.org/2000/svg">
        <path clip-rule="evenodd" fill-rule="evenodd"
          d="M2 4.75A.75.75 0 012.75 4h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 4.75zm0 10.5a.75.75 0 01.75-.75h7.5a.75.75 0 010 1.5h-7.5a.75.75 0 01-.75-.75zM2 10a.75.75 0 01.75-.75h14.5a.75.75 0 010 1.5H2.75A.75.75 0 012 10z">
        </path>
      </svg>
    </button>

    <!-- Dialogs -->
    <AlertDialog :open="openPassword" @update:open="openPassword = $event">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Modifier votre mot de passe</AlertDialogTitle>
          <AlertDialogDescription>
            <div class="space-y-4 pt-4">
              <Input v-model="passwords.old" placeholder="Ancien mot de passe" type="password" />
              <Input v-model="passwords.new" placeholder="Nouveau mot de passe" type="password" />
              <Input v-model="passwords.confirm" placeholder="Confirmer le nouveau mot de passe" type="password" />
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Annuler</AlertDialogCancel>
          <Button @click="handlePasswordChange" :disabled="loading">
            <span v-if="loading" class="mr-2 h-4 w-4 animate-spin">⏳</span>
            Valider
          </Button>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog v-model:open="openProfile">
      <DialogContent class="sm:max-w-[480px] gap-0 p-0 overflow-hidden">
        <DialogHeader class="border-b px-6 py-5 text-left">
          <DialogTitle class="text-lg font-semibold text-slate-900">
            Mon profil
          </DialogTitle>
          <DialogDescription class="text-sm text-slate-500">
            Informations associées à votre compte.
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-5 px-6 py-5">
          <!-- Identité -->
          <div class="flex items-center gap-4">
            <Avatar class="h-14 w-14">
              <AvatarFallback class="bg-amber-100 text-lg font-bold text-amber-700">
                {{ getInitials() }}
              </AvatarFallback>
            </Avatar>
            <div class="min-w-0">
              <p class="truncate text-base font-semibold text-slate-900">
                {{ user?.nom }} {{ user?.prenom }}
              </p>
              <span
                class="mt-1 inline-flex items-center rounded-full border border-amber-200 bg-amber-50 px-2.5 py-0.5 text-xs font-medium text-amber-700">
                {{ user?.r_user_role?.nom_role || "—" }}
              </span>
            </div>
          </div>

          <!-- Détails du compte -->
          <div class="divide-y divide-slate-100 overflow-hidden rounded-lg border border-slate-200">
            <div class="flex items-start gap-3 px-4 py-3">
              <Mail class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
              <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Email</p>
                <p class="break-all text-sm font-medium text-slate-700">{{ user?.email || "—" }}</p>
              </div>
            </div>

            <div class="flex items-start gap-3 px-4 py-3">
              <MapPin class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
              <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Site</p>
                <p class="text-sm font-medium text-slate-700">{{ user?.r_user_site?.nom_site || "—" }}</p>
                <p v-if="user?.r_user_site?.region" class="text-xs text-slate-400">
                  {{ user.r_user_site.region }}
                </p>
              </div>
            </div>

            <div class="flex items-start gap-3 px-4 py-3">
              <Building2 class="mt-0.5 h-4 w-4 shrink-0 text-slate-400" />
              <div class="min-w-0">
                <p class="text-[11px] font-semibold uppercase tracking-wide text-slate-400">Type d'établissement</p>
                <p class="text-sm font-medium text-slate-700">{{ user?.r_user_site?.type_site || "—" }}</p>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="border-t bg-slate-50/60 px-6 py-4">
          <Button variant="outline" @click="openProfile = false">Fermer</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { toast } from "vue-sonner";
import { Avatar, AvatarFallback } from "@/components/ui/avatar";
import { 
  DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, 
  DropdownMenuSeparator, DropdownMenuTrigger, DropdownMenuGroup 
} from "@/components/ui/dropdown-menu";
import { LogOut, Settings, User, Mail, MapPin, Building2 } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  AlertDialog, AlertDialogCancel, AlertDialogContent, AlertDialogDescription,
  AlertDialogFooter, AlertDialogHeader, AlertDialogTitle
} from "@/components/ui/alert-dialog";
import {
  Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle
} from "@/components/ui/dialog";

defineProps({
  showSidebarToggle: {
    type: Boolean,
    default: false
  }
});

const page = usePage();
const user = computed(() => page.props.auth_user?.data);

const openProfile = ref(false);
const openPassword = ref(false);
const loading = ref(false);

const passwords = reactive({
  old: '',
  new: '',
  confirm: ''
});

const getInitials = () => {
  if (user.value?.nom && user.value?.prenom) {
    return `${user.value.nom.charAt(0)}${user.value.prenom.charAt(0)}`.toUpperCase();
  }
  return "";
};

const logout = async () => {
  try {
    await axios.post("/logout");
    router.visit("/");
  } catch (error) {
    console.error("Erreur déconnexion:", error);
    router.visit("/");
  }
};

const handlePasswordChange = async () => {
  if (!passwords.old || !passwords.new || !passwords.confirm) {
    toast.error("Veuillez remplir tous les champs");
    return;
  }

  loading.value = true;
  try {
    await axios.post(`/users/change-password/${user.value.id}`, {
      old_password: passwords.old,
      new_password: passwords.new,
      new_password_confirmation: passwords.confirm,
    });
    toast.success("Mot de passe modifié avec succès");
    openPassword.value = false;
    passwords.old = passwords.new = passwords.confirm = "";
  } catch (error) {
    const msg = error.response?.data?.errors ? Object.values(error.response.data.errors)[0][0] : "Une erreur est survenue";
    toast.error(msg);
  } finally {
    loading.value = false;
  }
};
</script>
