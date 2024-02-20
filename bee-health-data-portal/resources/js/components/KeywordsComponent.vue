<template>
    <div class="form-group">
        <label for="keywords">Keywords*</label>
        <vue-tags-input
            style="max-width: 100%;"
            v-model="tag"
            placeholder="Start typing to add keywords"
            :tags="tags"
            :autocomplete-items="autocompleteItems"
            :add-only-from-autocomplete="false"
            :autocomplete-min-length="2"
            @tags-changed="update"
            @before-adding-tag="store"
            @before-deleting-tag="remove"
        />
        <div v-if="invalidfeedback" class="invalid-feedback d-block">{{ invalidfeedback }}</div>
        <small id="keywordsHelpBlock" class="form-text text-muted">
            Start typing to find keywords or add your own. Press enter to confirm the choice of keyword. Preferably add multiple keywords.
        </small>
    </div>
</template>

<script>
    import VueTagsInput from '@johmun/vue-tags-input';
    import axios from 'axios';

    const url = `/account/keywords`;
    export default {
        components:{
            VueTagsInput,
        },
        props: ['invalidfeedback', 'selectedJsonStringifyKeywords', 'datasetId'],
        mounted() {
            this.prepare()
        },
        data(){
            return {
                tag: '',
                tags: [],
                autocompleteItems: [],
                debounce: null,
            };
        },
        watch: {
            'tag': 'initItems',
        },
        methods: {
            prepare: function(){
                this.tags = JSON.parse(this.selectedJsonStringifyKeywords) || []
            },
            
            update(newTags) {
                this.autocompleteItems = [];
                this.tags = newTags;
            },
            initItems() {
                if (this.tag.length < 2) return;
                
                clearTimeout(this.debounce);
                this.debounce = setTimeout(() => {
                    axios.get(`${url}?query=${this.tag}&limit=10`).then(response => {
                        this.autocompleteItems = response.data.map(a => {
                            return { text: a.name, value:a.id };
                        });
                    });
                }, 500);
            },
            isDuplicate(tags, tag){
                return tags.map(t => t.text).indexOf(tag.text) !== -1;
            },
            store({tag, addTag}){
                if(!tag.text.length){
                    return;
                }

                if(this.isDuplicate(this.tags, tag) ){
                    this.tag = '';
                    return;
                }

                axios({
                    url:`${url}`,
                    method: 'POST',
                    data: {
                        dataset_id: this.datasetId,
                        name: tag.text
                    }
                }).then(response => {
                    const {id} = response.data;
                    tag.value = id
                    addTag(tag)
                })
            },
            remove({tag, index, deleteTag}){
                axios({
                        url:`${url}/${tag.value}`,
                        method: 'POST',
                        data: {
                            _method: 'delete',
                            dataset_id: this.datasetId,
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
