function d(s){const e=s.querySelectorAll('[data-js="tab-link"]'),c=s.querySelector('[data-js="tab-contents"]');e.forEach(a=>{a.addEventListener("click",()=>{const o=a.dataset.tab;c.querySelectorAll('[data-js="tab-content"]').forEach(t=>{t.classList.toggle("hidden",t.id!==o)}),e.forEach(t=>{t.classList.toggle("active",t===a)})})})}export{d as default};
//# sourceMappingURL=tabs.js.map
