<template>
    <div>
        <div class="form-group">
            <label for="keywords">Keywords</label>
            <input v-for="keyword in keyword.items" :key="keyword.value" type="hidden" name="keywords[]" :value="keyword.value">
            <vue-tags-input
                @focus="onFocusKeywords"
                v-model="keyword.item"
                placeholder="Start typing to add keywords"
                :tags="keyword.items"
                :autocomplete-items="keyword.autocompleteItems"
                :add-only-from-autocomplete="true"
                :autocomplete-min-length="0"
                @tags-changed="updateKeyword"
                @before-adding-tag="beforeAddingKeywordTag"
                :is-duplicate="isDuplicate"
            />
        </div>
        <div class="form-group">
            <input v-for="format in format.items" :key="format.value" type="hidden" name="formats[]" :value="format.value">
            <label for="formats">Format</label>
            <vue-tags-input
                @focus="onFocusFormats"
                v-model="format.item"
                placeholder="Start typing to add formats"
                :tags="format.items"
                :autocomplete-items="format.autocompleteItems"
                :add-only-from-autocomplete="true"
                :autocomplete-min-length="0"
                @tags-changed="updateFormat"
                @before-adding-tag="beforeAddingFormatTag"
            />
        </div>

        <div class="form-group">
            <label for="date">From Date</label>
            <div class="d-flex">
                <datepicker name="date" v-model="date" :format="date_format" :clear-button="true" clear-button-icon="fa fa-times" placeholder="select date" input-class="form-control"></datepicker>
            </div>
        </div>

        <div class="form-group">
            <label for="organisation">Organisation</label>
            <input v-for="organisation in organisation.items" :key="organisation.value" type="hidden" name="organisations[]" :value="organisation.value">
            <vue-tags-input
                @focus="onFocusOrganisations"
                v-model="organisation.item"
                placeholder="Start typing to add organsations"
                :tags="organisation.items"
                :autocomplete-items="organisation.autocompleteItems"
                :add-only-from-autocomplete="true"
                :autocomplete-min-length="0"
                @tags-changed="updateOrganisation"
                @before-adding-tag="beforeAddingOrganisationTag"
            />
        </div>

        <div class="form-group">
            <label for="authors">Author(s)</label>
            <input v-for="author in author.items" :key="author.value" type="hidden" name="authors[]" :value="author.value">
            <vue-tags-input
                @focus="onFocusAuthors"
                v-model="author.item"
                placeholder="Start typing to add authors"
                :tags="author.items"
                :autocomplete-items="author.autocompleteItems"
                :add-only-from-autocomplete="true"
                :autocomplete-min-length="0"
                @tags-changed="updateAuthor"
                @before-adding-tag="beforeAddingAuthorTag"
            />
        </div>

        <div class="form-group">
            <label for="sorting">Sorting</label>
            <select class="form-control" v-model="sort" name="sort" id="sorting">
                <option value="updated_at-desc">Date descending</option>
                <option value="updated_at-asc">Date ascending</option>
            </select>
        </div>
        <div class="form-group">
            <label class="checkbox-container">Datasets you have access to for download
                <input name="owner" v-model="owner" :checked="owner" :value="owner" type="checkbox">
                <span class="checkmark"></span>
            </label>
        </div>

    </div>
</template>

