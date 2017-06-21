var App = {
    user: null,

    getUser: function() {
        if(this.user == null) {
            return Vue.http.get('api/user/details').then(response => {
                this.user = response.body;
                return response.body;
            }, response => {
                this.user = false;
                return false;
            });
        } else {
            return this.user;
        }
    }
};

if(typeof document.getElementsByTagName('base')[0] != "undefined") {
    Vue.http.options.root = document.getElementsByTagName('base')[0].href;
}
