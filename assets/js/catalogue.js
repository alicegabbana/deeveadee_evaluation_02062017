$( document ).ready(function() {
    $('tbody tr td:first-child').each(function() {
        $(this).click(function() {
            detailDvd($(this).html());
        });
    });

    $('#genre').on('change', function() {
        dvdGenre(this.value);
    })

    $('#detail, #moyenne, #note, #saisiremarque, #remarques').empty();
});

function dvdGenre(id) {
    $.ajax({
        url: '/Welcomecontroller/dvdGenre',
        type: 'POST',
        data: {"id": id},
        success: function (data) {
            $('#detail, #moyenne, #note').empty();
            $('#catalogue').empty().append(data["tab"]);
        },
        error: function (data) {
            console.log("erreuuuuuuuuuuuuuuuuur" + data.toString());
        }
    });
}

function detailDvd(id) {
    $.ajax({
        url: '/Welcomecontroller/detaildvd',
        type: 'POST',
        data: {"id": id},
        success: function (data) {
            console.log(data);
            $('#detail, #note, #saisiremarque, #remarques').empty();
            $('#moyenne').empty().text(data['moyenne']);
            $(data['tab']).appendTo('#detail');
            if ( typeof(data['anote']) === 'undefined' ||data['anote'] === 0) {
                for (var i = 0; i < 6; i++) {
                    $('#note').append("<button onclick='note(" + i + "," + id + ")' class='btn btn-primary'>" + i + "</button>")
                }
            }
            $(data['rem']).appendTo('#remarques');
            if ($('#remarquesaisi').length === 0) {
            if ( typeof(data['aremarque']) === 'undefined' ||data['aremarque'] === 0) {
                $('#saisiremarque').append('<input type="text" id="remarquesaisi"  name="remarque"><button id="btnremarque" class="btn btn-primary">Envoyer</button>');
                $('#btnremarque').click(function(){ addremarque(id)});
            }
        }
        },
        error: function (data) {
            console.log("erreuuuuuuuuuuuuuuuuur" + data.toString());
        }
    });
}

function note(note,id) {
    $.ajax({
        url: '/Welcomecontroller/note',
        type: 'POST',
        data: {"note": note,"dvd": id},
        success: function (data) {
            console.log(data);
            $('#note').empty();
            $('#moyenne').empty().text(data['moyenne']);
        },
        error: function (data) {
            console.log("erreuuuuuuuuuuuuuuuuur" + data.toString());
        }
    });
}

function addremarque(id) {
    remarque = $('#remarquesaisi').val();
    $.ajax({
        url: '/Welcomecontroller/remarque',
        type: 'POST',
        data: {"remarque": remarque,"dvd": id},
        success: function (data) {
            $('#remarques, #saisiremarque').empty();
            $(data['rem']).appendTo('#remarques');
            $('#saisiremarque').append('<input type="text" id="remarquesaisi"  name="remarque"><button id="btnremarque" class="btn btn-primary">Envoyer</button>');
            $('#btnremarque').click(function(){ addremarque(id)});
            console.log(id);
        },
        error: function (data) {
            console.log("erreuuuuuuuuuuuuuuuuur" + data.toString());
        }
    });
}