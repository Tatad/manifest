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
import moment from 'moment';

defineProps({
    manifests: {
        type: Array,
    },
});

// const sortBy = "id";
// const sortType: SortType = "desc";

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
  { text: "", value: "selected" },
  { text: "ID", value: "id", sortable: true },
  { text: "Item #", value: "item", sortable: true},
  { text: "Description", value: "description", sortable: true},
  { text: "Quantity", value: "quantity", sortable: true},
  { text: "MSRP", value: "msrp", sortable: true},
  { text: "$ Total", value: "totalMsrp", sortable: true},
  { text: "Pallet #", value: "pallet", sortable: true},
  { text: "Costco Image", value: "images"},
  { text: "Product URL", value: "costcoUrl"},
  { text: "Actions",  value: "action"}
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

const selectedItems = ref([])

const total = () => {
    let basket_total = 0;
    if(selectedItems.value){
        selectedItems.value.forEach(val => {
            basket_total += Number(val.msrp);
           //or if you pass float numbers , use parseFloat()
        });
    }
    return basket_total;
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
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">

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
                      >

                        <template #item-selected="item">
                            <div class="customize-header">
                                <input type="checkbox" v-model="selectedItems" :value="item">
                            </div>
                        </template>

                        <template #item-images="item">
                            <div class="customize-header">
                                <a class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600" :href="item.images" v-if="item.images != 'not_available'">{{$filters.truncate(item.images)}}</a><span v-else>N/A</span>
                            </div>
                        </template>

                        <template #item-costcoUrl="item">
                            <div class="customize-header">
                                <a class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600" v-if="item.images != 'not_available'" target="_blank" :href="'https://www.costco.ca/CatalogSearch?keyword='+item.item">{{item.item_name}}<span></span></a><span v-else>N/A</span>
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
                        class="mt-1 block w-3/4"
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
                        class="mt-1 block w-3/4"
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
                        class="mt-1 block w-3/4"
                        placeholder="Palet Number"
                    />

                    <InputError :message="form.errors.pallet" class="mt-2" />
                    
                </div>

                <button class="button btn-primary" @click="addRow">Add row</button>

                <div v-for="(row, index) in form.images">

                      
                            <label class="fileContainer">
                               Image
                            </label>

                            <TextInput
                                id="image"
                                v-model="row.image"
                                type="text"
                                class="mt-1 block w-3/4"
                                placeholder="Image URL"
                            />
                       
                            <a v-on:click="removeElement(index);" style="cursor: pointer">Remove</a>
                        

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
