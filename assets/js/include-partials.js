(function () {
  function loadPartial(url) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", url, false);
    xhr.send(null);
    return xhr.status === 200 ? xhr.responseText : "";
  }

  function markCurrentPage(navRoot) {
    var current = location.pathname.split("/").pop() || "index.html";
    var links = navRoot.querySelectorAll("#top-menu a[href]");
    links.forEach(function (a) {
      if (a.getAttribute("href") !== current) return;
      a.setAttribute("aria-current", "page");
      var li = a.closest("li");
      li.classList.add("current-menu-item", "current_page_item");
      var ancestor = li.parentElement.closest("li.menu-item-has-children");
      while (ancestor) {
        ancestor.classList.add("current-menu-ancestor", "current-menu-parent");
        ancestor = ancestor.parentElement.closest("li.menu-item-has-children");
      }
    });
  }

  var headerMount = document.getElementById("site-header");
  if (headerMount) {
    headerMount.outerHTML = loadPartial("partials/nav.html");
    var navRoot = document.getElementById("main-header");
    if (navRoot) markCurrentPage(navRoot);
  }

  var footerMount = document.getElementById("site-footer");
  if (footerMount) {
    footerMount.outerHTML = loadPartial("partials/footer.html");
  }
})();
