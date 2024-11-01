
function startsWith(haystack, needle) {
    if (haystack === null) {
        return false;
    }
    if (needle === false) {
        return false;
    }
    return haystack.slice(0, needle.length) === needle;
}


function endsWith(haystack, needle) {
    if (haystack === null) {
        return false;
    }
    if (needle === false) {
        return false;
    }
    return needle === '' || haystack.slice(-needle.length) === needle;
}

var theResponse;


function ajax_save_settings_form() {
    var myform = document.getElementById("save_form_form");
    var fd = new FormData(myform);
    jQuery.ajax({
        url: ajaxurl,
        data: fd,
        cache: false,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (response) {
            if (response != "") {
                //alert(response);
            }
        },
        fail: function (response) {
            alert('Verbindungsfehler');
        }

    });
    return false;
}
function uploadForm() {
    var myform = document.getElementById("datas");
    var fd = new FormData(myform);
    jQuery.ajax({
        url: ajaxurl,
        data: fd,
        cache: false,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (response) {
            if (response == 'false') {
                alert("Das Formular konnte leider nicht erzeugt werden!");
            } else {
                window.location = response;
            }

        }
    });
    return false;
}


jQuery(".wcf-file-upload").click(function () {
    jQuery(".wcf-file-upload-toggle").toggle(500);
});



jQuery(".wcf-captcha").click(function () {
    jQuery(".wcf-captcha-toggle").toggle(500);
});


jQuery(".wcf-email").click(function () {
    jQuery(".wcf-email-toggle").toggle(500);
});


jQuery(".wcf-formdata").click(function () {
    jQuery(".wcf-formdata-toggle").toggle(500);
});



jQuery(".wcf-date-format").click(function () {
    jQuery(".wcf-date-format-toggle").toggle(500);
});

jQuery(".wcf-export-import").click(function () {
    jQuery(".wcf-export-import-toggle").toggle(500);
});



jQuery(".wcf-design").click(function () {
    jQuery(".wcf-design-toggle").toggle(500);
});


jQuery(".wcf-generate-form").click(function () {
    jQuery(".wcf-generate-form-toggle").toggle(500);
});

jQuery(".wcf-special").click(function () {
    jQuery(".wcf-special-toggle").toggle(500);
});


jQuery(".wcf-frist").click(function () {
    jQuery(".wcf-frist-toggle").toggle(500);
});


jQuery(".wcb-bill-number").click(function () {
    jQuery(".wcb-bill-number-toggle").toggle(500);
});

jQuery(".wcf-special-toggle").hide();
jQuery(".wcf-email-toggle").hide();
jQuery(".wcf-file-upload-toggle").hide();
jQuery(".wcf-captcha-toggle").hide();
jQuery(".wcf-formdata-toggle").hide();
jQuery(".wcf-date-format-toggle").hide();
jQuery(".wcf-export-import-toggle").hide();
jQuery(".wcf-design-toggle").hide();
jQuery(".wcf-generate-form-toggle").hide();
jQuery(".wcf-frist-toggle").hide();
jQuery(".wcf-bill-number-toggle").hide();


function deleteFormInit() {
    deleteForm();
}

function deleteForm() {
    var formId = getFormId();
    jQuery(document).ready(function ($) {
        var data = {
            'action': 'delete_form_action',
            'formdata': formId
        };
        // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
        jQuery.post(ajaxurl, data, function (response) {
            if (response == 'false') {
                alert("Can not delete form!");
            } else {
                location.href = response;
            }
        });
    });
    return false;

}

// the form builder stuff



var cnt = 0;
function allowDrop(ev) {
    ev.preventDefault();
}

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
}

function dropTrash(ev) {
    ev.preventDefault();
    var data = ev.dataTransfer.getData("text");
    var src = document.getElementById(data);
    src.parentElement.removeChild(src);
}



function drop(ev) {
    ev.preventDefault();
    //var targetContainer = ev.target.id;
    var targetContainer = ev.target.getAttribute('name');
    //alert(targetContainer); //container_1_1_14341234132
    var targetNode = ev.target;

    var data = ev.dataTransfer.getData("text");
    //alert('der target container ' + targetContainer);

    var field = document.getElementById(data);
    // container und reihe auf dem feld setzen.
    var cols = field.getElementsByTagName('*');
    var maxi = new Date().getTime();
    var maxrang = getMaxRang(targetNode);
    for (var i = 0; i < cols.length; i++) {
        var rang = cols[i].getAttribute('name');
        if (startsWith(rang, 'containerRang_')) {
            cols[i].setAttribute('value', maxi);
        }
        //var tagId = cols[i].getAttribute('id');
        if (startsWith(rang, 'containerColumn_')) {
            cols[i].setAttribute('value', targetContainer);
        } else
        if (startsWith(rang, 'container_')) {
            cols[i].setAttribute('value', targetContainer);
        }
    }
    ev.target.appendChild(field);
}

