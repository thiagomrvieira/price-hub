<template>
  <div>
    <div class="text-lg font-semibold mt-6">Result:</div>
    <div>Product SKU: {{ result.sku }}</div>
    <div>Product best price: ${{ result.price.toFixed(2) }}</div>
  </div>
</template>

<script>
export default {
  props: {
    result: {
      type: Object,
      required: true,
    },
  },
};
</script>

<style scoped>
</style>
