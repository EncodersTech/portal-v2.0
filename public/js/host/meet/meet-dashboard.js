!function(e){var t={};function n(o){if(t[o])return t[o].exports;var r=t[o]={i:o,l:!1,exports:{}};return e[o].call(r.exports,r,r.exports,n),r.l=!0,r.exports}n.m=e,n.c=t,n.d=function(e,t,o){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:o})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var o=Object.create(null);if(n.r(o),Object.defineProperty(o,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var r in e)n.d(o,r,function(t){return e[t]}.bind(null,r));return o},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/",n(n.s=45)}({45:function(e,t,n){e.exports=n("MQIl")},MQIl:function(e,t,n){"use strict";$(document).ready((function(){function e(e){return null==e||""===e}function t(e){$.alert({title:"Whoops",content:e,icon:"fas fa-exclamation-triangle",type:"red",typeAnimated:!0})}setTimeout((function(){$("#messageBody").summernote({placeholder:"Write message here...",minHeight:200,toolbar:[["style",["bold","italic","underline","clear"]],["font",["strikethrough","superscript","subscript"]],["fontsize",["fontsize"]],["insert",["link"]],["color",["color"]],["para",["paragraph"]],["height",["height"]]]}),$("#ckbCheckAll").click((function(){$(".gymCheck").prop("checked",$(this).prop("checked"))})),$(".gymCheck").on("click",(function(){$(".gymCheck:checked").length==$(".gymCheck").length?$("#ckbCheckAll").prop("checked",!0):$("#ckbCheckAll").prop("checked",!1)}))}),3200),$(document).on("submit","#submitMassNotification",(function(){var e=""===$("<div />").html($("#messageBody").summernote("code")).text().trim().replace(/ \r\n\t/g,"");return 0===$(".gymCheck:checked").length?(t("Please select at least one gym."),!1):$("#messageBody").summernote("isEmpty")||e?(t("Please write your message."),!1):($("#sedMailNotification").prop("disabled",!0),!0)})),$(document).on("change","#documentImage",(function(){var t=isValidDocument($(this),"#validationErrorsBox");e(t)||0==t||($("#validationErrorsBox").html("").hide(),function(e,t,n){var o=!0;if(e.files&&e.files[0]){var r=new FileReader;r.onload=function(e){var r=new Image;-1==$.inArray(n,["pdf","doc","docx","xlsx"])?r.src=e.target.result:r.src="pdf"==n?pdfDocumentImageUrl:"xlsx"==n?excelDocumentImageUrl:docxDocumentImageUrl,r.onload=function(){$(t).attr("src",r.src),o=!0}},o&&(r.readAsDataURL(e.files[0]),$(t).show())}}(this,"#previewImage",t))})),window.isValidDocument=function(n,o){var r=$(n).val().split(".").pop().toLowerCase();return console.log("ext",r),e(r)?($("#previewImage").attr("src",defaultImage),!1):-1==$.inArray(r,["png","jpg","jpeg","pdf","doc","docx","xlsx"])?($(n).val(""),t("The document must be a file of type: jpeg, jpg, png, pdf, doc, docx."),!1):r}}))}});