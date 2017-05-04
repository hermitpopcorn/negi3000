<template>
    <div class="card card-inverse card-danger">
        <div class="card-block pb-0">
            <h4 class="mb-0" v-html="$options.filters.currency(totalExpense)"/>
            <p>Total expense this month</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'card-expense-current-month',
    data: function() {
        return {
            totalExpense: 0
        }
    },
    created: function() {
        var self = this

        let date = new Date()
        let year = date.getFullYear()
        let month = date.getMonth() + 1
        self.$http.get('api/stats/expense/'+year+'/'+month).then(response => {
            self.totalExpense = response.body.expense
        }, response => {
            self.totalExpense = "???"
        })
    }
}
</script>
