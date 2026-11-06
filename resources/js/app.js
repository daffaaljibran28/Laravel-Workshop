import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const orderForm = document.querySelector('#order-form');

if (orderForm) {
    const itemsContainer = document.querySelector('#order-items');
    const itemTemplate = document.querySelector('#order-item-template');
    const orderStatus = document.querySelector('#order-status');
    const paymentAmount = document.querySelector('#payment_amount');
    const status = document.querySelector('#order-form-status');
    let nextItemIndex = itemsContainer.querySelectorAll('.order-item').length;

    document.querySelector('#add-order-item').addEventListener('click', () => {
        const row = itemTemplate.content.firstElementChild.cloneNode(true);

        row.querySelectorAll('[name]').forEach((input) => {
            input.name = input.name.replaceAll('__INDEX__', String(nextItemIndex));
        });

        nextItemIndex += 1;
        itemsContainer.append(row);
    });

    itemsContainer.addEventListener('click', (event) => {
        if (event.target.closest('.remove-order-item') && itemsContainer.querySelectorAll('.order-item').length > 1) {
            event.target.closest('.order-item').remove();
        }
    });

    orderForm.addEventListener('click', (event) => {
        const submitter = event.target.closest('[data-order-status]');

        if (submitter) {
            orderStatus.value = submitter.dataset.orderStatus;
        }
    });

    orderForm.addEventListener('change', (event) => {
        if (!event.target.matches('.order-product')) {
            return;
        }

        const productSelects = [...itemsContainer.querySelectorAll('.order-product')];

        productSelects.forEach((select) => {
            const duplicate = select.value && productSelects.some((other) => other !== select && other.value === select.value);
            select.setCustomValidity(duplicate
                ? 'Barang yang sama cukup ditambahkan satu kali; ubah jumlahnya di baris tersebut.'
                : '');
        });
    });

    orderForm.addEventListener('submit', async (event) => {
        event.preventDefault();

        const submitButtons = orderForm.querySelectorAll('button[type="submit"]');
        const completed = orderStatus.value === 'completed';
        paymentAmount.required = completed;
        submitButtons.forEach((button) => {
            button.disabled = true;
        });
        status.textContent = completed ? 'Menyimpan transaksi dan mengirim event...' : 'Menyimpan transaksi sebagai ditahan...';
        status.className = 'text-sm text-gray-600';

        try {
            const response = await window.axios.post(orderForm.action, new FormData(orderForm));
            status.textContent = response.data.message;
            status.className = 'text-sm text-green-700';
            orderForm.reset();
            orderStatus.value = 'completed';
            paymentAmount.required = true;
            itemsContainer.querySelectorAll('.order-item:not(:first-child)').forEach((row) => row.remove());
        } catch (error) {
            const validationErrors = error.response?.data?.errors;
            status.textContent = validationErrors
                ? Object.values(validationErrors).flat().join(' ')
                : error.response?.data?.message ?? 'Transaksi gagal disimpan. Periksa koneksi lalu coba lagi.';
            status.className = 'text-sm text-red-700';
        } finally {
            submitButtons.forEach((button) => {
                button.disabled = false;
            });
        }
    });
}

const userId = document.body.dataset.authUserId;

if (userId && window.Echo) {
    const notification = document.querySelector('#order-notification');
    const notificationMessage = document.querySelector('#order-notification-message');
    const dismissButton = document.querySelector('#dismiss-order-notification');
    const channelName = `user.${userId}`;
    const pusher = window.Echo.connector.pusher;
    let notificationTimeout;

    pusher.connection.bind('state_change', ({ previous, current }) => {
        console.info(`[Reverb] ${previous} -> ${current}`);
    });

    pusher.connection.bind('error', (error) => {
        console.error('[Reverb] Connection error:', error);
    });

    dismissButton?.addEventListener('click', () => {
        notification.classList.add('hidden');
        window.clearTimeout(notificationTimeout);
    });

    window.Echo.private(channelName)
        .subscribed(() => {
            console.info(`[Echo] Subscribed to private-${channelName}`);
        })
        .error((error) => {
            console.error(`[Echo] Could not subscribe to private-${channelName}:`, error);
        })
        .listen('.order.placed', (event) => {
            console.log('Order Placed:', event);

            notificationMessage.textContent =
                `Transaksi ${event.order_number} (Order #${event.order_id}) — Rp ${Number(event.total).toLocaleString('id-ID')} oleh ${event.user_name}`;
            notification.classList.remove('hidden');

            window.clearTimeout(notificationTimeout);
            notificationTimeout = window.setTimeout(() => {
                notification.classList.add('hidden');
            }, 6000);
        });
}
