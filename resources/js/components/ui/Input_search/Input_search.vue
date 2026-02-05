<script setup>
import { cn } from '@/lib/utils';
import { useVModel } from '@vueuse/core';
import { computed } from 'vue';

const props = defineProps({
  defaultValue: { type: [String, Number], required: false },
  modelValue: { type: [String, Number], required: false },
  class: { type: null, required: false },
});

const emits = defineEmits(['update:modelValue']);
const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
});

const showClear = computed(() => modelValue.value?.toString().length > 0);
function clearInput() {
  modelValue.value = '';
}
</script>

<template>
  <div class="relative w-full md:w-1/4">
    <!-- Champ avec padding à droite pour laisser place à l'icône -->
    <input placeholder="Recherche par vin ou chrono" v-model="modelValue" :class="cn(
      'flex h-9 w-full rounded-md border  px-3 py-1 pr-10 text-sm shadow-sm transition-colors placeholder:text-gray-400 focus:outline-none focus:ring-1 focus:ring-gray-200',
      props.class,
    )
      " />

    <!-- Bouton dans le champ (à l'intérieur des bordures) -->
    <button v-if="showClear" @click="clearInput" type="button"
      class="absolute right-2 top-1/2 -translate-y-1/2 flex h-5 w-5 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-500 hover:text-red-500">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg>
    </button>
  </div>
</template>
