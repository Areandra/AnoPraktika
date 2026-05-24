import Alpine from "alpinejs";
import { createIcons, icons } from "lucide";
window.Alpine = Alpine;

Alpine.start();

document.addEventListener("DOMContentLoaded", () => {
    createIcons({ icons });
});

document.addEventListener("alpine:navigated", () => {
    createIcons({ icons });
});

console.log("call");
