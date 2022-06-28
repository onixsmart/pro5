<template>
  <el-dialog :close-on-click-modal="false"
             :title="titleDialog"
             :visible="showDialog"
             top="7vh"
             :append-to-body="true"
             @close="clickClose"
             @open="create">
    <form autocomplete="off"
          @submit.prevent="clickAddItem">
      <div class="form-body">
        <div class="row">
          <div class="col-md-7 col-lg-7 col-xl-7 col-sm-7">
            <div id="custom-select"
                 :class="{'has-danger': errors.item_id}"
                 class="form-group">
              <label class="control-label">
                Producto/Servicio
                <a v-if="can_add_new_product"
                   href="#"
                   @click.prevent="showDialogNewItem = true">
                  [+ Nuevo]
                </a>
              </label>
              <template v-if="!search_item_by_barcode" id="select-append">
                <el-input id="custom-input">
                  <el-select v-model="itemId"
                             id="selectItem"
                             slot="prepend"
                             :disabled="recordItem != null"
                             :loading="loading_search"
                             :remote-method="searchRemoteItems"
                             filterable
                             placeholder="Buscar"
                             popper-class="el-select-items"
                             remote
                             @change="changeItem">
                    <el-tooltip
                      v-for="option in items"
                      :key="option.id"
                      placement="left">
                      <div slot="content"
                           v-html="itemSlotTooltipView(option)"></div>
                      <el-option :label="itemOptionDescriptionView(option)"
                                 :value="option.id"></el-option>
                    </el-tooltip>
                  </el-select>
                  <el-tooltip
                    slot="append"
                    :disabled="recordItem != null"
                    class="item"
                    content="Ver Stock del Producto"
                    effect="dark"
                    placement="bottom">
                    <el-button
                      :disabled="isEditItemNote"
                      @click.prevent="clickWarehouseDetail()">
                      <i class="fa fa-search"></i>
                    </el-button>
                  </el-tooltip>
                </el-input>
              </template>
              <template v-else>
                <el-input id="custom-input">
                  <el-select
                    id="select-width"
                    ref="selectBarcode"
                    slot="prepend"
                    v-model="itemId"
                    :disabled="recordItem != null"
                    :loading="loading_search"
                    :remote-method="searchRemoteItems"
                    remote
                    filterable
                    placeholder="Buscar"
                    popper-class="el-select-items"
                    value-key="id"
                    @change="changeItem">
                    <el-option
                      v-for="option in items"
                      :key="option.id"
                      :label="option.full_description"
                      :value="option.id"></el-option>
                  </el-select>
                  <el-tooltip
                    slot="append"
                    :disabled="recordItem != null"
                    class="item"
                    content="Ver Stock del Producto"
                    effect="dark"
                    placement="bottom">
                    <el-button
                      :disabled="isEditItemNote"
                      @click.prevent="clickWarehouseDetail()">
                      <i class="fa fa-search"></i>
                    </el-button>
                  </el-tooltip>
                </el-input>
              </template>

              <template v-if="!is_client">
                <el-checkbox v-model="search_item_by_barcode"
                             :disabled="recordItem != null">Buscar por código de barras
                </el-checkbox>
                <br>
              </template>
              <el-checkbox v-model="form.has_icbper"
                           v-if="showDiscounts"
                           :disabled="isEditItemNote">Impuesto a la Bolsa Plástica
              </el-checkbox>
              <small v-if="errors.item_id"
                     class="form-control-feedback"
                     v-text="errors.item_id[0]"></small>
            </div>
          </div>
          <div class="col-md-5">
            <x-input label="Afectación Igv" :error="errors.affectation_igv_type_id">
              <el-select v-model="form.affectation_igv_type_id"
                         :disabled="!change_affectation_igv_type_id">
                <el-option v-for="option in affectation_igv_types"
                           :key="option.id"
                           :label="option.description"
                           :value="option.id"></el-option>
              </el-select>
              <el-checkbox v-model="change_affectation_igv_type_id"
                           :disabled="recordItem != null">Editar
              </el-checkbox>
            </x-input>
          </div>

          <div class="col-md-4 col-sm-4">
            <x-input label="Cantidad" :error="errors.quantity">
              <el-input v-model="form.quantity"
                        :disabled="form.calculate_quantity"
                        @blur="validateQuantity"
                        @input.native="changeValidateQuantity">
                <el-button slot="prepend"
                           :disabled="form.quantity < 0.01"
                           icon="el-icon-minus"
                           style="padding-right: 5px ;padding-left: 12px"
                           @click="clickDecrease"
                           v-if="!form.calculate_quantity"></el-button>
                <el-button slot="append"
                           icon="el-icon-plus"
                           style="padding-right: 5px ;padding-left: 12px"
                           @click="clickIncrease"
                           v-if="!form.calculate_quantity"></el-button>
              </el-input>
            </x-input>
          </div>

          <div class="col-md-4 col-sm-4">
            <x-input label="Precio Unitario"
                     :tooltip-content="itemLastPrice"
                     :error="errors.unit_price">
              <el-input v-model="form.unit_price"
                        id="form_unit_price"
                        @input="calculateQuantity">
                <template v-if="form.currency_type_symbol"
                          slot="prepend">
                  {{ form.currency_type_symbol }}
                </template>
              </el-input>
            </x-input>
          </div>
          <div class="col-md-4 col-sm-4" v-if="!form.calculate_quantity">
            <x-input label="Total">
              <el-input v-model="form.total"
                        readonly
                        @input="calculateTotal"></el-input>
            </x-input>
          </div>
          <div class="col-md-4 col-sm-4" v-else>
            <x-input label="Total venta producto" :error="errors.total">
              <el-input v-model="form.total"
                        id="form_total"
                        :min="0.01"
                        @input="calculateQuantity">
                <template v-if="form.currency_type_symbol"
                          slot="prepend">
                  {{ form.currency_type_symbol }}
                </template>
              </el-input>
            </x-input>
          </div>
          <div class="clearfix"></div>
          <div class="col-md-12">
            <label class="control-label">Atributo extra (visible en PDF)</label>
          </div>
          <div class="col-md-6">
            <x-input :error="errors.extra_attr_name">
              <el-input v-model="form.extra_attr_name"></el-input>
            </x-input>
          </div>
          <div class="col-md-6">
            <x-input :error="errors.extra_attr_value">
              <el-input v-model="form.extra_attr_value"></el-input>
            </x-input>
          </div>
          <div class="col-md-12 col-sm-12 mt-2" v-if="config.edit_name_product">
            <x-input label="Nombre producto en PDF" :error="errors.name_product_pdf">
              <vue-ckeditor
                v-model="form.name_product_pdf"
                :editors="editors"
                type="classic"></vue-ckeditor>
            </x-input>
          </div>
          <template v-if="!is_client">
            <!--            <div v-if="has_list_prices"-->
            <!--                 class="col-md-12">-->
            <!--              <div class="table-responsive"-->
            <!--                   style="margin:3px">-->
            <!--                <h5 class="separator-title">-->
            <!--                  Lista de Precios-->
            <!--                  <el-tooltip class="item"-->
            <!--                              content="Aplica para realizar compra/venta en presentacion de diferentes precios y/o cantidades"-->
            <!--                              effect="dark"-->
            <!--                              placement="top">-->
            <!--                    <i class="fa fa-info-circle"></i>-->
            <!--                  </el-tooltip>-->
            <!--                </h5>-->
            <!--                <table class="table">-->
            <!--                  <thead>-->
            <!--                  <tr>-->
            <!--                    <th class="text-center">Unidad</th>-->
            <!--                    <th class="text-center">Descripción</th>-->
            <!--                    <th class="text-center">Factor</th>-->
            <!--                    <th class="text-center">Precio 1</th>-->
            <!--                    <th class="text-center">Precio 2</th>-->
            <!--                    <th class="text-center">Precio 3</th>-->
            <!--                    <th class="text-center">Precio Default</th>-->
            <!--                    <th></th>-->
            <!--                  </tr>-->
            <!--                  </thead>-->
            <!--                  <tbody>-->
            <!--                  <tr v-for="(row, index) in form.item_unit_types"-->
            <!--                      :key="index">-->
            <!--                    <td class="text-center">{{ row.unit_type_id }}</td>-->
            <!--                    <td class="text-center">{{ row.description }}</td>-->
            <!--                    <td class="text-center">{{ row.quantity_unit }}</td>-->
            <!--                    <td class="text-center">{{ row.price1 }}</td>-->
            <!--                    <td class="text-center">{{ row.price2 }}</td>-->
            <!--                    <td class="text-center">{{ row.price3 }}</td>-->
            <!--                    <td class="text-center">Precio {{ row.price_default }}</td>-->
            <!--                    <td class="series-table-actions text-right">-->
            <!--                      <button class="btn waves-effect waves-light btn-xs btn-success"-->
            <!--                              type="button"-->
            <!--                              @click.prevent="selectedPrice(row)">-->
            <!--                        <i class="el-icon-check"></i>-->
            <!--                      </button>-->
            <!--                    </td>-->
            <!--                  </tr>-->
            <!--                  </tbody>-->
            <!--                </table>-->
            <!--              </div>-->
            <!--            </div>-->

            <div class="col-md-12 mt-2" v-if="itemId">
              <el-collapse v-model="activePanel">
                <el-collapse-item name="1" title="+ Agregar Descuentos/Cargos/Atributos especiales">
                  <div v-if="discount_types.length > 0">
                    <label class="control-label">Descuentos
                      <a href="#" @click.prevent="clickAddDiscount">[+ Agregar]</a>
                    </label>
                    <table class="table">
                      <thead>
                      <tr>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Porcentaje</th>
                        <th></th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="(row, index) in form.discounts" :key="index">
                        <td>
                          <el-select v-model="row.discount_type_id"
                                     @change="changeDiscount(index, row.discount_type_id)">
                            <el-option v-for="option in discount_types"
                                       :key="option.id"
                                       :label="option.description"
                                       :value="option.id"></el-option>
                          </el-select>
                        </td>
                        <td>
                          <el-input v-model="row.description"></el-input>
                        </td>
                        <td>
                          <div style="display: flex">
                            <el-button type="primary" @click="row.is_amount = !row.is_amount" style="margin-right: 4px">
                              <template v-if="row.is_amount">
                                {{ form.currency_type_symbol }}
                              </template>
                              <template v-else>
                                %
                              </template>
                            </el-button>
                            <el-input v-model="row.amount">
                            </el-input>
                          </div>
                        </td>
                        <td>
                          <el-button type="danger" @click="clickRemoveDiscount(index)">X</el-button>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-if="charge_types.length > 0">
                    <label class="control-label">Cargos
                      <a href="#" @click.prevent="clickAddCharge">[+ Agregar]</a>
                    </label>
                    <table class="table">
                      <thead>
                      <tr>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Porcentaje</th>
                        <th></th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="(row, index) in form.charges"
                          :key="index">
                        <td>
                          <el-select v-model="row.charge_type_id"
                                     @change="changeCharge(index, row.charge_type_id)">
                            <el-option v-for="option in charge_types"
                                       :key="option.id"
                                       :label="option.description"
                                       :value="option.id"></el-option>
                          </el-select>
                        </td>
                        <td>
                          <el-input v-model="row.description"></el-input>
                        </td>
                        <td>
                          <div style="display: flex">
                            <el-button type="primary" @click="row.is_amount = !row.is_amount" style="margin-right: 4px">
                              <template v-if="row.is_amount">
                                {{ form.currency_type_symbol }}
                              </template>
                              <template v-else>
                                %
                              </template>
                            </el-button>
                            <el-input v-model="row.amount">
                            </el-input>
                          </div>
                        </td>
                        <td>
                          <el-button type="danger" @click="clickRemoveCharge(index)">X</el-button>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                  <div v-if="attribute_types.length > 0">
                    <label class="control-label">Atributos
                      <a href="#" @click.prevent="clickAddAttribute">[+ Agregar]</a>
                    </label>
                    <table class="table">
                      <thead>
                      <tr>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th></th>
                      </tr>
                      </thead>
                      <tbody>
                      <tr v-for="(row, index) in form.attributes"
                          :key="index">
                        <td>
                          <el-select v-model="row.attribute_type_id"
                                     filterable
                                     @change="changeAttributeType(index)">
                            <el-option
                              v-for="option in attribute_types"
                              :key="option.id"
                              :label="option.description"
                              :value="option.id"></el-option>
                          </el-select>
                        </td>
                        <td>
                          <el-input v-model="row.value"
                                    @input="inputAttribute(index)"></el-input>
                        </td>
                        <td>
                          <button class="btn btn-danger"
                                  type="button"
                                  @click.prevent="clickRemoveAttribute(index)">x
                          </button>
                        </td>
                      </tr>
                      </tbody>
                    </table>
                  </div>
                </el-collapse-item>
              </el-collapse>
            </div>
          </template>
        </div>
      </div>
      <!-- @todo: Mejorar evitando duplicar codigo -->
      <!-- Mostrar en cel -->

      <div class="row hidden-md-up form-actions text-center">
        <div class="col-12">
          &nbsp;
        </div>
        <div class="col-6">
          <el-button class="form-control"
                     @click.prevent="clickClose()">Cerrar
          </el-button>
        </div>
        <div class="col-6">
          <el-button v-if="form.item_id"
                     class="add form-control btn btn-primary"
                     native-type="submit"
                     type="primary">
            {{ titleAction }}
          </el-button>
        </div>
      </div>
      <!-- @todo: Mejorar evitando duplicar codigo -->
      <!-- Mostrar en cel -->
      <!-- @todo: Mejorar evitando duplicar codigo -->
      <!-- Ocultar en cel -->

      <div class="form-actions text-right pt-2  hidden-sm-down">
        <el-button @click.prevent="clickClose()">Cerrar</el-button>
        <el-button v-if="form.item_id"
                   class="add"
                   native-type="submit"
                   type="primary">
          {{ titleAction }}
        </el-button>
      </div>
    </form>
    <item-form :external="true"
               :showDialog.sync="showDialogNewItem"></item-form>
    <!--    <warehouses-detail-->
    <!--      :isUpdateWarehouseId="isUpdateWarehouseId"-->
    <!--      :showDialog.sync="showWarehousesDetail"-->
    <!--      :warehouses="warehousesDetail">-->
    <!--    </warehouses-detail>-->
  </el-dialog>
