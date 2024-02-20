<template>
    <div>
        <CButton color="primary" @click="toggleModal">{{name}}</CButton>
        
        <CModal :show.sync="modal" :closeOnBackdrop="false" v-if='isNew' centered :title="title" >
            <AuthorComponent :author="newAuthor" />

            <template #footer>
                <CButton @click="onAdd" color="primary" v-if="!isValidated()" >Save</CButton>
                <CButton @click="onAdd" color="primary" v-else disabled >Save</CButton>
            </template>
        </CModal>
        <CModal :show.sync="modal" :closeOnBackdrop="false" v-else centered :title="title" >
            <AuthorComponent :author="newAuthor" />

            <template #footer>
                <CButton @click="onUpdate" color="primary" v-if="!isValidated()" >Update</CButton>
                <CButton @click="onUpdate" color="primary" v-else disabled >Update</CButton>
            </template>
        </CModal>
    </div>
</template>

<script>
import AuthorComponent from './AuthorComponent.vue';
import { CButton, CModal } from '@coreui/vue';

export default {
    watch: {
        modal: function(val){
            if(val == false){
                this.reset();
            }
        }
    },
    components: {
        AuthorComponent,
        CButton,
        CModal
    },
    props: ['name','title', 'author', 'index'],
    data: function() {
        return {
            modal: false,
            newAuthor: {}
        }
    },
    mounted: function(){
        this.newAuthor = Object.assign({}, this.author);
    },
    methods: {
        isValidated(){
            if(this.newAuthor.lastname && this.newAuthor.initials){
                return !(this.newAuthor.lastname.length > 0 && this.newAuthor.initials.length > 0);
            }
            return true;
        },
        toggleModal() {
            this.modal = !this.modal
        },
        onAdd: function(){
            this.$emit('createAuthor', this.newAuthor);
            this.newAuthor = {}
            this.modal = false;
        },
        onUpdate: function(){
            this.$emit('updateAuthor', this.index, this.newAuthor);
            this.modal = false;
        },
        reset(){
            this.newAuthor = Object.assign({}, this.author)
        }
    }, 
    computed: {
        isNew: function() {
            return !this.author.id
        }
    }
}
</script>

