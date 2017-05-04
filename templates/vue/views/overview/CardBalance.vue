<template>
    <div class="card card-inverse card-primary">
        <div class="card-block pb-0">
            <h4 class="mb-0" v-html="$options.filters.currency(balance)"/>
            <p>Total Balance</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'card-balance',
    data: function() {
        return {
            balance: 0
        }
    },
    created: function() {
        var self = this
        self.$http.get('api/balance/all').then(response => {
            self.balance = response.body.balance
        }, response => {
            self.balance = "???"
        })
    }
}
</script>
