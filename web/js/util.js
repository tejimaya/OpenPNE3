/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

/**
 * OpenPNE utility JavaScript library
 *
 * @author Kousuke Ebihara <ebihara@tejimaya.com>
 * @author Shogo Kawahara <kawahara@tejimaya.net>
 */

function getCenterMuchScreen(element)
{
  var width  = $(element).getWidth();
  var height = $(element).getHeight();
  var screenWidth = document.viewport.getWidth();
  var screenHeight = document.documentElement.clientHeight;
  var screenTop = document.viewport.getScrollOffsets().top;

  var left = (screenWidth / 2) - (width / 2);
  var top = (screenHeight / 2 + screenTop) - (height / 2);

  if (top < 10)
  {
    top = 10;
  }

  return {"left": left + "px", "top" : top + "px"};
}

/**
 * opCookie class
 */
var opCookie = {

 /**
  * Sets a cookie data
  *
  * This method imitates of PHP's setcookie() function
  *
  * @params string name
  * @params string value
  * @params Date   expires
  * @params string path
  * @params string domain
  * @params bool   secure
  * @params bool   httponly
  */
  set: function(name, value, expires, path, domain, secure, httponly)
  {
    var result = '';

    if (value == undefined || (value instanceof String && !value))  // deletes cookie
    {
      var expires = new Date();
      expires.setTime((new Date()).getTime() - (12 * 30 * 24 * 60 * 60 * 1000));  // 1 year
      result = name + '=deleted; expires=' + expires.toUTCString();
    }
    else
    {
      if (!expires || !(expires instanceof Date))
      {
        var expires = new Date();
        console.debug(expires);
        expires.setTime((new Date()).getTime() + (60 * 60 * 1000));  // 1 hour
      }

      value = encodeURIComponent(value);
      result = name + "=" + value + "; expires=" + expires.toUTCString();
    }

    if (path && path.length)
    {
      result = result + "; path=" + path;
    }
    if (domain && domain.length)
    {
      result = result + "; domain=" + domain;
    }
    if (secure)
    {
      result = result + "; secure";
    }
    if (httponly)
    {
      result = result + "; secure";
    }

    document.cookie = result;
  },

 /**
  * Gets a cookie data
  *
  * @params string name
  */
  get: function(name)
  {
    var value = null;

    if (document.cookie && document.cookie.length)
    {
      var _cookie = document.cookie;

      var cookies = _cookie.split(';');
      for (var i = 0; i < cookies.length; i++)
      {
        var _cookie = cookies[i];
        var valuePos = name.length + 1;
        if (_cookie.substr(0, valuePos + 1).strip() == (name + '='))
        {
          value = decodeURIComponent(_cookie.substr(valuePos + 1)).strip();
          break;
        }
      }
    }

    return value;
  }
};
