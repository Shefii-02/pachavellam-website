<?php

return [
    /*
     | Address of the Node.js staff chat server (the chat-server folder next to web), as the BROWSER reaches it.
     | Local: http://localhost:4100   Live: https://chat.yourdomain.com (the same JWT_SECRET must be in chat-server/.env)
     */
    'server_url' => rtrim(env('CHAT_SERVER_URL', 'http://localhost:4100'), '/'),
];
