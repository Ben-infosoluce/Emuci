export function truncate(str, n) {
    return str.length > n ? str.substr(0, n - 2) : str;
}

export function dateTransformer(date) {
    const dateParts = date.split('-');
    const jour = dateParts[2];
    const mois = dateParts[1];
    const annee = dateParts[0];

    return `${jour}-${mois}-${annee}`;
}
//
export function dateFormater(date) {
    const date_parts = date.split('T');
    const dateData = date_parts[0].split('-');
    const jour = dateData[2];
    const mois = dateData[1];
    const annee = dateData[0];
    return `${jour}-${mois}-${annee}`;
}
//created _at date formater
export function createdDateFormater(date) {
    const date_parts = date.split(' ');
    const dateData = date_parts[0].split('-');
    const jour = dateData[2];
    const mois = dateData[1];
    const annee = dateData[0];
    return `${jour}-${mois}-${annee}`;
}
export function replaceDateRange(input) {
    let replaced = input.replace(/\//g, '_');
    replaced = replaced.replace(/-/g, '-');
    return replaced;
}

export function returnBack() {
    window.history.back();
}
//limite string to length
export function limiteString(str, length) {
    if (str.length > length) {
        return str.substring(0, length) + '...';
    } else {
        return str;
    }
}