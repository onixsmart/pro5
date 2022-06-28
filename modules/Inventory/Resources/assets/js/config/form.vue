<template>
  <div class="card">
    <div class="card-header bg-info">
      <h3 class="my-0">Configuraciones</h3>
    </div>
    <div class="card-body">
      <form autocomplete="off">
        <div class="form-body">
          <div class="row">
            <div class="col-md-12">
              <x-input label="Venta con restricción de stock" :error="errors.stock_control">
                <el-switch v-model="form.stock_control" active-text="Si" inactive-text="No"
                           @change="submit"></el-switch>
              </x-input>
            </div>
            <div class="col-md-12">
              <x-input label="Generar automaticamente codigo interno del producto" :error="errors.generate_internal_id">
                <el-switch v-model="form.generate_internal_id" active-text="Si" inactive-text="No"
                           @change="submit"></el-switch>
              </x-input>
            </div>
            <div class="col-md-12">
              <x-input label="Stock afectado por pedidos" :error="errors.generate_internal_id">
                <el-switch v-model="form.stock_change_by_order_notes" active-text="Si" inactive-text="No"
                           @change="submit"></el-switch>
              </x-input>
            </div>
          </div>
        </div>
      </form>
    </div>
  </div>
</template>

<script>

import XInput from "../../../../../../resources/js/components/XInput";

export default {
  name: 'InventoryConfiguration',
  components: {XInput},
  data() {
    return {
      loading_submit: false,
      resource: 'inventories/configuration',
      errors: {},
      form: {}
    }
  },
  async created() {
    this.initForm();
    await this.getRecord()
  },
  methods: {
    initForm() {
      this.errors = {};
      this.form = {
        id: null,
        stock_control: false,
        generate_internal_id: false,
        stock_change_by_order_notes: false,
      };
    },
    async getRecord() {
      await this.$http.get(`/${this.resource}/record`).then(response => {
        if (response.data !== '') this.form = response.data.data;
      });
    },
    async submit() {
      this.loading_submit = true;
      await this.$http.post(`/${this.resource}`, this.form).then(response => {
        if (response.data.success) {
          this.$message.success(response.data.message);
        } else {
          this.$message.error(response.data.message);
          this.getRecord()
        }
      }).catch(error => {
        if (error.response.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          console.log(error);
        }
      }).then(() => {
        this.loading_submit = false;
      });
    }
  }
}
</script>