</template>
<style>
.el-select-dropdown {
  margin-right: 5% !important;
  max-width: 80% !important;
}
</style>

<script>

import itemForm from '../../items/form.vue'

import {calculateRowItemOther} from '../../../../helpers/functions'
import WarehousesDetail from './warehouses.vue'

import ClassicEditor from '@ckeditor/ckeditor5-build-classic'
import VueCkeditor from 'vue-ckeditor5'
import {mapActions, mapState} from "vuex/dist/vuex.mjs";
import {ItemOptionDescription, ItemSlotTooltip} from "../../../../helpers/modal_item";
import XInput from "../../../../components/XInput";
import {uuid} from 'vue-uuid';

export default {
  name: 'QuotationItem',
  props: [
    'recordItem',
    'showDialog',
    'currencyTypeIdActive',
    'exchangeRateSale',
    'typeUser',
    'configuration',
    'displayDiscount',
    'customerId',
        'personTypeId',
  ],
  components: {
    XInput,
    itemForm,
    WarehousesDetail,
    'vue-ckeditor': VueCkeditor.component
  },
  data() {
    return {
      config: {},
      showDiscounts: true,
      extra_temp: undefined,
      operationTypeId: null,
      isEditItemNote: false,
      can_add_new_product: false,
      loading_search: false,
      titleAction: '',
      is_client: false,
      titleDialog: 'Agregar Producto o Servicio',
      resource: 'quotations',
      showDialogNewItem: false,
      has_list_prices: false,
      errors: {},
      form: {},
      all_items: [],
      items: [],
      operation_types: [],
      all_affectation_igv_types: [],
      aux_items: [],
      affectation_igv_types: [],
      system_isc_types: [],
      discount_types: [],
      charge_types: [],
      attribute_types: [],
      use_price: 1,
      change_affectation_igv_type_id: false,
      activePanel: 0,
      item_unit_types: [],
      item_unit_type: {},
      showWarehousesDetail: false,
      warehousesDetail: [],
      showListStock: false,
      search_item_by_barcode: false,
      // isUpdateWarehouseId: null,
      showDialogLots: false,
      showDialogSelectLots: false,
      lots: [],
      editors: {
        classic: ClassicEditor
      },
      readonly_total: 0,
      itemLastPrice: null,
      itemId: null,
      //item_unit_type: {}
    }
  },
  created() {
    this.initForm();
  },
  mounted() {
    this.getTables()
    this.$eventHub.$on('reloadDataItems', (item_id) => {
      this.reloadDataItems(item_id)
    })

    this.$eventHub.$on('selectWarehouseId', (warehouse_id) => {
      this.form.warehouse_id = warehouse_id
    })
    this.canCreateProduct();
  },
  computed: {
    edit_unit_price() {
      if (this.typeUser === 'admin') {
        return true
      }
      if (this.typeUser === 'seller') {
        return this.config.allow_edit_unit_price_to_seller;
      }
      return false;
    },
  },
  methods: {
    initForm() {
      this.itemId = null;
      this.items = [];
      this.errors = {};
      this.form = {};
    },
    async create() {
      this.initForm();
      this.titleDialog = (this.recordItem) ? ' Editar Producto o Servicio' : ' Agregar Producto o Servicio';
      this.titleAction = (this.recordItem) ? ' Editar' : ' Agregar';
      if (this.operation_types !== undefined) {
        let operation_type = await _.find(this.operation_types, {id: this.operationTypeId})
        if (operation_type !== undefined) {
          this.affectation_igv_types = await _.filter(this.all_affectation_igv_types, {exportation: operation_type.exportation})
        }
      }
      if (this.recordItem) {
        this.itemId = this.recordItem.item_id;
        this.items = [];
        await this.$http.get(`/store/search_item/${this.itemId}`)
          .then(response => {
            this.items = response.data;
          })
        // this.form = _.find(this.items, {'id': this.itemId});
        // this.form = this.items[0];
        this.form = Object.assign({}, this.items[0], this.recordItem);
        console.log(this.form);
        this.calculateTotal();
        // await this.reloadDataItems(this.recordItem.item_id)
        // this.form.item_id = await this.recordItem.item_id
        // await this.changeItem()
        // this.form.quantity = this.recordItem.quantity
        // this.form.unit_price = this.recordItem.unit_price
        // this.form.unit_price_value = this.recordItem.input_unit_price_value
        // this.form.unit_price_value = this.recordItem.input_unit_price_value
        // if (this.recordItem.item.has_igv == false) {
        //     this.form.unit_price = this.recordItem.total_base_igv
        // }

        // this.setHasIgvUpdate()
        // this.form.has_plastic_bag_taxes = (this.recordItem.total_plastic_bag_taxes > 0) ? true : false
        // this.form.warehouse_id = this.recordItem.warehouse_id
        //
        // if (this.recordItem.item.change_free_affectation_igv) {
        //
        //   this.form.affectation_igv_type_id = '15'
        //   this.form.item.change_free_affectation_igv = true
        //
        // } else {
        //   if (this.recordItem.item.original_affectation_igv_type_id) {
        //     this.form.affectation_igv_type_id = this.recordItem.item.original_affectation_igv_type_id
        //   }
        // }
        // this.calculateQuantity()
      } else {
        // this.isUpdateWarehouseId = null
        await this.searchRemoteItems('');
        this.$nextTick(_ => {
          this.focusSelectItem();
        })
      }


    },
    itemSlotTooltipView(item) {
      let label = 'Precio: ' + item.unit_price_label;
      if (item.warehouse_name) {
        label += '<br>Almacén: ' + item.warehouse_name
      }
      if (item.brand_name) {
        label += '<br>Marca: ' + item.brand_name
      }
      if (item.stock) {
        label += '<br>Stock: ' + item.stock
      }
      return label;
    },
    itemOptionDescriptionView(item) {
      let label = item.name;
      if (item.internal_id) {
        label = item.internal_id + ' - ' + label;
      }
      if (item.brand_name) {
        label += ' - ' + item.brand_name;
      }

      return label;
    },
    getTables() {
      this.$http.get(`/store/get_item_tables`).then(response => {
        let data = response.data;
        this.config = data.config
        this.operation_types = data.operation_types;
        this.all_affectation_igv_types = data.affectation_igv_types;
        this.affectation_igv_types = data.affectation_igv_types;
        this.system_isc_types = data.system_isc_types;
        this.discount_types = data.discount_types;
        this.charge_types = data.charge_types;
        this.attribute_types = data.attribute_types;
        this.is_client = data.is_client;
      })
    },
    canCreateProduct() {
      if (this.typeUser === 'admin') {
        this.can_add_new_product = true
      } else if (this.typeUser === 'seller') {
        if (this.config !== undefined && this.config.seller_can_create_product !== undefined) {
          this.can_add_new_product = this.config.seller_can_create_product;
        }
      }
      return this.can_add_new_product;
    },
    validateQuantity() {
      if (!this.form.quantity) {
        this.setMinQuantity()
      }
      if (isNaN(Number(this.form.quantity))) {
        this.setMinQuantity()
      }
      if (typeof parseFloat(this.form.quantity) !== 'number') {
        this.setMinQuantity()
      }
      if (this.form.quantity <= this.getMinQuantity()) {
        this.setMinQuantity()
      }
      this.calculateTotal()
    },
    changeValidateQuantity() {
      this.calculateTotal()
    },
    getMinQuantity() {
      return 0.01
    },
    setMinQuantity() {
      this.form.quantity = this.getMinQuantity()
    },
    clickDecrease() {
      this.form.quantity = parseInt(this.form.quantity) - 1;
      if (this.form.quantity <= this.getMinQuantity()) {
        this.setMinQuantity()
        return
      }
      this.calculateTotal()
    },
    clickIncrease() {
      this.form.quantity = parseInt(this.form.quantity + 1)
      this.calculateTotal()
    },
    async searchRemoteItems(input) {
      // if (input.length > 2) {
      this.loading_search = true
      await this.$http.post(`/store/search_items`, {
        'search': input,
        'search_by_barcode': this.search_item_by_barcode
      })
        .then(response => {
          this.items = response.data;
          this.enabledSearchItemsBarcode()
        })
      this.loading_search = false;
      // } else {
      //   this.filterItems()
      // }
    },
    filterItems() {
      this.items = this.all_items
    },
<<<<<<< HEAD
    methods: {
        ...mapActions([
            'loadConfiguration',
        ]),
        hasAttributes() {
            if (
                this.form.item !== undefined &&
                this.form.item.attributes !== undefined &&
                this.form.item.attributes !== null &&
                this.form.item.attributes.length > 0
            ) {
                return true
            }

            return false;
        },
        ItemSlotTooltipView(item) {
            return ItemSlotTooltip(item);
        },
        ItemOptionDescriptionView(item) {
            return ItemOptionDescription(item)
        },
        getTables() {

            let params = {};
            if(this.item_search_extra_parameters !== undefined){
                if(this.item_search_extra_parameters.only_service !== undefined){
                    params.only_service = 1;
                }
            }

            this.$http.get(`/${this.resource}/item/tables`, {params}).then(response => {
                let data = response.data
                this.all_items = data.items
                this.operation_types = data.operation_types
                this.all_affectation_igv_types = data.affectation_igv_types
                this.affectation_igv_types = data.affectation_igv_types
                this.system_isc_types = data.system_isc_types
                this.discount_types = data.discount_types
                this.charge_types = data.charge_types
                this.attribute_types = data.attribute_types
                this.is_client = data.is_client
                this.filterItems()

            })
        },
        canCreateProduct() {
            if (this.typeUser === 'admin') {
                this.can_add_new_product = true
            } else if (this.typeUser === 'seller') {
                if (this.config !== undefined && this.config.seller_can_create_product !== undefined) {
                    this.can_add_new_product = this.config.seller_can_create_product;
                }
            }
            return this.can_add_new_product;
        },
        validateQuantity() {

            if (!this.form.quantity) {
                this.setMinQuantity()
            }

            if (isNaN(Number(this.form.quantity))) {
                this.setMinQuantity()
            }

            if (typeof parseFloat(this.form.quantity) !== 'number') {
                this.setMinQuantity()
            }

            if (this.form.quantity <= this.getMinQuantity()) {
                this.setMinQuantity()
            }

            this.calculateTotal()
        },
        changeValidateQuantity(event) {
            this.calculateTotal()
        },
        getMinQuantity() {
            return 0.01
        },
        setMinQuantity() {
            this.form.quantity = this.getMinQuantity()
        },
        clickDecrease() {

            this.form.quantity = parseInt(this.form.quantity - 1)

            if (this.form.quantity <= this.getMinQuantity()) {
                this.setMinQuantity()
                return
            }

            this.calculateTotal()

        },
        clickIncrease() {
            this.form.quantity = parseInt(this.form.quantity + 1)
            this.calculateTotal()
        },
        async searchRemoteItems(input) {
            if (input.length > 2) {
                this.loading_search = true
                let params = {
                    'input': input,
                    'search_by_barcode': this.search_item_by_barcode ? 1 : 0
                }
                if(this.item_search_extra_parameters !== undefined){
                    if(this.item_search_extra_parameters.only_service !== undefined){
                        params.only_service = 1;
                    }
                }
                await this.$http.get(`/${this.resource}/search-items/`, {params})
                    .then(response => {
                        this.items = response.data.items
                        this.loading_search = false
                        this.enabledSearchItemsBarcode()
                        this.enabledSearchItemBySeries()
                        if (this.items.length == 0) {
                            this.filterItems()
                        }
                    })
            } else {
                this.filterItems()
            }

        },
        filterItems() {
            this.items = this.all_items
        },
        enabledSearchItemsBarcode() {
            if (this.search_item_by_barcode) {
                this.$refs.selectBarcode.$data.selectedLabel = '';
                if (this.items.length == 1) {
                    this.form.item_id = this.items[0].id;
                    this.$refs.selectBarcode.blur();
                    this.changeItem();
                }
            }
        },
        async enabledSearchItemBySeries() {

            if (this.config.search_item_by_series && this.items.length == 1) {

                this.$notify({title: "Serie ubicada", message: "Producto añadido!", type: "success", duration: 1200});
                this.form.item_id = this.items[0].id;
                this.$refs.selectSearchNormal.$data.selectedLabel = '';

                await this.changeItem();

                this.lots = await this.form.item.lots.map((lot) => {
                    lot.has_sale = true
                })

                await this.clickAddItem()

                this.$refs.selectSearchNormal.$data.selectedLabel = '';
            }

            if (this.config.search_item_by_series && this.items.length == 0) {
                this.$notify({title: "Serie no ubicada", message: "", type: "warning", duration: 1200});
            }

        },
        filterMethod(query) {

            let item = _.find(this.items, {'internal_id': query});

            if (item) {
                this.form.item_id = item.id
                this.changeItem()
            }
        },
        clickWarehouseDetail() {

            if (!this.form.item_id) {
                return this.$message.error('Seleccione un item');
            }

            let item = _.find(this.items, {'id': this.form.item_id});

            this.warehousesDetail = item.warehouses
            this.showWarehousesDetail = true
        },
        // filterItems(){
        //     this.items = this.items.filter(item => item.warehouses.length >0)
        // },
        initForm() {
            this.errors = {};

            this.form = {
                // category_id: [1],
                // edit: false,
                item_id: null,
                item: {},
                affectation_igv_type_id: null,
                affectation_igv_type: {},
                has_isc: false,
                system_isc_type_id: null,
                percentage_isc: 0,
                suggested_price: 0,
                quantity: 1,
                unit_price: 0,
                unit_price_value: 0,
                input_unit_price: 0,
                input_unit_price_value: 0,
                charges: [],
                discounts: [],
                attributes: [],
                has_igv: null,
                is_set: false,
                item_unit_types: [],
                prices_types:{
                    id: null,
                    description: null,
                    unit_type_id: 'NIU',
                    quantity_unit: 0,
                    price_default: 2,
                    prices: [],
                },
                has_plastic_bag_taxes: false,
                series_enabled: false,
                warehouse_id: null,
                lots_group: [],
                IdLoteSelected: null,
                document_item_id: null,
                item_unit_type_id: null,
                unit_type_id: null,
                extra_attr_name: 'Tiempo de entrega',
                extra_attr_value: '',
                name_product_pdf: ''
            };

            this.activePanel = 0;
            this.total_item = 0;
            this.item_unit_type = {};
            this.lots = []
            this.has_list_prices = false;
        },
        async create() {

            this.titleDialog = (this.recordItem) ? ' Editar Producto o Servicio' : ' Agregar Producto o Servicio';
            this.titleAction = (this.recordItem) ? ' Editar' : ' Agregar';
            if (this.operation_types !== undefined) {
                let operation_type = await _.find(this.operation_types, {id: this.operationTypeId})
                if (operation_type !== undefined) {
                    this.affectation_igv_types = await _.filter(this.all_affectation_igv_types, {exportation: operation_type.exportation})
                }
            }

            this.$http.get(`/price/search/${this.personTypeId}`)
            .then(response => {
                console.log(response.data)
                this.form.prices_types.prices = [];
                response.data.forEach(value => {
                    this.form.prices_types.prices.push(value.price)
                });

                this.form.prices_types.id=response.data[0].id
                this.form.prices_types.description=response.data[0].description
                this.form.prices_types.unit_type_id=response.data[0].unit_type_id
                this.form.prices_types.quantity_unit=response.data[0].quantity_unit
                this.form.prices_types.price_default=response.data[0].price_default
            })

            if (this.recordItem) {
                await this.reloadDataItems(this.recordItem.item_id)
                this.form.item_id = await this.recordItem.item_id
                await this.changeItem()
                this.form.quantity = this.recordItem.quantity
                this.form.unit_price = this.recordItem.unit_price
                this.form.unit_price_value = this.recordItem.input_unit_price_value
                // this.form.unit_price_value = this.recordItem.input_unit_price_value
                // if (this.recordItem.item.has_igv == false) {
                //     this.form.unit_price = this.recordItem.total_base_igv
                // }

                this.setHasIgvUpdate()
                this.form.has_plastic_bag_taxes = (this.recordItem.total_plastic_bag_taxes > 0) ? true : false
                this.form.warehouse_id = this.recordItem.warehouse_id
                if (this.recordItem.item.name_product_pdf) {
                    this.form.name_product_pdf = this.recordItem.item.name_product_pdf
                }
                if (this.recordItem.item.change_free_affectation_igv) {

                    this.form.affectation_igv_type_id = '15'
                    this.form.item.change_free_affectation_igv = true

                } else {
                    if (this.recordItem.item.original_affectation_igv_type_id) {
                        this.form.affectation_igv_type_id = this.recordItem.item.original_affectation_igv_type_id
                    }
                }
                this.calculateQuantity()
            } else {
                this.isUpdateWarehouseId = null
            }

        },
        setHasIgvUpdate(){

            if(this.recordItem.item)
            {
                this.form.has_igv = this.recordItem.item.has_igv

                if(this.form.item) this.form.item.has_igv = this.recordItem.item.has_igv
            }

        },
        async regularizeLots() {

            if (this.form.document_item_id && this.form.item.lots.length > 0) {

                await this.$http.get(`/${this.resource}/regularize-lots/${this.form.document_item_id}`).then((response) => {

                    let all_lots = this.form.item.lots
                    let available_lots = response.data

                    all_lots.forEach((lot, index) => {

                        let exist_lot = _.find(available_lots, (it) => {
                            return it.id == lot.id
                        })

                        if (!exist_lot) {
                            this.form.item.lots.splice(index, 1)
                        }

                    })
                })
                    .catch(error => {
                    })
                    .then(() => {
                    })

            }

        },
        clickAddDiscount() {
            this.form.discounts.push({
                discount_type_id: null,
                discount_type: null,
                description: null,
                percentage: 0,
                factor: 0,
                amount: 0,
                base: 0,
                is_amount: false
            })
        },
        clickRemoveDiscount(index) {
            this.form.discounts.splice(index, 1)
        },
        changeDiscountType(index) {
            let discount_type_id = this.form.discounts[index].discount_type_id
            this.form.discounts[index].discount_type = _.find(this.discount_types, {id: discount_type_id})
        },
        clickAddCharge() {
            this.form.charges.push({
                charge_type_id: null,
                charge_type: null,
                description: null,
                percentage: 0,
                factor: 0,
                amount: 0,
                base: 0
            })
        },
        clickRemoveCharge(index) {
            this.form.charges.splice(index, 1)
        },
        changeChargeType(index) {
            let charge_type_id = this.form.charges[index].charge_type_id
            this.form.charges[index].charge_type = _.find(this.charge_types, {id: charge_type_id})
        },
        clickAddAttribute() {
            this.form.attributes.push({
                attribute_type_id: null,
                description: null,
                value: null,
                start_date: null,
                end_date: null,
                duration: null,
            })
        },
        clickRemoveAttribute(index) {
            this.form.attributes.splice(index, 1)
        },
        changeAttributeType(index) {
            let attribute_type_id = this.form.attributes[index].attribute_type_id
            let attribute_type = _.find(this.attribute_types, {id: attribute_type_id})
            this.form.attributes[index].description = attribute_type.description
            this.inputAttribute(index)
        },
        inputAttribute(index) {

            let value = this.form.attributes[index].value
            let hotelAttributes = ['4003', '4004']

            this.form.attributes[index].start_date = (hotelAttributes.includes(this.form.attributes[index].attribute_type_id)) ? value : null

        },
        close() {
            this.initForm()
            this.$emit('update:showDialog', false)
        },
        async changeItem() {
            this.form.item = _.find(this.items, {'id': this.form.item_id});
            this.item_unit_types = this.form.item.item_unit_types;
            this.form.item_unit_types = _.find(this.items, {'id': this.form.item_id}).item_unit_types
            this.form.unit_price = this.form.item.sale_unit_price;
            this.form.unit_price_value = this.form.item.sale_unit_price;
            // this.lots = this.form.item.lots

            this.form.has_igv = this.form.item.has_igv;
            this.form.has_plastic_bag_taxes = this.form.item.has_plastic_bag_taxes;
            this.form.affectation_igv_type_id = this.form.item.sale_affectation_igv_type_id;
            this.form.quantity = 1;
            (this.item_unit_types.length > 0) ? this.has_list_prices = true : this.has_list_prices = false;
            this.cleanTotalItem();
            this.showListStock = true

            if (this.hasAttributes()) {
                const contex = this
                this.form.item.attributes.forEach((row) => {

                    contex.form.attributes.push({
                        attribute_type_id: row.attribute_type_id,
                        description: row.description,
                        value: row.value,
                        start_date: row.start_date,
                        end_date: row.end_date,
                        duration: row.duration,
                    })
                })
            }
            // this.form.lots_group = this.form.item.lots_group
            if(this.form.item.name_product_pdf && this.config.item_name_pdf_description){
                this.form.name_product_pdf = this.form.item.name_product_pdf;
            }

            this.getLastPriceItem()
        },
        focusTotalItem(change) {
            if (!change && this.form.item.calculate_quantity) {
                this.$refs.total_item.$el.getElementsByTagName('input')[0].focus()
                this.total_item = this.form.unit_price
            }
        },
        calculateQuantity() {
            if (this.form.item.calculate_quantity) {
                this.form.quantity = _.round((this.total_item / this.form.unit_price), 4)
            }
            this.calculateTotal()
        },
        calculateTotal() {
            this.readonly_total = _.round((this.form.quantity * this.form.unit_price), 4)
        },
        cleanTotalItem() {
            this.total_item = null
        },
        async clickAddItem() {

            this.validateQuantity()
            /*

                     if (this.form.item.lots_enabled) {
                         if (!this.form.IdLoteSelected)
                             return this.$message.error('Debe seleccionar un lote.');
                     }
                     */

            if (this.validateTotalItem().total_item) return;

            let affectation_igv_type_id = this.form.affectation_igv_type_id
            // let unit_price = (this.form.has_igv) ? this.form.unit_price : this.form.unit_price_value * 1.18;
            let unit_price = this.form.unit_price;
            if (this.form.has_igv === false) {
                if (
                    affectation_igv_type_id === "20" ||
                    affectation_igv_type_id === "21" ||
                    affectation_igv_type_id === "40"
                ) {
                    // do nothing
                    // exonerado de igv
                } else {
                    unit_price = this.form.unit_price * 1.18;

                }
            }

            this.form.input_unit_price_value = this.form.unit_price;
            // this.form.input_unit_price_value = this.form.unit_price_value;
            // let unit_price = (this.form.has_igv) ? this.form.unit_price : this.form.unit_price * 1.18;

            // this.form.item.unit_price = this.form.unit_price
            this.form.unit_price = unit_price;
            this.form.item.unit_price = unit_price;
            this.form.unit_price_value = unit_price;

            this.form.item.extra_attr_name = this.form.extra_attr_name;
            this.form.item.extra_attr_value = this.form.extra_attr_value;
            this.form.unit_price_value = this.form.unit_price;
            this.form.item.presentation = this.item_unit_type;
            this.form.affectation_igv_type = _.find(this.affectation_igv_types, {'id': affectation_igv_type_id});

            // let IdLoteSelected = this.form.IdLoteSelected
            // let document_item_id = this.form.document_item_id
            this.row = calculateRowItem(this.form, this.currencyTypeIdActive, this.exchangeRateSale);

            this.row.item.name_product_pdf = this.row.name_product_pdf || '';
            if (this.recordItem) {
                this.row.indexi = this.recordItem.indexi
            }
            /*

            let select_lots = await _.filter(this.row.item.lots, {'has_sale': true})
            let un_select_lots = await _.filter(this.row.item.lots, {'has_sale': false})

            if (this.form.item.series_enabled) {
                if (select_lots.length != this.form.quantity)
                    return this.$message.error('La cantidad de series seleccionadas son diferentes a la cantidad a vender');
            }

             */
            this.initForm();

            if (this.recordItem) {
                this.row.indexi = this.recordItem.indexi
            }

            // this.row.IdLoteSelected = IdLoteSelected
            // this.row.document_item_id = document_item_id

            this.$emit('add', this.row);

            if (this.search_item_by_barcode) {
                this.cleanItems()
            }

            if (this.recordItem) {
                this.close();
            } else {
                this.setFocusSelectItem();
            }
        },
        cleanItems() {
            this.items = []
            this.$refs.selectBarcode.$el.getElementsByTagName('input')[0].focus()
            // console.log("add cart barcode")
        },
        validateTotalItem() {

            this.errors = {}

            if (this.form.item.calculate_quantity) {
                if (this.total_item < 0.01)
                    this.$set(this.errors, 'total_item', ['total venta item debe ser mayor a 0.01']);
            }

            return this.errors
        },
        async reloadDataItems(item_id) {
            let params = {};
            if(this.item_search_extra_parameters !== undefined){
                if(this.item_search_extra_parameters.only_service !== undefined){
                    params.only_service = 1;
                }
            }

            if (!item_id) {

                await this.$http.get(`/${this.resource}/table/items`,{params}).then((response) => {
                    this.items = response.data
                    this.form.item_id = item_id
                    // if(item_id) this.changeItem()
                    // this.filterItems()
                })

            } else {

                await this.$http.get(`/${this.resource}/search/item/${item_id}`).then((response) => {

                    this.items = response.data.items
                    this.form.item_id = item_id
                    this.changeItem()

                })
            }

        },
        changePresentation() {
            let price = 0;

            this.item_unit_type = _.find(this.form.item.item_unit_types, {'id': this.form.item_unit_type_id});

            switch (this.item_unit_type.price_default) {
                case 1:
                    price = this.item_unit_type.price1
                    break;
                case 2:
                    price = this.item_unit_type.price2
                    break;
                case 3:
                    price = this.item_unit_type.price3
                    break;
            }

            this.form.unit_price = price;
            this.form.unit_price_value = price;
            this.form.item.unit_type_id = this.item_unit_type.unit_type_id;
        },
        selectedPrice(row) {
            let valor = 0
            switch (row.price_default) {
                case 1:
                    valor = row.price1
                    break
                case 2:
                    valor = row.price2
                    break
                case 3:
                    valor = row.price3
                    break
=======
    enabledSearchItemsBarcode() {
      if (this.search_item_by_barcode) {
        this.$refs.selectBarcode.$data.selectedLabel = '';
        if (this.items.length === 1) {
          this.form.item_id = this.items[0].id;
          this.$refs.selectBarcode.blur();
          this.changeItem();
        }
      }
    },
    filterMethod(query) {
      let item = _.find(this.items, {'internal_id': query});
      if (item) {
        this.form.item_id = item.id
        this.changeItem()
      }
    },
    clickWarehouseDetail() {
      if (!this.form.item_id) {
        return this.$message.error('Seleccione un item');
      }
      let item = _.find(this.items, {'id': this.form.item_id});
      this.warehousesDetail = item.warehouses
      this.showWarehousesDetail = true
    },
    setHasIgvUpdate() {
      if (this.recordItem.item) {
        this.form.has_igv = this.recordItem.item.has_igv
        if (this.form.item) this.form.item.has_igv = this.recordItem.item.has_igv
      }
    },
    clickAddDiscount() {
      this.form.discounts.push({
        discount_type_id: null,
        description: null,
        is_amount: false,
        amount: 0,
      })
    },
    clickRemoveDiscount(index) {
      this.form.discounts.splice(index, 1)
    },
    changeDiscount(index, id) {
      let record = _.find(this.discount_types, {'id': id})
      this.form.discounts[index].base = record.base
    },
    clickAddCharge() {
      this.form.charges.push({
        charge_type_id: null,
        description: null,
        is_amount: false,
        amount: 0,
        base: false,
      })
    },
    clickRemoveCharge(index) {
      this.form.charges.splice(index, 1)
    },
    changeCharge(index, id) {
      let record = _.find(this.charge_types, {'id': id})
      this.form.charges[index].base = record.base
    },
    clickAddAttribute() {
      this.form.attributes.push({
        attribute_type_id: null,
        description: null,
        value: null,
        start_date: null,
        end_date: null,
        duration: null,
      })
    },
    clickRemoveAttribute(index) {
      this.form.attributes.splice(index, 1)
    },
    changeAttributeType(index) {
      let attribute_type_id = this.form.attributes[index].attribute_type_id
      let attribute_type = _.find(this.attribute_types, {id: attribute_type_id})
      this.form.attributes[index].description = attribute_type.description
      this.inputAttribute(index)
    },
    inputAttribute(index) {
>>>>>>> 111c0329f5fcf368fa1ae331e2165a33c69fd15e

      let value = this.form.attributes[index].value
      let hotelAttributes = ['4003', '4004']

      this.form.attributes[index].start_date = (hotelAttributes.includes(this.form.attributes[index].attribute_type_id)) ? value : null

    },
    async changeItem() {
      this.form = _.find(this.items, {'id': this.itemId});
      this.form = Object.assign({}, this.form, {
        'index': uuid.v1(),
        'item_id': this.itemId,
        'quantity': 1,
        'discounts': [],
        'charges': [],
        'attributes': [],
        'extra_attr_name': 'Tiempo de entrega',
        'extra_attr_value': '',
        'total': 0
      });

      this.calculateTotal();

      this.$nextTick(_ => {
        if (this.form.calculate_quantity) {
          const form_total = document.getElementById('form_total');
          form_total.focus();
          form_total.select();
        } else {
          const form_unit_price = document.getElementById('form_unit_price');
          form_unit_price.focus();
          form_unit_price.select();
        }
      });
    },
    calculateQuantity() {
      if (this.form.calculate_quantity) {
        this.form.quantity = _.round((this.form.total / this.form.unit_price), 4)
      } else {
        this.calculateTotal();
      }
    },
    calculateTotal() {
      this.form.total = _.round((this.form.quantity * this.form.unit_price), 2)
    },
    async clickAddItem() {
      this.$emit('success', this.form);

      if (this.recordItem) {
        this.clickClose();
      } else {
        this.initForm();
        await this.searchRemoteItems('');
        this.focusSelectItem();
      }
      // this.close();
    },
    async reloadDataItems(item_id) {
      let params = {};
      if (this.item_search_extra_parameters !== undefined) {
        if (this.item_search_extra_parameters.only_service !== undefined) {
          params.only_service = 1;
        }
      }
      if (!item_id) {
        await this.$http.get(`/${this.resource}/table/items`, {params}).then((response) => {
          this.items = response.data
          this.form.item_id = item_id
          // if(item_id) this.changeItem()
          // this.filterItems()
        })
      } else {
        await this.$http.get(`/${this.resource}/search/item/${item_id}`).then((response) => {
          this.items = response.data.items
          this.form.item_id = item_id
          this.changeItem()
        })
      }
    },
    changePresentation() {
      let price = 0;
      this.item_unit_type = _.find(this.form.item.item_unit_types, {'id': this.form.item_unit_type_id});
      switch (this.item_unit_type.price_default) {
        case 1:
          price = this.item_unit_type.price1
          break;
        case 2:
          price = this.item_unit_type.price2
          break;
        case 3:
          price = this.item_unit_type.price3
          break;
      }
      this.form.unit_price = price;
      this.form.unit_price_value = price;
      this.form.item.unit_type_id = this.item_unit_type.unit_type_id;
    },
    selectedPrice(row) {
      let valor = 0
      switch (row.price_default) {
        case 1:
          valor = row.price1
          break
        case 2:
          valor = row.price2
          break
        case 3:
          valor = row.price3
          break
      }
      this.form.item_unit_type_id = row.id
      this.item_unit_type = row
      this.form.unit_price = valor
      this.form.unit_price_value = valor
      this.form.item.unit_type_id = row.unit_type_id
      this.calculateQuantity()
      this.getTables()
    },
    addRowLotGroup(id) {
      this.form.IdLoteSelected = id
    },
    clickLotGroup() {
      this.showDialogLots = true
    },
    focusSelectItem() {
      document.getElementById('selectItem').focus();
    },
    async getLastPriceItem() {
      this.itemLastPrice = null
      if (this.config.show_last_price_sale) {
        if (this.customerId && this.form.item_id) {
          const params = {
            'type_document': 'QUOTATION',
            'customer_id': this.customerId,
            'item_id': this.form.item_id
          }
          await this.$http.get(`/items/last-sale`, {params}).then((response) => {
            if (response.data.unit_price) {
              this.itemLastPrice = `Último precio de venta: ${response.data.unit_price}`
            }

          })
        }
      }
    },
    clickClose() {
      this.$emit('update:showDialog', false)
    },
  }
}

</script>
