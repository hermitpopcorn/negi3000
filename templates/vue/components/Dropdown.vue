<template>
    <div class="dropdown">
        <button class="btn dropdown-toggle" v-bind:class="type" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-on:click="toggle">
            <slot name="button">Dropdown</slot>
        </button>
        <slot name="dropdown-menu" v-if="opened"></slot>
    </div>
</template>

<script>

export default {
    name: 'dropdown',
    props: ['type'],
    data: function() {
        return {
            opened: false
        }
    },
    methods: {
        toggle: function() {
            if (this.opened) {
                return this.hide()
            }
            return this.show()
        },
        show: function() {
            this.opened = true;
            setTimeout(() => document.addEventListener('click',this.hide), 0);
        },
        hide: function() {
            this.opened = false;
            document.removeEventListener('click',this.hide);
        }
    }
}
</script>
