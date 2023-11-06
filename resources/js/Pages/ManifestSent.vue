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
    group_name: '',
    download_group_id: 0,
    item: '',
    pallet: 0,
    images: [],
    features: '',
    selected: [],
    file: ''
});
  
const headers: Header[] = [
  { text: "Select", value: "selected" },
  { text: "Downloaded at", value: "downloaded_at", sortable: true },
  { text: "Manifest name", value: "group_name", sortable: true },
  { text: "$ Total", value: "sum", sortable: true },
  { text: "Action", value: "action" },
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

    form.selected = selectedItems.value[0].group_id;

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

</script>

<template>
    <Head title="Manifest" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manifest</h2>
        </template>
        <notifications />
        <div class="py-12">
            <div class="max-w-8xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" >

                    <div class="">
                        <div class="m-6">
                            <!-- <InputLabel for="search" value="Filter Search"/>

                            <TextInput
                                id="search"
                                v-model="searchValue"
                                type="readonly"
                                class="border-solid border-2 border-black-600 p-2 mt-1 block"
                                placeholder="Search here..."
                            /> -->

                        </div>

                        <div class="m-6 pt-6" v-if="selectedItems.length">
                            <PrimaryButton class="mr-5" @click="downloadManifest('csv')"> Download Selected Manifest(CSV) </PrimaryButton>
                            <PrimaryButton class="mr-5" @click="downloadManifest('pdf')"> Download Selected Manifest(PDF) </PrimaryButton>
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
                    
                    <EasyDataTable
                        :headers="headers"
                        :items="manifests"
                        :search-value="searchValue"
                        show-index
                      >
                        <template #item-downloaded_at="item">
                            {{item.downloaded_at}}
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

                        <template #expand="item">
                            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <th scope="col" class="px-6 py-3">ID</th>
                                    <th scope="col" class="px-6 py-3">Item #</th>
                                    <th scope="col" class="px-6 py-3">Description</th>
                                    <th scope="col" class="px-6 py-3">Quantity</th>
                                    <th scope="col" class="px-6 py-3">MSRP</th>
                                    <th scope="col" class="px-6 py-3">$ Total</th>
                                    <th scope="col" class="px-6 py-3">Pallet #</th>
                                </thead>

                                <tbody>
                                    <tr v-for="data in item.data" class="text-white bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                                        <td class="px-6 py-3">{{data.id}}</td>
                                        <td class="px-6 py-3">{{data.item}}</td>
                                        <td class="px-6 py-3">{{data.description}}</td>
                                        <td class="px-6 py-3">{{data.quantity}}</td>
                                        <td class="px-6 py-3">{{data.msrp}}</td>
                                        <td class="px-6 py-3">{{data.totalMsrp}}</td>
                                        <td class="px-6 py-3">{{data.pallet}}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </template>

                      </EasyDataTable>
                    
                </div>
            </div>
        </div>
        <Modal :show="openManifestItem" @close="closeModal">
            <div class="p-6">

                <div class="mt-6">
                    <InputLabel for="item" value="Manifest Name"/>

                    <TextInput
                        id="item"
                        v-model="form.group_name"
                        type="readonly"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block block w-full"
                        placeholder="Manifest Name"
                    />
                    
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton class="mr-4" @click="closeModal"> Cancel </SecondaryButton>
                    <PrimaryButton @click="saveManifestName"> Submit </PrimaryButton>

                    
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
