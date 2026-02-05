<template>
    <Button>
        <MoveLeft class=" w-4 h-4 mr-2" size="sm" /> Retour
    </Button>
</template>

<script setup>
import { MoveRight, MoveLeft } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { returnBack } from "../composable/fonction"
</script>