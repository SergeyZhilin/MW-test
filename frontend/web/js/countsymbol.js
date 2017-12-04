jQuery(document).ready(function($){
    // добавим элемент
    $('.word-count').after('<span class="chars-count"></span>');

    var mceEditor,
        timeout;

    function timeout_update(){
        clearTimeout(timeout);
        timeout = setTimeout( update, 10 );
    }
    function update() {
        var text;
        if( ! mceEditor || mceEditor.isHidden() )
            text = jQuery('#content').val();
        else
            text = mceEditor.getContent({ format:'text' });
        var re = new RegExp(String.fromCharCode(160), "g");
        if( text ){
            text = text.replace(/\r?\n|\r/g, ' ') // удалим переносы строк
                .replace(/<[^>]+>/g, '') // удалим теги
                .replace(re, '')
                .replace(/\s+/g, '')
                .replace(/\[[^\]]+\]/g, ''); // удалим шотркоды

            jQuery('.chars-count').text( text.length );
        }
        checkCount = text.replace(/[ ]+/g, '').length;
        if (checkCount > 200) {
            alert('count symbol > 200')
        }

    }

    // событие нажатия в редакторе tinymce
    jQuery(document).on('tinymce-editor-init', function( event, editor ) {
        if( editor.id !== 'content' ) return; // это не наш редактор

        mceEditor = editor;

        editor.on('nodechange keyup', timeout_update );
    });

    // событие нажатия в текстовом редакторе
    jQuery('#content').on('input keyup', timeout_update );

    update(); // первый запуск

});