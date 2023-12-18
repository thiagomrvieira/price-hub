<template>
    <div class="max-w-2xl mx-auto bg-white p-8 rounded-md shadow-md">
      <div class="text-2xl font-semibold mb-4">Product Prices</div>
  
      <!-- Form para inserir parâmetros -->
      <form @submit.prevent="getProductPrice">
        <div class="flex mb-4">
          <label class="flex-1 mr-2">
            Product Code:
            <input v-model="productCode" type="text" class="border p-2 w-full" required />
          </label>
          <label class="flex-1 ml-2">
            Account ID (Optional):
            <input v-model="accountId" type="text" class="border p-2 w-full" />
          </label>
        </div>
        <button type="submit" class="bg-blue-500 text-white py-2 px-4 rounded-full hover:bg-blue-700 transition">Get Price</button>
      </form>
  
      <div v-if="result">
        <div class="text-lg font-semibold mt-6">Result:</div>
        <div>Product SKU: {{ result.sku }}</div>
        <div>Product best price: ${{ result.price.toFixed(2) }}</div>
      </div>
    </div>
  </template>
  
  <script>
  export default {
    data() {
      return {
        productCode: '',
        accountId: '',
        result: null,
      };
    },
    methods: {
        async getProductPrice() {
            try {
                const response = await axios.get(`/api/v1/prices/${this.productCode}/${this.accountId || ''}`);
                let data = response.data;
                this.result = {
                    sku: data.data.sku,
                    price: parseFloat(data.data.price),
                };
                this.error = null;
            } catch (error) {
                this.result = null;
                this.error = 'Error fetching product price.';
                console.error('Error fetching product price:', error.message);
            }
        },
    },
  };
  </script>
  
  <style scoped>
  </style>
  