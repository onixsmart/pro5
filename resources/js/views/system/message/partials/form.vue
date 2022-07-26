<template>
    <el-dialog
        :title="titleDialog"
        width="40%"
        :visible="showNew"
        @open="onOpened"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        append-to-body
        :show-close="false"
    >
        <div class="row">
            <div class="col-4">
                <el-select
                    v-model="filter.type"
                    :disabled="loading"
                >
                    <el-option
                        key="ruc"
                        value="ruc"
                        label="Ruc"
                    ></el-option>
                    <el-option key="name" value="name" label="Nombre"></el-option>
                    <el-option key="plan" value="plan" label="Plan"></el-option>
                    <el-option key="all" value="all" label="Todos"></el-option>
                </el-select>
            </div>
            <div class="col-5 form-group">
                <el-select
                    v-model="form.client_id"
                    filterable
                    remote
                    reserve-keyword
                    placeholder="Ingrese uno más caracteres"
                    :loading="loading"
                >
                    <el-option
                    >
                    </el-option>
                </el-select>
            </div>
            <div class="col-2 form-group">
                <el-button class="btn-block" @click="loadNv" type="primary">
                    <i class="fa fa-search"></i>
                </el-button>
            </div>

            
            <div class="col-3 form-group">
                <el-date-picker
                    v-model="form.date_of_issue"
                    type="date"
                    style="width: 100%"
                    placeholder="Fecha de ejecucion"
                    value-format="yyyy-MM-dd"
                >
                </el-date-picker>
            </div>

            <div class="col-3 form-group">
                <el-date-picker
                    v-model="form.hour_of_issue"
                    type="date"
                    style="width: 100%"
                    placeholder="Hora de ejecucion"
                    value-format="HH:mm:ss"
                >
                </el-date-picker>
            </div>

            <div class="col-3 form-group">
                <label class="control-label">Recurrente</label>
                <el-checkbox v-model="form.month" >Mensual</el-checkbox>
                <el-checkbox v-model="form.year" >Anual</el-checkbox>
            </div>

            <div class="col-md-12 py-2 border-top">
                <div :class="{'has-danger': errors.message}"
                        class="form-group">
                    <label class="control-label">Ingrese Mensaje</label>
                    <el-input v-model="form.message"
                                type="textarea">
                    </el-input>
                    <small v-if="errors.message"
                            class="form-control-feedback"
                            v-text="errors.message[0]"></small>
                </div>
            </div>
        </div>

        <div class="table-responsive pt-5" v-if="notes">
            <span>Seleccione uno o más clientes para poder continuar</span>
            <table class="table table-hover table-stripe">
                <thead>
                <tr>
                    <th>Confirme cliente</th>
                    <th>Nombre</th>
                    <th>Ruc</th>
                    <th>Plan</th>
                </tr>
                </thead>
                <tbody>
                <tr >
                    <td>
                        <el-switch
                        ></el-switch>
                    </td>
                </tr>
                </tbody>
            </table>
            <div class="text-center">
                <el-button
                    type="primary"
                    :disabled="loading"
                >Guardar
                </el-button
                >
                <el-button :disabled="loading" >Cerrar</el-button>
            </div>
        </div>
    </el-dialog>
</template>

<script>
export default {
    props: [
        "showNew",
    ],
    data() {
        return {
            titleDialog:'',
            loading: false,
            url: '',
            clients: [],
            filter: {
                type: "name",
                name: null,
            },
            form: {
                client_id: null,
                message:null,
                date_of_issue:null,
                hour_of_issue:null,
                month:false,
                year:false,
                selecteds: [],
            },
            notes: [],
            errors: {},
        };
    },
    mounted() {
        this.titleDialog =  "Enviar mensaje a clientes"
    },
    methods: {

        onOpened() {
            this.filter.type = "name";
            this.filter.name = null;
            this.form.client_id = null;
        },
        onClose() {
            this.notes = [];
            this.$emit("update:showNew", false);
        },
        async submit() {
            await this.$emit("update:showNew", false);
        },

        clickCancel(item) {
            //this.lots.splice(index, 1);
            item.deleted = true;
            // this.$emit("addRowLotGroup", this.lots);
        },

        async clickCancelSubmit() {
            await this.$emit("update:showNew", false);
        },
        close() {
            this.$emit("update:showNew", false);
        }
    }
};
</script>
