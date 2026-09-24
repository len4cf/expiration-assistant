# expiration assistant

i live with my partner and we often forget to tell each other when we opened a jar of tomato sauce or any other food that expires quickly.
so we end up wasting a lot of food.

so i built this basic rest api to eventually power an alexa skill, so we can simply ask, "alexa when did we open this tomato sauce" and waste less food at home.

### current features

- register products with name, expiry date, quantity, opening date and storage location (fridge, freezer, pantry…).
- list products, soonest to expire first, filtered by status (active, consumed or discarded).
- check what's expiring soon. you choose how many days ahead to look (default 3), and products that have already expired are included.
- view a single product, with days until expiry and whether it's already expired.
- mark products as consumed or discarded, with the date it happened recorded for future waste history.

### planned

- alexa skill integration
