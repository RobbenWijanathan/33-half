<?php

return [
    // Fixed storefront rate, expressed as rupiah per US dollar.
    'idr_per_usd' => (float) env('STOREFRONT_IDR_PER_USD', 16000),
    'delivery_fee' => (int) env('STOREFRONT_DELIVERY_FEE', 25000),
    'pickup_location' => env('STOREFRONT_PICKUP_LOCATION', '33½ Records — contact the store to arrange a pickup'),
    'pickup_instructions' => env('STOREFRONT_PICKUP_INSTRUCTIONS', 'Bring your order number and photo ID. Wait for the ready-for-pickup update before visiting.'),
    'payment_instructions' => env('STOREFRONT_PAYMENT_INSTRUCTIONS', 'Local demo: contact the store with your order number. An admin must verify your manual payment before fulfillment.'),
];
