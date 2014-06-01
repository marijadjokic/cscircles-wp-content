$ = jQuery;


function __t(s) {
    if (translationArray == null) return s;
    return translationArray[s];
}

function toggleVisibility(id) {
    $('#' + id).toggle();
}

function stylePybox(id, modeCharacter) {
    B = $("#pybox"+id);
    B.removeClass("modeNeutral modeCorrect modeInternalError");
    if (modeCharacter == 'E')
	B.addClass("modeInternalError");
    else if (modeCharacter == 'Y')
	B.addClass("modeCorrect");
    else // typically 'y' for correct in facultative, or 'N' for wrong
	B.addClass("modeNeutral");
}

function testingSI(fac, tni) {
    if (fac) return __t("Sakriti polje za unos");
    return __t("Vratiti se na ocenjivanje");
}
function gradingSI(fac, tni) { 
    if (fac) return __t("Unesite ulazne podatke");
    if (tni == "Y") return __t("Unesite izveštaje za testiranje");
    return __t("Unesite test ulaze");
}

function pbInputSwitch(id, tni) {
    var fac = $('#pybox'+id).hasClass('facultative');
    $('#pyinput'+id).toggle();
    if ($('#inputInUse'+id).val()=='N') {
	setCommandLabel(id, 'submit', __t("Run test"));
	setCommandLabel(id, 'switch', testingSI(fac, tni));
	$('#inputInUse'+id).val('Y');
    }
    else {
	setCommandLabel(id, 'submit', __t("Izvršiti program"));
	setCommandLabel(id, 'switch', gradingSI(fac, tni));
	$('#inputInUse'+id).val('N');
    }
}

function pbSetText(id, txt) { //should not be called on scrambles.
    w = $('#pybox'+id+" .pyboxCodewrap");
    if (w.hasClass('CM')) 
	cmed[id].setValue(txt);
    else 
	$('#usercode'+id).val(txt);		
}

function pbGetText(id) {
    if ($('#pybox'+id).hasClass('scramble') || $('#pybox'+id).hasClass('multiscramble')) 
	return $('#pyscramble'+id)
	.sortable()
	.children()
	.map(function(){return $(this).text();})
	.get()
	.join('\n');
    w = $('#pybox'+id+" .pyboxCodewrap");
    if (w.hasClass('CM')) 
	return cmed[id].getValue();
    else 
	return $('#usercode'+id).val();		
}	

function getID(event) {
    elt = event.target;
    id = elt.id.replace(/[^0-9]/g, '');
    return id;
}

function pbFormSubmit(event) {
    var id = getID(event);
    if ($('#pybox'+id).hasClass('multiscramble')) {
	pbMSFormSubmit(id);
	event.preventDefault(); 
	return false;
    }
    var values = {};
    var timeoutMS = DEFAULTTIMEOUTMS;
    $.each($('#pbform'+id).serializeArray(), function(i, field) {
	if (field.name == 'timeout')
	    timeoutMS = field.value;
	else
	    values[field.name] = field.value;
    });
    values['usercode'+id]=pbGetText(id);

    $('#submit'+id).attr('disabled', true);

  $('#pybox'+id+' .bumpit').removeClass('bumpit');
    
    $('#pbresults'+id).html("<p>"+__t("Izvršavanje...")+'</p>');
    $.ajax({
	type: "POST",
	url: SUBMITURL,
	data: $.param(values), 
	timeout: timeoutMS,
	success: function(data) {
 	    $('#pbresults'+id).html(data.substring(1));
	    stylePybox(id, data.charAt(0));
            //$("#pybox"+id).css("background-color", returnColours[data.charAt(0)]);
	    if (data.charAt(0)=="Y")
		happyFace(id);
	    $('#submit'+id).attr('disabled', false);
        },
        error: function(xhr, textStatus, thrownError) {
	    $('#submit'+id).attr('disabled', false);
	    stylePybox(id, 'E');
	    if (textStatus == "timeout") {
		alert(__t('Vreme je isteklo!') + timeoutMS);
	    }
	   
 	    $('#pbresults'+id).html(__t("Nije moguće program jer je komunikaicja sa serverom onemogućena. AJAX informacija: ")+xhr.statusText+" "+xhr.status+" "+thrownError);
        }
    });
    event.preventDefault(); 
}

function happyFace(id) {
    $("#pybox" + id + " .pycheck").attr({
	'title':__t('Izvršili ste ovaj problem najmanje jednom.'),
	'src':FILESURL+'checked.png'
    });
}

