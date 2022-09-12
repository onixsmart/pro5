<template>
    <el-dialog
        :title="titleDialog"
        width="40%"
        :visible="showNew"
        @open="create"
        :close-on-click-modal="false"
        :close-on-press-escape="false"
        append-to-body
        :show-close="false"
    >
        <div class="row">
            <div class="col-md-4" v-if="!recordId">
                <el-select
                    v-model="search.column"
                    @click="getDataClients"
                    @change="getDataClients"
                    :disabled="loading"
                >
                    <el-option
                        v-for="(label, key) in columns"
                        :key="key"
                        :value="key"
                        :label="label"
                    ></el-option>
                </el-select>
            </div>
            <div class="col-md-5 form-group" v-if="!recordId">
                <el-select
                    v-model="form.client_id"
                    filterable
                    remote
                    reserve-keyword
                    placeholder="Ingrese uno más caracteres"
                    :remote-method="findClients"
                    :loading="loading"
                >
                    <el-option
                        v-for="item in clients"
                        :key="item.id"
                        :label="item.name"
                        :value="item.id"
                    >
                    </el-option>
                </el-select>
            </div>
            <div class="col-md-2 form-group" v-if="!recordId">
                <el-button class="btn-block" @click="getDataClients" type="primary">
                    <i class="fa fa-search"></i>
                </el-button>
            </div>

            <template >
                <div class="col-md-3 form-group">
                    <el-date-picker
                        v-model="form.date_start"
                        type="date"
                        style="width: 100%"
                        placeholder="Fecha de ejecucion"
                        value-format="yyyy-MM-dd"
                    >
                    </el-date-picker>
                </div>

                <div class="col-md-3 form-group">
                    <el-time-picker
                        v-model="form.time_start"
                        type="date"
                        style="width: 100%"
                        placeholder="Hora de ejecucion"
                        value-format="HH:mm:ss"
                    >
                    </el-time-picker>
                </div>
                <div class="col-md-3 form-group" v-if="!recordId">
                    <label class="control-label">Recurrente</label>
                </div>
                <div class="col-md-2 form-group" v-if="!recordId">
                    <el-radio v-model="form.recurrence" label="1">Mensual</el-radio>
                    <el-radio v-model="form.recurrence" label="0">Anual</el-radio>
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
            </template>
            
        </div>

        <div class="table-responsive pt-5" v-if="!recordId">
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
                <tr v-for="record in records" :key="record.id">
                    <td>
                        <el-switch
                            v-model="record.selected"
                            @change="selectOption"
                        ></el-switch>
                    </td>
                    <td>{{ record.name }}</td>
                    <td>{{ record.number }}</td>
                    <td>{{ record.plan }}</td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="text-center">
            <el-button
                type="primary"
                :disabled="loading"
                @click.prevent="submit()"
            >Guardar
            </el-button
            >
            <el-button @click.prevent="close()" >Cerrar</el-button>
        </div>
    </el-dialog>
</template>

<script>
import queryString from "query-string";

export default {
    props: [
        "showNew",
        "recordId"
    ],
    data() {
        return {
            resource:'messages',
            titleDialog:'',
            loading: false,
            url: '',
            clients: [],
            search: {
                column: null,
                value: null
            },
            form: {
                client_id: null,
                message:null,
                date_start:null,
                time_start:null,
                recurrence:null,
                selecteds: [],
            },
            records: [],
            errors: {},
            columns:[],
            selecteds:[],
            getRecord:false,
        };
    },
    async mounted() {
        //this.titleDialog =  "Enviar mensaje a clientes"
        await this.$http
        .get(`/${this.resource}/columns`)
        .then(response => {
            this.columns = response.data;
            this.search.column = _.head(Object.keys(this.columns));
        });
    },
    methods: {
        create() {
            this.form.client_id = null;
            this.titleDialog = (this.recordId)? 'Editar mensaje a clientes':'Nuevo mensaje a clientes'
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`).then(response => {
                    this.form = response.data.data[0]
                })
            }
        },
        async findClients(query) {
            this.getRecord=false;
            this.search.value = query;
            this.getDataClients();
            await selectOption();
        },
        async getDataClients() {
            await this.$http.get(`/messages/filter?${this.getQueryParameters()}`)
            .then(response => {
                if (this.getRecord) {
                    this.records = response.data.data;
                    
                } else {
                    this.clients = response.data.data;
                    this.getRecord = true;
                }
                
            })
            .catch(error => {
                console.error(error)
            })

        },
        getQueryParameters() {
            return queryString.stringify({
                ...this.search
            });
        },
        onOpened() {
            
        },
        close() {
            this.records = [];
            this.$emit("update:showNew", false);
        },
        async submit() {
            this.form.client_id=!this.recordId?null:this.recordId;
            this.$http.post(`${this.resource}`, this.form)
                    .then(response => {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.close()
                    })
                    .catch(error => {
                        if (error.response.status === 422) {
                            this.errors = error.response.data 
                        } else {
                            console.log(error.response)
                        }
                    })
                    .then(() => {
                        this.loading = false
                    })
            /* await this.$emit("update:showNew", false); */
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
            this.getRecord=false;
            this.$emit("update:showNew", false);
        },
        async selectOption() {
            //this.form.selecteds = [];
            await this.records.map((d) => {
                //console.log(d)
                if (!d.selected) {
                    this.form.selecteds.splice(this.records.indexOf(d.id),1);
                }else{
                    this.form.selecteds.push(d.id);
                }
            });

            //console.log(this.form.selecteds)
        },
    }
};
</script>
