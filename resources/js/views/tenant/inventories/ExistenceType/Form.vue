<template>
    <el-dialog :title="title"
               :visible="showDialog"
               @close="clickClose"
               @open="handleOpen">
        <form autocomplete="off" @submit.prevent="onSubmit">
            <div class="form-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group" :class="{'has-danger': errors.id}">
                            <label class="control-label">Código</label>
                            <el-input v-model="form.id" :readonly="recordId !== null"></el-input>
                            <small class="form-control-feedback" v-if="errors.id" v-text="errors.id[0]"></small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group" :class="{'has-danger': errors.name}">
                            <label class="control-label">Nombre</label>
                            <el-input v-model="form.name"></el-input>
                            <small class="form-control-feedback" v-if="errors.name"
                                   v-text="errors.name[0]"></small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-actions text-right mt-4">
                <el-button @click.prevent="clickClose">Cancelar</el-button>
                <el-button type="primary" native-type="submit" :loading="loadingSubmit">Guardar</el-button>
            </div>
        </form>
    </el-dialog>
</template>

<script>
export default {
    name: 'InventoryExistenceTypeForm',
    props: ['showDialog', 'recordId'],
    data() {
        return {
            loadingSubmit: false,
            title: null,
            resource: 'existence_types',
            errors: {},
            form: {},
        }
    },
    methods: {
        initForm() {
            this.errors = {}
            this.form = {
                id: null,
                name: null,
            }
        },
        handleOpen() {
            this.initForm();
            this.title = (this.recordId) ? 'Editar Unidad' : 'Nueva Unidad'
            if (this.recordId) {
                this.$http.get(`/${this.resource}/record/${this.recordId}`)
                    .then(response => {
                        this.form = response.data.data
                    })
            }
        },
        onSubmit() {
            this.loadingSubmit = true
            this.$http.post(`/${this.resource}`, this.form)
                .then(response => {
                    if (response.data.success) {
                        this.$message.success(response.data.message)
                        this.$eventHub.$emit('reloadData')
                        this.clickClose()
                    } else {
                        this.$message.error(response.data.message)
                    }
                })
                .catch(error => {
                    if (error.response.status === 422) {
                        this.errors = error.response.data
                    } else {
                        console.log(error)
                    }
                })
                .then(() => {
                    this.loadingSubmit = false
                })
        },
        clickClose() {
            this.$emit('update:showDialog', false)
        },
    }
}
</script>
