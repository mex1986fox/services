// НЕ забудь session_token изменить а то работать не будет
function getXmlHttp() {
  var xmlhttp;
  try {
    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
  } catch (e) {
    try {
      xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    } catch (E) {
      xmlhttp = false;
    }
  }
  if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
    xmlhttp = new XMLHttpRequest();
  }
  return xmlhttp;
}
function RandomString(length) {   
	    var str = '';
	    for ( ; str.length < length; str += Math.random().toString(36).substr(2) );
	    return str.substr(0, length);
	}
function getPropInJSON(jsonObj, nameProp) {
  let retNameProp = undefined;
  JSON.parse(JSON.stringify(jsonObj),
    function (k, v) {
      if (k == nameProp) {
        retNameProp = v;
      }
      return v;
    }
  );
  return retNameProp;
}
function setCookie(name, value, days, path) {

  path = path || '/'; // заполняем путь если не заполнен
  days = days || 10;  // заполняем время жизни если не получен параметр

  var last_date = new Date();
  last_date.setDate(last_date.getDate() + days);
  var value = escape(value) + ((days == null) ? "" : "; expires=" + last_date.toUTCString());
  document.cookie = name + "=" + value + "; path=" + path; // вешаем куки
}
function posting_comment(comment, tracking, reply, token, csn, client_version) {
  var xmlhttp = getXmlHttp();
  setCookie('ST-u7xhd4', "itct=" + tracking + "&csn=" + csn, 2, '/');
  let sej =
  {
    "clickTrackingParams": tracking,
    "commandMetadata":
    {
      "webCommandMetadata":
      {
        "url": "/service_ajax",
        "sendPost": true
      }
    },
    "createCommentReplyEndpoint":
      { "createReplyParams": reply }
  };

  body = "comment_text=" + comment
    + "&sej=" + JSON.stringify(sej)
    + "&session_token=" + token



  xmlhttp.open('POST', 'service_ajax?name=createCommentReplyEndpoint', true); // Открываем асинхронное соединение
  xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded'); // Отправляем кодировку
  xmlhttp.setRequestHeader('X-YouTube-Client-Name', 1);
  xmlhttp.setRequestHeader('X-YouTube-Client-Version', client_version);
  xmlhttp.setRequestHeader('X-Youtube-Identity-Token', 'QUFFLUhqbm40elpaR2s4YmRqTTJvNnBEX2k5OFc4RVo2Z3w=');
  xmlhttp.send(body);
  xmlhttp.onreadystatechange = function () {
    // Ждём ответа от сервера
    if (xmlhttp.readyState == 4) {
      // Ответ пришёл
      console.dir("Отправлен: " + comment);
    }
  };
}
function start_flud() {

  let itemSectionRenderer, webResponseContextExtensionData, client_version;
  let iter = 1;
  let session_token = "QUFFLUhqbEQxaGt5VGZzdENPVVh6bTM0dG9oNHJTVGt3QXxBQ3Jtc0tuZGFyY2prSzkzZ2RZbzdXT1dlWTdGVTVkV2NJZk1FeDd4M3NtbkhZdURJNzZYZ2JsOWVoVnhrcE5kOVZGYVkwYzRiejZkQmp5b0RnTTdsUl9tT2VlQll6REdDOFNXdUpzT1ljd29BTDlmZjZmNzM5Snh2MXkwY2tLbHItRy1ETmJnUHE4T0xkSTVLVHFCWFV4RmZiWTlGaGh0Q3c=";

  itemSectionRenderer = getPropInJSON(window["ytInitialData"], "itemSectionRenderer");
  webResponseContextExtensionData = getPropInJSON(window["ytInitialData"].responseContext, "webResponseContextExtensionData");
  window["ytInitialData"]["responseContext"]["serviceTrackingParams"].forEach(tParam => {
    if (tParam["service"] == "ECATCHER") {
      tParam.params.forEach(p => {
        if (p.key == "client.version") {
          client_version = p.value;
        }
      });
    }
  });
  for (let coment of itemSectionRenderer.contents) {
    iter++;
    setTimeout(() => {

      posting_comment(
        "Так пернуть хочется **** " + RandomString(20),
        getPropInJSON(getPropInJSON(coment, "dialog"), "clickTrackingParams"),
        getPropInJSON(getPropInJSON(coment, "dialog"), "createReplyParams"),
        session_token,
        webResponseContextExtensionData.ytConfigData.csn,
        client_version
      );
    }, 2000 * iter);
  }
}
start_flud();