function setCompleted(name) {
    if (name != 'NULL') {
	$.ajax({
	    type: "POST",
	    url: SETCOMPLETEDURL,
	    data: {"problem":name},
	    error: function() {alert(__t("UPOZORENJE: nemoguća komunikacija sa serverom. Nije moguće postaviti status 'završen' za ovaj problem."))}
	});
    }
}

function helpClick(id) {
    $("#pybox"+id+" .helpOuter").toggle();
    $("#pybox"+id+" .helpOuter textarea").resizable({handles: "s"});
}

function sendMessage(id, slug) {
    recipient = $("#pybox"+id+" .recipient").val();
    message = $("#pybox"+id+" .helpInner textarea").val();
    code = pbGetText(id);
    if (recipient==0) {
	alert(__t('Molimo Vas odaberite važećeg primaoca za poruku. Ako radite sa mentorom ili prijateljem, odaberite ga kao mentora na Vašem profilu (vidljivo na korsinčkom meniju, u gornjem desnom uglu strane).'));
    }
    else if (message.replace('\s', '')=='') {
	alert(__t('Molimo Vas unesite poruku sa sadržajem.'));
    }
    else if (code.replace('\s', '')=='') {
	alert(__t('Polje za unos je prazno. Ono treba da sadrži deo Vašeg najboljeg rešenja do sada.'));
    }
    else {
	$.ajax({
	    type: "POST",
	    url : MESSAGEURL,
	    data: {"source":1,"slug":slug,"recipient":recipient,"message":message,"code":code},
	    error: function() {alert(__t("Nemoguće je izvršiti proces slanja poruke. Možda je izgubljena konekcija sa internetom."));}
	});
	alert(__t("Vaša poruka je poslata."));
	helpClick(id);
    }
}

function mailReply(id, slug) {
    var noreplyval = false;
    if (typeof noreply !== 'undefined')
	noreplyval = noreply.checked;
    var thedata = {"source":2,"id":id,"slug":slug,
		   "message":$('#mailform textarea').val(),
		   "noreply":noreplyval};

    var r = null;
    $('#mailform .recipient').each(function(i, item) {r = $(item);});
    if (r != null) {
	if (r.val()==0) {
	    alert(__t('Molimo Vas odaberite važećeg primaoca za poruku. Ako radite sa mentorom ili prijateljem, odaberite ga kao mentora na Vašem profilu (vidljivo na korsinčkom meniju, u gornjem desnom uglu strane).'));
	    return;
	}
	thedata['recipient'] = r.val();
    }
    $.ajax({
	type: "POST",
	url : MESSAGEURL,
	data: thedata,
	error: function() {alert(__t("Nemoguće je izvršiti proces slanja poruke. Možda je izgubljena konekcija sa internetom."));},
	success: function(data) {if (data == '#') location.reload(true); else window.location = MAILURL + '?who='+id+"&what="+slug+"&which="+data+"#m";}
    });
    alert(noreplyval?__t("Označite sve poruke ovog studenta u vezi ovog probelema kao rešene.")
	  :__t("Vaša poruka je poslata."));
}

// three types of short answer question: short answer, multiple choice, scramble
// all are client-side exercises not requiring execution on the server
function pbNoncodeShowResults(id, correct, answer) { 

    name = $('#pybox'+id).find('input[name="slug"]').val();
    stylePybox(id, correct?"Y":"N");
    $('#pybox'+id+' .pbresults').html((!correct)?__t("Netačno, pokušajte ponovo."):$('#pybox'+id+' .epilogue').html());
//added by Marija Djokic    
if (name != 'NULL') {
	$.ajax({
	    type: "POST",
	    url: "http://147.91.205.71/wordpress/wp-content/plugins/pybox/action-submission-complete.php",
	    data: {"problem":name, "usercode":answer, "result": correct},
	    error: function() {alert(__t("UPOZORENJE: nemoguća komunikacija sa serverom. Nije moguće postaviti status 'završen' za ovaj problem."))}
	});
    }
//
    if (correct) {
	happyFace(id);
	setCompleted(name);
    }
}

function pbShortCheck(id) {
    ans = document.getElementById("pyShortAnswer"+id).value;    
    JQtype = $('#pybox'+id).find('input[name="type"]');
    thetype = JQtype.get(0).value;
    JQcorrect = $('#pybox'+id).find('input[name="correct"]');
    lecorrect = JQcorrect.get(0).value;
    if (thetype=="number")
    { ok = parseFloat(ans) == parseFloat(lecorrect); }
    else
    { ok = ans == lecorrect;}
//modifided by Marija Djokic
    pbNoncodeShowResults(id, ok, ans);
//
}

