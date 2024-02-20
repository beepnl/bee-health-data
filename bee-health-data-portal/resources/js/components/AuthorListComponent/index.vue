<template>
    <div class="form-group">
        <label for="authors">Author(s)*</label>
        <div class="row mb-2" v-if="authors.length">
            <div class="col-3 font-bold">Last name</div>
            <div class="col-3 font-bold">Initials</div>
            <div class="col-3 font-bold">Organisation</div>
            <div class="col-3 font-bold">Actions</div>
        </div>

        <div class="row mb-2" v-for="(author, index) in authors"  :key="author.id">
            <div class="col-3">{{author.lastname}}</div>
            <div class="col-3">{{author.initials}}</div>
            <div class="col-2">{{author.organisation}}</div>
            <div class="col-4 d-flex justify-content-end">
                <CButton color="secondary" v-if="author.allow_up && author.allow_order_controll"  @click.prevent="up(author, index)">up</CButton>
                <CButton color="secondary" v-if='author.allow_down && author.allow_order_controll' class=" ml-1" @click.prevent="down(author, index)">down</CButton>
                <AuthorModalComponent v-on:updateAuthor='onUpdateAuthor' :index="index" class=" ml-1" name="Edit" title="Edit author" :author="author" /> 
                <CButton color="danger" class="ml-1" @click.prevent="deleteAuthor(author)">Remove</CButton>
            </div>
        </div>
        <div v-if="invalidfeedback" class="invalid-feedback d-block">{{ invalidfeedback }}</div>
        <AuthorModalComponent v-on:createAuthor='onCreateAuthor' name="Add author" title="Add author" :author='newAuthor' class='text-left mt-2'></AuthorModalComponent> 
    </div>
</template>

<script>
import AuthorModalComponent from './AuthorModalComponent.vue';
import sortBy from 'lodash/sortBy';
import maxBy from 'lodash/maxBy';
import axios from 'axios';
import { CButton } from '@coreui/vue';


export default {
    components: {
        AuthorModalComponent,
        CButton
    },
    props: ['invalidfeedback','selectedJsonStringifyAuthors', 'datasetId'],
    data: function () {
        return {
            newAuthor: {},
            authors: [],
        }
    },
    mounted: function() {
        this.prepareAuthors();
    },
    watch: {
        authors: function(){
            var lengthAuthors = this.authors.length;
            return this.authors.map(function(author){
                author.allow_up = author.order > 1;
                author.allow_down = author.order < lengthAuthors;
                author.allow_order_controll = author.allow_up || author.allow_down;

                return author;
            });
        }
    },
    beforeUpdate: function() {
        this.authors = sortBy(this.authors, function(author){return author.order});
    },
    computed: {
        nextOrder: function(){
            var latestAuthor = maxBy(this.authors, function(author) {return author.order}) || {};
            return (latestAuthor.order || 0) + 1;
        },
    },
    methods: {
        prepareAuthors: function(){
            this.authors = JSON.parse(this.selectedJsonStringifyAuthors) || []
        },
        onCreateAuthor: function(newAuthor){
            var vm = this;
            axios({
                url: `/authors`,
                method: 'POST',
                data: Object.assign(newAuthor, {dataset_id: vm.datasetId})
            }).then(response => {
                const {status, data} = response;
                if(status === 201){
                    vm.authors.push(Object.assign({}, newAuthor, {id:data.id, order: data.order, is_new:true}));
                }
            })
        },
        deleteAuthor: function(author){
            var vm = this;
            var index = this.authors.indexOf(author);
            
            if(confirm("Are you sure?")){
                axios({
                    url: `/authors/${author.id}`,
                    method: 'POST',
                    data: {
                        _method: 'delete',
                        dataset_id: vm.datasetId
                    }
                }).then(response => {
                    const {status} = response
                    if(status === 204){
                        vm.$delete(vm.authors, index);
                    }
                });
            }
        },
        onUpdateAuthor: function(index, author){
            var vm = this
            axios({
                url: `/authors/${author.id}`,
                method: 'POST',
                data: {
                    _method: 'PUT',
                    dataset_id: vm.datasetId,
                    lastname: author.lastname,
                    initials: author.initials,
                    organisation: author.organisation
                }   
            }).then(response => {
                vm.$set(vm.authors, index, Object.assign({}, author));
            })
        },
        up: function(author, index){
            var vm = this
            axios({
                url: `/authors/${author.id}`,
                method: 'POST',
                data: {
                    _method: 'PUT',
                    order: author.order - 1,
                    dataset_id: vm.datasetId
                }   
            }).then(response => {
                var prevIndex = index - 1;
                var prevAuthor = vm.authors[prevIndex];
                vm.$set(vm.authors, index, Object.assign({}, author, {order: author.order - 1}));
                vm.$set(vm.authors, prevIndex, Object.assign({}, prevAuthor, {order: author.order}));
            })

        },
        down: function(author, index){
            var vm = this
            axios({
                url: `/authors/${author.id}`,
                method: 'POST',
                data: {
                    _method: 'PUT',
                    order: author.order + 1,
                    dataset_id: vm.datasetId
                }   
            }).then(response => {
                vm.$set(vm.authors, index, Object.assign({}, author, {order: author.order + 1}));
                var nextIndex = index + 1;
                var nextAuthor = vm.authors[nextIndex];
                vm.$set(vm.authors, nextIndex, Object.assign({}, nextAuthor, {order: author.order}));
            })

        }
        
    }
}
</script>
