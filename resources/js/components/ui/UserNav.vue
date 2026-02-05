<script setup>
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'

import { Button } from '@/components/ui/button'
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuLabel, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu'
import { Link } from '@inertiajs/vue3';

// Définir les props
const props = defineProps({
    name: {
        type: String,
        required: true,
    },
    email: {
        type: String,
        required: true,
    },
    avatarSrc: {
        type: String,
        default: '/avatars/01.png', // Valeur par défaut
    },
    avatarAlt: {
        type: String,
        default: 'Avatar', // Valeur par défaut
    },
    avatarFallback: {
        type: String,
        default: 'SC', // Valeur par défaut
    },
})
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="relative h-8 w-8 rounded-full">
                <Avatar class="h-8 w-8">
                    <AvatarImage :src="props.avatarSrc" :alt="props.avatarAlt" />
                    <AvatarFallback>{{ props.avatarFallback }}</AvatarFallback>
                </Avatar>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56" align="end">
            <DropdownMenuLabel class="font-normal flex">
                <div class="flex flex-col space-y-1">
                    <p class="text-sm font-medium leading-none">
                        {{ props.name }}
                    </p>
                    <p class="text-xs leading-none text-muted-foreground">
                        {{ props.email }}
                    </p>
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuGroup>
                <DropdownMenuItem>
                    Administrateur
                    <DropdownMenuShortcut></DropdownMenuShortcut>
                </DropdownMenuItem>
                <DropdownMenuItem>
                    Site de cocody
                    <DropdownMenuShortcut></DropdownMenuShortcut>
                </DropdownMenuItem>
                <!-- <DropdownMenuItem>
                    Settings
                    <DropdownMenuShortcut></DropdownMenuShortcut>
                </DropdownMenuItem>
                <DropdownMenuItem>New Team</DropdownMenuItem> -->
            </DropdownMenuGroup>
            <DropdownMenuSeparator />


            <DropdownMenuItem>
                <Link :href="route('logout')" method="post" as="button">
                Déconnexion
                </Link>

                <DropdownMenuShortcut></DropdownMenuShortcut>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>