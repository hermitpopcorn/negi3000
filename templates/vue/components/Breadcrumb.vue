<template>
    <ol class="breadcrumb">
        <template v-for="(item, index) in list">
            <li class="active" v-if="isLast(index)">{{ showName(item) }}</li>
            <li v-else><router-link :to="getPath(item)">{{ showName(item) }}</router-link></li>
        </template>
    </ol>
</template>

<script>
export default {
    props: {
        list: {
            type: Array,
            required: true,
            default: () => []
        },
        separator: String
    },
    methods: {
        isLast(index) {
            return index === this.list.length - 1
        },
        showName(item) {
            return item.meta && item.meta.label || item.name
        },
        getPath(item) {
            return item.redirect || item.path
        }
    }
}
</script>
