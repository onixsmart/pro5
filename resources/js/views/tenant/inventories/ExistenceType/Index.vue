<template>
    <div class="card">
        <div class="card-header bg-info">
            <h3 class="my-0">Listado de tipo de existencias</h3>
        </div>
        <div class="card-body">
<!--            <div class="row">-->
<!--                <div class="col">-->
<!--                    <button type="button" class="btn btn-custom btn-sm  mt-2 mr-2" @click.prevent="clickCreate()"><i-->
<!--                        class="fa fa-plus-circle"></i> Nuevo-->
<!--                    </button>-->
<!--                </div>-->
<!--            </div>-->
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Código</th>
                        <th>Nombre</th>
<!--                        <th class="text-right">Acciones</th>-->
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(row, index) in records" :key="index">
                        <td>{{ index + 1 }}</td>
                        <td>{{ row.id }}</td>
                        <td>{{ row.name }}</td>
<!--                        <td class="text-right">-->
<!--                            <button type="button" class="btn waves-effect waves-light btn-xs btn-info"-->
<!--                                    @click.prevent="clickCreate(row.id)">Editar-->
<!--                            </button>-->
<!--                        </td>-->
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <inventory-existence-type-form :showDialog.sync="showDialog"
                                       :recordId="recordId"></inventory-existence-type-form>
    </div>
</template>

<script>

import InventoryExistenceTypeForm from "./Form";

export default {
    name: 'InventoryExistenceTypeIndex',
    components: {InventoryExistenceTypeForm},
    data() {
        return {
            showDialog: false,
            resource: 'existence_types',
            recordId: null,
            records: [],
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
        clickCreate(recordId = null) {
            this.recordId = recordId
            this.showDialog = true
        },
    }
}
</script>
