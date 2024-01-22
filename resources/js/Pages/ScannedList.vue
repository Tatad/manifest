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
  import type { Header, Item, FilterOption } from "vue3-easy-data-table";

  const props = defineProps({
    list: {
        type: Array,
    }
  });

  const form = useForm({
    id: 0,
    item: '',
    upc_code: ''
  });

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

  const itemNumber = (item) => {
    itemNumberModal.value = true;
    form.item = item.item;
    form.id = item.id;
    form.upc_code = item.upc_code;
  }

  const updateItem = () => {
    console.log(form.id)
    axios.post('/add-item-number', form).then((response) => {
        form.reset();
        form.get(route('scannedList'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
              openEnlargeImageModal.value = false;
              itemNumberModal.value = false;
            }
        });
    });
  }

  const closeModal = () => {
      openEnlargeImageModal.value = false;
      itemNumberModal.value = false;
  };
</script>

<template>
  <Head title="Scanned List" />
  <GuestLayout>
    <template #header>
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Scanned List</h2>
    </template>

    <div class="py-3">
      <EasyDataTable
          :headers="headers"
          :items="list"
          ref="dataTable"
        >
        <template #item-item="item">
          <p v-if="item.item">{{item.item}}</p><p v-else>N/A</p>
        </template>

        <template #item-image_name="item">
          <img :src="item.image_name" class="object-fit: contain;" @click.prevent="enlargeImage(item)">
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

      <Modal :show="itemNumberModal" @close="closeModal">
          <div class="p-6">
              <div class="mt-6">
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
              </div>

              <div class="mt-6 flex justify-end">
                  <SecondaryButton class="mr-4" @click="closeModal"> Close </SecondaryButton>
                  <PrimaryButton @click="updateItem(item)"> Save </PrimaryButton>
              </div>
          </div>
      </Modal>

    </div>
  </GuestLayout>
 
</template>

<style scoped>
a {
  color: #42b983;
}
.information {
  margin-top: 100px;
}
</style>
