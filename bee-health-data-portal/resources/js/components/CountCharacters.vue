<template>
    <span>{{string}}</span>
</template>

<script>
export default {
    props: {
        max: {
            type: Number,
            required: true,
            default: 0
        },
        value: {
            type: [String],
            required: true
        }
    },
    computed: {
        maxLength(){
            if(this.max < this.value.length){
                return this.value.length;
            }
            return this.max;
        },
        string(){
            return `${this.value.length} / ${this.maxLength}`
        }
    }
}
</script>
