var messageTimeout;

$("form#login-form").submit(function(e) {
    e.preventDefault();
    dom = this;

    $(dom).find(".input-block").show();
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
        $(dom).find(".input-block").hide();
        if(typeof response.responseJSON != "undefined") {
            showMessage("Sign in failure", response.responseJSON.message, 'card-danger');
        }
    });
});

function welcomeUser(name) {
    this.showMessage("Sign in successful", "Welcome back, <b>" + name + "</b>.", 'card-primary', function() {
        window.location.href = 'app';
    });
}

function showMessage(title, message, type, after) {
    $(".message-card").fadeIn(500);
    $(".message-card #message-title").text(title);
    $(".message-card #message-content").html(message);
    $(".message-card").removeClass("card-primary");
    $(".message-card").removeClass("card-danger");
    $(".message-card").removeClass("card-info");
    $(".message-card").removeClass("card-warning");
    $(".message-card").addClass(type);

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
    $(".message-card").fadeOut(500);
    $(".message-card #message-title").text("");
    $(".message-card #message-content").text("");
}
