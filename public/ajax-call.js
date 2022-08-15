setInterval(hideShowContainers, 3000);

function hideShowContainers() {
    $('#flashMessage').hide();
}

function commentSong(id){
    let ruta = Routing.generate('commentSong');
    let content = $('#comentArea').val();
    $.ajax({
        type: 'GET',
        url: ruta,
        data: ({id: id, content: content}),
        async: true,
        dataType: 'json',
        success: function (data) {
            let author = data['commentAuthor'];
            let content = data['commentContent'];
            $('#commentShow').append('<li class="media" id="commentShow">\n' +
                '                <a href="#" class="pull-left">\n' +
                '                    <img src="https://bootdey.com/img/Content/user_2.jpg" alt="" class="img-circle">\n' +
                '                </a>\n' +
                '                <div class="media-body">\n' +
                '                    <strong class="text-success">'+author+'</strong>\n' +
                '                    <p>\n' +
                '                        '+content+'\n' +
                '                    </p>\n' +
                '                </div>\n' +
                '            </li>');
        },
        error: function (data) {
            console.log('Error' + data);
        }
    })
}

$(document).on('keyup', '#searchSong', function () {
    let ruta = Routing.generate('searchSong');
    let title = $('#searchSong').val();
    $.ajax({
        type: 'GET',
        url: ruta,
        data: ({title: title}),
        async: true,
        dataType: 'json',
        success: function (data) {

            for (i = 0; i < data['allSongs'].length; i++) {
                if($('#searchSong').val().length === 0){
                    $('#song-' + data['allSongs'][i].id).show();
                }else{
                    $('#song-' + data['allSongs'][i].id).hide();
                }
            }

            console.log(data['song'].length);
            for(i = 0; i<data['song'].length; i++){
                console.log(data['song'][i].id);
                $('#song-' + data['song'][i].id).show();
            }
            console.log('Acierto ');
        },
        error: function (data) {
            console.log('Error' + data);
        }
    })
})


function MeGusta(id){
    let ruta = Routing.generate('Likes');
    $.ajax({
        type: 'POST',
        url: ruta,
        data: ({id: id}),
        async: true,
        dataType: "json",
        success: function (data){
            console.log(data);
            $('#countLikes').html(data['likes'] +' likes');
            $('#meGusta').show();
            $('#teHaGustado').hide();
        }
    });
}


function DeleteSong(id){
    let ruta2 = Routing.generate('Delete');
    Swal.fire({
        title: 'Do you want to delete the song?',
        showCancelButton: true,
        confirmButtonText: 'Delete',
        confirmButtonColor: 'red',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                type: 'POST',
                url: ruta2,
                data: ({id: id}),
                async: true,
                dataType: "json",
                success: function (data) {
                    console.log(data);
                    Swal.fire({
                        title: 'Success',
                        text: 'Song deleted successfully!!!',
                        icon: 'success',
                        confirmButtonText: 'Cool'
                    })
                },
                error: function (data){
                    console.log(data);
                    Swal.fire({
                        title: 'Success!!!',
                        text: 'Song deleted successfully!!!',
                        icon: 'success',
                    })
                    $('#song-'+ id).remove();
                }
            })
        }
    })

}


function DeleteSongShow(id){
    let ruta2 = Routing.generate('Delete');
    $.ajax({
        type: 'POST',
        url: ruta2,
        data: ({id: id}),
        async: true,
        dataType: "json",
        success: function (data) {
            console.log(data);
        },
        error: function (){
            Swal.fire({
                title: 'Success!!!',
                text: 'Song deleted successfully!!!',
                icon: 'success',
                confirmButtonText: 'Cool'
            })
            window.location.href = 'https://localhost:8000/show';
        }
    })
}



