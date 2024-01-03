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
import { useNotification } from "@kyvg/vue3-notification";


const props = defineProps({
    manifests: {
        type: Array,
    },
    manifestSumData: {
        type: Array,
    },
});

// const sortBy = "id";
// const sortType: SortType = "desc";

// const selectedItems = ref<Item[]>();
const selectedItems = ref([]);
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
  { text: "Pallet Number", value: "pallet", sortable: true },
  { text: "$ Total", value: "sum", sortable: true },
];

const manifestHeaders: Header[] = [
  { text: "", value: "selected" },
  { text: "ID", value: "id", sortable: true },
  { text: "Item #", value: "item", sortable: true},
  { text: "Description", value: "description", sortable: true, width: 400},
  { text: "Quantity", value: "quantity", sortable: true},
  { text: "MSRP", value: "msrp", sortable: true},
  { text: "$ Total", value: "totalMsrp", sortable: true},
  { text: "Pallet #", value: "pallet"},
  { text: "Costco Image", value: "images"},
  { text: "Product URL", value: "costcoUrl", width: 400}
];

const openManifestItem = ref(false);
const restoreManifestConfirm = ref(false);

const closeModal = () => {
    openManifestItem.value = false;
    restoreManifestConfirm.value = false;
};

const addRow = () => {
    form.images.push({
        image: ''
    });
}

const submitManifest = (item) => {
    let selectedItemNumber = [];
    if(selectedItems.value){
        selectedItems.value.forEach(val => {
            selectedItemNumber.push({"item": val.item, "pallet" : val.pallet});
           //or if you pass float numbers , use parseFloat()
        });
    }
    
    form.selected = selectedItemNumber;
    // console.log(form.selected);
    // return;
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
var zone = new Date().toLocaleTimeString('en-us',{timeZoneName:'short'}).split(' ')[2]
const downloadManifest = (type) => {
    console.log(type)
    // let selectedItemNumber = [];

    form.selected = selectedItems.value[0].group_id;

    var date = new Date();

    let url = ''
    let ext = ''
    let blobType = ''
    if(type == 'csv'){
        url = '/manifest-download-batch-csv';
        ext = '.csv';
        blobType = 'application/vnd.ms-excel';
    }else{
        url = '/manifest-download-batch-pdf'
        ext = '.pdf';
        blobType = 'application/pdf';
    }
    return new Promise((res, rej) => {
        axios({
            url: url, 
            method: 'POST',
            params:  form,
            responseType: 'blob', // important
        }).then((response) => {
            const filename = 'manifest_data_'+formatDate(date)+' '+zone+ext;
            const blob = new Blob([response.data], { type: blobType });
            saveAs(blob, filename);
            form.reset();
            selectedItems.value = [];
            openManifestItem.value = false;
            res(response.data);
            //refresh the component
            form.get(route('manifest.sent'), {
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    
                }
            });
        });
    });
}
const manifestData = ref(props.manifests)

const updateManifestItem = (item) => {
    console.log(item)
    form.group_name = item.group_name
    form.download_group_id = item.group_id
    openManifestItem.value = true;
};

const name = ref('')
const notification = useNotification()
const saveManifestName = () => {
    axios.post('/manifest-add-name',form).then((res) => {
        // using options
        notification.notify({
          title: "",
          text: "Manifest name successfully saved!",
        });
        form.get(route('manifest.sent'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                form.reset()
                openManifestItem.value = false;
            }
        });
    })
}
const manifestDataResult = ref('')
const manifestModal = ref(false)
const showRow = (item: ClickRowArgument) => {
    console.log(JSON.stringify(item.data))
    manifestDataResult.value = item.data
    manifestModal.value = true
};