/**
 * 
 * sucht den max. rang unterhalb des targets.
 * 
 * @param {type} node
 * @returns {undefined}
 */
function getMaxRang(node) {
    var max = 0;
    var nodes = node.getElementsByTagName('*');
    for (var i = 0; i < nodes.length; i++) {
        var rang = nodes[i].getAttribute('name');
        if (rang === 'container_rang') {
            var val = nodes[i].getAttribute('value');
            if (val !== null && val.length > 0) {
                // string 2 int ?
                var intval = parseInt(val);
                if (max < intval) {
                    max = intval;
                }
            }
        }
    }
    return max + 1;
}

function addOne() {
    // alert("add one geklickt ...");
    var tab = document.getElementById("tableau");
    var m = document.getElementById("container_one");
    var node = m.cloneNode(true);
    var maxi = new Date().getTime();
    //cnt++;
    node.setAttribute("id", maxi);
    node.setAttribute("tag", "container1");
    var cols = node.getElementsByTagName('*');
    for (var i = 0; i < cols.length; i++) {
        var tagName = cols[i].getAttribute('name');
        var clazz = cols[i].getAttribute('class');
        if (startsWith(clazz, "block")) {
            cols[i].setAttribute("name", tagName + maxi);
        } else
        if (tagName === "container1_1") {
            cols[i].setAttribute("id", "container_1_1_" + maxi);
        } else if (tagName === "headline_container_one") {
            cols[i].setAttribute("id", "headline_container_1_" + maxi);
            cols[i].setAttribute("name", "headline_container_1_" + maxi);
        }
    }
    tab.appendChild(node);
    return false;
}

function addTwo() {
    var tab = document.getElementById("tableau");
    var m = document.getElementById("container_two");
    var node = m.cloneNode(true);
    var maxi = new Date().getTime();
    node.setAttribute("id", maxi);
    node.setAttribute("tag", "container2");
    var cols = node.getElementsByTagName('*');
    for (var i = 0; i < cols.length; i++) {
        var tagName = cols[i].getAttribute('name');
        var clazz = cols[i].getAttribute('class');
        if (startsWith(clazz, "block")) {
            cols[i].setAttribute("name", tagName + maxi);
        } else

        if (tagName === "container2_1") {
            cols[i].setAttribute("id", "container_2_1_" + maxi);
        } else if (tagName === "container2_2") {
            cols[i].setAttribute("id", "container_2_2_" + maxi);
        } else if (tagName === "headline_container_two") {
            cols[i].setAttribute("id", "headline_container_2_" + maxi);
            cols[i].setAttribute("name", "headline_container_2_" + maxi);
        }
    }

    tab.appendChild(node);
    return false;
}
function addThree() {
    var tab = document.getElementById("tableau");
    var m = document.getElementById("container_three");
    var node = m.cloneNode(true);
    var maxi = new Date().getTime();
    node.setAttribute("id", cnt);
    node.setAttribute("tag", "container3");
    var cols = node.getElementsByTagName('*');
    for (var i = 0; i < cols.length; i++) {
        var tagName = cols[i].getAttribute('name');
        var clazz = cols[i].getAttribute('class');
        if (startsWith(clazz, "block")) {
            cols[i].setAttribute("name", tagName + maxi);
        } else
        if (tagName === "container3_1") {
            cols[i].setAttribute("id", "container_3_1_" + maxi);
        } else if (tagName === "container3_2") {
            cols[i].setAttribute("id", "container_3_2_" + maxi);
        } else if (tagName === "container3_3") {
            cols[i].setAttribute("id", "container_3_3_" + maxi);
        } else if (tagName === "headline_container_three") {
            cols[i].setAttribute("id", "headline_container_3_" + maxi);
            cols[i].setAttribute("name", "headline_container_3_" + maxi);
        }
    }


    tab.appendChild(node);
    return false;
}

function addNewComment() {
    var tab = document.getElementById("tableau");
    var m = document.getElementById("container_four");
    var node = m.cloneNode(true);
    cnt++;
    node.setAttribute("id", cnt);
    tab.appendChild(node);
    return false;
}


function setType(field) {
    var type = document.getElementById('type_' + field);
    // 1. in selectbox setzen:
    var ti = document.getElementById('input_type');
    var ti_hidden = document.getElementById('input_type_hidden');
    //ti.value = type;

    if (type.value === 'text') {
        setFileType(false);
        ti.value = "Text";
        ti_hidden.value = "Text";
    } else if (type.value === 'file') {
        setFileType(true);
        ti_hidden.value = "file";
    } else {
        setFileType(false);
        ti.value = type.value;
        ti_hidden.value = type.value;
    }
    // 2. die Values setzen ...
    checkRadio(ti.value);
}


