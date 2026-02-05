<script setup>
import { cn } from '@/lib/utils'

const props = defineProps({
  class: {
    type: String,
    required: false,
  },
})
</script>

<template>
  <div data-sidebar="header" :class="cn('flex flex-col gap-2 p-2', props.class)">
    <slot />
  </div>
</template>
