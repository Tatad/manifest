<script  lang="ts" setup>
  import { ref, computed } from "vue";
  import GuestLayout from '@/Layouts/GuestLayout.vue';
  import { Head } from '@inertiajs/vue3';
  import PrimaryButton from '@/Components/PrimaryButton.vue';
  import SecondaryButton from '@/Components/SecondaryButton.vue';
  import { useForm } from '@inertiajs/vue3';
  import Modal from '@/Components/Modal.vue';
  import TextInput from '@/Components/TextInput.vue';
  import InputError from '@/Components/InputError.vue';
  import InputLabel from '@/Components/InputLabel.vue';
  import TextArea from '@/Components/TextArea.vue';
  import Menu from '@/Pages/Menu.vue';
  import type { Header, Item, FilterOption } from "vue3-easy-data-table";
  import { createToaster } from "@meforma/vue-toaster";

  const props = defineProps({
    list: {
      type: Array,
    },
    data: {
      type: Object,
    }
  });

  const itemInfoData = ref(props.data);

  const form = useForm({
    id: 0,
    item: '',
    upc_code: '',
    description: '',
    isEmpty: 0,
    image: '',
    type: '',
    msrp: '',
    retail_price: ''
  });

  const itemInfo = ref([])
  const buttonDisabled = ref(false)

  const headers: Header[] = [
    { text: "UPC Code", value: "upc_code", sortable: true},
    { text: "Item Number", value: "item", sortable: true},
    { text: "Image", value: "image_name", sortable: true},
    { text: "Actions",  value: "action"}
  ];

  const openEnlargeImageModal = ref(false);
  const itemNumberModal = ref(false);
  const currentItem = ref('');
  const removeItemModal = ref(false);

  const enlargeImage = (item) => {
    currentItem.value = item;
    openEnlargeImageModal.value = true;
  }

  let onChange = (event) => {
      form.image = event.target.files ? event.target.files[0] : null;
  }

  const itemNumber = (item) => {
    itemNumberModal.value = true;
    form.item = item.item;
    form.id = item.id;
    form.upc_code = item.upc_code;
  }

  const removeItemConfirm = (item) => {
    removeItemModal.value = true;
    form.id = item.id;
  }

  const removeItem = () => {
    form.post(route('removeScannedItem'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
          removeItemModal.value = false;
          form.get(route('scannedList'), {
            onSuccess: () => {
              toaster.warning('Scanned Item successfully removed.', {
                position: "top-right",
              });
            }
          });
        }
    });
  }

  const lookUpItem = () => {
    buttonDisabled.value = true;
    buttonDisabled.value = true;
    form.post(route('addItemNumber'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
          buttonDisabled.value = false;
          form.description = response.props.data.description
          form.msrp = (response.props.data.msrp) ? response.props.data.msrp : 0
          form.retail_price = (response.props.data.retail_price) ? response.props.data.retail_price : 0
          form.item = response.props.data.item
          form.isEmpty = response.props.data.isEmpty
          itemInfo.value = response.props.data
        }
    });
  }

  const submit = () => {
    form.post(route('addItemViaScannedList'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
          openEnlargeImageModal.value = false;
          itemNumberModal.value = false;
          form.get(route('scannedList'), {
            onSuccess: () => {
              itemInfo.value = [];
              form.reset();
              form.item = '';
              form.description = '';
              form.msrp = '';
              form.retail_price = '';
              form.image = '';
              form.type = '';
              form.upc_code = '';
              form.image = '';
              openEnlargeImageModal.value = false;
              itemNumberModal.value = false;
            }
          });
        }
    });
  }

  const closeModal = () => {
    form.get(route('scannedList'), {
      onSuccess: () => {
        itemInfo.value = [];
        form.reset();
        form.item = '';
        form.description = '';
        form.msrp = '';
        form.retail_price = '';
        form.image = '';
        form.type = '';
        form.upc_code = '';
        form.image = '';
        openEnlargeImageModal.value = false;
        removeItemModal.value = false;
        itemNumberModal.value = false;
      }
    });
  };

  const toaster = createToaster({ /* options */ });
</script>

<template>
  <Head title="Scanned List" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Scanned List</h2>
    </template>

    <div class="py-3 mb-40">
      <EasyDataTable
          :headers="headers"
          :items="list"
          ref="dataTable"
        >
        <template #item-item="item">
          <p v-if="item.item">{{item.item}}</p><p v-else>N/A</p>
        </template>

        <template #item-image_name="item">
          <img v-if="item.image_name" :src="item.image_name" class="object-fit: contain;" @click.prevent="enlargeImage(item)">
          <span v-else>N/A</span>
        </template>

        <template #item-action="item">
          <div>
            <PrimaryButton @click.prevent="itemNumber(item)" class="px-2 my-4 bg-indigo-400 mr-2"><span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
</svg>
</span></PrimaryButton>
            <PrimaryButton @click.prevent="removeItemConfirm(item)" class="px-2 my-4 bg-red-400"><span class=""><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
  <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
