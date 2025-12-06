<?php
/*
 * Nice Panel library mdify
 *
* @version 2025-07-25
 * @path repo/mdify/mdify.php
 * @include
 * @argument $sHtmlContent
 * @copyright Copyright (C) 2025  Nice Creative Co., Ltd. Team (http://www.nim.com.tw)
 * @license Nice Co., Ltd License
 */

class mdify
{
  /**
   * HTML → Markdown main conversion method
   * Converts HTML content to Markdown format
   *
   * @param string $sHtmlContent HTML content
   * @return string Markdown formatted text
   */
  public static function htmlToMarkdown($sHtmlContent)
  {
    // Remove paragraphs containing only whitespace, line breaks, or empty tags
    $sPattern = '/<p>(\s|&nbsp;|<br\s*\/?>|<strong>(\s|&nbsp;)*<\/strong>|<span[^>]*>(\s|&nbsp;)*<\/span>)*<\/p>/i';
    $sMarkdown = preg_replace($sPattern, '', $sHtmlContent);

    // Process images first to ensure proper conversion
    $sMarkdown = self::convertHtmlImages($sMarkdown);
    // Process <a> links first to avoid matching failures due to paths or encoding
    $sMarkdown = self::convertHtmlLinks($sMarkdown);
    // Process HTML tables first
    $sMarkdown = self::convertHtmlTables($sMarkdown);
    // Process nested lists first
    $sMarkdown = self::nestedLists($sMarkdown);

    // Define replacement rules, ignoring any attributes within tags
    $aReplacePatterns = [
      '/<h1[^>]*>(.*?)<\/h1>/is' => "# $1\n\n",
      '/<h2[^>]*>(.*?)<\/h2>/is' => "## $1\n\n",
      '/<h3[^>]*>(.*?)<\/h3>/is' => "### $1\n\n",
      '/<h4[^>]*>(.*?)<\/h4>/is' => "#### $1\n\n",
      '/<h5[^>]*>(.*?)<\/h5>/is' => "##### $1\n\n",
      '/<h6[^>]*>(.*?)<\/h6>/is' => "###### $1\n\n",
      '/<strong[^>]*>(.*?)<\/strong>/is' => '**$1**',
      '/<b[^>]*>(.*?)<\/b>/is' => '**$1**',
      '/<em[^>]*>(.*?)<\/em>/is' => '*$1*',
      '/<i[^>]*>(.*?)<\/i>/is' => '*$1*',
      '/<br\s*\/?>/is' => "\n",
      '/<p[^>]*>(.*?)<\/p>/is' => "$1\n\n",
      // '/<img[^>]*src=[\"\'](.*?)[\"\'][^>]*alt=[\"\'](.*?)[\"\'][^>]*>/is' => '![$2]($1)', // Handled by convertHtmlImages
    ];

    // Apply replacements step by step
    foreach ($aReplacePatterns as $k1 => $v1) {
      $sMarkdown = preg_replace($k1, $v1, $sMarkdown);
    }

    // Remove remaining HTML tags
    $sMarkdown = strip_tags($sMarkdown);

    // Remove excessive blank lines
    $sMarkdown = preg_replace("/(\n\s*){3,}/", "\n\n", $sMarkdown);

    // Return processed Markdown text
    return trim($sMarkdown);
  }

