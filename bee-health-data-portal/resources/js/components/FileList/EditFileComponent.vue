<template>
    <div>
        <div v-if="isNewVersion">
            <CButton color="secondary" class="mr-2" @click.prevent="toggleModal">New version</CButton>
            <CModal :show.sync="modal" :closeOnBackdrop="false" title="Update new version file">
                <div class="text-center">
                    <label :for="'browse_file_' + file.id" class="text-primary cursor-pointer">Browse for file on your system</label>
                </div>
                <input type="file" @change="handleFile" :id="'browse_file_' + file.id">
                
                <template #footer>
                    <CButton @click="update" color="primary" v-if="isNewVersionValidation()" >New version</CButton>
                    <CButton @click="update" color="primary" v-else  disabled>New version</CButton>
                    <CButton @click="reset" color="secondary">Cancel</CButton>
                </template>

            </CModal>
        </div>
        <div v-else>
            <CButton color="primary" class="mr-2" @click.prevent="toggleModal">Edit</CButton>
            <CModal :show.sync="modal" :closeOnBackdrop="false" title="Update file">

                <div class="form-group">
                    <label for="name">Filename*</label>
                    <input type="text" maxlength="140" v-model="filename" required class="form-control" aria-describedby="filenameHelpBlock">
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
                        {{file.file_format}}
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
                    <CButton @click="update" color="primary" v-if="isEditFormValidation()" >Update</CButton>
                    <CButton @click="update" color="primary" v-else disabled >Update</CButton>
                    <CButton @click="reset" color="secondary">Cancel</CButton>
                </template>

            </CModal>
        </div>
        
    </div>
</template>

<script>
import filesize from 'filesize'
import CountCharacters from '../CountCharacters.vue';
import { CModal, CButton } from '@coreui/vue';
const pathParse = require("path-parse");
export default {
    watch: {
        modal: function(val){
            if(val == false){
                if(this.event_update){
                    this.reset(true);
                }else{
                    this.reset();
                }
            }
        }
    },
    components:{
        'count-characters': CountCharacters,
        CModal,
        CButton
    },
    props: [
        'file', 'isNewVersion'
    ],
    mounted(){
        this.filename = this.file.filename;
        this.description = this.file.description;
    },
    data: function(){
        return {
            newVersionFile: false,
            event_update: false,
            modal: false,
            filename: '',
            description: '',
            file_format: '',
            size: '',
        }
    },
    methods: {
        toggleModal(){
            this.modal = !this.modal;
        },
        update(){
            this.event_update = true;
            var data = {
                id: this.file.id,
                filename: this.filename,
                description: this.description,
            };
            if(this.isNewVersion){
                data = Object.assign(data, {
                    file: this.newVersionFile,
                    file_format: this.file_format,
                    size: this.size,
                });
            }
            this.$emit('update', data);
            this.modal = false;
        },
        handleFile(e){
            var files = e.target.files || e.dataTransfer.files;
            if (!files.length){
                return;
            }
            this.newVersionFile = files[0];
            this.file_format =  this.newVersionFile.name.split('.').pop() || '';
            this.size = this.newVersionFile.size;
        },
        isNewVersionValidation(){
            return this.newVersionFile instanceof File;
        },
        isEditFormValidation(){
            return this.filename.length > 0 && this.description.length > 0;
        },
        reset(triger_event_update = false){
            if(triger_event_update){
                this.event_update = false;
            }else{
                this.filename = this.file.filename;
                this.description = this.file.description;
            }
           
            this.newVersionFile = false;
            this.file_format = '';
            this.size = '';
            this.modal = false;
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
