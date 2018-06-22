function message_confirm(action, attr, id, name){
    var language = $('#language_active').attr('title');
    if(language == "English"){
        if(id == "" || name == ""){
            return 'Do you want to '+ action +' this ' + attr + '?';
        } else {
            return 'Do you want to '+ action +' this ' + attr + ' has ID: ' + id + ', Name: ' + name + ' ?';
        }
    } else if(language == "Vietnamese"){
        if(id == "" || name == ""){
            return 'Bạn có muốn ' + action + ' ' + attr + ' này không?';
        } else {
            return 'Bạn có muốn ' + action + ' ' + attr + ' có ID: ' + id + ', Tên: ' + name + ' không?';
        }
    }
}
function message_confirm_add(action, attr, name) {
    var language = $('#language_active').attr('title');
    if(language == "English"){
        if(name == ""){
            return 'Do you want to '+ action +' this ' + attr + '?';
        } else {
            return 'Do you want to '+ action +' this ' + attr + ' has Name: ' + name + ' ?';
        }
    } else if(language == "Vietnamese"){
        if(name == ""){
            return 'Bạn có muốn ' + action + ' ' + attr + ' này không?';
        } else {
            return 'Bạn có muốn ' + action + ' ' + attr + ' có Tên: ' + name + ' không?';
        }
    }
}
