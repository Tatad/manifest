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

  const lookUpItem = () => {
    buttonDisabled.value = true;
    // axios.post('/add-item-number', form).then((response) => {
    //   console.log(response.data)
    //     buttonDisabled.value = false;
    //     form.description = response.data.description
    //     form.msrp = (response.data.msrp) ? response.data.msrp : 0
    //     form.retail_price = (response.data.retail_price) ? response.data.retail_price : 0
    //     form.item = response.data.item
    //     form.isEmpty = response.data.isEmpty
    //     itemInfo.value = response.data
    //     form.get(route('scannedList'), {
    //         preserveScroll: true,
    //         preserveState: true,
    //         onSuccess: () => {

    //         }
    //     });
    // });
    buttonDisabled.value = true;
    form.post(route('addItemNumber'), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (response) => {
          console.log(response.props.data);
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
    console.log(form)
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
        itemNumberModal.value = false;
      }
    });
  };
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
          <PrimaryButton @click.prevent="itemNumber(item)">Edit</PrimaryButton>
        </template>
      </EasyDataTable>  

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
                        class="border-solid border-2 border-black-600 p-2 mt-1 block block w-3/4"
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
                  <PrimaryButton v-if="buttonDisabled == false && itemInfo.length == 0"  class="mr-4" @click="lookUpItem"> Lookup </PrimaryButton>
                  <PrimaryButton v-if="buttonDisabled == false && itemInfo.item" class="mr-4" @click="submit"> Submit </PrimaryButton>


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
