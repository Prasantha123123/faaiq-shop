<template>
    <Head title="POS" />
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-4 bg-gray-100 md:px-36 px-16">
        <!-- Include the Header -->
        <Header />

        <div class="w-full md:w-5/6 w-full py-12 space-y-16">
            <div class="flex items-center justify-between space-x-4">
                <div class="flex w-full space-x-4">
                    <Link href="/">
                        <img src="/images/back-arrow.png" class="w-14 h-14" />
                    </Link>
                    <p class="pt-3 text-4xl font-bold tracking-wide text-black uppercase">
                        PoS
                    </p>
                </div>
                <div class="flex items-center justify-between w-full space-x-4">
                    <p class="text-3xl font-bold tracking-wide text-black">
                        Order ID : #{{ orderid }}
                    </p>
                    <p class="text-3xl text-black cursor-pointer">
                        <i @click="refreshData" class="ri-restart-line"></i>
                    </p>
                </div>
            </div>
            <div class="flex md:flex-row flex-col w-full gap-4">
                <!-- LEFT SIDE -->
                <div class="flex flex-col md:w-1/2 w-full">
                    <div class="flex flex-col w-full">
                        <div class="p-16 space-y-8 bg-black shadow-lg rounded-3xl">
                            <p class="mb-4 text-5xl font-bold text-white">Customer Details</p>
                            <div class="mb-3">
                                <input
                                    v-model="customer.name"
                                    type="text"
                                    placeholder="Enter Customer Name"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div class="flex gap-2 mb-3 text-black">
                                <input
                                    v-model="customer.contactNumber"
                                    type="text"
                                    placeholder="Enter Customer Contact Number"
                                    class="flex-grow px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div class="text-black">
                                <input
                                    v-model="customer.email"
                                    type="email"
                                    placeholder="Enter Customer Email"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>

                            <div class="text-black">
                                <select
                                    required
                                    v-model="employee_id"
                                    id="employee_id"
                                    class="w-full px-4 py-4 text-black placeholder-black bg-white rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                >
                                    <option value="" disabled selected>Select an Employee</option>
                                    <option v-for="employee in allemployee" :key="employee.id" :value="employee.id">
                                        {{ employee.name }}
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex flex-col items-center justify-center w-full md:pt-32 py-8 md:py-0 space-y-8"
                    >
                        <img src="/images/Fading wheel.gif" class="object-cover w-32 h-32 rounded-full" />
                        <p class="text-3xl text-black">
                            Bar Code Scanner is in Progress...
                        </p>
                    </div>
                </div>

                <!-- RIGHT SIDE -->
                <div class="flex md:w-1/2 w-full p-8 border-4 border-black rounded-3xl">
                    <div class="flex flex-col items-start justify-center w-full md:px-12 px-4">
                        <div class="flex items-center justify-between w-full">
                            <h2 class="md:text-5xl text-4xl font-bold text-black">Billing Details</h2>
                            <span class="flex cursor-pointer" @click="isSelectModalOpen = true">
                                <p class="text-xl text-blue-600 font-bold">User Manual</p>
                                <img src="/images/selectpsoduct.svg" class="w-6 h-6 ml-2" />
                            </span>
                        </div>

                        <!-- BARCODE INPUT -->
                        <div class="flex items-end justify-between w-full my-5 border-2 border-black rounded-2xl">
                            <div class="flex items-center justify-center w-3/4">
                                <label for="search" class="text-xl font-medium text-gray-800"></label>
                                <input
                                    v-model="form.barcode"
                                    id="search"
                                    type="text"
                                    placeholder="Enter BarCode Here!"
                                    class="w-full h-16 px-4 rounded-l-2xl focus:outline-none focus:ring-2 focus:ring-blue-500"
                                />
                            </div>
                            <div class="flex items-end justify-end w-1/4">
                                <button
                                    @click="submitBarcode"
                                    class="px-12 py-4 text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-r-xl"
                                >
                                    Enter
                                </button>
                            </div>
                        </div>

                        <div class="w-full text-center">
                            <p v-if="products.length === 0" class="text-2xl text-red-500">
                                No Products to show
                            </p>
                        </div>

                        <!-- PRODUCT LIST -->
                        <div
                            class="flex items-center w-full py-4 border-b border-black"
                            v-for="item in products"
                            :key="item.id"
                        >
                            <div class="flex w-1/6">
                                <img
                                    :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'"
                                    alt="Supplier Image"
                                    class="object-cover w-16 h-16 border border-gray-500"
                                />
                            </div>
                            <div class="flex flex-col justify-between w-5/6">
                                <!-- COLOR SELECT - Only show if color_id exists -->
                                <select
  v-if="shouldShowColorSelect(item)"
  v-model="item.color_id"
  @change="handleColorChange(item)"
  class="form-select border border-gray-800 rounded-lg p-2"
