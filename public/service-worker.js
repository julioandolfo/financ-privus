/**
 * Service Worker para Web Push Notifications
 */

const CACHE_NAME = 'financeiro-v1';

// Instalação do Service Worker
self.addEventListener('install', (event) => {
    console.log('Service Worker: Instalado');
    self.skipWaiting();
});

// Ativação do Service Worker
self.addEventListener('activate', (event) => {
    console.log('Service Worker: Ativado');
    event.waitUntil(clients.claim());
});

// Recebimento de Push Notification
self.addEventListener('push', (event) => {
    console.log('Service Worker: Push recebido');
    
    let data = {
        title: 'Sistema Financeiro',
        body: 'Nova notificação',
        icon: '/assets/images/logo-icon.png',
        badge: '/assets/images/badge.png',
        url: '/'
    };
    
    if (event.data) {
        try {
            data = { ...data, ...event.data.json() };
        } catch (e) {
            data.body = event.data.text();
        }
    }
    
    const options = {
        body: data.body,
        icon: data.icon || '/assets/images/logo-icon.png',
        badge: data.badge || '/assets/images/badge.png',
        vibrate: [100, 50, 100],
        data: {
            url: data.url || '/',
            dateOfArrival: Date.now()
        },
        actions: [
            {
                action: 'open',
                title: 'Ver'
            },
            {
                action: 'close',
                title: 'Fechar'
            }
        ],
        requireInteraction: false,
        tag: data.tag || 'default'
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});

// Clique na notificação
self.addEventListener('notificationclick', (event) => {
    console.log('Service Worker: Notificação clicada');
    
    event.notification.close();
    
    if (event.action === 'close') {
        return;
    }
    
    const url = event.notification.data.url || '/';
    
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                // Verifica se já existe uma janela aberta
                for (let client of windowClients) {
                    if (client.url.includes(self.location.origin) && 'focus' in client) {
                        client.navigate(url);
                        return client.focus();
                    }
                }
                // Abre nova janela se não existir
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

// Fechamento da notificação
self.addEventListener('notificationclose', (event) => {
    console.log('Service Worker: Notificação fechada');
});

// Sincronização em background
self.addEventListener('sync', (event) => {
    console.log('Service Worker: Sync event');
    
    if (event.tag === 'sync-notifications') {
        event.waitUntil(syncNotifications());
    }
});

async function syncNotifications() {
    try {
        const response = await fetch('/notificacoes/contar');
        const data = await response.json();
        
        if (data.count > 0) {
            self.registration.showNotification('Sistema Financeiro', {
                body: `Você tem ${data.count} notificações não lidas`,
                icon: '/assets/images/logo-icon.png',
                badge: '/assets/images/badge.png'
            });
        }
    } catch (error) {
        console.error('Erro ao sincronizar notificações:', error);
    }
}
