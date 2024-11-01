/*
 * Please read the license to apply some modify.
 */
function removeFunction() {
  const topBarDom = document.querySelector("body .mc-notification");
  // topBarDom.classList.add('animated', 'fadeOutUp');
  topBarDom.remove();
}

 window.onload = function(){
   setTimeout(function(){
    const patt = new RegExp(/(?:\d+)/g);
    var id;
    var topBarDom;
    const body = document.getElementsByTagName('body')[0].getAttribute("class");

    if (patt.test(body)) {
        id = body.match(/(?:\d+)/g)[0];
    }else{
        return;
    }
    const data = 'action=tbn_ajax_input&id=' + id;
    return fetch(window.location.origin + "/wp-admin/admin-ajax.php", {
        credentials: 'same-origin',
        method: 'POST',
        body: data,
        headers: new Headers({
          'Content-type': 'application/x-www-form-urlencoded; charset=utf-8'
        }),
      })
      .then(function (response) {
        if (response.status === 200) {
            response.json().then(data =>{
              bodyDom = document.querySelector("body.page");

              function createElementFromHTML(htmlString) {
                var div = document.createElement('div');
                div.innerHTML = htmlString.trim();

                // Change this to div.childNodes to support multiple top-level nodes
                return div.firstChild;
              }
              const notification = createElementFromHTML(data.html);
              notification.classList.add('animated', 'fadeIn');
              bodyDom
              .insertBefore(notification, bodyDom.firstChild).querySelector('.close').onclick = removeFunction;
              notification.style.background = `${data.color}`;
            });
          }
      })
      .catch(function (error) {
        console.log('Request failed', error);
      });
    },1000);
}
