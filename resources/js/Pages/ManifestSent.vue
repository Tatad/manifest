<script  lang="ts" setup>

import { ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Header, Item  } from "vue3-easy-data-table";
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import Checkbox from '@/Components/Checkbox.vue';
import { saveAs } from 'file-saver';

defineProps({
    manifests: {
        type: Array,
    },
});

// const sortBy = "id";
// const sortType: SortType = "desc";

const selectedItems = ref<Item[]>([]);
const searchValue = ref("");

const form = useForm({
    id: 0,
    description: '',
    item: '',
    pallet: 0,
    images: [],
    features: '',
    selected: [],
    file: ''
});
  
const headers: Header[] = [
  { text: "ID", value: "id", sortable: true },
  { text: "Item #", value: "item", sortable: true},
  { text: "Description", value: "description", sortable: true},
  { text: "Quantity", value: "quantity", sortable: true},
  { text: "MSRP", value: "msrp", sortable: true},
  { text: "$ Total", value: "totalMsrp", sortable: true},
  { text: "Pallet #", value: "pallet", sortable: true},
  { text: "Costco Image", value: "images"},
  { text: "Product URL", value: "costcoUrl"}
];

const openManifestItem = ref(false);
const restoreManifestConfirm = ref(false);
const updateManifestItem = (item) => {
    form.id = item.id;
    form.description = item.description;
    form.item = item.item;
    form.pallet = item.pallet;
    form.features = item.features;
    form.images = item.images;
    openManifestItem.value = true;
};

const closeModal = () => {
    openManifestItem.value = false;
    restoreManifestConfirm.value = false;
};

const addRow = () => {
    form.images.push({
        image: ''
    });
}

const removeElement = (index) => {
    form.images.splice(index, 1);
}
const setFilename = (event, row) => {
    var file = event.target.files[0];
    row.file = file
}

const submit = () => {
    console.log('submitted')
    form.patch(route('manifest.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            openManifestItem.value = false;
            restoreManifestConfirm.value = false;
        }
    });
}

const submitManifest = (item) => {
    let selectedItemNumber = [];
    if(selectedItems.value){
        selectedItems.value.forEach(val => {
            selectedItemNumber.push(val.item);
           //or if you pass float numbers , use parseFloat()
        });
    }

    form.selected = selectedItemNumber;

    var date = new Date();
    
    return new Promise((res, rej) => {
        axios.post('/manifest-restore', form).then((response) => {
            // const filename = 'manifest_data_'+date+'.xlsx';
            // const blob = new Blob([response.data], { type: 'application/vnd.ms-excel' });
            // saveAs(blob, filename);
            form.reset();
            selectedItems.value = [];
            openManifestItem.value = false;
            restoreManifestConfirm.value = false;
            //refresh the component
            form.get(route('manifest.sent'), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    
                }
            });
        }).catch((err) => {
        rej(err);
      });
    });
}

let onChange = (event) => {
    form.file = event.target.files ? event.target.files[0] : null;
    if (form.file) {
        form.post(route('manifest.upload'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                form.reset()
            }
        });
    }
}


const total = () => {
    let basket_total = 0;
    if(selectedItems.value){
        selectedItems.value.forEach(val => {
            basket_total += Number(val.msrp);
           //or if you pass float numbers , use parseFloat()
        });
    }
    return basket_total.toFixed(2);
}


</script>