function pbMultiCheck(id) {
    ok = (document.getElementById("pyselect"+id).value == 'r');
    pbNoncodeShowResults(id, ok, document.getElementById("pyselect"+id).options[document.getElementById("pyselect"+id).selectedIndex].text);
}

function pbMultiscrambleCheck(id) { //NB: does not yet work if there are multiple identical lines
    lines = $('#pybox'+id+' li.pyscramble');
    var values={};
    //pbGetText(id);  
    //alert(values['usercode'+id]);
    values['usercode'+id]=pbGetText(id);
    len = lines.size();
    name = $('#pybox'+id+' input[name="name"]').val();
    x = lines.map(function(){x = $(this).attr('id'); return x.substr(x.lastIndexOf('_')+1);}).get().join();
    y = '0';
    for (i=1; i<len; i++) y = y + ',' + i;
//modifided by Marija Djokic
    pbNoncodeShowResults(id, x == y,values['usercode'+id]);
//
}

// end of client-side-evaluated exercise types.

function pbCodeMirror(id) {
    var cmwrap = $('#pybox'+id+" .pyboxCodewrap");
    var ro = cmwrap.hasClass("RO");
    cmwrap.addClass('autoCMsize');
    cmwrap.addClass('CM');

    cmed[id] = CodeMirror.fromTextArea
    (document.getElementById("usercode"+id), //"cmta"
     {
       mode: 
       {name: "python", 
	version: 3, 
	singleLineStringErrors: false
       }, 
       lineNumbers: true, 
       indentUnit: 3,
       tabSize: 3,
       matchBrackets: true,
       readOnly: ro,
       extraKeys: {
         Tab: function(cm) {
           var spaces = Array(cm.getOption("indentUnit") + 1).join(" ");
           cm.replaceSelection(spaces, "end", "+input");
         }
       }
     }
    );
    
    var cs = cmwrap.find('.CodeMirror-scroll');
    var cg = cmwrap.find('.CodeMirror-gutter');
    

    $(function() {
	cmwrap.resizable({
	    handles:'s', 
	    minHeight: 50,
	    resize: function(){
		cmwrap.removeClass('autoCMsize');
		cs.height(cmwrap.height());
		cg.css('min-height', cmwrap.height()+'px');
              cs.find(".CodeMirror-gutters").height("100%");
	    }}
			);
    });
    
}
 
function pbUndoCodeMirror(id) {
    cmed[id].toTextArea();
    w = $('#pybox'+id+" .pyboxCodewrap");
    w.removeClass('autoCMsize');
    w.removeClass('CM');
}

function pbToggleCodeMirror(id) {
    w = $('#pybox'+id+" .pyboxCodewrap");
    if (w.hasClass('CM')) {
	$('#toggleCM'+id).val(__t('Rich editor'));
	pbUndoCodeMirror(id);
    }
    else {
	$('#toggleCM'+id).val(__t('Simple editor'));
	pbCodeMirror(id);
    }
}

function pbConsoleCopy(id) {
   var code = pbGetText(id);
   var ecode =encodeURIComponent(code);
   var xurl = CONSOLEURL+"?consolecode="+ecode;
   //window.open(xurl);
//added by Marija Djokic
   popup(ecode);
}

function popup(ecodep)
{
$.ajax("http://147.91.205.71/wordpress/wp-content/plugins/pybox/action-get-console.php?consolecode="+ecodep,
{success: function(reply) {
AnythingPopup_OpenForm("AnythingPopup_BoxContainer1","AnythingPopup_BoxContainerBody1","AnythingPopup_BoxContainerFooter1","700","400");
$("#AnythingPopup_BoxContainerBody1").html(reply);}});
}
//

function pbVisualize(id, tni) {
    
    var form = document.createElement("form");
    form.setAttribute("method", "post");
    form.setAttribute("target", "_blank");

    usercode = pbGetText(id);

    params = {};

    if ($('#inputInUse'+id).val()=='Y') {
	extrainput = $('#pybox'+id+' textarea.pyboxInput').val();
	if (tni == 'Y') {
	    usercode += '\n# '+__t('kraj glavnog programa')+'\n\n# '+__t('početak testiranja')+'\n' + extrainput;
	}
	else {
	    params["raw_input"] = extrainput;
	}
    }

    params["code"] =  usercode;

  var bbquery = '';
  var numparams=0;
  for (var key in params) {
    if (params.hasOwnProperty(key)) {
      if (numparams==0) bbquery += '#';
      else bbquery += '&';
      numparams++;
      bbquery += encodeURIComponent(key);
      bbquery += '=';
      bbquery += encodeURIComponent(params[key]);
    }
  }

    form.setAttribute("action", VISUALIZEURL + bbquery);

    document.body.appendChild(form);
    form.submit();
}

