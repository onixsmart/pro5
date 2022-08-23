<template>
    <div>
        <div class="page-header pr-0">
            <h2><a href="/dashboard"><i class="fas fa-paper-plane"></i></a></h2>
            <ol class="breadcrumbs">
                <li class="active"><span>Mensajes</span></li>
            </ol>
            <div class="right-wrapper pull-right">

                <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i class="fa fa-plus-circle"></i> Nuevo</button>

                <!--<button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickImport()"><i class="fa fa-upload"></i> Importar</button>-->
            </div>
        </div>
        <div class="card">
            <div class="card-header bg-info">
                <h3 class="my-0">Envios Programados</h3>
            </div>
            <div class="card-body">
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>Mensaje</th>
                            <th>Ultima ejecucion</th>
                            <th>Recurrente</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(row, index) in records" :key="index">
                                <td>{{(index+1)}}</td>
                                <td>{{row.message}}</td>
                                <td>{{ row.updated }}</td>
                                <td>{{row.recurrence}}</td>
                                <td>
                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-info" @click.prevent="clickCreate(row.id)">Editar</button>

                                    <button type="button" class="btn waves-effect waves-light btn-xs btn-danger"  @click.prevent="clickDelete(row.id)">Eliminar</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
            </div>
        </div>

        <message-form
            :showNew.sync="showNew"
            :recordId="newRecord"
        ></message-form>
    </div>
</template>

<script>

    import MessageForm from './partials/form.vue'
    import {deletable} from "../../../mixins/deletable" 

    export default {
        mixins: [deletable],
        components:{MessageForm},
        data() {
            return {
                showNew: false,
                resource: 'messages',
                records: [],
                newRecord: null,
            }
        },
        created() {
            this.$eventHub.$on('reloadData', () => {
                this.getData()
            })
            this.getData()
        },
        methods: {
            getData() {
                this.$http.get(`/${this.resource}/records`)
                    .then(response => {
                        this.records = response.data.data
                    })
            },
            clickCreate(id) {
                this.newRecord=id
                this.showNew = true
                
            },
            clickActions(id) {
                
            },
            clickDelete(id) {
                this.destroy(`/${this.resource}/${id}`).then(() =>
                    this.$eventHub.$emit('reloadData')
                )
            }

        }
    }
</script>