function setFileType(fileType) {
    var ti = document.getElementById('input_type');
    var ti_hidden = document.getElementById('input_type_hidden');
    var mand = document.getElementById('input_mandatory');
    var place = document.getElementById('input_placeholder');
    var name = document.getElementById('input_name');
    // labels:
    var label_type = document.getElementById('label_typ');
    var label_mandatory = document.getElementById('label_mandatory');
    var label_name = document.getElementById('label_name');
    var label_placeholder = document.getElementById('label_placeholder');
    var style;
    if (fileType) {
        style = "display: none;";
        ti.value = "file";
    } else {
        style = "";
    }
    ti.style = style;
    mand.style = style;
    place.style = style;
    name.style = style;
    //labels:
    label_type.style = style;
    label_mandatory.style = style;
    label_name.style = style;
    label_placeholder.style = style;
}


function setCheckbox(valueId, checkboxId) {
    var valNode = document.getElementById(valueId);
    var value = valNode.getAttribute('value');
    var cb = document.getElementById(checkboxId);
    if (value === "true") {
        cb.checked = true;
    } else {
        cb.checked = false;
    }
}

function fieldSelected(fieldId) {
    setCheckbox('mandatory_' + fieldId, 'input_mandatory');
    setCheckbox('confirmationEmail_' + fieldId, 'input_confirmationEmail');
    copyVal('name_' + fieldId, 'input_name');
    copyVal('label_' + fieldId, 'input_label');
    copyVal('tooltip_' + fieldId, 'input_tooltip');
    copyVal('placeholder_' + fieldId, 'input_placeholder');
    //copyVal('mandatory_' + fieldId, 'input_mandatory');
    var name = document.getElementById('input_name');
    //name.value = fieldId;
    var chosen = document.getElementById('chosen_element');
    chosen.value = fieldId;
    setType(fieldId);
    for (var i = 0; i < 20; i++) {
        copyChoiceValues("val_" + i + "_" + fieldId, "val_" + i);
    }
}

function copyChoiceValues(src, dest) {
    var fsrc = document.getElementById(src);
    var fdest = document.getElementById(dest);
    if (fsrc == null) {
        fdest.value = "";
        if (dest != "val_0") {
            fdest.type = "hidden";
        }
    } else {
        fdest.value = fsrc.value;
        fdest.type = "text";
    }
}

function copyVal(src, dest) {
    var fsrc = document.getElementById(src);
    var fdest = document.getElementById(dest);
    fdest.value = fsrc.value;
}


function nameChange() {
    var elem = document.getElementById('chosen_element');
    var nn = document.getElementById('input_name');
    var oldname = document.getElementById('description_' + elem.value);
    oldname.value = nn.value;
}


function changeField(fieldName) {
    var elem = document.getElementById('chosen_element');
    var nn = document.getElementById('input_' + fieldName);
    var oldname = document.getElementById(fieldName + '_' + elem.value);
    if (fieldName === 'mandatory') {
        if (nn.checked) {
            oldname.value = "true";
        } else {
            oldname.value = "false";
        }
    } else {
        oldname.value = nn.value;
    }

    if (fieldName === 'confirmationEmail') {
        if (nn.checked) {
            oldname.value = "true";
        } else {
            oldname.value = "false";
        }
    }
    //
    if (fieldName == 'name') {
        var lbl = document.getElementById('lbl_' + elem.value);
        lbl.innerHTML = nn.value;
    }
}

function changeFieldValue(valId) {
    var elem = document.getElementById('chosen_element');
    var nn = document.getElementById(valId);
    var oldelem = document.getElementById(valId + '_' + elem.value);
    var cloneElem;
    if (oldelem == null) {
        cloneElem = nn.cloneNode(true);
        // muss an das ding angeklebt werden
        var papa = document.getElementById(elem.value);
        papa.appendChild(cloneElem);
        cloneElem.id = valId + '_' + elem.value;
        cloneElem.name = valId + '_' + elem.value;
        cloneElem.type = "hidden";
    } else {
        cloneElem = oldelem;
    }
    cloneElem.value = nn.value;
}

function changeType(pre, suc) {
    changeFieldValue(pre);
    var elem = document.getElementById(suc);
    elem.type = 'text';
}


