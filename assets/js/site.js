document.addEventListener("DOMContentLoaded", () => {
  const navigation = document.querySelector("#top-menu-nav");
  const toggle = document.querySelector(".mobile-menu-toggle");

  if (navigation && toggle) {
    toggle.addEventListener("click", () => {
      const open = navigation.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", String(open));
    });
  }
});