</svg>
</span></PrimaryButton>
          </div>
        </template>
      </EasyDataTable>  

      <Modal :show="removeItemModal" @close="closeModal">
          <div class="p-6">
              <div class="mt-6">
                <h1 class="text-xl text-gray-800">Are you sure you want to remove the scanned item?</h1>
              </div>

              <div class="mt-6 flex justify-end">
                  <SecondaryButton class="mr-4" @click="closeModal"> Close </SecondaryButton>
                  <PrimaryButton class="mr-4 bg-green-400 px-4" @click="removeItem"> Yes </PrimaryButton>
              </div>
          </div>
      </Modal>

      <Modal :show="openEnlargeImageModal" @close="closeModal">
          <div class="p-6">
              <div class="mt-6">
                <img :src="currentItem.image_name">
              </div>

              <div class="mt-6 flex justify-end">
                  <SecondaryButton class="mr-4" @click="closeModal"> Close </SecondaryButton>
              </div>
          </div>
      </Modal>

      <Modal :show="itemNumberModal == true" @close="closeModal">
          <div class="p-6">
              <div class="mt-6">
                  <div v-if="itemInfo.length == 0">
                    <InputLabel class="dark:text-black" for="item" value="Item Number"/>
                    <TextInput
                        id="item"
                        v-model="form.item"
                        type="number"
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-full"
                        placeholder="Item Number"
                    />
                    <InputError :message="form.errors.item" class="mt-2" />
                  </div>

                  <div v-if="itemInfo.scrapingbee == 0">
                    <h2 class="text-2xl font-extrabold dark:text-black">Is this the item you are looking for?</h2>
                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="UPC Code"/>
                      <TextInput
                          id="item"
                          v-model="form.upc_code"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="UPC Code" readonly
                      />
                    </div>
                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Description"/>
                      <TextInput
                          id="item"
                          v-model="itemInfo.description"
                          type="text"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item Description" readonly
                      />
                    </div>
                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Number"/>
                      <TextInput
                          id="item"
                          v-model="itemInfo.item"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item Number" readonly
                      />
                    </div>

                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item MSRP"/>
                      <TextInput
                          id="item"
                          v-model="itemInfo.msrp"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item Number" readonly
                      />
                    </div>

                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Retail Price"/>
                      <TextInput
                          id="item"
                          v-model="itemInfo.retail_price"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item  Retail Price" readonly
                      />
                    </div>

                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Type"/>
                      <TextInput
                          id="item"
                          v-model="itemInfo.type"
                          type="text"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item Type" readonly
                      />
                    </div>

                    <div class="pt-6" v-if="itemInfo.images" v-for="image in JSON.parse(itemInfo.images)">
                      <img class="pt-6 object-cover" :src="image" alt="image description">
                    </div>
                    
                  </div>
                  <div v-if="itemInfo.isEmpty  == 1">
                    <h2 class="text-2xl font-extrabold dark:text-black">Item not found. Please enter the details below.</h2>
                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="UPC Code"/>
                      <TextInput
                          id="item"
                          v-model="form.upc_code"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="UPC Code" readonly
                      />
                    </div>
                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Description"/>
                      <TextInput
                          id="item"
                          v-model="form.description"
                          type="text"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item Description"
                      />
                    </div>
                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Number"/>
                      <TextInput
                          id="item"
                          v-model="form.item"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item Number" readonly
                      />
                    </div>

                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item MSRP"/>
                      <TextInput
                          id="item"
                          v-model="form.msrp"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item MSRP"
                      />
                    </div>

                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Retail Price"/>
                      <TextInput
                          id="item"
                          v-model="form.retail_price"
                          type="number"
                          class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
                          placeholder="Item  Retail Price"
                      />
                    </div>

                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item Image"/>
                      <input class="block text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 dark:text-gray-400 focus:outline-none dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 w-3/4" id="file_input" type="file"  accept="image/*" capture="camera" @change="onChange">
                    </div>

                    <div class="mt-2">
                      <InputLabel class="dark:text-black" for="item" value="Item type"/>
                      <select
                        class="w-3/4"
                        v-model="form.type"
                        name="type"
                      >
                        <option value="Clothing">Clothing</option>
                        <option value="Mixed">Mixed</option>
                      </select>
                    </div>
                  </div>
              </div>

              <div class="mt-6 flex justify-end">
                  <SecondaryButton v-if="buttonDisabled == false" class="mr-4" @click="closeModal"> Close </SecondaryButton>
                  <PrimaryButton v-if="buttonDisabled == false && form.item.length <= 5 && itemInfo.length == 0"  class="mr-4 bg-gray-400 dark:bg-gray-400 text-xs px-4"> Lookup </PrimaryButton>
                  <PrimaryButton v-if="buttonDisabled == false && form.item.length > 5 && itemInfo.length == 0"  class="mr-4 bg-green-400 dark:bg-green-400 text-xs px-4" @click="lookUpItem"> Lookup </PrimaryButton>
                  <PrimaryButton v-if="buttonDisabled == false && itemInfo.item" class="mr-4 bg-green-400 dark:bg-green-400 text-xs px-4" @click="submit"> Submit </PrimaryButton>


                  <button v-if="buttonDisabled == true" type="button" class="bg-indigo-400 h-max w-max rounded-lg text-white font-bold hover:bg-indigo-300 hover:cursor-not-allowed duration-[500ms,800ms]" disabled>
                    <div class="flex items-center justify-center m-[10px]"> 
                        <div class="h-5 w-5 border-t-transparent border-solid animate-spin rounded-full border-white border-4"></div>
                        <div class="ml-2"> Processing... </div>
                    </div>
                </button>
              </div>
          </div>
      </Modal>

    </div>
  </GuestLayout>
  <Menu></Menu>
</template>

<style scoped>
a {
  color: #42b983;
}
.information {
  margin-top: 100px;
}
</style>
