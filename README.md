# negi3000

A simple multi-user income/expense recording app.
The app is on a precompiled bundle.js file because I'm hosting the app on a cheap hosting plan so I'm making the user load the entire thing and then cache it into their browser.

## Compiling
```sh
composer install
bower install
npm install
gulp
```

## Components
Uses these third party components:
[kevinongko/vue-numeric](https://github.com/kevinongko/vue-numeric/), [matiastucci/vue-input-tag](https://github.com/matiastucci/vue-input-tag), and a lot of [CoreUI](http://coreui.io/) vue components.
