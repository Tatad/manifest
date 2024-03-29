<script  lang="ts" setup>

import { ref, computed } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Header, Item, FilterOption } from "vue3-easy-data-table";
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

const props = defineProps({
    manifests: {
        type: Array,
    },
    pallets: {
        type: Array
    },
    types: {
        type: Array
    }
});

const manifestData = ref(props.manifests)
const palletsData = ref(props.pallets)
const selectedItems = ref<Item[]>([]);
const dataTable = ref();
const type = ref('');

const searchValue = ref("");

const form = useForm({
    id: 0,
    description: '',
    item: '',
    type: '',
    pallet: 0,
    images: [],
    features: '',
    selected: [],
    file: ''
});

const tableSortCriteria = ref('all');
const typeFilter = ref('all');

const showFavouriteSportFilter = ref(false);
const showNameFilter = ref(false);
  
const headers: Header[] = [
  { text: "", value: "selected" },
  { text: "Item #", value: "item", sortable: true},
  { text: "Description", value: "description", sortable: true, width: 400},
  { text: "Quantity", value: "quantity", sortable: true},
  { text: "MSRP", value: "msrp", sortable: true},
  { text: "$ Total", value: "totalMsrp", sortable: true},
  { text: "Pallet #", value: "pallet"},
  { text: "Type", value: "type"},
  { text: "Costco Image", value: "images"},
  { text: "Product URL", value: "costcoUrl", width: 400},
  { text: "Actions",  value: "action"}
];

const openManifestItem = ref(false);
const openRestoreDefaultImageConfirm = ref(false);
const sendManifestConfirm = ref(false);

const updateManifestItem = (item) => {
    form.id = item.id;
    form.description = item.description;
    form.item = item.item;
    form.pallet = item.pallet;
    form.features = item.features;
    form.images = item.images;
    form.type = item.type;
    openManifestItem.value = true;
};

const closeModal = () => {
    openManifestItem.value = false;
    openRestoreDefaultImageConfirm.value = false;
    sendManifestConfirm.value = false;
};

const addRow = () => {
    form.images.push([]);
}

const restoreDefaultImageModal = () => {
    openManifestItem.value = false;
    openRestoreDefaultImageConfirm.value = true
}

const restoreDefaultImage = () => {
    console.log(form)
    axios.post('/restore-image', form).then((response) => {
        form.reset();
        selectedItems.value = [];
        openManifestItem.value = false;
        sendManifestConfirm.value = false;
        openRestoreDefaultImageConfirm.value = false
        form.get(route('manifest'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                
            }
        });
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
    form.patch(route('manifest.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset()
            openManifestItem.value = false;
            sendManifestConfirm.value = false;
        }
    });
}

function padTo2Digits(num) {
  return num.toString().padStart(2, '0');
}

function formatDate(date) {
  return (
    [
      date.getFullYear(),
      padTo2Digits(date.getMonth() + 1),
      padTo2Digits(date.getDate()),
    ].join('-') +
    ' ' +
    [
      padTo2Digits(date.getHours()),
      padTo2Digits(date.getMinutes()),
      padTo2Digits(date.getSeconds()),
    ].join(':')
  );
}

var zone = new Date().toLocaleTimeString('en-us',{timeZoneName:'short'}).split(' ')[2]

