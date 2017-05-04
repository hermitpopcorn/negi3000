<template>
    <div class="card card-inverse card-success">
        <div class="card-block pb-0">
            <h4 class="mb-0" v-html="$options.filters.currency(totalIncome)"/>
            <p>Total income this month</p>
        </div>
    </div>
</template>

<script>
export default {
    name: 'card-expense-current-month',
    data: function() {
        return {
            totalIncome: 0
        }
    },
    created: function() {
        var self = this

        let date = new Date()
        let year = date.getFullYear()
        let month = date.getMonth() + 1
        self.$http.get('api/stats/income/'+year+'/'+month).then(response => {
            self.totalIncome = response.body.income
        }, response => {
            self.totalIncome = "???"
        })
    }
}
</script>
