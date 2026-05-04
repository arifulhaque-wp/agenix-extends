jQuery(document).ready(function($){
    var $range = $('#agenix-zoom-range');
    var $value = $('#agenix-zoom-value');

    if($range.length && $value.length){
        $range.on('input change', function(){
            $value.text($(this).val() + '×');
        });
    }
});