  /**
   * HTML → Markdown specialized handler for <img> images
   * Processes image tags first, converts to Markdown format, prevents removal by subsequent strip_tags
   *
   * @param string $sContent HTML content
   * @return string Converted content
   */
  private static function convertHtmlImages($sContent)
  {
    return preg_replace_callback('/<img\b[^>]*>/i', function ($aMatches) {
      $sImgTag = $aMatches[0];

      // Parse src attribute
      if (!preg_match('/src\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $sImgTag, $aSrcMatch)) {
        return '';
      }

      $sSrc = '';
      if (isset($aSrcMatch[2]) && $aSrcMatch[2] !== '') {
        $sSrc = $aSrcMatch[2];
      } elseif (isset($aSrcMatch[3]) && $aSrcMatch[3] !== '') {
        $sSrc = $aSrcMatch[3];
      } elseif (isset($aSrcMatch[4])) {
        $sSrc = $aSrcMatch[4];
      }

      // Parse alt attribute
      $sAlt = '';
      if (preg_match('/alt\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $sImgTag, $aAltMatch)) {
        if (isset($aAltMatch[2]) && $aAltMatch[2] !== '') {
          $sAlt = $aAltMatch[2];
        } elseif (isset($aAltMatch[3]) && $aAltMatch[3] !== '') {
          $sAlt = $aAltMatch[3];
        } elseif (isset($aAltMatch[4])) {
          $sAlt = $aAltMatch[4];
        }
      }

      // Decode HTML entities
      $sSrc = html_entity_decode($sSrc, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $sAlt = html_entity_decode($sAlt, ENT_QUOTES | ENT_HTML5, 'UTF-8');

      // Avoid Markdown conflicts in alt text
      $sAlt = str_replace(['[', ']'], ['\\[', '\\]'], $sAlt);

      return '![' . $sAlt . '](' . $sSrc . ')';
    }, $sContent);
  }

  /**
   * HTML → Markdown specialized handler for <a> links
   * Robustly parses href and content using callback to avoid failures due to paths, encoding, or attribute order
   *
   * @param string $sContent HTML content
   * @return string Converted content
   */
  private static function convertHtmlLinks($sContent)
  {
    return preg_replace_callback('/<a\b[^>]*>(.*?)<\/a>/is', function ($aMatches) {
      $sFullATag = $aMatches[0];
      $sInnerHtml = $aMatches[1];

      // Parse href attribute (supports double quotes, single quotes, or no quotes)
      if (!preg_match('/href\s*=\s*("([^"]*)"|\'([^\']*)\'|([^\s>]+))/i', $sFullATag, $aHrefMatch)) {
        // No href, return plain text only (remove internal tags)
        return strip_tags($sInnerHtml);
      }

      $sHref = '';
      if (isset($aHrefMatch[2]) && $aHrefMatch[2] !== '') {
        $sHref = $aHrefMatch[2];
      } elseif (isset($aHrefMatch[3]) && $aHrefMatch[3] !== '') {
        $sHref = $aHrefMatch[3];
      } elseif (isset($aHrefMatch[4])) {
        $sHref = $aHrefMatch[4];
      }

      // Inner content: preserve basic formatting (bold, italic, line breaks), remove other tags and excess whitespace
      $aInnerPatterns = [
        '/<strong[^>]*>(.*?)<\/strong>/is' => '**$1**',
        '/<b[^>]*>(.*?)<\/b>/is' => '**$1**',
        '/<em[^>]*>(.*?)<\/em>/is' => '*$1*',
        '/<i[^>]*>(.*?)<\/i>/is' => '*$1*',
        '/<br\s*\/>/is' => ' ',
        '/<p[^>]*>(.*?)<\/p>/is' => '$1 ',
      ];

      foreach ($aInnerPatterns as $sPattern => $sReplacement) {
        $sInnerHtml = preg_replace($sPattern, $sReplacement, $sInnerHtml);
      }

      $sText = strip_tags($sInnerHtml);
      $sText = preg_replace('/\s+/', ' ', $sText);
      $sText = trim($sText);

      // Avoid Markdown conflicts (exclude image syntax)
      $sText = preg_replace('/\](?!\()/', '\\]', $sText);

      // Decode common HTML entities
      $sHref = html_entity_decode($sHref, ENT_QUOTES | ENT_HTML5, 'UTF-8');

      return '[' . $sText . '](' . $sHref . ')';
    }, $sContent);
  }

  /**
   * HTML → Markdown handles nested list conversion
   * Identifies and converts HTML nested list structures to Markdown format
   *
   * @param string $sContent Content containing HTML lists
   * @return string Converted content
   */
  private static function nestedLists($sContent)
  {
    // Use stack to correctly parse nested ul/ol tags
    return self::parseNestedLists($sContent, 0);
  }

  /**
   * HTML → Markdown recursively parses nested lists
   * Uses recursion to correctly parse HTML lists containing multiple levels of nesting
   *
   * @param string $sContent HTML content
   * @param int $iLevel Current indentation level
   * @return string Converted Markdown list
   */
  private static function parseNestedLists($sContent, $iLevel)
  {
    $sResult = '';
    $iPos = 0;
    $iLength = strlen($sContent);

    while ($iPos < $iLength) {
      // Find next ul or ol tag
      $iUlPos = strpos($sContent, '<ul', $iPos);
      $iOlPos = strpos($sContent, '<ol', $iPos);

      // Determine the nearest list tag
      $iListPos = false;
      $sListType = '';

      if ($iUlPos !== false && ($iOlPos === false || $iUlPos < $iOlPos)) {
        $iListPos = $iUlPos;
        $sListType = 'ul';
      } elseif ($iOlPos !== false) {
        $iListPos = $iOlPos;
        $sListType = 'ol';
      }

      if ($iListPos === false) {
        // No more lists found, add remaining content
        $sResult .= substr($sContent, $iPos);
        break;
      }

      // Add content before the list
      $sResult .= substr($sContent, $iPos, $iListPos - $iPos);

      // Find corresponding closing tag
      $sStartTag = '<' . $sListType;
      $sEndTag = '</' . $sListType . '>';

      // Find complete list structure
      $aListData = self::extractCompleteList($sContent, $iListPos, $sListType);

      if ($aListData) {
        // Convert list content
        $sListMarkdown = self::convertListToMarkdown($aListData['content'], $iLevel, $sListType);
        $sResult .= $sListMarkdown;
        $iPos = $aListData['end_pos'];
      } else {
        // If parsing fails, skip this tag
        $iPos = $iListPos + strlen($sStartTag);
      }
    }

    return $sResult;
  }

  /**
   * HTML → Markdown extracts complete list structure
   * Uses stack-based approach to match start and end tags, extracts complete structure including nested lists
   *
   * @param string $sContent HTML content
   * @param int $iStartPos List start position
   * @param string $sListType List type ('ul' or 'ol')
   * @return array|false Array containing content and end position, returns false on failure
   */
  private static function extractCompleteList($sContent, $iStartPos, $sListType)
  {
    $sStartTag = '<' . $sListType;
    $sEndTag = '</' . $sListType . '>';

    // Find the end position of the start tag
    $iTagEnd = strpos($sContent, '>', $iStartPos);
    if ($iTagEnd === false) return false;

    $iPos = $iTagEnd + 1;
    $iLevel = 1;
    $iLength = strlen($sContent);

    while ($iPos < $iLength && $iLevel > 0) {
      $iNextStart = strpos($sContent, $sStartTag, $iPos);
      $iNextEnd = strpos($sContent, $sEndTag, $iPos);

      if ($iNextEnd === false) break;

      if ($iNextStart !== false && $iNextStart < $iNextEnd) {
        // Found another start tag
        $iLevel++;
        $iPos = strpos($sContent, '>', $iNextStart) + 1;
      } else {
        // Found closing tag
        $iLevel--;
        if ($iLevel === 0) {
          // Found matching closing tag
          $sListContent = substr($sContent, $iTagEnd + 1, $iNextEnd - $iTagEnd - 1);
          return [
            'content' => $sListContent,
            'end_pos' => $iNextEnd + strlen($sEndTag)
          ];
        }
        $iPos = $iNextEnd + strlen($sEndTag);
      }
    }

    return false;
  }

  /**
   * HTML → Markdown correctly extracts li tag content
   * Handles li tags containing nested structures, ensures correct matching of start and end tags
   *
   * @param string $sListContent List content (inside ul/ol)
   * @return array Extracted li items array
   */
  private static function extractListItems($sListContent)
  {
    $aItems = [];
    $iPos = 0;
    $iLength = strlen($sListContent);

    while ($iPos < $iLength) {
      // Find next <li> tag
      $iLiStart = strpos($sListContent, '<li', $iPos);
      if ($iLiStart === false) break;

      // Find end position of <li> tag
      $iLiTagEnd = strpos($sListContent, '>', $iLiStart);
      if ($iLiTagEnd === false) break;

      // Find corresponding </li> tag
      $iPos = $iLiTagEnd + 1;
      $iLevel = 1;

      while ($iPos < $iLength && $iLevel > 0) {
        $iNextLiStart = strpos($sListContent, '<li', $iPos);
        $iNextLiEnd = strpos($sListContent, '</li>', $iPos);

        if ($iNextLiEnd === false) break;

        if ($iNextLiStart !== false && $iNextLiStart < $iNextLiEnd) {
          // Found nested <li>
          $iLevel++;
          $iPos = strpos($sListContent, '>', $iNextLiStart) + 1;
        } else {
          // Found </li>
          $iLevel--;
          if ($iLevel === 0) {
            // Found matching </li>
            $sLiContent = substr($sListContent, $iLiTagEnd + 1, $iNextLiEnd - $iLiTagEnd - 1);
            $aItems[] = [1 => $sLiContent]; // Simulate original format
            $iPos = $iNextLiEnd + 5; // Skip </li>
            break;
          }
          $iPos = $iNextLiEnd + 5;
        }
      }
    }

    return $aItems;
  }

  /**
   * HTML → Markdown converts list content to Markdown
   * Converts parsed HTML list items to Markdown list format, supports nested structures
   *
   * @param string $sListContent List content
   * @param int $iLevel Indentation level
   * @param string $sListType List type ('ul' or 'ol')
   * @return string Markdown formatted list
   */
  private static function convertListToMarkdown($sListContent, $iLevel, $sListType)
  {
    // Use correct method to parse li tags (handles nested structures)
    $aMatches = self::extractListItems($sListContent);

    $sMarkdown = '';
    $iCounter = 1;
    $sIndent = str_repeat('  ', $iLevel);

    foreach ($aMatches as $aMatch) {
      $sLiContent = $aMatch[1];

      // Check if contains nested list
      if (preg_match('/<(ul|ol)[^>]*>.*?<\/\1>/is', $sLiContent)) {
        // Separate text and nested list
        $sText = preg_replace('/<(ul|ol)[^>]*>.*?<\/\1>/is', '', $sLiContent);
        $sText = self::processListItemContent($sText);

        // Extract nested list portion
        preg_match('/<(ul|ol)[^>]*>.*?<\/\1>/is', $sLiContent, $aNestedMatch);
        $sNestedListHtml = $aNestedMatch[0];

        // Process nested list, ensure correct indentation
        $sNestedContent = self::parseNestedLists($sNestedListHtml, $iLevel + 1);

        // Generate marker
        $sMarker = ($sListType === 'ol') ? $sIndent . $iCounter . '. ' : $sIndent . '- ';
        $sMarkdown .= $sMarker . $sText . "\n" . $sNestedContent;
      } else {
        // Plain text content
        $sText = self::processListItemContent($sLiContent);
        $sMarker = ($sListType === 'ol') ? $sIndent . $iCounter . '. ' : $sIndent . '- ';
        $sMarkdown .= $sMarker . $sText . "\n";
      }

      if ($sListType === 'ol') {
        $iCounter++;
      }
    }

    return $sMarkdown;
  }

  /**
   * HTML → Markdown processes p tags within list items
   * Converts p tags inside li tags to appropriate text format, prevents formatting errors
   *
   * @param string $sContent li tag content
   * @return string Processed plain text content
   */
  private static function processListItemContent($sContent)
  {
    // Process p tags first, convert to line breaks
    $sContent = preg_replace('/<p[^>]*>(.*?)<\/p>/is', "$1\n", $sContent);

    // Remove excessive line breaks and whitespace
    $sContent = preg_replace("/\n+/", "\n", $sContent);

    // Remove other HTML tags
    $sContent = strip_tags(trim($sContent));

    // Convert line breaks to spaces (in markdown lists, multi-line content within same item is separated by spaces)
    $sContent = str_replace("\n", " ", $sContent);

    // Remove excessive spaces
    $sContent = preg_replace("/\s+/", " ", $sContent);

    return trim($sContent);
  }

  /**
   * HTML → Markdown converts HTML tables to Markdown tables
   * Identifies HTML table tags and converts to Markdown table format
   *
   * @param string $sContent Content containing HTML tables
   * @return string Converted content
   */
  private static function convertHtmlTables($sContent)
  {
    // Use regular expression to match entire table
    return preg_replace_callback('/<table[^>]*>(.*?)<\/table>/is', function ($aMatches) {
      return self::parseHtmlTable($aMatches[1]);
    }, $sContent);
  }

  /**
   * HTML → Markdown parses single HTML table
   * Parses HTML table structure (thead, tbody, tr, th, td) and converts to Markdown table format
   *
   * @param string $sTableContent table tag content
   * @return string Markdown formatted table
   */
  private static function parseHtmlTable($sTableContent)
  {
    // Remove thead and tbody tags, preserve content
    $sTableContent = preg_replace('/<\/?t(head|body)[^>]*>/i', '', $sTableContent);

    // Extract all tr tags
    preg_match_all('/<tr[^>]*>(.*?)<\/tr>/is', $sTableContent, $aRows, PREG_SET_ORDER);

    if (empty($aRows)) {
      return '';
    }

    $aMarkdownRows = [];
    $bFirstRowIsHeader = false;

    // Check if first row contains th tags
    if (preg_match('/<th[^>]*>/', $aRows[0][1])) {
      $bFirstRowIsHeader = true;
    }

    foreach ($aRows as $iIndex => $aRow) {
      $sRowContent = $aRow[1];

      // Extract all td or th tags
      preg_match_all('/<(td|th)[^>]*>(.*?)<\/\1>/is', $sRowContent, $aCells, PREG_SET_ORDER);

      $aCellContents = [];
      foreach ($aCells as $aCell) {
        // Process cell content, remove HTML tags and clean whitespace
        $sCellContent = self::processCellContent($aCell[2]);
        $aCellContents[] = $sCellContent;
      }

      if (!empty($aCellContents)) {
        // Build Markdown table row
        $sMarkdownRow = '| ' . implode(' | ', $aCellContents) . ' |';
        $aMarkdownRows[] = $sMarkdownRow;

        // If this is header row, add separator
        if ($iIndex === 0 && $bFirstRowIsHeader) {
          $aSeparator = array_fill(0, count($aCellContents), '---');
          $sSeparatorRow = '| ' . implode(' | ', $aSeparator) . ' |';
          $aMarkdownRows[] = $sSeparatorRow;
        }
      }
    }

    // If first row is not header, treat first row as header and add separator
    if (!$bFirstRowIsHeader && !empty($aMarkdownRows)) {
      $iColCount = substr_count($aMarkdownRows[0], '|') - 1;
      $aSeparator = array_fill(0, $iColCount, '---');
      $sSeparatorRow = '| ' . implode(' | ', $aSeparator) . ' |';
      array_splice($aMarkdownRows, 1, 0, [$sSeparatorRow]);
    }

    return "\n" . implode("\n", $aMarkdownRows) . "\n\n";
  }

  /**
   * HTML → Markdown processes table cell content
   * Cleans HTML tags within table cells, preserves basic formatting, avoids Markdown syntax conflicts
   *
   * @param string $sContent Cell content
   * @return string Cleaned cell content
   */
  private static function processCellContent($sContent)
  {
    // Process internal HTML tags first (preserve formatting)
    $aInnerPatterns = [
      '/<strong[^>]*>(.*?)<\/strong>/is' => '**$1**',
      '/<b[^>]*>(.*?)<\/b>/is' => '**$1**',
      '/<em[^>]*>(.*?)<\/em>/is' => '*$1*',
      '/<i[^>]*>(.*?)<\/i>/is' => '*$1*',
      '/<a[^>]*href=[\"\'](.*?)[\"\'][^>]*>(.*?)<\/a>/is' => '[$2]($1)',
      '/<br\s*\/?>/is' => ' ',
      '/<p[^>]*>(.*?)<\/p>/is' => '$1 ',
    ];

    foreach ($aInnerPatterns as $sPattern => $sReplacement) {
      $sContent = preg_replace($sPattern, $sReplacement, $sContent);
    }

    // Remove remaining HTML tags
    $sContent = strip_tags($sContent);

    // Clean whitespace and line breaks
    $sContent = preg_replace('/\s+/', ' ', $sContent);
    $sContent = trim($sContent);

    // Avoid Markdown table syntax conflicts
    $sContent = str_replace('|', '\\|', $sContent);

    return $sContent;
  }

  /**
   * Markdown → HTML main conversion method
   * Converts Markdown content to HTML format
   *
   * @param string $sMarkdownContent Markdown content
   * @return string HTML formatted text
   */
  public static function markdownToHtml($sMarkdownContent)
  {
    // Process tables first
    $sMarkdownContent = self::markdownTables($sMarkdownContent);

    // Process links and images first
    $aReplacePatterns = [
      // Process image syntax ![alt](url)
      '/!\[([^\]]*)\]\(([^)]+)\)/' => '<img src="$2" alt="$1" />',
      // Process link syntax [text](url)
      '/\[(.*?)\]\((.*?)\)/' => '<a href="$2">$1</a>',
      // Process headings
      '/^###### (.*)$/m' => '<h6>$1</h6>',
      '/^##### (.*)$/m' => '<h5>$1</h5>',
      '/^#### (.*)$/m' => '<h4>$1</h4>',
      '/^### (.*)$/m' => '<h3>$1</h3>',
      '/^## (.*)$/m' => '<h2>$1</h2>',
      '/^# (.*)$/m' => '<h1>$1</h1>',
      // Bold and italic (order matters)
      '/\*\*\*(.*?)\*\*\*/s' => '<strong><em>$1</em></strong>',
      '/\*\*(.*?)\*\*/s' => '<strong>$1</strong>',
      '/\*(.*?)\*/s' => '<em>$1</em>',
      // Don't process lists yet, handle with specialized method later
    ];

    // Perform basic conversion first
    foreach ($aReplacePatterns as $k1 => $v1) {
      $sMarkdownContent = preg_replace($k1, $v1, $sMarkdownContent);
    }

    // Process lists (including nested lists)
    $sMarkdownContent = self::markdownLists($sMarkdownContent);

    // Convert paragraphs (non-heading/list/table) to <p>
    $aLines = preg_split("/\r\n|\n|\r/", $sMarkdownContent);
    $sHtmlContent = '';

    foreach ($aLines as $sLine) {
      $sTrimmed = trim($sLine);
      if ($sTrimmed === '') {
        continue;
      }

      // If not starting with HTML tag (avoid wrapping headings, ul, table repeatedly)
      if (!preg_match('/^<.*>/', $sTrimmed)) {
        $sHtmlContent .= '<p>' . $sTrimmed . '</p>' . "\n";
      } else {
        $sHtmlContent .= $sTrimmed . "\n";
      }
    }

    return trim($sHtmlContent);
  }
  /**
   * Markdown → HTML converts Markdown lists to HTML
   * Parses Markdown formatted lists and converts to HTML ul/ol structure, supports nested lists
   *
   * @param string $sContent Markdown content
   * @return string Content containing HTML lists
   */
  private static function markdownLists($sContent)
  {
    $aLines = preg_split("/\r\n|\n|\r/", $sContent);
    $aProcessedLines = [];

    for ($i = 0; $i < count($aLines); $i++) {
      $sLine = $aLines[$i];

      // Check if it's a list item
      if (preg_match('/^(\s*)-\s+(.*)$/', $sLine, $aMatches)) {
        $iIndent = strlen($aMatches[1]); // Calculate indentation character count
        $sItemContent = trim($aMatches[2]);

        // Determine current level (every 2 spaces is one level)
        $iLevel = intval($iIndent / 2);

        // Mark as list item, record level and content
        $aProcessedLines[] = [
          'type' => 'list_item',
          'level' => $iLevel,
          'content' => $sItemContent
        ];
      } else {
        // Not a list item
        $aProcessedLines[] = [
          'type' => 'text',
          'content' => $sLine
        ];
      }
    }

    // Convert to HTML
    return self::nestedListHtml($aProcessedLines);
  }
  /**
   * Markdown → HTML builds nested list HTML structure
   * Builds correct HTML nested list structure based on parsed Markdown list data
   *
   * @param array $aProcessedLines Parsed list line data
   * @return string HTML formatted nested list
   */
  private static function nestedListHtml($aProcessedLines)
  {
    $aResult = [];
    $aOpenLevels = []; // Record opened levels
    $bLastWasListItem = false;

    for ($i = 0; $i < count($aProcessedLines); $i++) {
      $aLine = $aProcessedLines[$i];

      if ($aLine['type'] === 'list_item') {
        $iLevel = $aLine['level'];
        $sContent = $aLine['content'];

        // Close lists deeper than current level
        while (!empty($aOpenLevels) && end($aOpenLevels) > $iLevel) {
          array_pop($aOpenLevels);
          $aResult[] = '</ul>';
          if (!empty($aOpenLevels)) {
            $aResult[] = '</li>';
          }
        }

        // If need to open new level
        if (empty($aOpenLevels) || end($aOpenLevels) < $iLevel) {
          if (!empty($aOpenLevels) && $bLastWasListItem) {
            // Remove previous </li>, prepare to add <ul> inside it
            $sLastItem = array_pop($aResult);
            $sLastItem = str_replace('</li>', '', $sLastItem);
            $aResult[] = $sLastItem;
            $aResult[] = '<ul>';
          } else {
            // First level list or independent list
            $aResult[] = '<ul>';
          }
          $aOpenLevels[] = $iLevel;
        }

        $aResult[] = '<li>' . $sContent . '</li>';
        $bLastWasListItem = true;
      } else {
        // Not a list item, close all lists
        while (!empty($aOpenLevels)) {
          array_pop($aOpenLevels);
          $aResult[] = '</ul>';
          if (!empty($aOpenLevels)) {
            $aResult[] = '</li>';
          }
        }

        // Add original line (if not empty line)
        if (trim($aLine['content']) !== '') {
          $aResult[] = $aLine['content'];
        }
        $bLastWasListItem = false;
      }
    }

    // Close remaining lists
    while (!empty($aOpenLevels)) {
      array_pop($aOpenLevels);
      $aResult[] = '</ul>';
      if (!empty($aOpenLevels)) {
        $aResult[] = '</li>';
      }
    }

    return implode("\n", $aResult);
  }

  /**
   * Markdown → HTML converts Markdown tables to HTML
   * Parses Markdown table syntax and converts to HTML table structure
   *
   * @param string $sContent Markdown content
   * @return string Content containing HTML tables
   */
  private static function markdownTables($sContent)
  {
    // Use regular expression to match entire table
    $sContent = preg_replace_callback('/^(\|.*\|)\s*\n(\|[-:| ]+\|)\s*\n((?:\|.*\|\s*\n?)*)/m', function ($aMatches) {
      $sHeaderRow = trim($aMatches[1]);
      $sAlignRow = trim($aMatches[2]);
      $sDataRows = trim($aMatches[3]);

      // Parse header row
      $aHeaders = array_map('trim', explode('|', trim($sHeaderRow, '|')));

      // Parse alignment
      $aAligns = array_map('trim', explode('|', trim($sAlignRow, '|')));
      $aAlignments = [];

      foreach ($aAligns as $sAlign) {
        if (strpos($sAlign, ':') === 0 && substr($sAlign, -1) === ':') {
          $aAlignments[] = 'center';
        } elseif (substr($sAlign, -1) === ':') {
          $aAlignments[] = 'right';
        } else {
          $aAlignments[] = 'left';
        }
      }

      // Build table HTML
      $sTableHtml = '<table width="100%" border="0" cellpadding="0" cellspacing="0">';

      // Table header
      $sTableHtml .= '<thead><tr>';
      foreach ($aHeaders as $iIndex => $sHeader) {
        $sAlign = isset($aAlignments[$iIndex]) ? ' style="text-align: ' . $aAlignments[$iIndex] . '"' : '';
        $sTableHtml .= '<th' . $sAlign . '>' . trim($sHeader) . '</th>';
      }
      $sTableHtml .= '</tr></thead>';

      // Table body
      if (!empty($sDataRows)) {
        $sTableHtml .= '<tbody>';
        $aDataLines = array_filter(explode("\n", $sDataRows));
        foreach ($aDataLines as $sDataLine) {
          $sDataLine = trim($sDataLine);
          if (empty($sDataLine)) continue;

          $aCells = array_map('trim', explode('|', trim($sDataLine, '|')));
          $sTableHtml .= '<tr>';
          foreach ($aCells as $iIndex => $sCell) {
            $sAlign = isset($aAlignments[$iIndex]) ? ' style="text-align: ' . $aAlignments[$iIndex] . '"' : '';
            $sTableHtml .= '<td' . $sAlign . '>' . trim($sCell) . '</td>';
          }
          $sTableHtml .= '</tr>';
        }
        $sTableHtml .= '</tbody>';
      }

      $sTableHtml .= '</table>';

      return $sTableHtml;
    }, $sContent);

    return $sContent;
  }
}
