<template>
  <Head title="Voucher Categories" />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <div class="w-full space-y-6">
      <div class="flex justify-between items-center">
        <h1 class="text-4xl font-bold text-gray-800">Voucher Categories</h1>
        <button
          @click="openCreateModal"
          class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
        >
          + Add Category
        </button>
      </div>

      <!-- Categories Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="category in categories"
          :key="category.id"
          class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow"
        >
          <div class="space-y-4">
            <div class="flex justify-between items-start">
              <div>
                <h3 class="text-2xl font-bold text-gray-800">{{ category.name }}</h3>
                <p class="text-3xl font-bold text-blue-600 mt-2">Rs {{ formatNumber(category.amount) }}</p>
              </div>
              <span
                :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                class="px-3 py-1 rounded-full text-sm font-semibold"
              >
                {{ category.is_active ? 'Active' : 'Inactive' }}
              </span>
            </div>
            
            <p class="text-gray-600">{{ category.description || 'No description' }}</p>
            
            <div class="flex space-x-2 pt-4 border-t">
              <button
                @click="openEditModal(category)"
                class="flex-1 px-4 py-2 bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition-colors"
              >
                Edit
              </button>
              <button
                @click="deleteCategory(category.id)"
                class="flex-1 px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="categories.length === 0" class="text-center py-12">
        <p class="text-gray-500 text-xl">No voucher categories found. Create one to get started!</p>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <div
      v-if="showModal"
      class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      @click.self="closeModal"
    >
      <div class="bg-white rounded-xl p-8 max-w-md w-full mx-4">
        <h2 class="text-2xl font-bold mb-6">{{ editingCategory ? 'Edit' : 'Create' }} Category</h2>
        
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category Name</label>
            <input
              v-model="form.name"
              type="text"
              required
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="e.g., 1000, 3000, 10000"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Amount (Rs)</label>
            <input
              v-model="form.amount"
              type="number"
              step="0.01"
              required
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="Enter amount"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="Enter description (optional)"
            ></textarea>
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
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors"
            >
              {{ editingCategory ? 'Update' : 'Create' }}
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
import { Head, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';

const props = defineProps({
  categories: Array,
});

const showModal = ref(false);
const editingCategory = ref(null);
const form = reactive({
  name: '',
  amount: '',
  description: '',
  is_active: true,
});

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
};

const openCreateModal = () => {
  editingCategory.value = null;
  form.name = '';
  form.amount = '';
  form.description = '';
  form.is_active = false;
  showModal.value = true;
};

const openEditModal = (category) => {
  editingCategory.value = category;
  form.name = category.name;
  form.amount = category.amount;
  form.description = category.description;
  form.is_active = category.is_active;
  showModal.value = true;
};

const closeModal = () => {
  showModal.value = false;
  editingCategory.value = null;
};

const submitForm = () => {
  if (editingCategory.value) {
    router.put(`/voucher-categories/${editingCategory.value.id}`, form, {
      onSuccess: () => closeModal(),
    });
  } else {
    router.post('/voucher-categories', form, {
      onSuccess: () => closeModal(),
    });
  }
};

const deleteCategory = (id) => {
  if (confirm('Are you sure you want to delete this category?')) {
    router.delete(`/voucher-categories/${id}`);
  }
};
</script>

<style scoped>
/* Add any additional styles here if needed */
</style>
