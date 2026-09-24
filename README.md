# expiration assistant

i live with my partner and we often forget to tell each other when we opened a jar of tomato sauce or any other food that expires quickly.
so we end up wasting a lot of food.

so i built this basic rest api to eventually power an alexa skill, so we can simply ask, "alexa when did we open this tomato sauce" and waste less food at home.

### current features

- add products with their name, expiry date, quantity, opening date, and where they're stored (fridge, freezer, pantry, etc.).
- list all products, with the ones expiring soonest first, and filter them by status (active, consumed, or discarded).
- check what's expiring soon. you can choose how many days ahead to look (3 by default), and already expired products are included too.
- view a product and see how many days it has left or if it's already expired.
- mark products as consumed or discarded and keep track of when it happened for future waste tracking.

### planned

- alexa skill integration
