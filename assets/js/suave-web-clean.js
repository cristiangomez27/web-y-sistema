(() => {
  const CART_KEY = "suaveurban_cart_clean_v1";
  const FAV_KEY = "suaveurban_favs_clean_v1";

  const $all = (s, root = document) => Array.from(root.querySelectorAll(s));
  const money = (n) => "$" + Number(n || 0).toLocaleString("es-MX") + " MXN";
  const read = (key) => {
    try { return JSON.parse(localStorage.getItem(key) || "[]"); } catch { return []; }
  };
  const write = (key, value) => localStorage.setItem(key, JSON.stringify(value));
  const getCart = () => read(CART_KEY);
  const setCart = (cart) => { write(CART_KEY, cart); updateBadges(); renderCartPage(); };
  const getFavs = () => read(FAV_KEY);
  const setFavs = (favs) => { write(FAV_KEY, favs); updateBadges(); renderFavoritesPage(); };
  const esc = (s) => String(s ?? "").replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  function cartCount() {
    return getCart().reduce((total, item) => total + Number(item.qty || 1), 0);
  }

  function updateBadges() {
    $all("[data-cart-count]").forEach(el => el.textContent = cartCount());
    $all("[data-fav-count]").forEach(el => el.textContent = getFavs().length);
  }

  function currentOption(selector) {
    const active = document.querySelector(selector + ".is-active");
    return active ? active.textContent.trim() : "";
  }

  function addCart(button) {
    const id = String(button.dataset.id || "");
    if (!id) return;
    const item = {
      id,
      name: button.dataset.name || "Producto",
      price: Number(button.dataset.price || 0),
      image: button.dataset.image || "",
      size: currentOption("[data-size-option]"),
      color: currentOption("[data-color-option]"),
      qty: 1
    };
    const cart = getCart();
    const found = cart.find(x => x.id === item.id && x.size === item.size && x.color === item.color);
    if (found) found.qty = Number(found.qty || 1) + 1;
    else cart.push(item);
    setCart(cart);
    button.classList.add("is-added");
    const old = button.textContent;
    button.textContent = "Agregado ✓";
    setTimeout(() => { button.classList.remove("is-added"); button.textContent = old; }, 1200);
  }

  function toggleFav(data) {
    const favs = getFavs();
    const idx = favs.findIndex(x => String(x.id) === String(data.id));
    if (idx >= 0) favs.splice(idx, 1);
    else favs.push(data);
    setFavs(favs);
  }

  function waUrl(phone, text) {
    phone = String(phone || "").trim();
    if (!phone) return "#";
    if (/^https?:\/\//i.test(phone)) return phone + (phone.includes("?") ? "&" : "?") + "text=" + encodeURIComponent(text);
    return "https://wa.me/" + phone.replace(/\D+/g, "") + "?text=" + encodeURIComponent(text);
  }

  function renderCartPage() {
    const page = document.querySelector("[data-cart-page]");
    if (!page) return;
    const cart = getCart();
    if (!cart.length) {
      page.innerHTML = `<div class="empty-state">Tu carrito está vacío.</div><a class="btn btn--gold" href="/colecciones">Ver colecciones</a>`;
      return;
    }
    const total = cart.reduce((sum, item) => sum + Number(item.price || 0) * Number(item.qty || 1), 0);
    const text = cart.map(i => `${i.qty} x ${i.name}${i.size ? " talla " + i.size : ""}${i.color ? " color " + i.color : ""}`).join(", ");
    const checkout = waUrl(page.dataset.whatsapp, `Hola, quiero comprar: ${text}. Total: ${money(total)}`);
    page.innerHTML = `
      <div class="cart-list">
        ${cart.map((item, index) => `
          <article class="cart-item">
            <span>${item.image ? `<img src="${esc(item.image)}" alt="">` : "SU"}</span>
            <div>
              <h3>${esc(item.name)}</h3>
              <p>${item.size ? "Talla: " + esc(item.size) : ""} ${item.color ? "Color: " + esc(item.color) : ""}</p>
              <strong>${money(item.price)}</strong>
            </div>
            <div class="qty">
              <button data-cart-minus="${index}">−</button>
              <b>${item.qty || 1}</b>
              <button data-cart-plus="${index}">+</button>
            </div>
            <button class="remove" data-cart-remove="${index}">Eliminar</button>
          </article>
        `).join("")}
      </div>
      <div class="cart-total"><b>Total</b><strong>${money(total)}</strong></div>
      <a class="btn btn--gold btn--wide" href="${checkout}" target="_blank" rel="noopener">Finalizar por WhatsApp</a>
    `;
  }

  function renderFavoritesPage() {
    const page = document.querySelector("[data-favorites-page]");
    if (!page) return;
    const favs = getFavs();
    if (!favs.length) {
      page.innerHTML = `<div class="empty-state">Aún no tienes favoritos.</div><a class="btn btn--gold" href="/colecciones">Ver colecciones</a>`;
      return;
    }
    page.innerHTML = `<section class="product-grid">${favs.map(item => `
      <article class="product-card">
        <a class="product-card__media" href="/producto/${esc(item.id)}">
          ${item.image ? `<img src="${esc(item.image)}" alt="${esc(item.name)}">` : `<span class="image-placeholder">SU</span>`}
        </a>
        <div class="product-card__body">
          <h3><a href="/producto/${esc(item.id)}">${esc(item.name)}</a></h3>
          <strong>${money(item.price)}</strong>
          <div class="product-card__actions">
            <a href="/producto/${esc(item.id)}">Ver modelo</a>
            <button data-remove-fav="${esc(item.id)}">Quitar</button>
          </div>
        </div>
      </article>
    `).join("")}</section>`;
  }

  document.addEventListener("click", (ev) => {
    const menu = ev.target.closest("[data-menu-toggle]");
    if (menu) document.body.classList.toggle("menu-open");

    const add = ev.target.closest("[data-add-cart]");
    if (add) addCart(add);

    const option = ev.target.closest("[data-size-option], [data-color-option]");
    if (option) {
      const group = option.hasAttribute("data-size-option") ? "[data-size-option]" : "[data-color-option]";
      $all(group).forEach(x => x.classList.remove("is-active"));
      option.classList.add("is-active");
    }

    const thumb = ev.target.closest("[data-thumb]");
    if (thumb) {
      const main = document.querySelector("[data-main-product-image]");
      if (main) main.src = thumb.dataset.thumb;
    }

    const plus = ev.target.closest("[data-cart-plus]");
    const minus = ev.target.closest("[data-cart-minus]");
    const remove = ev.target.closest("[data-cart-remove]");
    if (plus || minus || remove) {
      const cart = getCart();
      const index = Number((plus || minus || remove).dataset.cartPlus ?? (plus || minus || remove).dataset.cartMinus ?? (plus || minus || remove).dataset.cartRemove);
      if (remove) cart.splice(index, 1);
      else if (plus) cart[index].qty = Number(cart[index].qty || 1) + 1;
      else if (minus) {
        cart[index].qty = Math.max(1, Number(cart[index].qty || 1) - 1);
      }
      setCart(cart);
    }

    const removeFav = ev.target.closest("[data-remove-fav]");
    if (removeFav) {
      const favs = getFavs().filter(x => String(x.id) !== String(removeFav.dataset.removeFav));
      setFavs(favs);
    }
  });

  $all(".product-card").forEach(card => {
    const btn = document.createElement("button");
    btn.type = "button";
    btn.className = "fav-floating";
    btn.innerHTML = "♡";
    btn.setAttribute("aria-label", "Agregar a favoritos");
    const addBtn = card.querySelector("[data-add-cart]");
    const media = card.querySelector(".product-card__media");
    if (addBtn && media) {
      btn.addEventListener("click", () => toggleFav({
        id: addBtn.dataset.id,
        name: addBtn.dataset.name,
        price: Number(addBtn.dataset.price || 0),
        image: addBtn.dataset.image || ""
      }));
      media.appendChild(btn);
    }
  });

  updateBadges();
  renderCartPage();
  renderFavoritesPage();
})();