function checkRadio(selected) {


    var theElement = document.getElementById('chosen_element');
    var oldname = document.getElementById('type_' + theElement.value);
    if (oldname.value == "file") {
        // image kann nicht geaendert werden.
        return;
    }
    oldname.value = selected;

    var elem = document.getElementById('values_radio');
    var email = document.getElementById('confirm_email_value');
    if (selected == 'Radio') {
        elem.style = "";
        email.style = "display: none;";
    } else if (selected == 'Choicebox') {
        elem.style = "";
        email.style = "display: none;";
    } else if (selected == 'Email') {
        elem.style = "display: none;";
        email.style = "";
    } else {
        elem.style = "display: none;";
        email.style = "display: none;";
    }
}




function showPreview() {
    var win = window.open("about:blank", "width=500, height=500", "_blank");
    win.document.write('<h2>Coming soon</h2>');
    //  win.document.open('http://localhost');
}


function addNewInputField() {
    var newId = getMaxFieldId();
    var formId = getFormId();
    var fid = formId + "_" + newId;
    var ti = new Date().getTime();
    var elements = document.getElementById('elements');
    var div = document.createElement("div");
    div.setAttribute("id", fid);
    div.setAttribute("class", "drag_field");
    div.setAttribute("ondragstart", "drag(event);");
    div.setAttribute("draggable", "true");
    div.setAttribute("onclick", "fieldSelected('" + fid + "')");

    div.setAttribute("tabindex", ti);

    var lbl = document.createElement("label");
    lbl.setAttribute("id", "lbl_" + fid);
    lbl.setAttribute("class", "exmpl");
    lbl.innerHTML = "New";
    div.appendChild(lbl);
    var inp = document.createElement("input");
    inp.setAttribute("class", "exmpl");
    inp.setAttribute("readonly", "true");
    div.appendChild(inp);
    div.appendChild(getHidden("containerColumn", fid));
    div.appendChild(getHidden("container", fid));
    div.appendChild(getHidden("containerRang", fid));
    var nom = getHidden("name", fid);
    nom.setAttribute("value", "New");
    div.appendChild(nom);
    div.appendChild(getHidden("label", fid));
    div.appendChild(getHidden("placeholder", fid));
    div.appendChild(getHidden("tooltip", fid));
    div.appendChild(getHidden("mandatory", fid));
    div.appendChild(getHidden("order", fid));
    var theId = getHidden("id", fid);
    theId.setAttribute("value", newId);
    div.appendChild(theId);
    var typ = getHidden("type", fid);
    typ.setAttribute("value", "text");
    div.appendChild(typ);
    div.appendChild(getHidden("imageNumber", fid));
    div.appendChild(getHidden("confirmationEmail", fid));
    elements.appendChild(div);
    return false;
}

function getHidden(fieldName, fid) {
    var ip = document.createElement("input");
    ip.setAttribute("type", "hidden");
    ip.setAttribute("id", fieldName + "_" + fid);
    ip.setAttribute("name", fieldName + "_" + fid);
    return ip;

}

function getFormId() {
    var node = document.getElementById('unique_form_id');
    return node.getAttribute('value');
}

function getMaxFieldId() {
    var max = 0;
    var nodes = document.getElementsByTagName('*');
    for (var i = 0; i < nodes.length; i++) {
        var name = nodes[i].getAttribute('name');
        if (startsWith(name, "id_")) {
            var val = nodes[i].getAttribute('value');
            var ival = parseInt(val);
            if (ival > max) {
                max = ival;
            }
        }
    }
    return parseInt(max) + 1;
}


// Focus Stuff

var last;

jQuery(".drag_field").click(function () {

    if (last != null) {
        jQuery(last).css("background-color", "#BDC6DE");
    }
    last = this;
    jQuery(this).css("background-color", "#949CCE");
    jQuery("#outer_settings_div").css("background-color", "#949CCE");
});
/*      
 .focusout(function() {
 jQuery(this).css("background-color","yellow");
 jQuery("#outer_settings_div").css("background-color", "yellow");
 });
 
 
 jQuery(".drag_field").focusin(function() {
 jQuery(this).addClass("focus");
 }).focusout(function() {
 jQuery(this).removeClass("focus");
 });
 */


var theResponse;
function uploadForm() {
    var myform = document.getElementById("datas");
    var fd = new FormData(myform);
    jQuery.ajax({
        url: ajaxurl,
        data: fd,
        cache: false,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (response) {
            if (response == 'false') {
                alert("Form can not be created. Please check your file permissions on your server!");
            } else {
                window.location = response;
            }

        }
    });
    return false;
}





function ajax_upload() {
    var myform = document.getElementById("upload_form");
    var fd = new FormData(myform);
    jQuery.ajax({
        url: ajaxurl,
        data: fd,
        cache: false,
        processData: false,
        contentType: false,
        type: 'POST',
        success: function (response) {
            if (response != "") {
                alert(response);
            }
        },
        fail: function (response) {
            alert('Connection Error');
        }

    });
    return false;
}
