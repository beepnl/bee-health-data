<template>
    <div>
        <table v-if="hasFiles" class="table">
            <thead>
                <tr>
                <th scope="col">Name</th>
                <th scope="col">Description</th>
                <th scope="col">Type</th>
                <th scope="col">Size</th>
                <th scope="col">Last Modified</th>
                <th scope="col">Version</th>
                <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <file-item v-for="(file, index) in files" :file="file" :dataset-id="datasetId" :id="index" :key="index" @delete="onDelete" @update="onUpdate" />
            </tbody>
        </table>
        <create-file @create="onCreate"/>
        <div v-if="invalidfeedback" class="invalid-feedback d-block">{{ invalidfeedback }}</div>
    </div>
</template>

<script>
import Axios from 'axios';
import FileItemComponent from './FileItemComponent.vue';
import CreateFileComponent from './CreateFileComponent.vue';

export default {
    props: ['datasetId', 'selectedJsonStringifyFiles', 'invalidfeedback'],
    components:{
        'file-item': FileItemComponent,
        'create-file': CreateFileComponent
    },
    mounted(){
        this.prepareData()
    },
    data(){
        return {
            files: []
        }
    },
    methods: {
        onCreate(file){
            this.files.push(file);
        },
        prepareData(){
            this.files = JSON.parse(this.selectedJsonStringifyFiles) || []
        },
        onUpdate(index, data){
            var vm = this
            if(data.file){
                vm.files.splice(index, 1, Object.assign({}, vm.files[index], data))
            }else{
                Axios({
                    url: `/files/${data.id}`,
                    method: 'PUT',
                    data: data
                }).then(response => {
                    const {status} = response
                    if(status === 200){
                        vm.files.splice(index, 1, Object.assign({}, vm.files[index], data))
                    }
                });
            }
        },
        onDelete(file){
            var vm = this;
            Axios({
                url: `/files/${file.id}`,
                method: "DELETE",
                data: {
                    dataset_id: this.datasetId
                },
            }).then(response => {
                vm.files.splice(vm.files.indexOf(file), 1)
            });
        }
    },
    computed: {
        hasFiles(){
            return !!this.files.length
        }
    }
}
</script>
