var messageTimeout;

$("form#login-form").submit(function(e) {
    e.preventDefault();
    dom = this;

    $("input").blur();

    hideMessage();

    loginData = {
        username: $("input[name=username]").val(),
        password: $("input[name=password]").val()
    };

    $.post({
        url: 'auth/login',
        data: loginData
    })
    .done(function(response) {
        $.ajax({
            url: 'user/details'
        })
        .done(function(response) {
            welcomeUser(response.name);
        });
    })
    .fail(function(response) {
        if(typeof response.responseJSON != "undefined") {
            showMessage("Sign in failure", response.responseJSON.message, 'callout-danger');
        }
    });
});

function welcomeUser(name) {
    this.showMessage("Sign in successful", "Welcome back, <b>" + name + "</b>.", 'callout-success', function() {
        window.location.href = 'app';
    });
}

function showMessage(title, message, type, after) {
    $("#message-callout").fadeIn(500);
    $("#message-callout #message-title").text(title);
    $("#message-callout #message-content").html(message);
    $("#message-callout").removeClass("callout-success");
    $("#message-callout").removeClass("callout-danger");
    $("#message-callout").removeClass("callout-info");
    $("#message-callout").removeClass("callout-warning");
    $("#message-callout").addClass(type);

    clearTimeout(messageTimeout);
    if(typeof after == "undefined") {
        messageTimeout = setTimeout(function() {
            hideMessage();
        }, 5000);
    } else {
        messageTimeout = setTimeout(after, 3000);
    }
}

function hideMessage() {
    $("#message-callout").fadeOut(500, function() {
        $("#message-callout #message-title").text("");
        $("#message-callout #message-content").text("");
    });
}
