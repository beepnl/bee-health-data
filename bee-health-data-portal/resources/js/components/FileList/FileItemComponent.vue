<template>
    <tr>
        <td scope="col">{{file.filename}}</td>
        <td scope="col">{{file.description}}</td>
        <td scope="col">{{file.file_format}}</td>
        <td scope="col">{{prettySize}}</td>
        <td scope="col">{{file.lastModified}}</td>
        <td scope="col">{{file.version}}</td>
        <td scope="col">
            <div v-if="!loading">
                <div class="d-flex justify-content-end">
                    <edit-file :file="file" @update="onUpdate" />
                    <CButton color="danger" class="mr-2" @click.prevent="onRemove">Remove</CButton>
                    <edit-file :file="file" :is-new-version="true"  @update="onUpdate" />
                </div>
            </div>
            <div v-else>
                <CProgress class="mb-3">
                    <CProgressBar :value="progress" animated :label="`${progress}%`"></CProgressBar>
                </CProgress>
            </div>
        </td>
    </tr>
</template>

<script>
import Axios from 'axios';
import EditFileComponent from './EditFileComponent.vue'
import { CProgress, CProgressBar, CButton } from '@coreui/vue';
import filesize from 'filesize'

const blobSize = 6000000;
export default {
    props: ['file', 'id', 'datasetId'],
    components: {
        'edit-file': EditFileComponent,
        CButton,
        CProgress,
        CProgressBar
    },
    data(){
        return {
            uploaded: 0,
            loading: false,
            blobs: [],
            uploadId: null,
            partNumber: 1,
            filename: '',
        }
    },
    watch: {
        blobs(n, o){
            if(n.length > 0){
                this.upload();
            }
        }
    },
    updated(){
        if(this.file.file && !this.blobs.length){
           this.onCreate()
       }
    },
    created(){
       if(this.file.file){
           this.onCreate()
       }
    },
    methods: {
        onCreate(){
            this.loading = true;
            this.createBlobs();
        },
        createBlobs(){
            var numberOfBlobs = Math.ceil(this.file.file.size / blobSize );
            for(var i = 0; i < numberOfBlobs; i++){
                this.blobs.push(
                    this.file.file.slice(i * blobSize, Math.min(i * blobSize + blobSize, this.file.file.size), this.file.file.type)
                )
            }
        },
        upload(){
            var vm = this;
            Axios({
                url: '/files',
                method: "POST",
                data: this.formData,
                headers: {
                    'Content-Type': 'application/octet-stream'
                },
            }).then(response => {
                let blob = this.blobs.shift();
                vm.uploaded += blob.size;
                const {completed, id, version, uploadId, partNumber} = response.data;
                if(completed){
                    vm.file.version = version;
                    delete vm.file.file;
                    vm.loading = false;
                    vm.uploaded = 0;
                    vm.uploadId = null
                }else{
                    vm.uploadId = uploadId
                }
                vm.file.id = id;
                vm.partNumber = partNumber + 1;
            });
        },
        onUpdate(obj){
            this.$emit('update', this.id, obj)
        },
        onRemove(){
            if(confirm("Are you sure?")){
                this.$emit('delete', this.file);
            }
        }
    },
    computed:{
        isLastBlob(){
            return this.blobs.length === 1;
        },
        formData(){
            var formData = new FormData;
            formData.set('file', this.blobs[0], `${this.file.file.name}.part`)
            formData.set('is_last_blob', this.isLastBlob);
            formData.set('dataset_id', this.datasetId);
            formData.set('uploadId', this.uploadId);
            formData.set('partNumber', this.partNumber);
            formData.set('filename', this.file.filename);

            if(this.file.id){
                formData.set('id', this.file.id);
            }
            if(this.isLastBlob){
                formData.set('description', this.file.description);
            }
            return formData;
        },
        progress(){
            let _uploaded = this.uploaded
            if(_uploaded == 0){
                _uploaded = blobSize;
            }
            var progress =  Math.floor((_uploaded * 100) / this.file.file.size);
            return parseInt((progress / 100) * 100);
        },
        prettySize(){
            return filesize(this.file.size)
        }
    }
}
</script>
