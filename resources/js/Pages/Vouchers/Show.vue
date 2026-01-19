<template>
  <Head :title="`${category.name} Vouchers`" />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <div class="w-full space-y-6">
      <div class="flex justify-between items-center">
        <div>
          <Link href="/vouchers" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
            ← Back to Vouchers
          </Link>
          <h1 class="text-4xl font-bold text-gray-800">{{ category.name }} Vouchers</h1>
          <p class="text-xl text-gray-600 mt-2">Rs {{ formatNumber(category.amount) }} each</p>
        </div>
        <div class="text-right">
          <p class="text-3xl font-bold text-blue-600">{{ vouchers.length }}</p>
          <p class="text-gray-600">Total Vouchers</p>
        </div>
      </div>

      <!-- Filter Options -->
      <div class="bg-white rounded-xl shadow p-4">
        <div class="flex space-x-4">
          <button
            @click="filterStatus = 'all'"
            :class="filterStatus === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded-lg transition-colors"
          >
            All ({{ vouchers.length }})
          </button>
          <button
            @click="filterStatus = 'active'"
            :class="filterStatus === 'active' ? 'bg-green-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded-lg transition-colors"
          >
            Active ({{ activeCount }})
          </button>
          <button
            @click="filterStatus = 'inactive'"
            :class="filterStatus === 'inactive' ? 'bg-gray-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded-lg transition-colors"
          >
            Inactive ({{ inactiveCount }})
          </button>
          <button
            @click="filterStatus = 'used'"
            :class="filterStatus === 'used' ? 'bg-red-600 text-white' : 'bg-gray-200 text-gray-700'"
            class="px-4 py-2 rounded-lg transition-colors"
          >
            Used ({{ usedCount }})
          </button>
        </div>
      </div>

      <!-- Search -->
      <div class="bg-white rounded-xl shadow p-4">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by voucher code..."
          class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <!-- Vouchers Table -->
      <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">#</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Voucher Code</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Status</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Issued Date</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Used Date</th>
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr
                v-for="(voucher, index) in filteredVouchers"
                :key="voucher.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 text-sm text-gray-700">{{ index + 1 }}</td>
                <td class="px-6 py-4">
                  <code class="px-3 py-1 bg-gray-100 rounded text-sm font-mono">{{ voucher.voucher_code }}</code>
                </td>
                <td class="px-6 py-4">
                  <span
                    :class="voucher.is_used ? 'bg-red-100 text-red-800' : (voucher.sale_id ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800')"
                    class="px-3 py-1 rounded-full text-xs font-semibold"
                  >
                    {{ voucher.is_used ? 'Used' : (voucher.sale_id ? 'Active' : 'Inactive') }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ formatDate(voucher.issued_at) }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-700">
                  {{ voucher.used_at ? formatDate(voucher.used_at) : '-' }}
                </td>
                <td class="px-6 py-4">
                  <div class="flex space-x-2">
                    <button
                      v-if="!voucher.is_used && voucher.sale_id"
                      @click="markAsUsed(voucher.id)"
                      class="px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 transition-colors text-sm"
                    >
                      Mark as Used
                    </button>
                    <button
                      v-if="!voucher.sale_id"
                      @click="deleteVoucher(voucher.id)"
                      class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600 transition-colors text-sm"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="filteredVouchers.length === 0" class="text-center py-12">
          <p class="text-gray-500 text-lg">No vouchers found matching your criteria.</p>
        </div>
      </div>
    </div>
  </div>

  <Footer />
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';

const props = defineProps({
  category: Object,
  vouchers: Array,
});

const filterStatus = ref('all');
const searchQuery = ref('');

const activeCount = computed(() => props.vouchers.filter(v => !v.is_used && v.sale_id).length);
const inactiveCount = computed(() => props.vouchers.filter(v => !v.is_used && !v.sale_id).length);
const usedCount = computed(() => props.vouchers.filter(v => v.is_used).length);

const filteredVouchers = computed(() => {
  let filtered = props.vouchers;

  // Filter by status
  if (filterStatus.value === 'active') {
    filtered = filtered.filter(v => !v.is_used && v.sale_id);
  } else if (filterStatus.value === 'inactive') {
    filtered = filtered.filter(v => !v.is_used && !v.sale_id);
  } else if (filterStatus.value === 'used') {
    filtered = filtered.filter(v => v.is_used);
  }

  // Filter by search query
  if (searchQuery.value) {
    filtered = filtered.filter(v =>
      v.voucher_code.toLowerCase().includes(searchQuery.value.toLowerCase())
    );
  }

  return filtered;
});

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(num);
};

const formatDate = (date) => {
  if (!date) return '-';
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};

const markAsUsed = (id) => {
  if (confirm('Are you sure you want to mark this voucher as used?')) {
    router.post(`/vouchers/${id}/mark-used`);
  }
};

const deleteVoucher = (id) => {
  if (confirm('Are you sure you want to delete this voucher?')) {
    router.delete(`/vouchers/${id}`);
  }
};
</script>

<style scoped>
@media print {
  .no-print {
    display: none;
  }
}
</style>
