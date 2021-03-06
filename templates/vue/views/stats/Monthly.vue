<template>
    <section class="content">
        <div class="input-block semi-transparent-white-cover" v-show="block"></div>
        <div>
            <div class="box box-solid">
                <div class="row">
                    <div class="col-xs-2 text-center" v-on:click="previousMonth">
                        <i class="fa fa-angle-left"></i>
                    </div>
                    <div class="col-xs-8 text-center">
                        {{ cursor.month | date('month') }} {{ cursor.year }}
                    </div>
                    <div class="col-xs-2 text-center" v-on:click="nextMonth">
                        <i class="fa fa-angle-right"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="stat callout callout-danger">
                            <small>Total expense</small>
                            <br>
                            <strong class="h4" v-html="$options.filters.currency(totalExpense)"></strong>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat callout callout-success">
                            <small>Total income</small>
                            <br>
                            <strong class="h4" v-html="$options.filters.currency(totalIncome)"></strong>
                        </div>
                    </div>
                </div>

                <hr class="mt-0">

                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                    <li>
                        <div class="row">
                            <div class="col-xs-4">
                                <span style="color: #318a4f">income</span>
                            </div>
                            <div class="col-xs-4 text-center">
                                <span style="color: #7d7b9a">balance</span>
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

                <hr class="mt-0">

                <h4>Expense by tags</h4>
                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                    <template v-for="(tag, tagName) in sortTags('e', tags)">
                        <li v-if="tag.totalExpense != 0" v-on:click="toggleTransactionsVisibility('e', tagName)" style="cursor:pointer">
                            <h6 style="color: #7d7b9a; margin: 0">#{{ tagName }}</h6>
                            <div class="bars">
                                <div class="progress progress-sm stat expense">
                                    <div class="progress-bar" role="progressbar" :style="{ width: statPercentage('e', tag.totalExpense) + '%' }" :aria-valuenow="statPercentage('e', tag.totalExpense)" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <span style="color: #a83838" v-html="$options.filters.currency(tag.totalExpense)"></span>
                            (<span v-text="statPercentage('e', tag.totalExpense)"></span>%)
                        </li>
                        <transition name="slide-fade">
                            <li v-show="expenseTransactionsVisibility.includes(tagName)">
                                <template v-for="(transaction, index) in tag.transactions">
                                    <div class="transaction transaction-mini transaction-padded-left" :class="transaction.type">
                                        <div class="transaction-head">
                                            <span class="account" v-if="transaction.type !== 'x'">{{ transaction.accountName }}</span>
                                            <span class="account" v-if="transaction.type === 'x'">
                                                <template v-if="transaction.target">
                                                    {{ transaction.accountName }} > {{ transaction.targetName }}
                                                </template>
                                                <template v-else>
                                                    {{ transaction.accountName }} > ???
                                                </template>
                                            </span>
                                            <span>
                                                {{ transaction.date.split(' ')[0] | date }} <span v-if="transaction.date.split(' ')[1] !== '00:00:00'">at {{ $options.filters.date(transaction.date).split(' ').slice(-2).join(' ') }}</span>
                                            </span>
                                        </div>
                                        <div class="transaction-body">
                                            <h1 v-html="$options.filters.currency(transaction.amount)"/>
                                            <p v-if="transaction.note">{{ transaction.note }}</p>
                                        </div>
                                    </div>
                                </template>
                            </li>
                        </transition>
                    </template>
                </ul>

                <h4>Income by tags</h4>
                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                    <template v-for="(tag, tagName) in sortTags('i', tags)">
                        <li v-if="tag.totalIncome != 0" v-on:click="toggleTransactionsVisibility('i', tagName)" style="cursor:pointer">
                            <h6 style="color: #7d7b9a; margin: 0">#{{ tagName }}</h6>
                            <div class="bars">
                                <div class="progress progress-sm stat income">
                                    <div class="progress-bar" role="progressbar" :style="{ width: statPercentage('i', tag.totalIncome) + '%' }" :aria-valuenow="statPercentage('i', tag.totalIncome)" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <span style="color: #318a4f" v-html="$options.filters.currency(tag.totalIncome)"></span>
                            (<span v-text="statPercentage('i', tag.totalIncome)"></span>%)
                        </li>
                        <transition name="slide-fade">
                            <li v-show="incomeTransactionsVisibility.includes(tagName)">
                                <template v-for="(transaction, index) in tag.transactions">
                                    <div class="transaction transaction-mini transaction-padded-left" :class="transaction.type">
                                        <div class="transaction-head">
                                            <span class="account" v-if="transaction.type !== 'x'">{{ transaction.accountName }}</span>
                                            <span class="account" v-if="transaction.type === 'x'">
                                                <template v-if="transaction.target">
                                                    {{ transaction.accountName }} > {{ transaction.targetName }}
                                                </template>
                                                <template v-else>
                                                    {{ transaction.accountName }} > ???
                                                </template>
                                            </span>
                                            <span>
                                                {{ transaction.date.split(' ')[0] | date }} <span v-if="transaction.date.split(' ')[1] !== '00:00:00'">at {{ $options.filters.date(transaction.date).split(' ').slice(-2).join(' ') }}</span>
                                            </span>
                                        </div>
                                        <div class="transaction-body">
                                            <h1 v-html="$options.filters.currency(transaction.amount)"/>
                                            <p v-if="transaction.note">{{ transaction.note }}</p>
                                        </div>
                                    </div>
                                </template>
                            </li>
                        </transition>
                    </template>
                </ul>
            </div>
        </div>
    </section>
</template>

<style>
.slide-fade-enter-active {
  transition: all .3s ease;
}
.slide-fade-leave-active {
  transition: all .15s cubic-bezier(1.0, 0.5, 0.8, 1.0);
}
.slide-fade-enter, .slide-fade-leave-to {
  transform: translateY(-10px);
  opacity: 0;
}
</style>

