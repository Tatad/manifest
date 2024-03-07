<script  lang="ts" setup>

import { ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Header, Item  } from "vue3-easy-data-table";
import Modal from '@/Components/Modal.vue';
import AdminAddNewItem from '@/Admin/AddItem.vue';
import AdminEditItem from '@/Admin/EditItem.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { saveAs } from 'file-saver';
import { useNotification } from "@kyvg/vue3-notification";
import { createToaster } from "@meforma/vue-toaster";

const props = defineProps({
    manifests: {
        type: Array,
    },
    scannedItems: {
        type: Array,
    }, 
    upcCodes: {
        type: Array,
    },
    pallets: {
        type: Array
    }
});

const form = useForm({
    description: '',
    item_number: '',
    type: '',
    pallet: 0,
    image: [],
    quantity: '',
    msrp: '',
    upc_code: '',
    retail_price: '',
    errors: []
});

const itemHeaders: Header[] = [
  { text: "", value: "selected" },
  { text: "Item #", value: "item", sortable: true},
  { text: "Description", value: "description", sortable: true, width: 400},
  { text: "Quantity", value: "quantity", sortable: true},
  { text: "MSRP", value: "msrp", sortable: true},
  { text: "$ Total", value: "totalMsrp", sortable: true},
  { text: "Pallet #", value: "pallet"},
  { text: "Type", value: "type", sortable: true},
  { text: "Costco Image", value: "images"},
  { text: "UPC Code", value: "upc_code"},
  { text: "Actions",  value: "action"}
];

const upcCodesHeaders: Header[] = [
    { text: "", value: "selected" },
    { text: "Item #", value: "item", sortable: true},
    { text: "UPC Code", value: "upc_code", sortable: true},
    { text: "Actions",  value: "action"}
];

const scannedItemsHeader: Header[] = [
    { text: "", value: "selected" },
    { text: "Item #", value: "item", sortable: true},
    { text: "UPC Code", value: "upc_code", sortable: true},
    { text: "Image", value: "image_name", sortable: true},
    { text: "Actions",  value: "action"}
];

const addNewItemModal = ref(false);
const editItemModal = ref(false);
const deleteItemModal = ref(false);
const addNewUPCModal = ref(false);

const itemData = ref({})
const editItem = (item) => {
    console.log(item)
    itemData.value = item;
    editItemModal.value = true;
}

const closeModal = () => {
    addNewItemModal.value = false;
    editItemModal.value = false;
    deleteItemModal.value = false;
}

const removeItem = () => {
    form.item_number = itemData.value.item
    form.upc_code = itemData.value.upc_code
    form.post(route('admin.deleteItem'), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            form.get(route('admin'), {
                onSuccess: () => {
                  toaster.warning('Item removed successfully.', {
                    position: "top-right",
                  });
                }
          });
        }
    })
}
const toaster = createToaster({ /* options */ });

</script>

<template>
    <Head title="Admin" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Admin</h2>
        </template>
        <div class="py-3">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg px-8 py-8">
                    <div class="flex w-full mb-4">
                        <div><h1 class="text-lg font-semibold w-2/12 pr-4 mt-1">Items</h1></div>
                        <PrimaryButton @click.prevent="addNewItemModal = true;" class="w-1/12">
                            <span class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>Add New Item</span>
                        </PrimaryButton>
                    </div>

                    <EasyDataTable :headers="itemHeaders"
                      :items="manifests"
                      ref="itemsDataTable">

                      <template #item-action="item">
                        <PrimaryButton class="my-2" @click.prevent="editItem(item);">
                            Edit
                        </PrimaryButton>

                        <DangerButton class="ml-2 my-2" @click.prevent="itemData = item;deleteItemModal = true;">
                            Delete
                        </DangerButton>
                      </template>

                      <template #item-upc_code=item>
                          {{item.upc_code}}
                      </template>

                      <template #item-images="item">
                            <div class="customize-header">
                                <div v-for="image in item.images" v-if="item.images && item.images.length >= 1 && item.images != 'not_available'">
                                    <a target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600" :href="image" v-if="image && image != 'not_available'">{{$filters.truncate(image)}}</a>
                                </div>
                                <span v-else-if="item.images.length == 0"><i class="fa fa-spin"></i>N/A</span>
                                <span v-else>
                                    <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-gray-200 animate-spin dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#1C64F2"/>
                                    </svg>
                                    Loading...
                                </span>
                                
                            </div>
                        </template>

                    </EasyDataTable>

                    <AdminAddNewItem v-if="addNewItemModal" :pallets="pallets" @close="closeModal"></AdminAddNewItem>
                    <AdminEditItem v-if="editItemModal" :itemData="itemData" :pallets="pallets" @close="closeModal"></AdminEditItem>

                    <Modal :show="deleteItemModal" @close="closeModal">
                        <div class="p-6">
                          <div class="mt-6">
                            <h1 class="text-xl text-gray-800">Are you sure you want to remove the item? This action is irreversible.</h1>
                          </div>

                          <div class="mt-6 flex justify-end">
                              <SecondaryButton class="mr-4" @click="closeModal"> Close </SecondaryButton>
                              <PrimaryButton class="mr-4 bg-green-400 px-4" @click="removeItem"> Yes </PrimaryButton>
                          </div>
                      </div>
                    </Modal>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="flex w-full mt-4 mb-4">
                                <div><h1 class="text-lg font-semibold pr-4 mt-1">UPC Codes</h1></div>
                                <PrimaryButton @click.prevent="addNewUPCModal = true;" class="w-2/12">
                                    <span class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3 mr-2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>Add UPC Code</span>
                                </PrimaryButton>
                            </div>
                            <EasyDataTable :headers="upcCodesHeaders"
                              :items="upcCodes">

                            </EasyDataTable>
                        </div>

                        <div>
                            <h1 class="text-lg py-4 font-semibold">Scanned Items</h1>
                            <EasyDataTable :headers="scannedItemsHeader"
                              :items="scannedItems">
                                <template #item-image_name="item">
                                    <img :src="item.image_name" width="50" class="py-4">
                                </template>
                            </EasyDataTable>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
