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
                            <!-- <th>#</th> -->
                            <th>Mensaje</th>
                            <th>Ultima ejecucion</th>
                            <!-- <th>Recurrente</th> -->
                            <!-- <th>Acciones</th> -->
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            {{row.message}}
                        </tr>
                        <tr>{{ row.updated }}</tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <message-form
            :showNew.sync="showNew"
        ></message-form>
    </div>
</template>

<script>

    import MessageForm from './partials/form.vue'

    export default {
        components:{MessageForm},
        data() {
            return {
                showNew: false,
                resource: '',
                recordId: null,
                records: [],
            }
        },
        methods: {
            clickCreate() {
                this.showNew = true
            },
        }
    }
</script>