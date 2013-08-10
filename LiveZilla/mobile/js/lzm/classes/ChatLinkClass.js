/****************************************************************************************
 * LiveZilla ChatLinkClass.js
 *
 * Copyright 2013 LiveZilla GmbH
 * All rights reserved.
 * LiveZilla is a registered trademark.
 *
 ***************************************************************************************/

function ChatLinkClass() {
    this.iframesArray = [];
    this.iframeCounter = 0;
    this.activeLink = '';
    this.iframeCloseButton = $('#iframe-close-button');
    this.iframeHideButton = $('#iframe-hide-button');
    this.iframeContainer = $('#iframe-container');
    this.iframeContainerHeadline = $('#iframe-container-headline');
    this.iframeUrl = $('#iframe-url');
}

ChatLinkClass.prototype.openLinkInIframe = function (url) {
    var iframeWidth = $(window).width() - 4;
    var iframeHeight = $(window).height() - 4 - 50;

    this.iframeContainer.css({
        'z-index': 4000,
        position: 'absolute',
        left: '0px',
        top: '0px',
        width: $(window).width() + 'px',
        height: $(window).height() + 'px',
        'background-color': '#ffffff'
    });
    this.iframeContainerHeadline.css({
        'z-index': 4200,
        position: 'absolute',
        left: '0px',
        top: '0px',
        width: $(window).width() + 'px',
        height: '30px',
        'background-color': '#e9e9e9',
        'border-bottom': '1px solid #555'
    });
    this.iframeUrl.css({
        'z-index': 5000,
        position: 'absolute',
        left: '4px',
        top: '2px',
        width: ($(window).width() - 120) + 'px',
        height: '20px',
        'background-color': '#f5f5f5',
        'border': '1px solid #ccc',
        'border-radius': '2px',
        padding: '2px 0px 2px 10px',
        'font-style': 'italic',
        'color': '#797979'
    });
    this.iframeUrl.html(url);
    this.iframeCloseButton.css({
        position: 'absolute',
        left: (iframeWidth + 2 - 45) + 'px',
        top: '4px',
        width: '40px',
        height: '20px',
        'z-index': 5000,
        'background-image': 'url("img/205-close.png")',
        'background-repeat': 'no-repeat',
        'background-position': 'center',
        'border': '1px solid #ccc',
        'border-radius': '2px'
    });
    this.iframeHideButton.css({
        position: 'absolute',
        left: (iframeWidth + 2 - 90) + 'px',
        top: '4px',
        width: '40px',
        height: '20px',
        'z-index': 5000,
        'background-image': 'url("img/204-delete3.png")',
        'background-repeat': 'no-repeat',
        'background-position': 'center',
        'border': '1px solid #ccc',
        'border-radius': '2px'
    });

    var linkAlreadyOpened = false;
    for (var i = 0; i < this.iframesArray.length; i++) {
        //console.log(url + ' --- ' + this.iframesArray[i].url);
        if (this.iframesArray[i].url == url) {
            this.iframeContainer.css('display', 'block');
            this.iframeUrl.css('display', 'block');
            this.iframesArray[i].domElement.css('display', 'block');
            this.iframeCloseButton.css('display', 'block');
            this.iframeHideButton.css('display', 'none');
            linkAlreadyOpened = true;
            this.activeLink = url;
            break;
        }
    }
    if (!linkAlreadyOpened) {
        this.iframeCounter++;
        var newIframe = $('<iframe id="linkIframe-' + this.iframeCounter + '" src="' + url + '"' +
            ' width="' + iframeWidth + '" height="' + iframeHeight + '"' +
            ' seamless="seamless" />');
        var thisIframeObject = {
            url: url,
            domElement: newIframe
        };
        this.iframeContainer.css('display', 'block');
        thisIframeObject.domElement.appendTo(this.iframeContainer);
        thisIframeObject.domElement.css({
            display: 'block',
            'z-index': 4100,
            position: 'absolute',
            left: '2px',
            top: '52px',
            width: iframeWidth + 'px',
            height: iframeHeight + 'px'
        });
        this.iframeUrl.css('display', 'block');
        this.iframeCloseButton.css('display', 'block');
        this.iframeHideButton.css('display', 'none');
        this.activeLink = url;
        this.iframesArray.push(thisIframeObject);
    }
};

ChatLinkClass.prototype.closeLinkInIframe = function (action) {
    var tmpIframeArray = [];
    for (var i = 0; i < this.iframesArray.length; i++) {
        if (this.iframesArray[i].url == this.activeLink) {
            this.iframesArray[i].domElement.css('display', 'none');
            this.activeLink = '';
            if (action == 'close') {
                this.iframesArray[i].domElement.remove()
            }
        } else {
            tmpIframeArray.push(this.iframesArray[i]);
        }
    }
    this.iframeContainer.css('display', 'none');
    this.iframeUrl.css('display', 'none');
    this.iframeCloseButton.css('display', 'none');
    this.iframeHideButton.css('display', 'none');
    if (action == 'close') {
        this.iframesArray = tmpIframeArray;
    }
};
