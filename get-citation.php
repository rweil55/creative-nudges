    <?php
	class creative_nudges_get_Citation {
    private static $errorBeg= "<span style='color:red; font-weight:bold;'>";
    private static $errorEnd= "</span>";
    private static $eol = "<br/>\n";
		// (Legacy fromDOI removed; using unified enhanced version below)
		public static function fromDOI( $doi, $style = 'chicago-note-bibliography' ) {
			$doi = trim( $doi );
			if ( $doi === '' ) return creative_nudges::$errorBeg . 'Empty DOI.' . creative_nudges::$errorEnd;
			// Always fetch structured JSON from CrossRef for flexible formatting
			$apiUrl = 'https://api.crossref.org/works/' . rawurlencode( $doi );
			$json = @file_get_contents( $apiUrl );
			if ( $json === false ) {
				return creative_nudges::$errorBeg . 'CrossRef lookup failed for DOI ' . htmlspecialchars( $doi ) . creative_nudges::$errorEnd;
			}
			$data = json_decode( $json, true );
			$item = $data['message'] ?? array();
			$title = isset( $item['title'][0] ) ? $item['title'][0] : '';
			$container = isset( $item['container-title'][0] ) ? $item['container-title'][0] : '';
			$issuedYear = '';
			if ( isset( $item['issued']['date-parts'][0][0] ) ) $issuedYear = $item['issued']['date-parts'][0][0];
			$authors = array();
			if ( ! empty( $item['author'] ) && is_array( $item['author'] ) ) {
				foreach ( $item['author'] as $a ) {
					$given = $a['given'] ?? '';
					$family = $a['family'] ?? '';
					$name = trim( $family . ', ' . $given );
					$authors[] = $name;
				}
			}
			$urlDoi = 'https://doi.org/' . $doi;
			// MLA formatting fallback (article / generic)
			if ( strtolower( $style ) === 'mla' ) {
				$mlaAuthors = self::formatMlaAuthors( $authors );
				// Title in quotes if article/journal present
				$titleSeg = $title !== '' ? '"' . $title . '."' : '';
				$containerSeg = $container !== '' ? ' ' . $container . ',' : '';
				$yearSeg = $issuedYear !== '' ? ' ' . $issuedYear . ',' : '';
				$citation = trim( self::joinWithSpaces( array( $mlaAuthors . '.', $titleSeg, $containerSeg, $yearSeg, $urlDoi ) ) );
				return $citation;
			}
			// Otherwise use CrossRef content negotiation for Chicago if available
			$context = stream_context_create( [ 'http' => [ 'header' => 'Accept: text/x-bibliography; style=' . $style . "\r\n" ] ] );
			$bib = @file_get_contents( 'https://doi.org/' . $doi, false, $context );
			if ( $bib === false ) {
				// Fallback Chicago minimal
				$chAuthors = self::formatChicagoAuthors( array_map( function ( $n ) { return $n; }, $authors ) );
				$parts = array();
				if ( $chAuthors ) $parts[] = $chAuthors . '.';
				if ( $title ) $parts[] = $title . '.';
				if ( $container ) $parts[] = $container . '.';
				if ( $issuedYear ) $parts[] = $issuedYear . '.';
				$parts[] = $urlDoi;
				return self::joinWithSpaces( $parts );
			}
			return trim( $bib );
		}
		public static function fromIsbn( $isbn, $style = 'chicago-note-bibliography' ) {
			try {
				$isbn = trim( $isbn ?? '' );
				if ( $isbn === '' ) {
					return self::$errorBeg . "Missing ISBN." . self::$errorEnd;
				}
				// keep only digits and X
				$isbnClean = preg_replace( '/[^0-9Xx]/', '', $isbn );
				if ( $isbnClean === '' ) {
					return self::$errorBeg . "Invalid ISBN: $isbn" . self::$errorEnd;
				}
				$url = "https://openlibrary.org/api/books?bibkeys=ISBN:" . urlencode( $isbnClean ) . "&format=json&jscmd=data";
				$json = @file_get_contents( $url );
				if ( $json === false ) {
					return self::$errorBeg . "Lookup failed for ISBN $isbnClean." . self::$errorEnd;
				}
				$data = json_decode( $json, true );
				if ( ! is_array( $data ) || empty( $data[ "ISBN:$isbnClean" ] ) ) {
					return self::$errorBeg . "No metadata found for ISBN $isbnClean." . self::$errorEnd;
				}
				$book = $data[ "ISBN:$isbnClean" ]; // Open Library data object
				// Title and optional subtitle
				$title = isset( $book['title'] ) ? trim( $book['title'] ) : '';
				$subtitle = isset( $book['subtitle'] ) ? trim( $book['subtitle'] ) : '';
				$fullTitle = $title;
				if ( $subtitle !== '' )
					$fullTitle .= ": $subtitle";
				// Authors
				$authorNames = array();
				if ( ! empty( $book['authors'] ) && is_array( $book['authors'] ) ) {
					foreach ( $book['authors'] as $a ) {
						if ( is_array( $a ) && ! empty( $a['name'] ) )
							$authorNames[] = $a['name'];
						elseif ( is_string( $a ) )
							$authorNames[] = $a;
					}
				}
				$authorsChicago = self::formatChicagoAuthors( $authorNames );
				// Publisher (take first)
				$publisher = '';
				if ( ! empty( $book['publishers'] ) && is_array( $book['publishers'] ) ) {
					$p = $book['publishers'][0];
					if ( is_array( $p ) && ! empty( $p['name'] ) )
						$publisher = $p['name'];
					elseif ( is_string( $p ) )
						$publisher = $p;
				}
				// Place (if present)
				$place = '';
				if ( ! empty( $book['publish_places'] ) && is_array( $book['publish_places'] ) ) {
					$pl = $book['publish_places'][0];
					if ( is_array( $pl ) && ! empty( $pl['name'] ) )
						$place = $pl['name'];
					elseif ( is_string( $pl ) )
						$place = $pl;
				}
				// Year from publish_date
				$year = '';
				if ( ! empty( $book['publish_date'] ) && is_string( $book['publish_date'] ) ) {
					if ( preg_match( '/\b(1[0-9]{3}|20[0-9]{2})\b/', $book['publish_date'], $m ) ) {
						$year = $m[1];
					}
				}
				// MLA format: Last, First, et al. Title: Subtitle. Publisher, Year.
				if ( strtolower( $style ) === 'mla' ) {
					$mlaAuthors = self::formatMlaAuthors( $authorNames );
					$parts = array();
					if ( $mlaAuthors ) $parts[] = $mlaAuthors . '.';
					if ( $fullTitle ) $parts[] = $fullTitle . '.';
					$pubYear = '';
					if ( $publisher && $year ) $pubYear = $publisher . ', ' . $year . '.';
					elseif ( $publisher ) $pubYear = $publisher . '.';
					elseif ( $year ) $pubYear = $year . '.';
					if ( $pubYear ) $parts[] = $pubYear;
					return trim( self::joinWithSpaces( $parts ) );
				}
				// Chicago variants
				if ( $style === 'chicago-author-date' ) {
					$parts = array();
					if ( $authorsChicago ) $parts[] = $authorsChicago;
					if ( $year ) $parts[] = $year . '.';
					if ( $fullTitle ) $parts[] = $fullTitle . '.';
					if ( $publisher ) $parts[] = $publisher . '.';
					return trim( self::joinWithSpaces( $parts ) );
				}
				$parts = array();
				if ( $authorsChicago ) $parts[] = $authorsChicago . '.';
				if ( $fullTitle ) $parts[] = $fullTitle . '.';
				$pubPart = '';
				if ( $place && $publisher && $year ) $pubPart = "$place: $publisher, $year.";
				elseif ( $publisher && $year ) $pubPart = "$publisher, $year.";
				elseif ( $publisher ) $pubPart = $publisher . '.';
				elseif ( $year ) $pubPart = $year . '.';
				if ( $pubPart ) $parts[] = $pubPart;
				return trim( self::joinWithSpaces( $parts ) );
			} catch (Exception $e) {
				return self::$errorBeg . "Error: " . $e->getMessage() . self::$errorEnd;
			}
		}
		private static function formatChicagoAuthors( $names ) {
			if ( empty( $names ) || ! is_array( $names ) )
				return '';
			$count = count( $names );
			// Normalize "Last, First" form for each
			$norm = array();
			foreach ( $names as $n ) {
				$norm[] = self::invertNameToChicago( $n );
			}
			if ( $count === 1 )
				return $norm[0];
			if ( $count === 2 )
				return $norm[0] . ", and " . self::nameToRegular( $names[1] );
			if ( $count === 3 )
				return $norm[0] . ", " . self::nameToRegular( $names[1] ) . ", and " . self::nameToRegular( $names[2] );
			// 4 or more
			return $norm[0] . ", et al.";
		}
		private static function invertNameToChicago( $name ) {
			$name = trim( $name );
			if ( $name === '' )
				return '';
			// If already "Last, First", keep
			if ( strpos( $name, ',' ) !== false )
				return $name;
			$parts = preg_split( '/\s+/', $name );
			if ( count( $parts ) === 1 )
				return $name;
			$last = array_pop( $parts );
			$firsts = implode( ' ', $parts );
			return $last . ", " . $firsts;
		}
		private static function nameToRegular( $name ) {
			$name = trim( $name );
			if ( $name === '' )
				return '';
			if ( strpos( $name, ',' ) !== false ) {
				list( $last, $firsts ) = array_map( 'trim', explode( ',', $name, 2 ) );
				return $firsts . ' ' . $last;
			}
			return $name;
		}
		private static function joinWithSpaces( $parts ) {
			return preg_replace( '/\s+/', ' ', trim( implode( ' ', array_filter( $parts, function ( $p ) {
				return trim( $p ) !== ''; } ) ) ) );
		}
			// MLA author formatting: First Last, First Last, and First Last (<=3) / First Last et al. (>3)
			private static function formatMlaAuthors( $names ) {
				if ( empty( $names ) || ! is_array( $names ) ) return '';
				$clean = array();
				foreach ( $names as $n ) {
					$n = trim( $n );
					if ( $n === '' ) continue;
					// If "Last, First" convert to "First Last" for second+ authors in <=2 case
					if ( strpos( $n, ',' ) !== false ) {
						list( $last, $firsts ) = array_map( 'trim', explode( ',', $n, 2 ) );
						$clean[] = $firsts . ' ' . $last;
					} else {
						$clean[] = $n;
					}
				}
				$count = count( $clean );
				if ( $count === 0 ) return '';
				// First author in MLA presented Last, First
				$first = $clean[0];
				if ( strpos( $first, ' ' ) !== false ) {
					$parts = preg_split( '/\s+/', $first );
					$last = array_pop( $parts );
					$firstReversed = $last . ', ' . implode( ' ', $parts );
				} else { $firstReversed = $first; }
				if ( $count === 1 ) return $firstReversed;
				if ( $count === 2 ) return $firstReversed . ', and ' . $clean[1];
				if ( $count === 3 ) return $firstReversed . ', ' . $clean[1] . ', and ' . $clean[2];
				return $firstReversed . ' et al.';
			}
		// --- Lookup by title: find DOI(s) via CrossRef and ISBN(s) via OpenLibrary ---
		public static function fromTitle( $title, $limit = 5 ) {
			$title = trim( $title ?? '' );
			if ( $title === '' ) {
				return self::$errorBeg . 'Title missing Italics' . self::$errorEnd;
			}
			$limit = intval( $limit ); if ( $limit < 1 ) $limit = 5; if ( $limit > 25 ) $limit = 25;
			$html = "<div class='citation-title-lookup'>";
			$html .= '<strong>Query Title:</strong> ' . htmlspecialchars( $title ) . '<br/>';
			$dois = array(); $isbns = array(); $notes = array();
			// CrossRef DOI search
			$crUrl = 'https://api.crossref.org/works?rows=' . $limit . '&query.title=' . rawurlencode( $title );
			$crJson = @file_get_contents( $crUrl );
			if ( $crJson !== false ) {
				$cr = json_decode( $crJson, true );
				if ( isset( $cr['message']['items'] ) && is_array( $cr['message']['items'] ) ) {
					foreach ( $cr['message']['items'] as $item ) {
						if ( ! empty( $item['DOI'] ) ) $dois[] = $item['DOI'];
					}
					$dois = array_values( array_unique( $dois ) );
				} else {
					$notes[] = 'CrossRef: no items.';
				}
			} else {
				$notes[] = 'CrossRef request failed.';
			}
			// OpenLibrary ISBN search
			$olUrl = 'https://openlibrary.org/search.json?limit=' . $limit . '&title=' . rawurlencode( $title );
			$olJson = @file_get_contents( $olUrl );
			if ( $olJson !== false ) {
				$ol = json_decode( $olJson, true );
				if ( isset( $ol['docs'] ) && is_array( $ol['docs'] ) ) {
					foreach ( $ol['docs'] as $doc ) {
						if ( ! empty( $doc['isbn'] ) && is_array( $doc['isbn'] ) ) {
							foreach ( $doc['isbn'] as $isbn ) {
								if ( ! in_array( $isbn, $isbns ) ) $isbns[] = $isbn;
							}
						}
					}
				} else {
					$notes[] = 'OpenLibrary: no docs.';
				}
			} else {
				$notes[] = 'OpenLibrary request failed.';
			}
			// Output DOIs
			if ( ! empty( $dois ) ) {
				$html .= '<strong>DOI candidates:</strong><ul>';
				foreach ( $dois as $d ) $html .= '<li>' . htmlspecialchars( $d ) . '</li>';
				$html .= '</ul>';
			} else {
				$html .= '<strong>DOI candidates:</strong> none<br/>';
			}
			// Output ISBNs
			if ( ! empty( $isbns ) ) {
				$maxIsbnShow = min( 15, count( $isbns ) );
				$html .= '<strong>ISBN candidates:</strong><ul>';
				for ( $i = 0; $i < $maxIsbnShow; $i++ ) $html .= '<li>' . htmlspecialchars( $isbns[$i] ) . '</li>';
				$html .= '</ul>';
			} else {
				$html .= '<strong>ISBN candidates:</strong> none<br/>';
			}
			// Notes
			if ( ! empty( $notes ) ) {
				$html .= '<strong>Notes:</strong><ul>';
				foreach ( $notes as $n ) $html .= '<li>' . htmlspecialchars( $n ) . '</li>';
				$html .= '</ul>';
			}
			// Provide first citation (prefers DOI)
			if ( ! empty( $dois ) ) {
				$first = $dois[0];
				$cit = self::fromDOI( $first );
				$html .= '<div><em>First DOI citation:</em><br/>' . nl2br( htmlspecialchars( $cit ) ) . '</div>';
			} elseif ( ! empty( $isbns ) ) {
				$firstI = $isbns[0];
				$citI = self::fromIsbn( $firstI );
				$html .= '<div><em>First ISBN citation:</em><br/>' . nl2br( htmlspecialchars( $citI ) ) . '</div>';
			} else {
				$html .= '<div>' . creative_nudges::$errorBeg . 'No doi or ISBN could be found for title' . creative_nudges::$errorEnd;
			}
			$html .= '</div>';
			return $html;
		} // end function fromTitle
public static function generateMlaWebsiteCitation(
    $authorLastName,
    $authorFirstName,
    $pageTitle,
    $websiteName,
    $publisher = null, // Optional
    $publicationDate = null, // Format: 'Day Month Year'
    $url = "",
    $accessDate = null // Format: 'Day Month Year'
) {
    $citationParts = [];
    // Author
    if (!empty($authorLastName) && !empty($authorFirstName)) {
        $citationParts[] = $authorLastName . ', ' . $authorFirstName . '.';
    } elseif (!empty($authorLastName)) { // For organizational authors
        $citationParts[] = $authorLastName . '.';
    }
    // Page Title
    $citationParts[] = '"' . $pageTitle . '."';
    // Website Name
    $citationParts[] = $websiteName . ',';
    // Publisher (if different from website name or author)
    if (!empty($publisher) && $publisher !== $websiteName && $publisher !== $authorLastName) {
        $citationParts[] = $publisher . ',';
    }
    // Publication Date
    if (!empty($publicationDate)) {
        $citationParts[] = $publicationDate . ',';
    }
    // URL
    $citationParts[] = $url . '.';
    // Access Date (if provided)
    if (!empty($accessDate)) {
        $citationParts[] = 'Accessed ' . $accessDate . '.';
    }
    return implode(' ', $citationParts);
}
/*
// Example Usage:
$citation = generateMlaWebsiteCitation(
    'Smith',
    'John',
    'How to Write a Great Essay',
    'The Writing Hub',
    null, // No separate publisher
    '15 May 2023',
    'https://www.writinghub.com/essay-guide',
    '20 Nov. 2025'
);
echo $citation;
echo "\n\n";
// Example with no author and no specific publication date, only access date
$citationNoAuthor = generateMlaWebsiteCitation(
    null,
    null,
    'Understanding PHP Basics',
    'PHP Tutorials',
    null,
    null,
    'https://www.phptutorials.com/basics',
    '20 Nov. 2025'
);
echo $citationNoAuthor;
*/
	} // end class  creative_nudges_get_Citation