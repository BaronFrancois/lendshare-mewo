document.addEventListener('DOMContentLoaded', async function() {
    await initialiserHeader();
    initialiserScrollHeader();
});

function initialiserScrollHeader() {
    const header = document.querySelector('header');
    if (!header) return;
    
    window.addEventListener('scroll', function() {
        if (window.scrollY > 50) {
            header.classList.add('header-scrolled');
        } else {
            header.classList.remove('header-scrolled');
        }
    });
}

async function initialiserHeader() {
    const header = document.querySelector('header');
    if (!header) return;
    
    const pageActuelle = determinerPageActuelle();
    const estConnecte = await verifierStatutConnexion();
    
    header.innerHTML = creerHeaderHTML(pageActuelle, estConnecte);
}

function determinerPageActuelle() {
    const path = window.location.pathname;
    const filename = path.split('/').pop();
    
    if (filename === 'index.html' || filename === '' || filename === 'index.php') {
        return 'accueil';
    } else if (filename === 'connexion.html') {
        return 'connexion';
    } else if (filename === 'inscription.html') {
        return 'inscription';
    } else if (filename === 'categories.php' || filename.includes('category')) {
        return 'categories';
    } else if (path.includes('/pages/')) {
        return 'pages';
    }
    
    return 'accueil';
}

function creerHeaderHTML(pageActuelle, estConnecte = false) {
    const estDansPages = pageActuelle !== 'accueil';
    const prefixe = estDansPages ? '../' : '';

    let headerHTML = `
        <div id="logo-div">
            <a href="${prefixe}index.html">
                <img src="${prefixe}assets/header_logo.png" alt="Logo Lend&Share" />
            </a>
            <h1 class="logo-text">Lend&Share</h1>
        </div>
    `;

    headerHTML += '<nav id="nav-principale">';

    switch (pageActuelle) {
        case 'accueil':
            headerHTML += `<a href="pages/categories.php" class="bouton-catalogue"><span class="texte-complet">Accès au Catalogue Complet</span><span class="texte-court">Catalogue</span></a>`;
            break;

        case 'categories':
        case 'pages':
            headerHTML += `<a href="${prefixe}index.html" class="bouton-catalogue">Accueil</a>`;
            break;

        case 'connexion':
            headerHTML += `
                <a href="${prefixe}index.html" class="bouton-catalogue">Accueil</a>
                <a href="categories.php" class="bouton-catalogue">Catalogue</a>
            `;
            break;

        case 'inscription':
            headerHTML += `
                <a href="${prefixe}index.html" class="bouton-catalogue">Accueil</a>
                <a href="categories.php" class="bouton-catalogue">Catalogue</a>
            `;
            break;
    }

    headerHTML += '</nav>';
    headerHTML += '<div id="boutons-auth">';

    if (estConnecte) {
        headerHTML += `
            <a href="${prefixe}pages/mon_compte.html" class="bouton-secondaire bouton-desktop">Mon compte</a>
            <button onclick="deconnexion()" class="bouton-principal bouton-desktop">Déconnexion</button>

            <div class="menu-compte-mobile">
                <button class="bouton-compte-mobile" onclick="toggleMenuCompteMobile(event)">
                    <i class="fas fa-user" style="font-size: 1.2rem;"></i>
                </button>
                <div class="menu-deroulant-mobile" id="menu-compte-mobile">
                    <a href="${prefixe}pages/mon_compte.html">Mon compte</a>
                    <button onclick="deconnexion()">Déconnexion</button>
                </div>
            </div>
        `;
    } else {
        switch (pageActuelle) {
            case 'accueil':
            case 'categories':
            case 'pages':
                headerHTML += `
                    <a href="${prefixe}pages/inscription.html" class="bouton-secondaire bouton-desktop">Inscription</a>
                    <a href="${prefixe}pages/connexion.html" class="bouton-principal bouton-desktop">Connexion</a>

                    <div class="menu-compte-mobile">
                        <button class="bouton-compte-mobile" onclick="toggleMenuCompteMobile(event)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="menu-deroulant-mobile" id="menu-compte-mobile">
                            <a href="${prefixe}pages/inscription.html">Inscription</a>
                            <a href="${prefixe}pages/connexion.html">Connexion</a>
                        </div>
                    </div>
                `;
                break;

            case 'connexion':
                headerHTML += `
                    <a href="inscription.html" class="bouton-secondaire bouton-desktop">Inscription</a>

                    <div class="menu-compte-mobile">
                        <button class="bouton-compte-mobile" onclick="toggleMenuCompteMobile(event)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="menu-deroulant-mobile" id="menu-compte-mobile">
                            <a href="inscription.html">Inscription</a>
                        </div>
                    </div>
                `;
                break;

            case 'inscription':
                headerHTML += `
                    <a href="connexion.html" class="bouton-principal bouton-desktop">Connexion</a>

                    <div class="menu-compte-mobile">
                        <button class="bouton-compte-mobile" onclick="toggleMenuCompteMobile(event)">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </button>
                        <div class="menu-deroulant-mobile" id="menu-compte-mobile">
                            <a href="connexion.html">Connexion</a>
                        </div>
                    </div>
                `;
                break;
        }
    }

    headerHTML += '</div>';

    return headerHTML;
}

function toggleMenuCompteMobile(event) {
    event.stopPropagation();
    const menu = document.getElementById('menu-compte-mobile');
    menu.classList.toggle('ouvert');
}

// Fermer le menu si on clique ailleurs
document.addEventListener('click', function() {
    const menu = document.getElementById('menu-compte-mobile');
    if (menu) {
        menu.classList.remove('ouvert');
    }
});

async function verifierStatutConnexion() {
    try {
        const prefixe = window.location.pathname.includes('/pages/') ? '../' : '';
        const response = await fetch(`${prefixe}backend/api/api_verifier_session.php`);
        const data = await response.json();
        return data.connecte || false;
    } catch (error) {
        console.log('Impossible de vérifier le statut de connexion:', error);
        return false;
    }
}

async function deconnexion() {
    try {
        const prefixe = window.location.pathname.includes('/pages/') ? '../' : '';
        const response = await fetch(`${prefixe}backend/api/api_deconnexion.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.succes) {
            window.location.href = prefixe + 'index.html';
        } else {
            alert('Erreur lors de la déconnexion');
        }
    } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
        alert('Erreur lors de la déconnexion');
    }
}