function pbSelectChange(event) {
    id = getID(event);
    act = $('#pbSelect'+id+' :selected').attr('data-pbonclick');
    eval(act);
    $('#pbSelect'+id).val(__t('Više akcija...')).blur();
}

function stayHere(event) {
  //console.log('a');
    if (!
	($(event.target).hasClass('open-same-window')
	 || $(event.target).parents('.open-same-window').length > 0)
       )
	$(event.target).attr('target', '_self');
   // console.log('A');
    return true;
}

function scrollToTop() {
    $('html,body').animate({scrollTop:0}, 1000);
}
	
cmed = {}; //list of code mirror instances, indexed by pyid

hflex = {}; //history flexigrid instances
hflexhelp = {};

$(".hintlink").live("click", function(e) {
    n = $(this).attr("id").substring(8);
    o = $("#hintbox" + n);
    o.insertBefore('#page');
    o.css({"display":"block","top": e.pageY,"left": e.pageX});
    o.draggable({ cancel: ".hintboxlink" });
});

$(".hintboxlink").live("click", function(e) {
    n = $(this).attr("id").substring(11);
    $("#hintbox"+n).css("display","none");
});   

$(".pbform").live("submit", pbFormSubmit);
$(".selectmore").live("change", pbSelectChange);
$('.entry-content a').live('click', stayHere);
$('.hintbox a').live('click', stayHere);
$('.pyflexClose').live('click', function (e) {historyClick($(e.target).closest('.pybox').find('input[name="pyId"]').val(),"");});
$('.flexigrid pre').live('dblclick', function (e) {
    jq = $(e.target);
    id = jq.closest('.pybox').find('input[name="pyId"]').val();
    code = jq.html();
    var div = document.createElement('div');
    div.innerHTML = code;
    var decoded = div.firstChild.nodeValue;
    pbSetText(id, decoded);
});
$('div.jqpHintLink a').live('click', function (e) {
    $(e.target).closest('.jqpHintOuter').find('.jqpHintContent').dialog({'dialogClass' : 'wp-dialog','width':800});
});

function flexfix(index, flexigrid) {
    G = $(flexigrid);
    i = 0;
    sum = 0;
    G.find("th").each(function(index, th) {
	w = $(G.find("div.bDiv tbody tr:first-child td")[i]).width();
	if (w > $(th).width()) {
	    $(th).find('div').width(w-10);
	    sum += 2+w;
	}
	else {
	    G.find("div.bDiv tbody tr td:nth-child("+(i+1)+") div").width($(th).width()-10);
	    sum += 2+$(th).width();
	}
	$(G.find(".cDrag > div")[i]).css("left", sum);
	i++;
    });
}

function flexfixall() {
    $('.flexigrid').each(flexfix);
}

function pyflex(options) {
    //options['id']       : div id which will be filled with the flexibox
    //options['url']      : url that performs the database call
    //options['dbparams'] : extra arguments to send in database call
    //options['flparams'] : extra arguments for flexigrid, overwriting defaults
    $.ajax
    ({type:"POST",
      url:options['url'],
      data:$.param('dbparams' in options ? options['dbparams'] : []),
      success:function(data){pyflexSuccess(options, data);},
      failure:function(){$("#"+options['id']).html(__t('Greška: ne može se uspostaviti konekcija sa bazom.'));}
     });
}
function pyflexSuccess(options, data) {
    $('#'+options['id']+' .pyflexerror').remove();
    //console.log('b');
    if (!(data instanceof Object) || !("rows" in data) || data["rows"].length==0) {
	hflexhelp[options['id']] = options;
	msg = (!(data instanceof Object) || !("rows" in data)) ? data : __t('Konekcija sa bazom je uspela, ali podaci nisu pronađeni.'); 
	info = "<a onclick='pyflex(hflexhelp[\""+options['id']+"\"])'>"+__t("Pokušajte ponovo.")+"</a>";
	$('#'+options['id']).html('<span class="pyflexerror">' + msg + ' ' + info + '</span>');
	//alert(msg);
	return;
    }
    //console.log('B');
    firstRow = data['rows'][0]['cell'];
    model = new Array();
    for (colname in firstRow) {
	colModel = {display: colname, name: colname, sortable: true};
	if (colname == 'user code' && $('#'+options['id']).parents('.pybox').length > 0) 
	    colModel['attrs'] = {'class': 'usercodecol', 'title': 'double-click to reload version'};
	model.push(colModel);
    }
    //console.log('Z');
    xp = new Array();
    if ('dbparams' in options)
	for (paramname in options['dbparams']) 
	    xp.push({name: paramname, value: options['dbparams'][paramname]});
    $('#' + options['id']).prepend('<span class="pyflex"></span>');

    $(function() {
	opts = {
	    url: options['url'], 
	    dataType: 'json',
	    colModel: model, 
	    usepager: true,
	    resizableVForce: true,
	    useRp: true,
	    unselectable: true,
	    showToggleBtn: false,
	    rp: 4, 
	    rpOptions: [1, 2, 4, 8, 16, 32, 64], 
	    onSuccess: flexfixall,
	    onDragCol: flexfixall,
	    params: xp,
	    canRearrange: false,
            pagestat: __t('Displaying {from} to {to} of {total} items'),
            pagetext: __t('Page'),
	    outof: __t('out of'),
            procmsg:__t( 'Processing, please wait ...')
	};
	if ('flparams' in options) 
	    for (optname in options['flparams'])
		opts[optname] = options['flparams'][optname];
	hflex[options['id']] = $('#' + options['id'] + ' span.pyflex').flexigrid(opts);
	$('#' + options['id']).resizable({handles:'e'});
    });
}