<script>
import VueTagsInput from '@johmun/vue-tags-input';
import axios from 'axios';
import Datepicker from 'vuejs-datepicker';
export default {
    components:{
        VueTagsInput,
        Datepicker
    },
    props: [
        'selected-owner',
        'selected-keywords',
        'selected-formats',
        'selected-date',
        'selected-organisations',
        'selected-authors',
        'selected-sort',
    ],
    mounted() {
            this.prepare()
        },
    data(){
        return {
            date: '',
            date_format: "d MMMM yyyy",
            sort: '',
            owner: false,
            keyword: {
                item: '',
                items: [],
                autocompleteItems: [],
            },
            format: {
                item: '',
                items: [],
                autocompleteItems: [],
            },
            organisation: {
                item: '',
                items: [],
                autocompleteItems: [],
            },
            author: {
                item: '',
                items: [],
                autocompleteItems: [],
            },
            
            debounce: null,
        };
    },
    watch: {
        'keyword.item': 'initKeywords',
        'format.item': 'initFormats',
        'organisation.item': 'initOrganisations',
        'author.item': 'initAuthors',
    },
    methods: {
        prepare(){
            this.owner = this.selectedOwner || false;
            this.keyword.items = JSON.parse(this.selectedKeywords) || [];
            this.format.items = JSON.parse(this.selectedFormats) || [];
            this.organisation.items = JSON.parse(this.selectedOrganisations) || [];
            this.author.items = JSON.parse(this.selectedAuthors) || [];
            this.date = this.selectedDate || '';
            this.sort = this.selectedSort || 'updated_at-desc';
        },
        isDuplicate(tags, tag){
            return tags.map(t => t.text).indexOf(tag.text) !== -1;
        },
        beforeAddingKeywordTag({tag, addTag}){
            if(this.isDuplicate(this.keyword.items, tag) || !this.isDuplicate(this.keyword.autocompleteItems, tag)){
                this.keyword.item = '';
                return;
            }
            addTag(tag)
        },
        beforeAddingFormatTag({tag, addTag}){
            if(this.isDuplicate(this.format.items, tag) || !this.isDuplicate(this.format.autocompleteItems, tag)){
                this.format.item = '';
                return;
            }
            addTag(tag)
        },
        beforeAddingOrganisationTag({tag, addTag}){
            if(this.isDuplicate(this.organisation.items, tag) || !this.isDuplicate(this.organisation.autocompleteItems, tag)){
                this.organisation.item = '';
                return;
            }
            addTag(tag)
        },
        beforeAddingAuthorTag({tag, addTag}){
            if(this.isDuplicate(this.author.items, tag) || !this.isDuplicate(this.author.autocompleteItems, tag)){
                this.author.item = '';
                return;
            }
            addTag(tag)
        },
        onFocusKeywords(){
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/keywords`).then(response => {
                    this.keyword.autocompleteItems = response.data.map(a => {
                        return { text: a.name, value:a.id };
                    });
                });
            }, 500);
        },
        onFocusAuthors(){
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/authors`).then(response => {
                    this.author.autocompleteItems = response.data.map(a => {
                        var names = a.lastname +', '+ a.initials;
                        return { text: names, value:a.id };
                    });
                });
            }, 500);
        },
        onFocusFormats(){
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/file_formats`).then(response => {
                    this.format.autocompleteItems = response.data.map(a => {
                        return { text: a.file_format, value:a.file_format };
                    });
                });
            }, 500);
        },
        onFocusOrganisations(){
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/organisations`).then(response => {
                    this.organisation.autocompleteItems = response.data.map(a => {
                        return { text: a.name, value:a.id };
                    });
                });
            }, 500);
        },
        initKeywords() {
            if (this.keyword.item.length < 1) return;
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/keywords?query=${this.keyword.item}&limit=10`).then(response => {
                    this.keyword.autocompleteItems = response.data.map(a => {
                        return { text: a.name, value:a.id };
                    });
                });
            }, 500);
        },
        updateKeyword(newTags) {
            this.keyword.autocompleteItems = [];
            this.keyword.items = newTags;
        },
        initFormats() {
            if (this.format.item.length < 1) return;
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/file_formats?query=${this.format.item}&limit=10`).then(response => {
                    this.format.autocompleteItems = response.data.map(a => {
                        return { text: a.file_format, value:a.file_format };
                    });
                });
            }, 500);
        },
        updateFormat(newTags) {
            this.format.autocompleteItems = [];
            this.format.items = newTags;
        },

        // DATE HERE

        initOrganisations() {
            if (this.organisation.item.length < 1) return;
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/organisations?query=${this.organisation.item}&limit=10`).then(response => {
                    this.organisation.autocompleteItems = response.data.map(a => {
                        return { text: a.name, value:a.id };
                    });
                });
            }, 500);
        },
        updateOrganisation(newTags) {
            this.organisation.autocompleteItems = [];
            this.organisation.items = newTags;
        },

        initAuthors() {
            if (this.author.item.length < 1) return;
            clearTimeout(this.debounce);
            this.debounce = setTimeout(() => {
                axios.get(`/facet/authors?query=${this.author.item}&limit=10`).then(response => {
                    this.author.autocompleteItems = response.data.map(a => {
                        var names = a.lastname +', '+ a.initials;
                        return { text: names, value:a.id };
                    });
                });
            }, 500);
        },
        updateAuthor(newTags) {
            this.author.autocompleteItems = [];
            this.author.items = newTags;
        }
    }

}
</script>
