<template>
    <div>
        <!-- Create new file -->
        <CButton color="gray-dark" @click="toggleModal">Add file(s)</CButton>
        <CModal id="bv-modal-create-file" :closeOnBackdrop="false" :show.sync="modalIsOpen" title="File upload">

                <div class="text-center">
                    <label for="browse_file" class="text-primary cursor-pointer">Browse for file on your system</label>
                </div>
                <input class="d-none" type="file" @change="handleFile" id="browse_file">

                <div class="form-group">
                    <label for="name">Filename*</label>
                    <input id="filename" type="text" maxlength="140" v-model="filename" required class="form-control" aria-describedby="filenameHelpBlock">
                    <small id="filenameHelpBlock" class="form-text text-muted">
                        <count-characters :max="140" :value="filename"/>
                    </small>
                </div>

                <div class="form-group">
                    <label for="name">Description*</label>
                    <input id="description" type="text" maxlength="140" v-model="description" required class="form-control" aria-describedby="descriptionHelpBlock">
                    <small id="descriptionHelpBlock" class="form-text text-muted">
                        <count-characters :max="140" :value="description"/>
                    </small>
                </div>

                <div class="form-group">
                    <label for="name">Type</label>
                    <small class="form-text text-muted">
                        {{file_format}}
                    </small>
                </div>

                <div class="form-group">
                    <label for="name">Size</label>
                    <small class="form-text text-muted">
                        {{prettySize}}
                    </small>
                </div>

                <div class="form-group">
                    <label for="name">Last modified</label>
                    <small class="form-text text-muted">
                        {{prettyLastModified}}
                    </small>
                </div>

            <template #footer>
                <CButton @click="onAdd" color="primary" v-if="isValidated()" >Add to dataset</CButton>
                <CButton @click="onAdd" color="primary" v-else disabled >Add to dataset</CButton>
                <CButton @click="reset" color="secondary">Cancel</CButton>
            </template>
        </CModal>
    </div>
</template>
<script>
const pathParse = require("path-parse");
import filesize from 'filesize'
import CountCharacters from '../CountCharacters.vue';
import { CModal, CButton } from '@coreui/vue';

export default {
    components:{
        'count-characters': CountCharacters,
        CButton,
        CModal
    },
    props: ['datasetId'],
    data(){
        return {
            file: '',
            filename: '',
            description: '',
            file_format: '',
            size: '',
            modalIsOpen: false,
        }
    },
    watch:{
        modalIsOpen: function(val){
            if(val == false){
                this.reset()
            }
        }
    },
    methods: {
        toggleModal(){
            this.modalIsOpen = !this.modalIsOpen;
        },
        handleFile(e){
            var files = e.target.files || e.dataTransfer.files;
            if (!files.length){
                return;
            }
            this.file = files[0];

            this.filename = this.filename ? this.filename : pathParse(this.file.name).name;
            this.file_format =  this.file.name.split('.').pop() || '';
            this.size = this.file.size;
            this.lastModified = this.file.lastModified; 
        },
        onAdd(){
            this.$emit('create', {
                id: null,
                file: this.file,
                filename: this.filename,
                description: this.description,
                file_format: this.file_format,
                size: this.size,
                lastModified: new Date(this.lastModified).toShortFormat()
            });
            this.reset();
        },
        reset(){
            this.file = '';
            this.file_format = '';
            this.filename = '';
            this.description = '';
            this.modalIsOpen = false;
        },
        isValidated(){
            return this.filename.length > 0 && this.description.length > 0 && (this.file instanceof File)
        }
    },
    computed: {
        prettyLastModified(){
            if(!this.file.lastModified){
                return;
            }
            return new Date(this.file.lastModified).toShortFormat();
        },
        prettySize(){
            return filesize(this.file.size || 0);
        }
    }
    
}
</script>