<template>
    <Head title="Manifest" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manifest</h2>
        </template>

        <div class="py-12">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" >

                    <div class="grid grid-cols-3 gap-3">
                        <div class="m-6">
                            <InputLabel for="search" value="Filter Search"/>

                            <TextInput
                                id="search"
                                v-model="searchValue"
                                type="readonly"
                                class="border-solid border-2 border-black-600 p-2 mt-1 block"
                                placeholder="Search here..."
                            />

                            
                        </div>

                        <!-- <div class="m-6 pt-6">
                            <input type="file" @change="onChange" >
                        </div> -->

                        <div class="m-6 pt-6" v-if="selectedItems.length">
                            <PrimaryButton @click="restoreManifestConfirm = true"> Restore Selected Manifest </PrimaryButton>

                            <Modal :show="restoreManifestConfirm" @close="closeModal">
                                <div class="p-6">

                                    <div class="mt-6">

                                        <h1>Are you sure you want to restore the selected manifest?</h1>
                                        
                                    </div>

                                    <div class="mt-6 flex justify-end">
                                        <SecondaryButton class="mr-4" @click="closeModal"> Cancel </SecondaryButton>
                                        <PrimaryButton @click="submitManifest"> Submit </PrimaryButton>

                                        
                                    </div>
                                </div>
                            </Modal>
                        </div>
                    </div>
                    
                    <h3 class="text-lg font-bold mr-7 pb-5 text-right">Total: ${{ total() }}</h3>
                   <EasyDataTable
                        :headers="headers"
                        :items="manifests"
                        :search-value="searchValue"
                        v-model:items-selected="selectedItems"
                      >

                      <!--   <template #item-selected="item">
                            <div class="customize-header">
                                <input type="checkbox" v-model="selectedItems" :value="item">
                            </div>
                        </template> -->

                        <template #item-images="item">
                            <div class="customize-header">

                                <div v-for="image in item.images" v-if="item.images && item.images != 'not_available'">
                                    <a class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600" :href="image" v-if="image && image != 'not_available'">{{$filters.truncate(image)}}</a>
                                </div>
                                <span v-else-if="item.images == 'not_available'"><i class="fa fa-spin"></i>N/A</span>
                                <span v-else>
                                    <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-gray-200 animate-spin dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                    <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#1C64F2"/>
                                    </svg>
                                    Loading...
                                </span>
                                
                            </div>
                        </template>

                        <template #item-costcoUrl="item">
                            <div class="customize-header">
                                <a class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600" v-if="item.item_name && item.item_name != 'not_available'" target="_blank" :href="'https://www.costco.ca/CatalogSearch?keyword='+item.item">{{item.item_name}}<span></span></a>
                                <span v-else-if="!item.item_name">
                                    <svg aria-hidden="true" role="status" class="inline w-4 h-4 mr-3 text-gray-200 animate-spin dark:text-gray-600" viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z" fill="currentColor"/>
                                        <path d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z" fill="#1C64F2"/>
                                        </svg>
                                        Loading...
                                </span>
                                <span v-else>N/A</span>
                            </div>
                        </template>

                        <!-- <template #item-totalMsrp="item">
                            <div class="customize-header">
                                {{ item.msrp * item.quantity }}
                            </div>
                        </template> -->
                      
                        <template #item-action="item">
                          <div class="customize-header">
                            <PrimaryButton @click="updateManifestItem(item)"> Edit </PrimaryButton>
                            </div>
                        </template>
                    </EasyDataTable>
                    
                </div>
            </div>
        </div>
        <Modal :show="openManifestItem" @close="closeModal">
            <div class="p-6">

                <div class="mt-6">
                    <InputLabel for="item" value="Item Number"/>

                    <TextInput
                        id="item"
                        v-model="form.item"
                        type="readonly"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block block w-3/4"
                        placeholder="Item Number"
                    />

                    <InputError :message="form.errors.item" class="mt-2" />
                    
                </div>

                <div class="mt-6">
                    <InputLabel for="description" value="Description"/>

                    <TextInput
                        id="description"
                        v-model="form.description"
                        type="readonly"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block block w-3/4"
                        placeholder="Description"
                    />

                    <InputError :message="form.errors.description" class="mt-2" />
                    
                </div>

                <div class="mt-6">
                    <InputLabel for="pallet" value="Palet Number"/>

                    <TextInput
                        id="pallet"
                        v-model="form.pallet"
                        type="readonly"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block block w-3/4"
                        placeholder="Palet Number"
                    />

                    <InputError :message="form.errors.pallet" class="mt-2" />
                    
                </div>

                <button class="button btn-primary" @click="addRow">Add row</button>

                
                <div class="mt-6">
                    <label class="fileContainer">
                       Image URL
                    </label>

                    <TextInput
                        id="image"
                        v-model="form.images"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="Image URL"
                    />
                </div>

                <div class="mt-6">
                    <InputLabel for="features" value="Features"/>

                    <TextArea
                        id="features"
                        v-model="form.features"
                        type="text"
                        class="mt-1 block w-3/4"
                        placeholder="Features"
                    />

                    <InputError :message="form.errors.features" class="mt-2" />
                    
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="mr-4" @click="closeModal"> Cancel </SecondaryButton>
                    <PrimaryButton @click="submit"> Submit </PrimaryButton>

                    
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
