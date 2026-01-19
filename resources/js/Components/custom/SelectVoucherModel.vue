<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="closeModal">
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
        />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-white border-1 border-gray-600 rounded-[20px] shadow-xl w-5/6 p-6 text-center"
          >
            <div class="flex flex-col items-center justify-start">
              <div class="w-full flex">
                <div class="w-full py-12 space-y-8">
                  <h2 class="text-3xl font-bold text-gray-800">Select Voucher to Purchase</h2>
                  <div class="flex items-center space-x-4 justify-start">
                    <div class="w-full">
                      <input
                        v-model="search"
                        @input="filterVouchers"
                        type="text"
                        placeholder="Search by category name or amount..."
                        class="w-full px-6 py-4 text-lg border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div class="flex w-full space-x-4 mb-6">
                <select
                  v-model="statusFilter"
                  @change="filterVouchers"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-purple-600 bg-white border-2 border-purple-300 rounded-lg cursor-pointer"
                >
                  <option value="">All Categories</option>
                  <option value="active">Active Only</option>
                  <option value="inactive">Inactive Only</option>
                </select>
                <span
                  @click="resetFilters"
                  class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-purple-600 rounded-lg cursor-pointer hover:bg-purple-700"
                >
                  Reset
                </span>
              </div>
            </div>

            <div class="mt-8">
              <template v-if="loading">
                <div class="text-center text-gray-500">
                  <p class="text-center text-purple-500 text-[17px]">Loading...</p>
                </div>
              </template>
              <template v-else>
                <template v-if="filteredCategories.length > 0">
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                      v-for="category in filteredCategories"
                      :key="category.id"
                      @click="category.available_count > 0 && selectVoucher(category)"
                      :class="[
                        'space-y-4 transition-transform duration-300 transform shadow-lg rounded-xl p-6 cursor-pointer',
                        category.available_count > 0
                          ? selectedVouchers.find((v) => v.id === category.id)
                            ? 'bg-purple-600 text-white border-4 border-green-500 hover:-translate-y-2'
                            : 'bg-white text-gray-800 border-4 border-purple-300 hover:-translate-y-2 hover:border-purple-500'
                          : 'bg-gray-200 text-gray-500 border-4 border-gray-400 cursor-not-allowed opacity-60',
                      ]"
                    >
                      <div class="flex justify-between items-start">
                        <div>
                          <h3 class="text-2xl font-bold">{{ category.name }}</h3>
                          <p class="text-sm mt-1" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'text-purple-100' : 'text-gray-600' : 'text-gray-500'">
                            {{ category.description || 'Gift Voucher' }}
                          </p>
                        </div>
                        <span
                          :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                          class="px-3 py-1 rounded-full text-xs font-semibold"
                        >
                          {{ category.is_active ? 'Active' : 'Inactive' }}
                        </span>
                      </div>
                      
                      <div class="py-4 border-t" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'border-purple-400' : 'border-gray-200' : 'border-gray-300'">
                        <p class="text-4xl font-bold" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'text-white' : 'text-purple-600' : 'text-gray-500'">
                          Rs {{ formatNumber(category.amount) }}
                        </p>
                      </div>

                      <div class="grid grid-cols-2 gap-4 pt-4 border-t" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'border-purple-400' : 'border-gray-200' : 'border-gray-300'">
                        <div class="text-center">
                          <p class="text-2xl font-bold" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'text-white' : 'text-green-600' : 'text-gray-500'">
                            {{ category.available_count }}
                          </p>
                          <p class="text-sm" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'text-purple-100' : 'text-gray-600' : 'text-gray-500'">Available</p>
                        </div>
                        <div class="text-center">
                          <p class="text-2xl font-bold" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'text-white' : 'text-gray-800' : 'text-gray-500'">
                            {{ category.total_count }}
                          </p>
                          <p class="text-sm" :class="category.available_count > 0 ? selectedVouchers.find((v) => v.id === category.id) ? 'text-purple-100' : 'text-gray-600' : 'text-gray-500'">Total</p>
                        </div>
                      </div>

                      <div v-if="category.available_count === 0" class="mt-2">
                        <p class="text-sm font-semibold text-red-600">
                          No vouchers available
                        </p>
                      </div>
                    </div>
                  </div>
                  
                  <div class="flex items-center justify-end mt-8">
                    <button
                      class="px-8 py-3 text-lg font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:bg-gray-400 disabled:cursor-not-allowed"
                      @click.prevent="closeModal(true)"
                      :disabled="selectedVouchers.length === 0"
                    >
                      Add to Cart ({{ selectedVouchers.length }})
                    </button>
                  </div>
                </template>
                <template v-else>
                  <div class="text-center text-gray-500 py-12">
                    <p class="text-center text-purple-500 text-[17px]">
                      No voucher categories available
                    </p>
                  </div>
                </template>
              </template>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { onMounted, ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

const loading = ref(false);
const search = ref("");
const statusFilter = ref("");
const voucherCategories = ref([]);
const selectedVouchers = ref([]);

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
};

const selectVoucher = (category) => {
  const index = selectedVouchers.value.findIndex((v) => v.id === category.id);
  if (index === -1) {
    selectedVouchers.value.push(category);
  } else {
    selectedVouchers.value.splice(index, 1);
  }
};

const filteredCategories = computed(() => {
  let filtered = voucherCategories.value;

  // Filter by search
  if (search.value) {
    filtered = filtered.filter(cat =>
      cat.name.toLowerCase().includes(search.value.toLowerCase()) ||
      cat.amount.toString().includes(search.value)
    );
  }

  // Filter by status
  if (statusFilter.value === 'active') {
    filtered = filtered.filter(cat => cat.is_active);
  } else if (statusFilter.value === 'inactive') {
    filtered = filtered.filter(cat => !cat.is_active);
  }

  return filtered;
});

const filterVouchers = () => {
  // Trigger reactivity
};

const resetFilters = () => {
  search.value = "";
  statusFilter.value = "";
};

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

const emit = defineEmits(["update:open", "selected-vouchers"]);

const { open } = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
});

const closeModal = (triggerImport = false) => {
  playClickSound();
  emit("update:open", false);

  if (triggerImport) {
    emit("selected-vouchers", selectedVouchers.value);
  }
  selectedVouchers.value = [];
};

const fetchVoucherCategories = async () => {
  loading.value = true;
  try {
    const response = await axios.get('/api/voucher-categories');
    voucherCategories.value = response.data.categories;
  } catch (error) {
    console.error("Error fetching voucher categories:", error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchVoucherCategories();
});
</script>
