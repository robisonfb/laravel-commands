# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {YOUR_TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

You can obtain your token by logging in through the <code>POST api/v1/auth/login</code> endpoint. The token must be sent in the Authorization header as <code>Bearer {token}</code>.