>
  <option disabled value="">Select Color</option>
  <option
    v-for="c in getColorOptionsForItem(item)"
    :key="c.color_id"
    :value="c.color_id"
  >
    {{ c.color_name }}
  </option>
</select>


                                <p class="text-xl text-black">
                                    {{ item.name }}
                                </p>

                                <!-- PROMOTION BLOCK -->
                                <div
                                    v-if="Number(item.is_promotion) === 1"
                                    class="mt-2 rounded-lg border border-gray-200 p-3 bg-gray-50"
                                >
                                    <p class="text-md font-bold text-black mb-2">
                                        Pack items
                                    </p>

                                    <ul
                                        class="mt-1 list-disc pl-5 space-y-1 max-h-40 overflow-y-auto pr-2 scrollbar-thin scrollbar-thumb-gray-300 scrollbar-track-gray-100"
                                    >
                                        <li
                                            v-for="pi in item.promotion_items ?? []"
                                            :key="pi.id"
                                            class="text-sm text-gray-700 bg-white rounded-md px-2 py-1 shadow-sm hover:bg-gray-50"
                                        >
                                            <span v-if="pi.product">
                                                {{ pi.product.name }}
                                            </span>
                                            <span class="ml-2 text-lg text-dark">× {{ pi.quantity }}</span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="flex items-center justify-between w-full">
                                    <div class="flex space-x-4">
                                        <p
                                            @click="incrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer"
                                        >
                                            <i class="ri-add-line"></i>
                                        </p>
                                        <input
                                            type="number"
                                            v-model="item.quantity"
                                            min="0"
                                            class="bg-[#D9D9D9] border-2 border-black h-8 w-24 text-black flex justify-center items-center rounded text-center"
                                        />
                                        <p
                                            @click="decrementQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer"
                                        >
                                            <i class="ri-subtract-line"></i>
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-center">
                                        <div>
                                            <p
                                                @click="applyDiscount(item.id)"
                                                v-if="
                                                    item.discount &&
                                                    item.discount > 0 &&
                                                    item.apply_discount == false &&
                                                    !appliedCoupon
                                                "
                                                class="cursor-pointer py-1 text-center px-4 bg-green-600 rounded-xl font-bold text-white tracking-wider"
                                            >
                                                Apply {{ item.discount }}% off
                                            </p>

                                            <p
                                                v-if="
                                                    item.discount &&
                                                    item.discount > 0 &&
                                                    item.apply_discount == true &&
                                                    !appliedCoupon
                                                "
                                                @click="removeDiscount(item.id)"
                                                class="cursor-pointer py-1 text-center px-4 bg-red-600 rounded-xl font-bold text-white tracking-wider"
                                            >
                                                Remove {{ item.discount }}% Off
                                            </p>
                                            <p class="text-2xl font-bold text-black text-right">
                                                {{ item.selling_price }}
                                                LKR
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end w-1/6">
                                <p
                                    @click="removeProduct(item.id)"
                                    class="text-3xl text-black border-2 border-black rounded-full cursor-pointer"
                                >
                                    <i class="ri-close-line"></i>
                                </p>
                            </div>
                        </div>

                        <!-- SUMMARY -->
                        <div class="w-full pt-6 space-y-2">
                            <div class="flex items-center justify-between w-full px-8">
                                <p class="text-xl">Sub Total</p>
                                <p class="text-xl">{{ subtotal }} LKR</p>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 py-2 pb-4 border-b border-black">
                                <p class="text-xl">Discount</p>
                                <p class="text-xl">( {{ totalDiscount }} LKR )</p>
                            </div>

                            <!-- CUSTOM DISCOUNT -->
                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Custom Discount</p>
                                <span class="flex items-center">
                                    <CurrencyInput
                                        v-model="custom_discount"
                                        @blur="validateCustomDiscount"
                                        placeholder="Enter value"
                                        class=" rounded-md px-2 py-1 text-black text-md"
                                    />
                                    <select
                                        v-model="custom_discount_type"
                                        class="ml-2 px-8 border-black rounded-md text-black py-1 text-md"
                                    >
                                        <option value="percent">%</option>
                                        <option value="fixed">Rs</option>
                                    </select>
                                </span>
                            </div>

                            <!-- CASH -->
                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Cash</p>
                                <span>
                                    <CurrencyInput v-model="cash" :options="{ currency: 'EUR' }" />
                                    <span class="ml-2">LKR</span>
                                </span>
                            </div>
                            <div class="flex items-center justify-between w-full px-8 pt-4">
                                <p class="text-3xl text-black">Total</p>
                                <p class="text-3xl text-black">{{ total }} LKR</p>
                            </div>

                            <div class="flex items-center justify-between w-full px-8 pt-4 pb-4 border-b border-black">
                                <p class="text-xl text-black">Balance</p>
                                <p>{{ balance }} LKR</p>
                            </div>
                        </div>

                        <!-- COUPON -->
                        <div class="w-full my-5">
                            <div class="relative flex items-center">
                                <label for="coupon" class="sr-only">Coupon Code</label>
                                <input
                                    id="coupon"
                                    v-model="couponForm.code"
                                    type="text"
                                    placeholder="Enter Coupon Code"
                                    class="w-full h-16 px-6 pr-40 text-lg text-gray-800 placeholder-gray-500 border-2 border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                />

                                <template v-if="!appliedCoupon">
                                    <button
                                        type="button"
                                        @click="submitCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-blue-600 rounded-full hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        Apply Coupon
                                    </button>
                                </template>
                                <template v-else>
                                    <button
                                        type="button"
                                        @click="removeCoupon"
                                        class="absolute right-2 top-2 h-12 px-6 text-lg font-semibold text-white uppercase bg-red-600 rounded-full hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
                                    >
                                        Remove Coupon
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- PAYMENT + SUBMIT -->
                        <div class="flex flex-col w-full space-y-8">
                            <div class="flex items-center justify-center w-full pt-8 space-x-8">
                                <p class="text-xl text-black">Payment Method :</p>
                                <div
                                    @click="selectedPaymentMethod = 'cash'"
                                    :class="[
                                        'cursor-pointer w-[100px]  border border-black rounded-xl flex flex-col justify-center items-center text-center',
                                        selectedPaymentMethod === 'cash'
                                            ? 'bg-yellow-500 font-bold'
                                            : 'text-black',
                                    ]"
                                >
                                    <img src="/images/money-stack.png" alt="" class="w-24" />
                                </div>
                                <div
                                    @click="selectedPaymentMethod = 'card'"
                                    :class="[
                                        'cursor-pointer w-[100px] border border-black rounded-xl flex flex-col justify-center items-center text-center',
                                        selectedPaymentMethod === 'card'
                                            ? 'bg-yellow-500 font-bold'
                                            : 'text-black',
                                    ]"
                                >
                                    <img src="/images/bank-card.png" alt="" class="w-24" />
                                </div>
                            </div>

                            <div class="flex items-center justify-center w-full">
                                <button
                                    @click="() => { submitOrder(); }"
                                    type="button"
                                    :disabled="products.length === 0"
                                    :class="[
                                        'w-full bg-black py-4 text-2xl font-bold tracking-wider text-center text-white uppercase rounded-xl',
                                        products.length === 0 ? ' cursor-not-allowed' : ' cursor-pointer',
                                    ]"
                                >
                                    <i class="pr-4 ri-add-circle-fill"></i> Confirm Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /RIGHT SIDE -->
            </div>
        </div>
    </div>

    <!-- MODALS -->
    <PosSuccessModel
        :open="isSuccessModalOpen"
        @update:open="handleModalOpenUpdate"
        :products="products"
        :employee="employee"
        :cashier="loggedInUser"
        :customer="customer"
        :orderid="orderid"
        :cash="cash"
        :balance="balance"
        :subTotal="subtotal"
        :totalDiscount="totalDiscount"
        :total="total"
        :custom_discount_type="custom_discount_type"
        :custom_discount="custom_discount"
    />
    <AlertModel v-model:open="isAlertModalOpen" :message="message" />

    <SelectProductModel
        v-model:open="isSelectModalOpen"
        :allcategories="allcategories"
        :colors="colors"
        :sizes="sizes"
        @selected-products="handleSelectedProducts"
    />
    <Footer />
