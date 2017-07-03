<template>
    <section class="content">
        <div class="row">
            <div class="col-lg-8 col-lg-push-2">
                <div class="box" :class="accentType">
                    <form>
                        <div class="box-body">
                            <div class="input-block semi-transparent-white-cover" v-show="form.block"></div>
                            <div class="form-group">
                                <label class="form-control-label">Type</label>
                                <div class="switch-field">
                                    <input id="type-i" type="radio" name="switch_3" value="i" v-model="form.type"/>
                                    <label class="i" for="type-i">Income</label>
                                    <input id="type-e" type="radio" name="switch_3" value="e" v-model="form.type"/>
                                    <label class="e" for="type-e">Expense</em></label>
                                    <input id="type-x" type="radio" name="switch_3" value="x" v-model="form.type"/>
                                    <label class="x" for="type-x">Transfer</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Account</label>
                                <select class="form-control" v-model="form.account">
                                    <template v-for="i in accounts">
                                        <option :value="i.uid">{{ i.name }}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group" v-if="form.type === 'x'">
                                <label class="form-control-label">Transfer to</label>
                                <select class="form-control" v-model="form.target">
                                    <template v-for="i in accounts">
                                        <option :value="i.uid">{{ i.name }}</option>
                                    </template>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Amount</label>
                                <vue-numeric class="form-control text-right" currency="" separator=" " v-model="form.amount" :minus="false" :precision="2" name="amount"></vue-numeric>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Note</label>
                                <textarea class="form-control" v-model="form.note"></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Date</label>
                                <input type="text" class="form-control" v-model="form.date"></input>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Tags</label>
                                <input-tag class="form-control" :tags="form.tags" />
                            </div>
                            <div class="form-group">
                                <button class="btn btn-primary btn-block" @click="submit">Save</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<style lang="css">
.switch-field {
    margin-top: 6px;
    overflow: hidden;
}

.switch-title {
    margin-bottom: 6px;
}

.switch-field input {
    position: absolute !important;
    clip: rect(0, 0, 0, 0);
    height: 1px;
    width: 1px;
    border: 0;
    overflow: hidden;
}

.switch-field label {
    float: left;
}

.switch-field label {
    margin: auto;
    display: inline-block;
    width: 33.3%;
    background-color: #e4e4e4;
    color: rgba(0, 0, 0, 0.6);
    font-size: 14px;
    font-weight: normal;
    text-align: center;
    text-shadow: none;
    padding: 6px 14px;
    -webkit-transition: all 0.1s ease-in-out;
    -moz-transition:    all 0.1s ease-in-out;
    -ms-transition:     all 0.1s ease-in-out;
    -o-transition:      all 0.1s ease-in-out;
    transition:         all 0.1s ease-in-out;
}

.switch-field label:hover {
    cursor: pointer;
}

.switch-field input:checked + label.i {
    background-color: #a1e79c;
}
.switch-field input:checked + label.e {
    background-color: #fbbaba;
}
.switch-field input:checked + label.x {
    background-color: #cfafe9;
}
</style>

<script>
import VueNumeric from '../../components/Numeric.vue'
import InputTag from '../../components/InputTag.vue'
import moment from 'moment'

export default {
    name: 'transactions-form',
    props: ['transaction'],
    data: function() {
        return {
            accounts: [],
            form: {
                type: 'i',
                account: null,
                target: null,
                tags: [],
                amount: 0,
                note: "",
                date: moment().format("YYYY-MM-DD HH:mm"),
                block: false
            },
            temp: {
                account: null,
                target: null
            }
        }
    },
    components: {
        VueNumeric,
        InputTag
    },
    created: function() {
        var self = this

        self.$http.get('api/accounts').then(response => {
            self.accounts = response.body.accounts
            if(self.temp.account === null) {
                self.$set(self.form, 'account', self.accounts[0].uid)
            } else {
                self.$set(self.form, 'account', self.temp.account)
                self.$set(self.form, 'target', self.temp.target)
            }
        }, response => {
            self.accounts = []
            self.$set(self.form, 'account', null)
        })

        if(typeof self.transaction !== "undefined" && self.transaction !== null) {
            self.$set(self.form, 'block', true)
            self.$http.get('api/transaction/'+self.transaction).then(response => {
                self.$set(self.form, 'block', false)
                self.$set(self.form, 'type', response.body.transaction.type)
                self.$set(self.form, 'account', response.body.transaction.account)
                self.$set(self.temp, 'account', response.body.transaction.account)
                self.$set(self.form, 'target', response.body.transaction.target)
                self.$set(self.temp, 'target', response.body.transaction.target)
                self.$set(self.form, 'tags', response.body.transaction.tags)
                self.$set(self.form, 'amount', response.body.transaction.amount)
                self.$set(self.form, 'note', response.body.transaction.note)
                self.$set(self.form, 'date', moment(new Date(response.body.transaction.date)).format("YYYY-MM-DD HH:mm"))
            }, response => {
                self.$swal({
                    title: 'Not Found',
                    text: 'Could not find a transaction with that identifier.',
                    type: 'warning'
                }).then(function() {
                    self.$router.push('/transaction/list')
                })
            })
        }
    },
    computed: {
        accentType: function() {
            if(this.form.type == 'i') {
                return 'box-success'
            } else if(this.form.type == 'e') {
                return 'box-danger'
            } else if(this.form.type == 'x') {
                return 'box-primary'
            }
        }
    },
    methods: {
        submit: function(e) {
            e.preventDefault()
            var self = this

            if(self.form.block) {
                return false
            }

            self.$set(self.form, 'block', true)

            var data = {
                type: self.form.type,
                account: self.form.account,
                target: self.form.type === 'x' ? self.form.target : null,
                amount: self.form.amount,
                note: self.form.note,
                date: self.form.date,
                tags: self.form.tags
            }

            if(data.account === data.target) {
                self.$set(self.form, 'block', false)
                return false
            }

            if(typeof self.transaction == "undefined" || self.transaction == null) {

                self.$http.post('api/transactions', data).then(response => {
                    var transaction = response.body.transaction;

                    self.$swal({
                        title: 'Success',
                        text: 'Transaction saved.',
                        type: 'success'
                    }).then(function() {
                        self.goToList(transaction)
                    }, function() {
                        self.goToList(transaction)
                    })
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Data not saved.',
                        type: 'error'
                    })
                    self.$set(self.form, 'block', false)
                })
            } else {
                self.$http.put('api/transaction/'+self.transaction, data).then(response => {
                    var transaction = response.body.transaction

                    self.$swal({
                        title: 'Success',
                        text: 'Transaction saved.',
                        type: 'success'
                    }).then(function() {
                        self.goToList(transaction)
                    }, function() {
                        self.goToList(transaction)
                    })
                    self.$set(self.form, 'block', false)
                }, response => {
                    self.$swal({
                        title: 'Failure',
                        text: 'Data not saved.',
                        type: 'error'
                    })
                    self.$set(self.form, 'block', false)
                })
            }
        },

        goToList: function(transaction) {
            var self = this

            self.$http.get('api/transaction/'+transaction).then(response => {
                let date = new Date(response.body.transaction.date)
                self.$router.push('/transaction/list/'+date.getFullYear()+'/'+(parseInt(date.getMonth()+1)))
            }, response => {
                self.$router.push('/transaction/list')
            });
        }
    }
}
</script>
