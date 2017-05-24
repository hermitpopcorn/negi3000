<template>
    <section class="content">
        <div class="row">
            <div class="col-lg-8 col-lg-push-2">
                <div class="box">
                    <form>
                        <div class="box-body">
                            <div class="input-block semi-transparent-white-cover" v-show="form.block"></div>
                            <div class="form-group">
                                <label class="form-control-label">Change Name</label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-user"></i>
                                    </span>
                                    <input type="text" class="form-control" v-model="form.name"></input>
                                    <span class="input-group-btn">
                                        <button @click="saveName" class="btn btn-primary">Change</button>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-control-label">Change Password</label>
                                <div class="input-group">
                                    <span class="input-group-addon">
                                        <i class="fa fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control" v-model="form.password"></input>
                                    <span class="input-group-btn">
                                        <button @click="savePassword" class="btn btn-primary">Change</button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</template>

<script>
export default {
    name: 'profile-form',
    data: function() {
        return {
            form: {
                block: false,
                name: "",
                password: ""
            }
        }
    },
    created: function() {
        var self = this

        self.$set(self.form, 'name', window.user.name);
    },
    methods: {
        saveName: function(e) {
            e.preventDefault()
            var self = this

            if(self.form.block) {
                return false
            }

            self.$set(self.form, 'block', true)

            var data = {
                csrfToken: window.user.csrfToken,
                name: self.form.name
            }

            self.$http.post('api/profile', data).then(response => {
                window.user.name = self.form.name
                self.$swal({
                    title: 'Success',
                    text: 'Name changed.',
                    type: 'success'
                })
                self.$set(self.form, 'block', false)
            }, response => {
                self.$swal({
                    title: 'Failure',
                    text: 'New name could not be saved.',
                    type: 'error'
                })
                self.$set(self.form, 'block', false)
            })
        },

        savePassword: function(e) {
            e.preventDefault()
            var self = this

            if(self.form.block) {
                return false
            }

            self.$set(self.form, 'block', true)

            var data = {
                csrfToken: window.user.csrfToken,
                password: self.form.password
            }

            self.$http.post('api/profile', data).then(response => {
                window.user.name = self.form.name
                self.$swal({
                    title: 'Success',
                    text: "Password changed. Don't forget it!",
                    type: 'success'
                })
                self.$set(self.form, 'block', false)
            }, response => {
                self.$swal({
                    title: 'Failure',
                    text: 'New password could not be saved. Try making it a little bit more secure.',
                    type: 'error'
                })
                self.$set(self.form, 'block', false)
            })
        }
    }
}
</script>