</template>

<script setup>
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import { useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";
import { Head, Link } from "@inertiajs/vue3";
import axios from "axios";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import { generateOrderId } from "@/Utils/Other.js";

const product = ref(null);
const error = ref(null);
const products = ref([]);
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const appliedCoupon = ref(null);
const cash = ref(0);
const custom_discount = ref(0);
const isSelectModalOpen = ref(false);
const custom_discount_type = ref("percent");
const orderid = computed(() => generateOrderId());

// Store colors only for that scanned barcode
const barcodeColorMap = ref({});

const handleModalOpenUpdate = (newValue) => {
    isSuccessModalOpen.value = newValue;
    if (!newValue) {
        refreshData();
    }
};

const props = defineProps({
    loggedInUser: Object,
    allcategories: Array,
    allemployee: Array,
    colors: Array,
    sizes: Array,
});

const discount = ref(0);

const customer = ref({
    name: "",
    countryCode: "",
    contactNumber: "",
    email: "",
});

const employee_id = ref("");

const selectedPaymentMethod = ref("cash");

const refreshData = () => {
    router.visit(route("pos.index"), {
        preserveScroll: false,
        preserveState: false,
    });
};

const removeProduct = (id) => {
    products.value = products.value.filter((item) => item.id !== id);
};

const removeCoupon = () => {
    appliedCoupon.value = null;
    couponForm.code = "";
};

const incrementQuantity = (id) => {
    const p = products.value.find((item) => item.id === id);
    if (p) p.quantity += 1;
};

const decrementQuantity = (id) => {
    const p = products.value.find((item) => item.id === id);
    if (p && p.quantity > 1) p.quantity -= 1;
};

const submitOrder = async () => {
    if (balance.value < 0) {
        isAlertModalOpen.value = true;
        message.value = "Cash is not enough";
        return;
    }
    try {
        const response = await axios.post("/pos/submit", {
            customer: customer.value,
            products: products.value,
            employee_id: employee_id.value,
            paymentMethod: selectedPaymentMethod.value,
            userId: props.loggedInUser.id,
            orderid: orderid.value,
            cash: cash.value,
            custom_discount: custom_discount.value,
            custom_discount_type: custom_discount_type.value,
        });
        isSuccessModalOpen.value = true;
        console.log(response.data);
    } catch (error) {
        if (error.response?.status === 423) {
            isAlertModalOpen.value = true;
            message.value = error.response.data.message;
        }
        console.error("Error submitting:", error.response?.data || error.message);
    }
};

const subtotal = computed(() => {
    return products.value
        .reduce(
            (total, item) => total + parseFloat(item.selling_price) * item.quantity,
            0
        )
        .toFixed(2);
});

const totalDiscount = computed(() => {
    const productDiscount = products.value.reduce((total, item) => {
        if (item.discount && item.discount > 0 && item.apply_discount == true) {
            const discountAmount =
                (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
                item.quantity;
            return total + discountAmount;
        }
        return total;
    }, 0);

    const couponDiscount = appliedCoupon.value
        ? Number(appliedCoupon.value.discount)
        : 0;

    return (productDiscount + couponDiscount).toFixed(2);
});

const validateCustomDiscount = () => {
    if (!custom_discount.value || isNaN(custom_discount.value)) {
        custom_discount.value = 0;
    }
};

const total = computed(() => {
    const subtotalValue = parseFloat(subtotal.value) || 0;
    const discountValue = parseFloat(totalDiscount.value) || 0;
    const customDiscount = parseFloat(custom_discount.value) || 0;

    let customValue = 0;

    if (custom_discount_type.value === "percent") {
        customValue = (subtotalValue * customDiscount) / 100;
    } else if (custom_discount_type.value === "fixed") {
        customValue = customDiscount;
    }

    return (subtotalValue - discountValue - customValue).toFixed(2);
});

const balance = computed(() => {
    if (cash.value == null || cash.value === 0) {
        return 0;
    }
    return (parseFloat(cash.value) - parseFloat(total.value)).toFixed(2);
});

const form = useForm({
    employee_id: "",
    barcode: "",
});

const couponForm = useForm({
    code: "",
});

// barcode scanner
let barcode = "";
let timeout;

const submitCoupon = async () => {
    try {
        const response = await axios.post(route("pos.getCoupon"), {
            code: couponForm.code,
        });

        const { coupon: fetchedCoupon, error: fetchedError } = response.data;

        if (fetchedCoupon) {
            appliedCoupon.value = fetchedCoupon;
            products.value.forEach((product) => {
                product.apply_discount = false;
            });
        } else {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError;
        }
    } catch (err) {
        if (err.response?.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }
    }
};

// UPDATED submitBarcode with default color_id
const submitBarcode = async () => {
    try {
        const response = await axios.post(route("pos.getProduct"), {
            barcode: form.barcode,
        });

        const {
            product: fetchedProduct,
            products: fetchedProducts,
            colorOptions,
            error: fetchedError,
        } = response.data;

        // Save color options for this barcode
        if (colorOptions && Array.isArray(colorOptions)) {
            barcodeColorMap.value[form.barcode] = colorOptions;
        }

        // Single product
        if (fetchedProduct) {
            if (fetchedProduct.stock_quantity < 1) {
                isAlertModalOpen.value = true;
                message.value = "Product is out of stock";
                return;
            }

            const existingProduct = products.value.find(
                (item) => item.id === fetchedProduct.id
            );

            if (existingProduct) {
                existingProduct.quantity += 1;
            } else {
                // Set default color_id if not present
                const defaultColorId = colorOptions && colorOptions.length > 0 
                    ? colorOptions[0].color_id 
                    : (fetchedProduct.color_id || null);

                products.value.push({
                    ...fetchedProduct,
                    quantity: 1,
                    apply_discount: false,
                    barcode: form.barcode,
                    color_id: defaultColorId, // Set default color
                });
            }

            product.value = fetchedProduct;
            error.value = null;
            form.barcode = "";
            return;
        }

        // Multiple products (same barcode, different colors)
        if (fetchedProducts && fetchedProducts.length > 0) {
            const first = fetchedProducts[0];

            if (first.stock_quantity < 1) {
                isAlertModalOpen.value = true;
                message.value = "Product is out of stock";
                return;
            }

            const exists = products.value.find((p) => p.id === first.id);
            if (exists) {
                exists.quantity += 1;
            } else {
                // Set default color_id for multiple products scenario
                const defaultColorId = colorOptions && colorOptions.length > 0 
                    ? colorOptions[0].color_id 
                    : (first.color_id || null);

                products.value.push({
                    ...first,
                    quantity: 1,
                    apply_discount: false,
                    barcode: form.barcode,
                    color_id: defaultColorId, // Set default color
                });
            }

            form.barcode = "";
            error.value = null;
            return;
        }

        if (fetchedError) {
            isAlertModalOpen.value = true;
            message.value = fetchedError;
            error.value = fetchedError;
        }
    } catch (err) {
        if (err.response?.status === 422) {
            isAlertModalOpen.value = true;
            message.value = err.response.data.message;
        }

        console.error("An error occurred:", err.response?.data || err.message);
        error.value = "An unexpected error occurred. Please try again.";
    }
};

const handleScannerInput = (event) => {
    clearTimeout(timeout);
    if (event.key === "Enter") {
        form.barcode = barcode;
        submitBarcode();
        barcode = "";
    } else {
        barcode += event.key;
    }

    timeout = setTimeout(() => {
        barcode = "";
    }, 100);
};

onMounted(() => {
    document.addEventListener("keypress", handleScannerInput);
});

// apply/remove discount
const applyDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = true;
        }
    });
};

