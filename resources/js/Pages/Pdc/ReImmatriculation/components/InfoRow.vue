<template>
  <div class="flex m-1 ">
    <p class="font-medium">{{ label }} : </p>
    <p :class="highlight ? 'text-orange-500 font-semibold mx-1' : 'text-gray-700 font-semibold mx-1'">{{ value || '' }}</p>
  </div>
</template>

<script>
export default {
  props: {
    label: String,
    value: [String, Number, null],
    highlight: {
      type: Boolean,
      default: false
    }
  }
}
</script>
