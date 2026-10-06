document.addEventListener('DOMContentLoaded', function() {
    // Animation au ajout au panier
    const addForms = document.querySelectorAll('form[action*="cart-add"]');
    addForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            // Le formulaire soumet normalement, mais on peut ajouter un effet visuel
            const btn = form.querySelector('button');
            if(btn) {
                btn.style.transform = 'scale(0.9)';
                setTimeout(() => btn.style.transform = 'scale(1)', 200);
            }
        });
    });
});