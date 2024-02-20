<template>
    <div>
        <small id="access_typeHelpBlock" class="form-text text-muted mb-2">
            <p>Users for the organisations you select are able to download this dataset.</p>
            <a @click.prevent="getBgoodOrganisations" href="#" class="text-primary font-weight-bold">Click here to add all B-GOOD partners</a>
        </small>
        <div class="form-group">
            <vue-tags-input
                v-model="organisation"
                placeholder="Start typing to add organsations"
                :tags="organisations"
                :autocomplete-items="autocompleteOrganisations"
                :add-only-from-autocomplete="false"
                :autocomplete-min-length="2"
                :add-from-paste="false"
                @tags-changed="update"
                @before-adding-tag="store"
                @before-deleting-tag="remove"
            />
            <div v-show="invalidfeedback" class="invalid-feedback">{{ invalidfeedback }}</div>
        </div>
    </div>
</template>

<script>
    import VueTagsInput from '@johmun/vue-tags-input';
    import axios from 'axios';
    const endpoint = '/account/organisations';

    export default {
        components:{
            VueTagsInput,
        },
        props: ['invalidfeedback', 'selectedJsonStringifyOrganisations', 'datasetId'],
        data: function(){
            return {
                organisation: '',
                organisations: [],
                autocompleteOrganisations: [],
                debounce: null,
                handlerAddTag: '',
            }
        },
        watch: {
            'organisation': 'initOrganisations',
        },
        mounted: function(){
            this.prepare()
        },
        methods: {
            prepare: function(){
                this.organisations = JSON.parse(this.selectedJsonStringifyOrganisations) || []
            },
            initOrganisations: function() {
                if (this.organisation.length < 2) return;
                
                clearTimeout(this.debounce);
                this.debounce = setTimeout(() => {
                    var vm = this;
                    axios.get(`${endpoint}?query=${this.organisation}&dataset=${vm.datasetId}&limit=10`).then(response => {
                        this.autocompleteOrganisations = response.data.map(organisation => {
                            return { text: organisation.name, value:organisation.id };
                        });
                    });
                }, 500);
            },
            getBgoodOrganisations: function(){
                var vm = this;
                axios.get(`${endpoint}?scope=bgood_partner&dataset=${vm.datasetId}`).then(response => {
                    const {data, status} = response;
                    var bgood_organisations = data.map(o => ({text:o.name, value: o.id}));

                    organisations_loop: for(var i = 0; i < vm.organisations.length; i++){
                        var organisation = vm.organisations[i];

                        for(var j = 0; j < bgood_organisations.length; j++){
                            if(bgood_organisations[j] != undefined && organisation.value === bgood_organisations[j].value){
                                bgood_organisations.splice(j, 1)
                                continue organisations_loop;
                            }
                        }
                    }
                    bgood_organisations.map(bgood_organisation => {
                        vm.store({tag: bgood_organisation});
                    });
                    vm.organisations = vm.organisations.concat(bgood_organisations)
                });
            },
            update(newOrganisations) {
                this.autocompleteOrganisations = [];
                this.organisations = newOrganisations;
            },
            store({tag, addTag}){
                var vm = this;
                if(tag.text.length){
                    axios({
                        url:`/authorization`,
                        method: 'POST',
                        data: {
                            dataset_id: vm.datasetId,
                            organisation_id: tag.value
                        }
                    }).then(response => {                        
                        if(addTag !== undefined){
                            addTag(tag)
                        }
                    })
                }
            },
            remove({tag, deleteTag, index}){
                var vm = this;
                axios({
                    url:`/authorization`,
                    method: 'POST',
                    data: {
                        _method: 'delete',
                        dataset_id: vm.datasetId,
                        organisation_id: tag.value
                    }
                }).then(response => {
                    const {status} = response
                    if(status === 204){
                        deleteTag(tag)
                    }
                })
            }
        },
    }
</script>
