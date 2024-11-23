// popup for property valuation
document.querySelectorAll('.pop-pv a').forEach((el) => {
    el.addEventListener('click', (el) => {
        el.preventDefault();
        bricksOpenPopup(655); // replace this value with the post id of the popup template
    });
});
// popup for mortgage calculator
document.querySelectorAll('.pop-mc a').forEach((el) => {
    el.addEventListener('click', (el) => {
        el.preventDefault();
        bricksOpenPopup(650); // replace this value with the post id of the popup template
    });
});
