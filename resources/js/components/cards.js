export default (cards) => ({
    cards,
    i: 0,
    flipped: false,
    get c() {
        return this.cards[this.i];
    },
    flip() {
        this.flipped = !this.flipped;
    },
    go(d) {
        this.flipped = false;
        this.i = (this.i + d + this.cards.length) % this.cards.length;
    },
});
