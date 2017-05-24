<template>
    <section class="content">
        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat callout callout-info">
                            <small>Total Balance</small>
                            <br>
                            <strong class="h4" v-html="$options.filters.currency(totalBalance)"></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat callout callout-danger">
                            <small>Total expense this month</small>
                            <br>
                            <strong class="h4" v-html="$options.filters.currency(totalExpense)"></strong>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat callout callout-success">
                            <small>Total income this month</small>
                            <br>
                            <strong class="h4" v-html="$options.filters.currency(totalIncome)"></strong>
                        </div>
                    </div>
                </div>
            </div>

            <hr class="no-margin-top">

            <div class="box-body">
                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                    <li>
                        <div class="row">
                            <div class="col-xs-4">
                                <span style="color: #318a4f">income</span>
                            </div>
                            <div class="col-xs-4 text-center">
                                <span style="color: #7d7b9a">balance this month</span>
                            </div>
                            <div class="col-xs-4 text-right">
                                <span style="color: #a83838">expense</span>
                            </div>
                        </div>
                        <div class="bars">
                            <div class="progress progress-lg income-v-expense">
                                <div class="progress-bar" role="progressbar" :style="{ width: incomePercentage + '%' }" :aria-valuenow="incomePercentage" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xs-4">
                                <span style="color: #318a4f">{{ incomePercentage }}%</span>
                            </div>
                            <div class="col-xs-4 text-center">
                                <span style="color: #7d7b9a" v-html="$options.filters.currency(totalIncome - totalExpense)"></span>
                            </div>
                            <div class="col-xs-4 text-right">
                                <span style="color: #a83838">{{ expensePercentage }}%</span>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>

            <hr class="no-margin">

            <template v-for="account in accounts">
                <div class="info-box" :class="{ 'bg-green': !account.isSink, 'bg-yellow': account.isSink }">
                    <span class="info-box-icon">
                        <i class="fa fa-book" v-if="account.balance !== null"></i>
                        <i class="fa fa-credit-card" v-if="account.balance === null"></i>
                    </span>

                    <div class="info-box-content">
                        <span class="info-box-text" v-if="account.balance !== null">regular account</span>
                        <span class="info-box-text" v-if="account.balance === null">money sink</span>
                        <span class="info-box-number">{{ account.name }}</span>

                        <p v-if="account.balance !== null">Remaining balance: <strong v-html="$options.filters.currency(account.balance)"></strong></p>
                    </div>
                </div>
            </template>
        </div>
    </section>
</template>

<style>
.info-box {
    margin-bottom: 0;
}
</style>

<script>
export default {
    name: 'overview',
    data: function() {
        return {
            totalBalance: "Loading...",
            totalIncome: "Loading...",
            totalExpense: "Loading...",
            accounts: []
        }
    },
    mounted: function() {
        var self = this

        this.getTotalBalance()
        this.getAccountsBalance()
        this.getTotalIncome()
        this.getTotalExpense()
    },
    methods: {
        getTotalBalance: function() {
            var self = this
            self.$http.get('api/balance/all').then(response => {
                self.totalBalance = response.body.balance
            }, response => {
                self.totalBalance = "???"
            });
        },

        getAccountsBalance: function() {
            var self = this
            self.$http.get('api/accounts').then(response => {
                self.accounts = response.body.accounts
                for(let i = 0; i < self.accounts.length; i++) {
                    if(!self.accounts[i].isSink) {
                        self.$set(self.accounts[i], 'balance', 0)
                        self.$http.get('api/balance/'+self.accounts[i].uid).then(response => {
                            self.accounts[i].balance = response.body.balance
                            self.$set(self.accounts[i], 'balance', response.body.balance)
                        }, response => {
                            self.$set(self.accounts[i], 'balance', 0)
                        })
                    } else {
                        self.$set(self.accounts[i], 'balance', null)
                    }
                }
            }, response => {
                self.accounts = []
            })
        },

        getTotalIncome: function() {
            var self = this

            let date = new Date()
            let year = date.getFullYear()
            let month = date.getMonth() + 1
            self.$http.get('api/stats/income/'+year+'/'+month).then(response => {
                self.totalIncome = response.body.income
            }, response => {
                self.totalIncome = "???"
            })
        },

        getTotalExpense: function() {
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
    },
    computed: {
        incomePercentage: function() {
            let income = !isNaN(this.totalIncome) ? this.totalIncome : 0
            let expense = !isNaN(this.totalExpense) ? this.totalExpense : 0
            let percentage = ((income / (income + expense)) * 100)
            return Math.round(percentage * 100) / 100
        },
        expensePercentage: function() {
            let income = this.incomePercentage
            let percentage = (100 - income)
            return Math.round(percentage * 100) / 100
        }
    }
}
</script>
