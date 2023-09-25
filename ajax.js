jQuery(document).ready(function($) {

    $('#histographai-form').submit(function(e) {
        e.preventDefault();
        submitHistographaiForm();
    });

});


function generateSocialShareLinks(year) {
    const currentPage = window.location.href.split('?')[0]; // Gets the current page URL without any query parameters
    const cleanPage = currentPage.split('#')[0];  // Remove any existing hash
    const shareUrl = `${cleanPage}?histo_year=${year}`;
    const encodedShareUrl = encodeURIComponent(shareUrl); // Encode the entire URL
    const shareText = `Check out the AI summary for the year ${year}!`;
    const encodedShareText = encodeURIComponent(shareText); // Encode the share text

    return {
        direct: shareUrl,
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodedShareUrl}`,
        twitter: `https://twitter.com/intent/tweet?url=${encodedShareUrl}&text=${encodedShareText}`,
        linkedin: `https://www.linkedin.com/shareArticle?mini=true&url=${encodedShareUrl}&title=${encodeURIComponent(document.title)}&summary=${encodedShareText}`
    };
}


function submitHistographaiForm() {
    var year = jQuery('#year').val();

    jQuery.ajax({
        type: "POST",
        url: frontendajax.ajaxurl,
        data: {
            action: "fetch_year_summary",
            year: year
        },
        success: function(response) {
            jQuery('#histographai-result').html(response);
            var $table = jQuery('#histographai-result').find('table');
            if ($table.length) {
                typeTableContent($table);
            } else {
                // Handle non-table content, if necessary
            }


            // Generate share links and append them below the summary
            const shareLinks = generateSocialShareLinks(year);
            const shareHtml = `                              
                    <div class="social-sharing text-center text-md-start">
                        <p>Share this amazing year in history!</p>
                        <div class="share-buttons d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                            <a href="${shareLinks.facebook}" target="_blank"  class="btn btn-sm wp-element-button"><i class="fab fa-facebook-f"></i> Facebook</a>
                            <a href="${shareLinks.twitter}" target="_blank" class="btn btn-sm wp-element-button"><i class="fab fa-twitter"></i> Twitter</a>
                            <a href="${shareLinks.linkedin}" target="_blank" class="btn btn-sm wp-element-button"><i class="fab fa-linkedin-in"></i> LinkedIn</a>
                            <a href="${shareLinks.direct}" target="_blank" id="copy-link" class="btn btn-sm wp-element-button"><i class="fas fa-link"></i> Direct Link</a>
                        </div>
                    </div>
                    
                `;
            jQuery('#histographai-sharing-links').html(shareHtml).removeClass("d-none");


        },
        error: function() {
            jQuery('#histographai-result').html('An error occurred.');
        }
    });
}

function typeTableContent($table) {
    // Fetch all cells and set their data-content attributes
    const cells = $table.find('th, td').toArray().map(cellElement => {
        const $cell = jQuery(cellElement);
        const cellText = $cell.text();

        const placeholder = '<span style="visibility: hidden;">' + cellText + '</span>';
        $cell.attr('data-content', cellText).html(placeholder);

        return $cell;
    });

    function typeContentIntoCell($cell, callback) {
        const content = $cell.attr('data-content') || '';
        let text = '';
        let charIndex = 0;
        const typingSpeed = 25;

        function typeChar() {
            if (charIndex < content.length) {
                text += content.charAt(charIndex);
                $cell.html(text);
                charIndex++;
                setTimeout(typeChar, typingSpeed);
            } else if (callback) {
                callback();
            }
        }

        typeChar();
    }

    function typeNextCell(index) {
        if (index < cells.length) {
            typeContentIntoCell(cells[index], function() {
                typeNextCell(index + 1);
            });
        }
    }

    typeNextCell(0);
}