<script>
export default {
    name: 'overview',
    data: function() {
        return {
            block: false,
            totalIncome: "Loading...",
            totalExpense: "Loading...",
            tags: {},
            transactions: [],
            expenseTransactionsVisibility: [],
            incomeTransactionsVisibility: [],
            cursor: { }
        }
    },
    mounted: function() {
        var self = this

        let date = new Date()

        // set cursor's default date as now
        self.$set(self.cursor, 'month', date.getMonth() + 1)
        self.$set(self.cursor, 'year', date.getFullYear())

        // if actually defined
        if(typeof self.year !== "undefined" && typeof self.month !== "undefined") {
            if(parseInt(self.year) <= 9999 && parseInt(self.year) >= 1900 && parseInt(self.month) <= 12 && parseInt(self.month) >= 1) {
                self.$set(self.cursor, 'year', self.year)
                self.$set(self.cursor, 'month', self.month)
            }
        }

        self.loadData()
    },
    methods: {
        getTotalIncome: function(year, month) {
            var self = this

            self.totalIncome = "Loading..."

            self.$http.get('api/stats/income/'+year+'/'+month).then(response => {
                self.totalIncome = response.body.income
            }, response => {
                self.totalIncome = "???"
            })
        },

        getTotalExpense: function(year, month) {
            var self = this

            self.totalExpense = "Loading..."

            self.$http.get('api/stats/expense/'+year+'/'+month).then(response => {
                self.totalExpense = response.body.expense
            }, response => {
                self.totalExpense = "???"
            })
        },

        getTransactions: function(year, month) {
            var self = this

            self.block = true
            self.tags = {}
            self.transactions = []

            self.$http.get('api/transactions/all/'+year+'/'+month).then(response => {
                self.transactions = response.body.transactions
                self.organizeTags(self.transactions)
                self.block = false
            }, response => {
                self.transactions = []
                self.block = false
            })
        },

        organizeTags: function(transactions) {
            var self = this

            // group transactions in tags
            for(var i of transactions) {
                if(i.tags.length > 0) {
                    for(var v of i.tags) {
                        if(typeof self.tags[v] === "undefined") {
                            self.tags[v] = { transactions: [] }
                        }
                        self.tags[v].transactions.push(i)
                    }
                } else {
                    if(typeof self.tags["untagged"] === "undefined") {
                        self.tags["untagged"] = { transactions: [] }
                    }
                    self.tags["untagged"].transactions.push(i)
                }
            }

            // make up stats
            for(var i in self.tags) {
                self.tags[i].totalIncome = 0
                self.tags[i].totalExpense = 0
                self.tags[i].showExpenseTransactions = false
                self.tags[i].showIncomeTransactions = false

                for(var x of self.tags[i].transactions) {
                    if(x.type == 'i') {
                        self.tags[i].totalIncome += parseFloat(x.amount)
                    } else if(x.type == 'e') {
                        self.tags[i].totalExpense += parseFloat(x.amount)
                    } else if(x.type == 'x' && x.targetIsSink) {
                        self.tags[i].totalExpense += parseFloat(x.amount)
                    } else if(x.type == 'x' && x.accountIsSink && x.targetIsSink == false) {
                        self.tags[i].totalIncome += parseFloat(x.amount)
                    }
                }
            }

            return true
        },

        loadData: function() {
            this.getTotalIncome(this.cursor.year, this.cursor.month)
            this.getTotalExpense(this.cursor.year, this.cursor.month)
            this.getTransactions(this.cursor.year, this.cursor.month)
        },

        previousMonth: function() {
            var self = this

            var date = new Date(self.cursor.year + "-" + self.cursor.month)
            date.setDate(0)

            // Set the new cursor
            self.$set(self.cursor, 'month', date.getMonth() + 1)
            self.$set(self.cursor, 'year', date.getFullYear())

            self.loadData()
        },

        nextMonth: function() {
            var self = this

            var date = new Date(self.cursor.year + "-" + self.cursor.month)
            date.setMonth(date.getMonth()+1)

            self.$set(self.cursor, 'month', date.getMonth() + 1)
            self.$set(self.cursor, 'year', date.getFullYear())

            self.loadData()
        },

        sortTags: function(type, tags) {
            var sortable = {};
            for(var t in tags) {
                if(type == 'i') {
                    sortable[t] = tags[t].totalIncome
                }
                else if(type == 'e') {
                    sortable[t] = tags[t].totalExpense
                }
            }

            var keysSorted = Object.keys(sortable).sort(function(a,b){return sortable[b]-sortable[a]})

            var sorted = {}
            for(var i of keysSorted) {
                sorted[i] = tags[i]
            }

            return sorted
        },

        statPercentage: function(type, value) {
            var self = this

            var total = 0
            if(type == 'i') {
                for(var i in self.tags) {
                    total += self.tags[i].totalIncome
                }
            } else if(type == 'e') {
                for(var i in self.tags) {
                    total += self.tags[i].totalExpense
                }
            }

            let percentage = ((value / (total)) * 100)
            return Math.round(percentage * 100) / 100
        },

        toggleTransactionsVisibility: function(which, tagName) {
            var transactionsVisibility;
            if(which == 'e') {
                transactionsVisibility = this.expenseTransactionsVisibility;
            } else if(which == 'i') {
                transactionsVisibility = this.incomeTransactionsVisibility;
            }
            if(!transactionsVisibility.includes(tagName)) {
                transactionsVisibility.push(tagName)
            } else {
                transactionsVisibility.splice(transactionsVisibility.indexOf(tagName), 1)
            }
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