function historyClick(id,thename) {
    $('#pbhistory'+id).toggle();
    //console.log('c');
    createNow = !$('#pbhistory'+id).is(":hidden") && ($('#pbhistory' + id + ' .flexigrid').length == 0);
    //console.log('C');
    if (createNow) {
	var url = HISTORYURL;
	pyflex({'id':'pbhistory'+id, 'url':url, 'dbparams':{'p': thename}, 'flparams':{'showCloseBtn':true}});
    }
    if ($('#pbhistory'+id).is(":hidden")) 
	setCommandLabel(id, 'history', __t('Istorija'));
    else {
	setCommandLabel(id, 'history', __t('Sakriti istoriju'));
	if (!createNow) hflex['pbhistory'+id].flexReload();
    }
}

function setCommandLabel(id, name, label) {
    $('#pybox'+id+' input[name="'+name+'"]').attr('value', label);
    $('#pybox'+id+' option[name="'+name+'"]').html(label);
}

function descape(S) {
    return jQuery.parseJSON('"' + S + '"');
}

function toggleSibling(event) {
    var con = $(this).parents('.collapseContain'); // .collapseContain
//    console.log(con.size(), con);
    var hideNow = con.hasClass("showing");
    if (!hideNow) {
	var accord = $(this).parents('.accordion').find('.collapseContain.showing');
	accord.children('.collapseBody').slideUp();
	accord.removeClass('showing');
	accord.addClass('hiding');
	con.children('.collapseBody').slideDown();
	con.removeClass('hiding');
	con.addClass('showing');
    }
    else {
	con.children('.collapseBody').slideUp();
	con.removeClass('showing');
	con.addClass('hiding');
    }
    return false;
}

$('.quoth').live('click', quoteIt);
function quoteIt(event) {
    $('#mailform').insertAfter($(this).parents('.collapseContain'));
    var text = undo_htmlspecialchars($(this).parents('.collapseContain').find('pre').html());
    var currreply = $('#mailform').find('textarea').val();
    if (currreply.substring(-1, 0)=="\n") currreply = substring(currreply(0, -1));
    currreply += ("\n"+text).replace(/\n/g, "\n>");
    $('#mailform').find('textarea').val(currreply);
}

function undo_htmlspecialchars(S) {
    S = S.replace(/&gt;/g, '>');
    S = S.replace(/&lt;/g, '<');    
    S = S.replace(/&amp;/g, '&');
    return S;
}

$( // this call to $ makes it delay until the DOM is loaded
    function() {   

      $('.collapseHead').live('click', toggleSibling);
      $('.collapseContain.showing > .collapseBody').css('display', 'block'); // fix weird bug with diappearing instead of sliding

      if (typeof justVisualizing === 'undefined') {
        $('ul.pyscramble').sortable();
	$('.resizy').resizable({handles:'s',minHeight:50});

	if (window.location.hash) {
	    setTimeout("window.scrollBy(0, -60)", 10); // so direct links aren't hidden by adminbar
	} 

	$("#wp-admin-bar-site-name").after($("#pylangswitcher li"));
	$("#pylangswitcher").remove();
      }
      flexfixall();
    }
);
