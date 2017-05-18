<template>
    <div class="animated fadeIn">
        <div class="row">
            <div class="col-12">
                <div class="input-block semi-transparent-white-cover" v-show="block"></div>
                <div class="card">
                    <div class="card-block row p-0">
                        <div class="col-2 text-center pt-2 pb-2" v-on:click="previousMonth">
                            <i class="icon-arrow-left"></i>
                        </div>
                        <div class="col-8 text-center pt-2 pb-2">{{ cursor.month | date('month') }} {{ cursor.year }}</div>
                        <div class="col-2 text-center pt-2 pb-2" v-on:click="nextMonth">
                            <i class="icon-arrow-right"></i>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-block">
                        <div class="row">
                            <div class="col-12">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="callout callout-danger">
                                            <small class="text-muted">Total expense</small>
                                            <br>
                                            <strong class="h4" v-html="$options.filters.currency(totalExpense)"></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="callout callout-success">
                                            <small class="text-muted">Total income</small>
                                            <br>
                                            <strong class="h4" v-html="$options.filters.currency(totalIncome)"></strong>
                                        </div>
                                    </div>
                                </div>

                                <hr class="mt-0">
                                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                                    <li>
                                        <div class="row">
                                            <div class="col-4">
                                                <span style="color: #318a4f">income</span>
                                            </div>
                                            <div class="col-4 text-center">
                                                <span style="color: #7d7b9a">balance</span>
                                            </div>
                                            <div class="col-4 text-right">
                                                <span style="color: #a83838">expense</span>
                                            </div>
                                        </div>
                                        <div class="bars">
                                            <div class="progress progress-lg income-v-expense">
                                                <div class="progress-bar" role="progressbar" :style="{ width: incomePercentage + '%' }" :aria-valuenow="incomePercentage" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4">
                                                <span style="color: #318a4f">{{ incomePercentage }}%</span>
                                            </div>
                                            <div class="col-4 text-center">
                                                <span style="color: #7d7b9a" v-html="$options.filters.currency(totalIncome - totalExpense)"></span>
                                            </div>
                                            <div class="col-4 text-right">
                                                <span style="color: #a83838">{{ expensePercentage }}%</span>
                                            </div>
                                        </div>
                                    </li>
                                </ul>

                                <hr class="mt-0">

                                <h4>Expense by tags</h4>
                                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                                    <template v-for="(tag, tagName) in sortTags('e', tags)">
                                        <li v-if="tag.totalExpense != 0">
                                            <div class="row">
                                                <div class="col-12">
                                                    <h6 style="color: #7d7b9a; margin: 0">#{{ tagName }}</h6>
                                                </div>
                                            </div>
                                            <div class="bars">
                                                <div class="progress progress-sm stat expense">
                                                    <div class="progress-bar" role="progressbar" :style="{ width: statPercentage('i', tag.totalExpense) + '%' }" :aria-valuenow="statPercentage('i', tag.totalExpense)" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <span style="color: #a83838" v-html="$options.filters.currency(tag.totalExpense)"></span>
                                                    (<span v-text="statPercentage('i', tag.totalExpense)"></span>%)
                                                </div>
                                            </div>
                                        </li>
                                    </template>
                                </ul>

                                <h4>Income by tags</h4>
                                <ul style="list-style: none; padding: 0; margin: 0; margin-bottom: 1em">
                                    <template v-for="(tag, tagName) in sortTags('i', tags)">
                                        <li v-if="tag.totalIncome != 0">
                                            <div class="row">
                                                <div class="col-12">
                                                    <h6 style="color: #7d7b9a; margin: 0">#{{ tagName }}</h6>
                                                </div>
                                            </div>
                                            <div class="bars">
                                                <div class="progress progress-sm stat income">
                                                    <div class="progress-bar" role="progressbar" :style="{ width: statPercentage('i', tag.totalIncome) + '%' }" :aria-valuenow="statPercentage('i', tag.totalIncome)" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-12">
                                                    <span style="color: #318a4f" v-html="$options.filters.currency(tag.totalIncome)"></span>
                                                    (<span v-text="statPercentage('i', tag.totalIncome)"></span>%)
                                                </div>
                                            </div>
                                        </li>
                                    </template>
                                </ul>

                                <hr class="mt-0">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

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

                for(var x of self.tags[i].transactions) {
                    if(x.accountIsSink == false) {
                        if(x.type == 'i') {
                            self.tags[i].totalIncome += parseFloat(x.amount)
                        } else if(x.type == 'e') {
                            self.tags[i].totalExpense += parseFloat(x.amount)
                        } else if(x.type == 'x' && x.targetIsSink) {
                            self.tags[i].totalExpense += parseFloat(x.amount)
                        }
                    } else {
                        if(x.type == 'x' && x.targetIsSink == false) {
                            self.tags[i].totalIncome += parseFloat(x.amount)
                        }
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
