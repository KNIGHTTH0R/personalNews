document.addEventListener("DOMContentLoaded",function(){function e(e){var t="themeswitch.php?theme=";switchingElement=document.getElementById(e),switchingElement.onclick=function(){xmlhttp=new XMLHttpRequest,this.checked?(document.getElementsByTagName("body")[0].classList.add(m),document.getElementsByTagName("body")[0].classList.remove(c),xmlhttp.open("GET",t+m,!0),xmlhttp.send(),localStorage.setItem("theme",m),console.log("localStorage Theme is: "+m)):(document.getElementsByTagName("body")[0].classList.add(c),document.getElementsByTagName("body")[0].classList.remove(m),xmlhttp.open("GET","themeswitch.php?theme="+c,!0),xmlhttp.send(),localStorage.setItem("theme",c),console.log("localStorage Theme is: "+c))}}function t(e,n){t=document.getElementById(e),t.onclick=function(){targetElement=document.getElementById(n),targetElement.classList.contains("js-hidden")?targetElement.classList.remove("js-hidden"):targetElement.classList.add("js-hidden"),event.preventDefault()}}function n(){document.getElementsByTagName("body")[0].classList.add("js")}function l(e,t){l=document.getElementById(e),l.classList.add("sticky"),stickyHeight=l.clientHeight+"px",scrollElement=document.getElementById(t),scrollElement.style.marginTop=stickyHeight}function o(e){overlayElement=document.getElementById(e),overlayElement.style.top=stickyHeight}function a(e){function t(e){xmlhttp=new XMLHttpRequest,e.target!==e.currentTarget&&(channelLink=e.target.getAttribute("href"),renderFile="render-feeds.php",e.preventDefault(),xmlhttp.open("GET",renderFile+channelLink,!0),xmlhttp.send(),xmlhttp.onreadystatechange=function(){4===xmlhttp.readyState&&xmlhttp.readyState&&(outputContainer=document.getElementById("content"),overlayContainer=document.getElementById("application-overlay"),outputContainer.innerHTML=xmlhttp.response,overlayContainer.classList.add("js-hidden"),console.log("finish"))}),e.stopPropagation()}elementContainer=document.getElementById(e),elementContainer.addEventListener("click",t,!1)}var c="light",m="dark",s=localStorage.getItem("theme");console.log(s),null!==s&&(document.getElementsByTagName("body")[0].classList.remove(c,m),document.getElementsByTagName("body")[0].classList.add(s),document.getElementsByTagName("body")[0].style.opacity="1",s===c&&(document.getElementById("theme-switcher").checked=!1),s===m&&(document.getElementById("theme-switcher").checked=!0)),n(),l("application-head","content"),e("theme-switcher"),t("toggle-overlay","application-overlay"),o("application-overlay"),a("channels")});
//# sourceMappingURL=script.js.map
