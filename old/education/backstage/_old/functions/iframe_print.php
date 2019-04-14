<script>
var printDivBtnHolder = false;
var printDivActive = false;

function printDiv(A, divId, doPrint) {

	if( printDivActive && !doPrint ) {
		return false;
	}
	var Loader = $('<img src="images/indicator.gif" />');
    if( !doPrint )
    {
    	printDivActive = true;
    	printDivBtnHolder = $(A).html();

    	$(A).empty().append( Loader );
    }
	window.frames["print_frame"].document.body.innerHTML = '';
	$("link").each( function(){
		if( this.rel == 'stylesheet' )
		{
			window.frames["print_frame"].document.body.innerHTML += getOuterHtml(this);
		}
	});
	$("style").each( function(){
		window.frames["print_frame"].document.body.innerHTML += getOuterHtml(this);
	});
    if( doPrint )
    {
    	printDivActive = false;
    	$(A).html( printDivBtnHolder );

    	window.frames["print_frame"].document.body.innerHTML += '<style type="text/css">\
    	#printDivBox td, #printDivBox th {\
    	    font-size: 12px;\
    	}\
    	</style>';
    	
    	window.frames["print_frame"].document.body.innerHTML += '<div id="printDivBox">'+getOuterHtml('#'+divId)+'</div>';
        window.frames["print_frame"].window.focus();
        window.frames["print_frame"].window.print();
    }
    else
    {
    	setTimeout(function(){
    		printDiv(A, divId, true)
        }, 1000);
    }
}
function getOuterHtml(Element) {
	return $('<div>').append($( Element ).clone()).html();
}
</script>
<iframe name=print_frame width=0 height=0 frameborder=0 src="about:blank"></iframe>