const submitManifest = (item) => {
    let selectedItemNumber = [];
    if(selectedItems.value){
        selectedItems.value.forEach(val => {
            selectedItemNumber.push({"item": val.item, "pallet" : val.pallet});
        });
    }

    form.selected = selectedItemNumber;

    var date = new Date();
    
    return new Promise((res, rej) => {
        axios.post('/manifest', form, { responseType: 'blob'}).then((response) => {
            const filename = 'manifest_data_'+formatDate(date)+' '+zone+'.csv';
            const blob = new Blob([response.data], { type: 'text/csv;charset=utf-8,' });
            saveAs(blob, filename);
            form.reset();
            selectedItems.value = [];
            openManifestItem.value = false;
            sendManifestConfirm.value = false;
            res(response.data);
            //refresh the component
            form.get(route('manifest'), {
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
            basket_total += Number(val.totalMsrp);
        });
    }
    return basket_total.toFixed(2);
}

const downloadManifestPdf = (item) => {
    let selectedItemNumber = [];
    if(selectedItems.value){
        selectedItems.value.forEach(val => {
            selectedItemNumber.push({"item": val.item, "pallet" : val.pallet});
           //or if you pass float numbers , use parseFloat()
        });
    }

    form.selected = selectedItemNumber;

    var date = new Date();

    return new Promise((res, rej) => {
        axios({
            url: '/manifest-download-pdf', 
            method: 'POST',
            params: {form: form},
            responseType: 'blob', // important
        }).then((response) => {
            const filename = 'manifest_data_'+formatDate(date)+' '+zone+'.pdf';
            const blob = new Blob([response.data], { type: 'application/pdf' });
            saveAs(blob, filename);
            form.reset();
            selectedItems.value = [];
            openManifestItem.value = false;
            sendManifestConfirm.value = false;
            res(response.data);
            //refresh the component
            form.get(route('manifest'), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    
                }
            });
        });
    });
}

let totalVal = ref(manifestData.value.reduce((total, obj) => obj.totalMsrp + total,0).toFixed(2));
const clientItemsLength = computed(() => dataTable.value?.clientItems);
const filterHandler = (e) =>{
    let basket_total = 0;
    totalVal.value = 0;
    selectedItems.value = []
    let results = []

    if(searchValue.value != ""){
        let filterData = manifestData.value.filter(function(data) {
            let transformedId = data.id.toString();
            let transformedMsrp = data.msrp.toString();
            //console.log(data)
            // if(data.item.match(searchValue.value)){
            //     //console.log(data)
            //     results.push(data)
            // }

            // if(data.pallet.match(searchValue.value)){
            //     //console.log(data)
            //     results.push(data)
            // }

            // if(transformedId.match(parseInt(searchValue.value))){
            //     results.push(data)
            // }
            // if(transformedMsrp.match(searchValue.value)){
            //     results.push(data)
            // }
        });
        const uniq = [...new Set(results)];

        uniq.forEach(val => {
            basket_total += Number(val.totalMsrp);
        });
        totalVal.value = basket_total.toFixed(2);
    }else{
        manifestData.value.forEach(val => {
            basket_total += Number(val.totalMsrp);
        });
        totalVal.value = basket_total.toFixed(2);
    }
}

const filterOptions = computed((): FilterOption[] => {
    let basket_total = 0;
    totalVal.value = 0;
    let results = []
    let palletsArray = []
    let palletFilterResult = []
    const filterOptionsArray: FilterOption[] = [];

    if (typeFilter.value !== 'all') {
        filterOptionsArray.push({
            field: 'type',
            comparison: '=',
            criteria: typeFilter.value,
        });
    }
    //condition to filter out the pallet based on the chosen Type
    if (typeFilter.value !== 'all') {
        manifestData.value.filter(function(data) {
            if(data.type.match(typeFilter.value)){
                palletsData.value = []
                palletsArray.push(data.pallet)
            }
        });
        const uniqArray = [...new Set(palletsArray)];
           
        uniqArray.forEach(val => {
            console.log(val)
            palletFilterResult.push({'pallet':val})
        })
        palletsData.value = palletFilterResult
    }

    if (tableSortCriteria.value !== 'all') {
        filterOptionsArray.push({
          field: 'pallet',
          comparison: '=',
          criteria: tableSortCriteria.value,
        });
    }
    if(tableSortCriteria.value !== 'all'){
        manifestData.value.filter(function(data) {
            if(data.pallet.match(tableSortCriteria.value)){
                results.push(data)
            }
        });
        const uniq = [...new Set(results)];
        uniq.forEach(val => {
            basket_total += Number(val.totalMsrp);
        });
        totalVal.value = basket_total.toFixed(2);
    }else{
        manifestData.value.forEach(val => {
            basket_total += Number(val.totalMsrp);
        });
        
        totalVal.value = basket_total.toFixed(2);
    }

  return filterOptionsArray;
});

const resetHandler = () => {
    selectedItems.value = []
}

</script>

<template>
    <Head title="Manifest" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manifest</h2>
        </template>

        <div class="py-3">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">

                    <div class="grid grid-cols-4 gap-2 xl:grid-cols-4 xl:gap-2 sm:grid-cols-2 sm:gap-2 xs:grid-cols-1">
                        <div class="sm:w-full m-6">
                            <InputLabel for="search" value="Filter Search"/>

                            <TextInput
                                id="search"
                                v-model="searchValue"
                                type="search"
                                class="sm:w-3/4 xs:w-full border-solid border-2 border-black-600 p-2 mt-1 block"
                                placeholder="Search here..."
                                @input="filterHandler"
                            />
                        </div>
                        <div class="m-4">
                            <div class="md:flex md:items-center mb-6 mt-6">

                                <a href="/manifest-grouped"><PrimaryButton> Switch to pallet view </PrimaryButton></a>
                            </div>

                        </div>
                        <div class="m-4">
                            <InputLabel class="mb-2" for="search" value="Upload Manifest"/>
                            <input type="file" @change="onChange" >
                        </div>

                        <div class="m-6">
                            <InputLabel class="mb-2" for="search" value="Download Manifest"/>
                            <PrimaryButton class="mr-1" v-if="selectedItems.length" @click="sendManifestConfirm = true;type = 'CSV'"> Download via(CSV) </PrimaryButton>
                            <PrimaryButton class="ml-1 sm:ml-0 sm:mt-4" v-if="selectedItems.length" @click="sendManifestConfirm = true;type = 'PDF'"> Download via(PDF) </PrimaryButton>
                                <button class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-500 dark:hover:bg-gray-500 dark:hover:text-white focus:bg-gray-500 dark:focus:bg-white active:bg-gray-400 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" v-if="selectedItems.length == 0"> Download Selected Manifest </button>

                            <Modal :show="sendManifestConfirm" @close="closeModal">
                                <div class="p-6">
                                    <div class="mt-6">
                                        <h1>Are you sure you want to send the selected manifest?</h1>
                                    </div>
                                    <div class="mt-6 flex justify-end">
                                        <SecondaryButton class="mr-4" @click="closeModal"> Cancel </SecondaryButton>
                                        <PrimaryButton v-if="type == 'CSV'" @click="submitManifest"> Proceed </PrimaryButton>
                                        <PrimaryButton v-if="type == 'PDF'" @click="downloadManifestPdf"> Proceed </PrimaryButton>
                                    </div>
                                </div>
                            </Modal>
                        </div>
                    </div>
                    <h3 class="text-lg font-bold mr-7 pb-5 text-right">Selected Total: ${{ total() }} | Total: ${{ totalVal }}</h3>
                   <EasyDataTable
                        :headers="headers"
                        :items="manifests"
                        :search-value="searchValue"
                        v-model:items-selected="selectedItems"
                        :filter-options="filterOptions"
                        ref="dataTable"
                        :rows-items="[25,50,100,manifestData.length]"
                      >

                        <template #header-type="header">
                          <div class="filter-column">
                            <span class="cursor-pointer inline-flex items-baseline" @click.stop="showNameFilter=!showNameFilter"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
</svg><span>{{ header.text  }}</span>
</span>
                            <div class="filter-menu" v-if="showNameFilter">
                              <select
                                class="favouriteSport-selector"
                                v-model="typeFilter"
                                name="type"
                                @change="resetHandler"
                              >
                                <option v-for="type in types" :value="type.type">
                                  {{type.type}}
                                </option>
                                <option value="all">
                                  all
                                </option>
                              </select>
                            </div>
                          </div>
                        </template>

                        <template #header-pallet="header">
                          <div class="filter-column">
                            <span class="cursor-pointer inline-flex items-baseline" @click.stop="showFavouriteSportFilter=!showFavouriteSportFilter"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3 h-3">
  <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
</svg><span>{{ header.text  }}</span>
</span>
                            <div class="filter-menu filter-sport-menu" v-if="showFavouriteSportFilter">
                              <select
                                class="favouriteSport-selector"
                                v-model="tableSortCriteria"
                                name="pallet"
                                @change="resetHandler"
                              >
                                <option v-for="pallet in palletsData" :value="pallet.pallet">
                                  {{pallet.pallet}}
                                </option>
                                <option value="all">
                                  all
                                </option>
                              </select>
                            </div>
                          </div>
                        </template>
                        <template #item-images="item">
                            <div class="customize-header">
                                <div v-for="image in item.images" v-if="item.images && item.images != 'not_available'">
                                    <a target="_blank" class="underline text-blue-600 hover:text-blue-800 visited:text-purple-600" :href="image" v-if="image && image != 'not_available'">{{$filters.truncate(image)}}</a>
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
                      
                        <template #item-action="item">
                          <div class="customize-header">
                            <PrimaryButton @click="updateManifestItem(item)"> Edit </PrimaryButton>
                            </div>
                        </template>

                    </EasyDataTable>
                </div>
            </div>
        </div>

        <Modal :show="openRestoreDefaultImageConfirm" @close="closeModal">
            <div class="p-6">
                <div class="mt-6">
                    <h1>Are you sure you want to replace the first image to the original Costco image?</h1>
                </div>
                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="mr-4" @click="closeModal"> Cancel </SecondaryButton>
                    <PrimaryButton @click="restoreDefaultImage"> Proceed </PrimaryButton>
                </div>
            </div>
        </Modal>

        <Modal :show="openManifestItem" @close="closeModal">
            <div class="p-6">

                <div class="mt-6">
                    <InputLabel for="item" value="Item Number"/>
                    <TextInput
                        id="item"
                        v-model="form.item"
                        type="readonly"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
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
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                        placeholder="Description"
                    />
                    <InputError :message="form.errors.description" class="mt-2" />
                </div>

                <div class="mt-6">
                    <InputLabel for="pallet" value="Palet Number"/>
                    <input type="text" readonly v-model="form.pallet" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4">
                    <InputError :message="form.errors.pallet" class="mt-2" />
                </div>
                <InputLabel class="mt-6" for="pallet" value="Image URL"/>
                <button class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" @click="addRow">Add row</button>

                <button class="ml-2 inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" @click="restoreDefaultImageModal" v-if="form.type == 'Mixed'">Restore Default Costco image</button>

                <div v-for="(row, index) in form.images" v-if="form.images && form.images != 'not_available'">
                        <TextInput
                            id="image"
                            :value="row"
                            @input="event => form.images[index] = event.target.value"
                            type="text"
                            class="mt-1 block w-3/4"
                            placeholder="Image URL"
                        />
                        <a v-on:click="removeElement(index);" style="cursor: pointer" class="mt-2 inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-red-500 hover:text-white dark:hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">Remove</a>
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
                    <PrimaryButton @click="submit"> Save </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
