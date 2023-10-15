<script  lang="ts" setup>

import { ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import type { Header, Item } from "vue3-easy-data-table";
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
  { text: "ID", value: "id" },
  { text: "Item #", value: "item"},
  { text: "Description", value: "description"},
  { text: "Quantity", value: "quantity"},
  { text: "MSRP", value: "msrp"},
  { text: "Pallet #", value: "pallet", sortable: true},
  { text: "Actions",  value: "action"}
];

const openManifestItem = ref(false);
const sendManifestConfirm = ref(false);
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
    sendManifestConfirm.value = false;
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
            sendManifestConfirm.value = false;
        }
    });
}

const submitManifest = (item) => {
    form.selected = selectedItems;
    form.get(route('manifest'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {

        }
    });
    var date = new Date();
    
    return new Promise((res, rej) => {
        axios.post('/manifest', form, { responseType: 'blob'}).then((response) => {
            const filename = 'manifest_data_'+date+'.xlsx';
            const blob = new Blob([response.data], { type: 'application/vnd.ms-excel' });
            saveAs(blob, filename);
            form.reset();
            selectedItems.value = [];
            openManifestItem.value = false;
            sendManifestConfirm.value = false;
            res(response.data);
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

</script>

<template>
    <Head title="Manifest" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Manifest</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
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

        <div class="m-6 pt-6">
            <input type="file" @change="onChange" >
        </div>

        <div class="m-6 pt-6" v-if="selectedItems.length">
            <PrimaryButton @click="sendManifestConfirm = true"> Send Selected Manifest </PrimaryButton>

            <Modal :show="sendManifestConfirm" @close="closeModal">
                <div class="p-6">

                    <div class="mt-6">

                        <h1>Are you sure you want to send the selected manifest?</h1>
                        
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
  >

  <template #item-selected="item">
      <div class="customize-header">
        <input type="checkbox" v-model="selectedItems" :value="item.item">
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
