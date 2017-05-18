export default {
    currency: function(value, currency, decimals) {
        if(isNaN(value)) {
            return value;
        }

        var digitsRE = /(\d{3})(?=\d)/g
        value = parseFloat(value)
        if (!isFinite(value) || (!value && value !== 0)) return ''
        currency = currency != null ? currency : ''
        decimals = decimals != null ? decimals : 2
        var stringified = Math.abs(value).toFixed(decimals)
        var _int = decimals
        ? stringified.slice(0, -1 - decimals)
        : stringified
        var i = _int.length % 3
        var head = i > 0
        ? (_int.slice(0, i) + (_int.length > 3 ? ' ' : ''))
        : ''
        var _float = decimals
        ? stringified.slice(-1 - decimals)
        : ''
        var sign = value < 0 ? '-' : ''
        return sign + currency + head +
        _int.slice(i).replace(digitsRE, '$1 ') +
        "<span class='decimal'>" + _float + "</span>"
    },

    date: function(value, modifier) {
        var months = [
            "Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ]
        var longMonths = [
            "January", "February", "March", "April", "May", "June",
            "July", "August", "September", "October", "November", "December"
        ]

        if(typeof modifier !== "undefined") {
            if(modifier == "month") {
                return longMonths[value - 1]
            }
        }

        var split = value.split(' ')
        var date = split[0]
        var time = split[1]
        // date
        date = date.split('-')
        if(date[2].length == 2 && date[2][0] == '0') {
            date[2] = date[2][1]
        }
        var dateString = date[2] + " " + months[date[1] - 1] + " " + date[0]
        // time
        if(typeof time != "undefined") {
            time = time.split(':')
            let suffix = (parseInt(time[0]) >= 12)? 'PM' : 'AM';
            let hour = (parseInt(time[0]) > 12)? parseInt(time[0]) - 12 : time[0];
                hour = (hour == 0 || hour == '00')? 12 : hour;

            dateString = dateString + " " + hour + ":" + time[1] + " " + suffix
        }

        return dateString
    }
}
