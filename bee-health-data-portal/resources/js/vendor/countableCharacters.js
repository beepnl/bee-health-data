export default function countableCharacters() {
    var elements = document.querySelectorAll("input[maxlength]:not([aria-countable]),textarea[maxlength]:not([aria-countable])");

    function getLengths(element) {
        var maxLength = parseInt(element.getAttribute('maxlength'));
        var curruntLength = parseInt(element.value.length);
        return { maxLength, curruntLength };
    }

    function remainCharacters(e, element) {
        if (element.nextElementSibling.getAttribute('aria-countable-text') != null) {
            const { maxLength, curruntLength } = getLengths(element);
            element.nextElementSibling.innerHTML = 'maximum charachers: ' + curruntLength + '/' + maxLength;
        }
    }

    elements.forEach(function (element) {
        if (element.nextElementSibling == null || element.nextElementSibling.getAttribute('aria-countable-text') === null) {
            const { maxLength, curruntLength } = getLengths(element);
            var spanElement = document.createElement("small");
            spanElement.setAttribute('aria-countable-text', true);
            spanElement.setAttribute('class', 'form-text text-muted');
            spanElement.innerHTML = 'maximum charachers: ' + curruntLength + '/' + maxLength;
            element.parentNode.insertBefore(spanElement, element.nextSibling);
            element.setAttribute('aria-countable', true);
            element.addEventListener('keyup', e => { remainCharacters(e, element) });
        }
    });
}