const removeDiscount = (id) => {
    products.value.forEach((product) => {
        if (product.id === id) {
            product.apply_discount = false;
        }
    });
};

 const getColorOptionsForItem = (item) => {
    if (item.barcode && barcodeColorMap.value[item.barcode]?.length) {
        return barcodeColorMap.value[item.barcode];
    }
    return [];
};


// Handle color change
const handleColorChange = (item) => {
    // You can add logic here if you want to fetch updated price/stock based on color
    console.log('Color changed for item:', item.id, 'New color:', item.color_id);
};

 const handleSelectedProducts = (selectedProducts) => {
    selectedProducts.forEach((fetchedProduct) => {
        const existingProduct = products.value.find(
            (item) => item.id === fetchedProduct.id
        );

        if (existingProduct) {
            existingProduct.quantity += 1;
        } else {
            products.value.push({
                ...fetchedProduct,
                quantity: 1,
                apply_discount: false,
                barcode: fetchedProduct.barcode ?? null,
                from_manual: true, // 👈 mark that this was added manually
            });
        }
    });
};
const shouldShowColorSelect = (item) => {
    // if it was selected manually → DO NOT show color select
    if (item.from_manual) {
        return false;
    }

    // barcode item with colors → show
    if (item.barcode && barcodeColorMap.value[item.barcode]?.length) {
        return true;
    }

    return false;
};

 

</script>