<script setup>
import { DateField, DateFieldInput } from '@/components/ui/date-field';
import { Label } from '@/components/ui/label';

import { useId } from "vue";

const id = useId();
</script>

<template>
  <div class="*:not-first:mt-2">
    <DateField :id="id" v-slot="{ segments }">
      <template v-for="item in segments" :key="item.part">
        <DateFieldInput :part="item.part">
          {{ item.value }}
        </DateFieldInput>
      </template>
    </DateField>
  </div>
</template>
