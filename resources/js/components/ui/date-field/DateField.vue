<script setup>
import { cn } from "@/lib/utils";
import { DateFieldRoot, useForwardPropsEmits } from "reka-ui";
import { computed } from "vue";
const props = defineProps({
  defaultValue: { type: null, required: false },
  defaultPlaceholder: { type: null, required: false },
  placeholder: { type: null, required: false },
  modelValue: { type: null, required: false },
  hourCycle: { type: null, required: false },
  step: { type: Object, required: false },
  granularity: { type: String, required: false },
  hideTimeZone: { type: Boolean, required: false },
  maxValue: { type: null, required: false },
  minValue: { type: null, required: false },
  locale: { type: String, required: false },
  disabled: { type: Boolean, required: false },
  readonly: { type: Boolean, required: false },
  isDateUnavailable: { type: Function, required: false },
  id: { type: String, required: false },
  dir: { type: String, required: false },
  asChild: { type: Boolean, required: false },
  as: { type: null, required: false },
  name: { type: String, required: false },
  required: { type: Boolean, required: false },
  class: { type: null, required: false },
});

const emits = defineEmits(["update:modelValue", "update:placeholder"]);

const delegatedProps = computed(() => {
  const { class: _, ...delegated } = props;

  return delegated;
});

const forwarded = useForwardPropsEmits(delegatedProps, emits);
</script>

<template>
  <DateFieldRoot v-bind="forwarded" v-slot="slotProps" :class="cn(
    'border-input bg-background text-foreground focus-within:border-ring focus-within:ring-ring/50 flex h-9 items-center rounded-md border px-3 shadow-xs transition-[color,box-shadow] outline-none focus-within:ring-[3px]',
    props.class,
  )
    ">
    <slot v-bind="slotProps" />
  </DateFieldRoot>
</template>
