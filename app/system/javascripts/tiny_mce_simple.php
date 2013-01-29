<?php
//Generate the root URL
	$URL = explode("wp-content", $_SERVER['REQUEST_URI']);

//Output this file as a text file
	header("Content-type: text/javascript");
?>
$(document).ready(function() {
    $('textarea').tinymce({
        mode : "textareas",
        theme : "advanced",
        skin : "o2k7",
        skin_variant : "silver",
        plugins : "inlinepopups,spellchecker,tabfocus,AtD,autosave,autolink",
        
        atd_button_url : "<?php echo $URL[0]; ?>wp-content/plugins/book-exchange/app/system/tiny_mce/plugins/AtD/atdbuttontr.gif",
        atd_rpc_url : "<?php echo $URL[0]; ?>wp-content/plugins/book-exchange/app/system/tiny_mce/plugins/AtD/server/proxy.php?url=",
        atd_rpc_id : "jmyppg6c5k5ajtqcra7u4eql4l864mps48auuqliy3cccqrb6b",
        atd_css_url : "<?php echo $URL[0]; ?>wp-content/plugins/book-exchange/app/system/tiny_mce/plugins/AtD/css/content.css",
        atd_show_types : "Bias Language,Cliches,Complex Expression,Diacritical Marks,Double Negatives,Hidden Verbs,Jargon Language,Passive voice,Phrases to Avoid,Redundant Expression",
        atd_ignore_strings : "AtD,rsmudge",
        theme_advanced_buttons1_add : "AtD",
        atd_ignore_enable : "true",
        tab_focus : ':prev,:next',
        
        theme_advanced_buttons1 : "bold,italic,underline,strikethrough,forecolor,backcolor,|,justifyleft,justifycenter,justifyright, justifyfull,|,bullist,numlist,|,undo,redo,link,unlink",
        theme_advanced_buttons2 : "",
        theme_advanced_buttons3 : "",
        theme_advanced_toolbar_location : "top",
        theme_advanced_toolbar_align : "left",
        theme_advanced_statusbar_location : "bottom",
        theme_advanced_resizing : true,
        autosave_ask_before_unload : true,
        editor_deselector : "noEditorSimple",
        gecko_spellcheck : false
    });
});
