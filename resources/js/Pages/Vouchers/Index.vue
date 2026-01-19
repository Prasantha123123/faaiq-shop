<template>
  <Head title="Vouchers" />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <div class="w-full space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold text-gray-800">Vouchers Management</h1>
        <div class="flex space-x-4">
          <Link
            href="/voucher-categories"
            class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
          >
            Manage Categories
          </Link>
          <button
            @click="openCreateModal"
            class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
          >
            + Generate Vouchers
          </button>
        </div>
      </div>

      <!-- Voucher Groups by Category -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="group in voucherGroups"
          :key="group.category.id"
          class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer"
          @click="viewVouchers(group.category)"
        >
          <div class="space-y-4">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ group.category.name }}</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2">Rs {{ formatNumber(group.category.amount) }}</p>
              </div>
            </div>
            
            <div class="grid grid-cols-3 gap-2 pt-4 border-t">
              <div class="text-center">
                <p class="text-2xl font-bold text-gray-800">{{ group.total_vouchers }}</p>
                <p class="text-sm text-gray-600">Total</p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-green-600">{{ group.available_vouchers }}</p>
                <p class="text-sm text-gray-600">Available</p>
              </div>
              <div class="text-center">
                <p class="text-2xl font-bold text-red-600">{{ group.used_vouchers }}</p>
                <p class="text-sm text-gray-600">Used</p>
              </div>
            </div>

            <button
              class="w-full px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors"
            >
              View Vouchers
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="voucherGroups.length === 0" class="text-center py-12">
        <p class="text-gray-500 text-xl">No vouchers generated yet. Create some to get started!</p>
      </div>
    </div>

    <!-- Generate Vouchers Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold mb-6">Generate Vouchers</h2>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Select Category</label>
            <select
              v-model="form.voucher_category_id"
              required
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
            >
              <option value="">Choose a category</option>
              <option v-for="category in categories" :key="category.id" :value="category.id">
                {{ category.name }} - Rs {{ formatNumber(category.amount) }}
              </option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Vouchers</label>
            <input
              v-model="form.quantity"
              type="number"
              min="1"
              max="1000"
              required
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="How many vouchers to generate?"
            />
            <p class="text-sm text-gray-500 mt-1">Enter a number between 1 and 1000</p>
          </div>

          <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-blue-800">
              <strong>Note:</strong> Each voucher will be assigned a unique random code.
            </p>
          </div>

          <div class="flex space-x-3 pt-4">
            <button
              type="button"
              @click="closeModal"
              class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="processing"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            >
              {{ processing ? 'Generating...' : 'Generate' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, reactive } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';

const props = defineProps({
  voucherGroups: Array,
  categories: Array,
});

const showModal = ref(false);
const processing = ref(false);
const form = reactive({
  voucher_category_id: '',
  quantity: 1,
});

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
};

const openCreateModal = () => {
  form.voucher_category_id = '';
  form.quantity = 1;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
};

const submitForm = () => {
  processing.value = true;
  router.post('/vouchers', form, {
    onSuccess: () => {
      closeModal();
      processing.value = false;
    },
    onError: () => {
      processing.value = false;
    },
  });
};

const viewVouchers = (category) => {
  router.get(`/vouchers/category/${category.id}`);
};
</script>

<style scoped>
/* Add any additional styles here if needed */
</style>
