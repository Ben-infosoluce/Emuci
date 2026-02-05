<script setup>
import { ref, onMounted } from 'vue';
import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { useVModel } from '@vueuse/core';
import { cn } from '@/lib/utils';
const props = defineProps({
  defaultValue: { type: String, required: false },
  modelValue: { type: String, required: false },
  class: { type: null, required: false },
});

const emits = defineEmits(['update:modelValue']);
const modelValue = useVModel(props, 'modelValue', emits, {
  passive: true,
  defaultValue: props.defaultValue,
});
const inputRef = ref(null);

onMounted(() => {
  flatpickr(inputRef.value, {
    dateFormat: 'd/m/Y',
    onChange: (selectedDates) => {
      modelValue.value = selectedDates[0] ? selectedDates[0].toLocaleDateString('fr-FR') : '';
    },
  });
});
</script>

<template>
  <input ref="inputRef" v-model="modelValue" :class="cn(
    'flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus:outline-none focus:ring-1 focus:ring-gray-200 disabled:opacity-50',
    props.class,
  )" placeholder="jj/mm/aaaa" />
</template>
