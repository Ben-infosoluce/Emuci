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

    <AlertDialog :open="openProfile" @update:open="openProfile = $event">
      <AlertDialogContent class="sm:max-w-[450px] overflow-hidden p-0 rounded-2xl border-none shadow-[0_20px_50px_rgba(0,0,0,0.15)] bg-white">
        <!-- Premium Header with Pattern -->
        <div class="relative h-40 bg-[#1e293b] overflow-hidden">
          <div class="absolute inset-0 opacity-30">
            <div class="absolute -top-24 -left-20 w-64 h-64 bg-amber-500 rounded-full blur-[80px]"></div>
            <div class="absolute -bottom-24 -right-20 w-64 h-64 bg-blue-600 rounded-full blur-[80px]"></div>
          </div>
          
          <div class="absolute top-5 right-5 z-20">
            <button @click="openProfile = false" class="p-2 rounded-full bg-white/10 text-white/80 hover:bg-white/20 hover:text-white transition-all backdrop-blur-md">
              <X class="h-4 w-4" />
            </button>
          </div>
          
          <div class="absolute bottom-0 left-1/2 -translate-x-1/2 z-99">
            <div class="relative group">
              <div class="absolute -inset-1 bg-gradient-to-tr from-amber-500 to-amber-200 rounded-full blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
              <Avatar class="h-28 w-28 border-[6px] border-white shadow-2xl relative">
                <AvatarFallback class="text-3xl font-black bg-gray-100 text-slate-800 tracking-tighter">
                  {{ getInitials() }}
                </AvatarFallback>
              </Avatar>
            </div>
          </div>
        </div>

        <!-- Content Area -->
        <div class="pt-16 pb-10 px-8">
          <div class="text-center space-y-1 mb-8">
            <h2 class="text-2xl font-black text-slate-900 tracking-tight leading-tight">
              {{ user?.nom }} {{ user?.prenom }}
            </h2>
            <div class="flex items-center justify-center gap-2">
              <span class="px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-[11px] font-bold uppercase tracking-widest border border-amber-100/50 shadow-sm">
                {{ user?.r_user_role?.nom_role }}
              </span>
            </div>
          </div>

          <div class="grid gap-3">
            <!-- Info Card: Email -->
            <div class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-amber-200 hover:bg-white hover:shadow-md transition-all duration-300">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm border border-slate-100 group-hover:bg-amber-50 group-hover:border-amber-100 transition-colors">
                <span class="text-slate-400 group-hover:text-amber-600"><User class="h-5 w-5" /></span>
              </div>
              <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-tighter text-slate-400 mb-0.5">Identifiant Email</p>
                <p class="text-sm font-semibold text-slate-700 truncate">{{ user?.email }}</p>
              </div>
            </div>

            <!-- Info Card: Site -->
            <div class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-blue-200 hover:bg-white hover:shadow-md transition-all duration-300">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm border border-slate-100 group-hover:bg-blue-50 group-hover:border-blue-100 transition-colors">
                <span class="text-slate-400 group-hover:text-blue-600"><Settings class="h-5 w-5" /></span>
              </div>
              <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-tighter text-slate-400 mb-0.5">Localisation de service</p>
                <p class="text-sm font-semibold text-slate-700">
                  {{ user?.r_user_site?.nom_site }}
                  <span class="text-slate-300 mx-1">/</span>
                  <span class="text-slate-500 font-medium">{{ user?.r_user_site?.region }}</span>
                </p>
              </div>
            </div>

            <!-- Info Card: Agency Type -->
            <div class="group flex items-center gap-4 p-4 rounded-xl bg-slate-50/50 border border-slate-100 hover:border-emerald-200 hover:bg-white hover:shadow-md transition-all duration-300">
              <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm border border-slate-100 group-hover:bg-emerald-50 group-hover:border-emerald-100 transition-colors">
                <span class="text-slate-400 group-hover:text-emerald-600"><LockKeyhole class="h-5 w-5" /></span>
              </div>
              <div class="flex-1">
                <p class="text-[10px] font-bold uppercase tracking-tighter text-slate-400 mb-0.5">Type d'établissement</p>
                <p class="text-sm font-semibold text-slate-700">{{ user?.r_user_site?.type_site || 'N/A' }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Decorative Footer -->
        <div class="h-1.5 w-full bg-gradient-to-r from-amber-500 via-amber-300 to-blue-600 opacity-80"></div>
      </AlertDialogContent>
    </AlertDialog>
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
import { LogOut, Settings, User, X, LockKeyhole } from "lucide-vue-next";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import {
  AlertDialog, AlertDialogCancel, AlertDialogContent, AlertDialogDescription,
  AlertDialogFooter, AlertDialogHeader, AlertDialogTitle
} from "@/components/ui/alert-dialog";

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