const filterHandler = (e) =>{
    let basket_total = 0;
    totalVal.value = 0;
    selectedItems.value = []
    let results = []

    if(searchValue.value != ""){
        let filterData = manifestData.value.filter(function(data) {
            let transformedId = data.id.toString();
            let transformedMsrp = data.msrp.toString();
            console.log(data)
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

</script>

<template>
    <Head title="Manifest" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manifest</h2>
        </template>
        <notifications />
        <div class="py-3">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" >

                     <div class="grid grid-cols-4 gap-2 xl:grid-cols-4 xl:gap-2 sm:grid-cols-2 sm:gap-2 xs:grid-cols-1">
                        <!-- <div class="m-6">
                            <InputLabel for="search" value="Filter Search"/>

                            <TextInput
                                id="search"
                                v-model="searchValue"
                                type="readonly"
                                class="border-solid border-2 border-black-600 p-2 mt-1 block"
                                placeholder="Search here..."
                            /> 
                            
                        </div> -->
                        <div class="m-6 mt-10">
                            <a href="/manifest"><PrimaryButton> Switch to list view </PrimaryButton></a>
                            </div>

                            
                    </div>
                    
                    <EasyDataTable
                        :headers="headers"
                        :items="manifests"
                        show-index
                        @click-row="showRow"
                      >
                        <template #item-downloaded_at="item">
                            {{item.downloaded_at}}
                        </template>

                        <template #item-group_name="item">
                            <p v-if="item.group_name">{{item.group_name}}</p><p v-else>N/A</p>
                        </template>

                        <template #item-sum="item">
                            ${{item.sum.toFixed(2)}}
                        </template>

                        <template #item-action="item">
                            <div class="customize-header pt-2 pb-2">
                            <PrimaryButton @click="updateManifestItem(item)"> Update Manifest Name </PrimaryButton>
                            </div>
                        </template>

                        <template #item-selected="item">
                            <div class="customize-header">
                                <input type="checkbox" v-model="selectedItems" :value="item">
                            </div>
                        </template>

                        <!-- <template #expand="item">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <th scope="col" class="px-6 py-3">Item #</th>
                                    <th scope="col" class="px-6 py-3">Description</th>
                                    <th scope="col" class="px-6 py-3">Quantity</th>
                                    <th scope="col" class="px-6 py-3">MSRP</th>
                                    <th scope="col" class="px-6 py-3">$ Total</th>
                                    <th scope="col" class="px-6 py-3">Pallet #</th>
                                </thead>

                                <tbody>
                                    <tr v-for="data in item.data" class="text-gray dark:text-white bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-3">{{data.item}}</td>
                                        <td class="px-6 py-3">{{data.description}}</td>
                                        <td class="px-6 py-3">{{data.quantity}}</td>
                                        <td class="px-6 py-3">{{data.msrp}}</td>
                                        <td class="px-6 py-3">{{data.msrp * data.quantity}}</td>
                                        <td class="px-6 py-3">{{data.pallet}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </template> -->

                      </EasyDataTable>
                    
                </div>
            </div>
        </div>
        <Modal :show="manifestModal" @close="closeModal" :maxWidth="40">
            <div class="p-6">
                <PrimaryButton class="mr-1" v-if="selectedItems.length" @click="submitManifest()"> Download via(CSV) </PrimaryButton>
                            <PrimaryButton class="ml-1 sm:ml-0 sm:mt-4" v-if="selectedItems.length" @click="downloadManifestPdf()"> Download via(PDF) </PrimaryButton>
                <PrimaryButton class="float-right" @click="manifestModal = false">Close</PrimaryButton>
                <div class="sm:w-full m-6">
                    <InputLabel for="search" value="Filter Search"/>

                    <TextInput
                        id="search"
                        v-model="searchValue"
                        type="search"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block"
                        placeholder="Search here..."
                        @input="filterHandler"
                    />
                </div>
                <EasyDataTable
                    :headers="manifestHeaders"
                    :items="manifestDataResult"
                    :search-value="searchValue"
                    v-model:items-selected="selectedItems"
                    :rows-items="[25,50,100,manifestData.length]"
                  >

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

                  </EasyDataTable>
               
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
