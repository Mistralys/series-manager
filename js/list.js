"use strict";

/**
 */
function filterList()
{
    const terms = parseTerms($('#list-filter').val());
    const favsOnly = $('#list-favorites').prop('checked');

    $.each($('#series-list .list-row'), function(idx, row) {
        checkVisibility($(row), terms, favsOnly);
    });
}

/**
 *
 * @param {jQuery} row
 * @param {String[]} terms
 * @param {Bool} favsOnly
 */
function checkVisibility(row, terms, favsOnly)
{
    const haystack = row.attr('data-search-text');

    let visible = true;

    if (favsOnly && !row.hasClass('favorite')) {
        visible = false;
    }

    if(visible && terms.length > 0) {
        let found = false;
        for (let i = 0; i < terms.length; i++) {
            if (visible && haystack.search(new RegExp(terms[i], "i")) >= 0) {
                found = true;
            }
        }

        if(!found) {
            visible = false;
        }
    }

    if (visible) {
        row.show();
    } else {
        row.hide();
    }
}

/**
 * @param {String} searchTerms
 * @returns {String[]}
 */
function parseTerms(searchTerms)
{
    let terms = [];
    const parts = searchTerms.split(" ");

    for(let i=0; i < parts.length; i++)
    {
        let term = parts[i].trim();
        if(term.length <= 1) {
            continue;
        }

        terms.push(term);
    }

    return terms;
}
