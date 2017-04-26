var xmlParseError = false;

function loadXMLDoc(url, callback) {
	var xhr;
	if (window.XMLHttpRequest) {
		try {
			xhr = new XMLHttpRequest();
		} catch (e) {}
	} else if (window.ActiveXObject) {
		try {
			xhr = new ActiveXObject('Msxml2.XMLHTTP.5.0');
		} catch (e) {
			try {
				xhr = new ActiveXObject('Microsoft.XMLHTTP');
			} catch (e) {}
		}
	}
	if (xhr) {
		xhr.onreadystatechange = callback;
		xhr.open('GET', url, true);
		xhr.timeout = 5000;
		xhr.send();
	} else {
		alert('Your browser does not support AJAX');
	}
}

function parseXML(xmlStr) {

	if (typeof window.DOMParser != 'undefined') {

		var parser = new DOMParser();
		var xmlDoc = parser.parseFromString(xmlStr, 'text/xml');

		if (xmlDoc.getElementsByTagName('parsererror').length > 0) {
			xmlParseError = checkErrorXML(xmlDoc.getElementsByTagName('parsererror')[0]);
			return false;
		}

		return xmlDoc;

	} else if (typeof window.ActiveXObject != 'undefined' && new window.ActiveXObject('Microsoft.XMLDOM')) {

		var xmlDoc = new ActiveXObject('Microsoft.XMLDOM');
		xmlDoc.async = false;
		xmlDoc.loadXML(xmlStr);

		if (xmlDoc.parseError.errorCode !== 0) {
			xmlParseError = xmlDoc.parseError.errorCode;
			return false;
		}

		return xmlDoc;

	} else {

		xmlParseError = 'Your browser doesn\'t support XML Parsing';
		return false;

	}

}

function _getTextContent(el) {
	return el.textContent || el.innerText || el.text;
}
