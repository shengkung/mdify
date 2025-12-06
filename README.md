# mdify

**HTML ↔ Markdown Bidirectional Converter** (HTML ↔ Markdown 雙向轉換工具)

---

## Introduction

`mdify` is a PHP class that provides bidirectional conversion between HTML and Markdown formats. It supports common formats such as headings, bold, italic, images, links, tables, and nested lists.

`mdify` 是一個 PHP 類別，提供 HTML 與 Markdown 格式之間的雙向轉換功能。支援標題、粗體、斜體、圖片、連結、表格和巢狀列表等常見格式。

---

## Features

### HTML → Markdown
- ✅ Heading conversion (H1-H6) - 標題轉換
- ✅ Text formatting (bold, italic) - 文字格式（粗體、斜體）
- ✅ Image conversion - 圖片轉換
- ✅ Link conversion - 連結轉換
- ✅ Table conversion - 表格轉換
- ✅ Nested list conversion - 巢狀列表轉換
- ✅ Automatic empty tag cleanup - 自動清理空白標籤

### Markdown → HTML
- ✅ Heading conversion (# to ######) - 標題轉換
- ✅ Text formatting (**bold**, *italic*) - 文字格式
- ✅ Image conversion - 圖片轉換
- ✅ Link conversion - 連結轉換
- ✅ Table conversion (with alignment support) - 表格轉換（支援對齊）
- ✅ Nested list conversion - 巢狀列表轉換

---

## Installation

Include the `mdify.php` file in your project:

將 `mdify.php` 檔案包含到您的專案中：

```php
require_once 'mdify/mdify.php';
```

---

## Usage

### HTML → Markdown

```php
$sHtmlContent = '<h1>Title</h1><p>This is <strong>bold</strong> text.</p>';
$sMarkdown = mdify::htmlToMarkdown($sHtmlContent);
echo $sMarkdown;
// Output: # Title
//
// This is **bold** text.
```

### Markdown → HTML

```php
$sMarkdown = '# Title\n\nThis is **bold** text.';
$sHtml = mdify::markdownToHtml($sMarkdown);
echo $sHtml;
// Output: <h1>Title</h1>
//         <p>This is <strong>bold</strong> text.</p>
```

---

## API Reference

### `htmlToMarkdown($sHtmlContent)`

Converts HTML content to Markdown format.

將 HTML 內容轉換為 Markdown 格式。

**Parameters:**
- `$sHtmlContent` (string) - HTML content (HTML 內容)

**Returns:**
- (string) - Markdown formatted text (Markdown 格式文字)

**Example:**

```php
$sHtml = '<h2>Subtitle</h2><p>Content</p>';
$sMarkdown = mdify::htmlToMarkdown($sHtml);
```

---

### `markdownToHtml($sMarkdownContent)`

Converts Markdown content to HTML format.

將 Markdown 內容轉換為 HTML 格式。

**Parameters:**
- `$sMarkdownContent` (string) - Markdown content (Markdown 內容)

**Returns:**
- (string) - HTML formatted text (HTML 格式文字)

**Example:**

```php
$sMarkdown = '## Subtitle\n\nContent';
$sHtml = mdify::markdownToHtml($sMarkdown);
```

---

## Supported Formats

### HTML → Markdown Support

| HTML Tag | Markdown Syntax |
|----------|----------------|
| `<h1>` ~ `<h6>` | `#` ~ `######` |
| `<strong>`, `<b>` | `**text**` |
| `<em>`, `<i>` | `*text*` |
| `<img src="..." alt="...">` | `![alt](src)` |
| `<a href="...">text</a>` | `[text](href)` |
| `<table>...</table>` | Markdown table |
| `<ul>`, `<ol>` | `- ` or `1. ` |

### Markdown → HTML Support

| Markdown Syntax | HTML Tag |
|----------------|----------|
| `#` ~ `######` | `<h1>` ~ `<h6>` |
| `**text**` | `<strong>text</strong>` |
| `*text*` | `<em>text</em>` |
| `![alt](url)` | `<img src="url" alt="alt" />` |
| `[text](url)` | `<a href="url">text</a>` |
| Markdown table | `<table>...</table>` |
| `- ` or `1. ` | `<ul>`, `<ol>` |

---

## Notes

1. **Nested Lists**: Supports multi-level nested structures, using 2 spaces for each indentation level.
   支援多層巢狀結構，使用 2 個空格縮排表示一個層級。

2. **Table Alignment**: When converting Markdown → HTML, supports left, center, and right alignment.
   Markdown → HTML 轉換時，支援左對齊、置中、右對齊。

3. **HTML Entities**: Automatically handles HTML entity decoding.
   自動處理 HTML 實體的解碼。

4. **Whitespace Cleanup**: Automatically removes excessive blank lines and empty tags.
   自動移除多餘的空白行和空標籤。

---

## Version

- **Version**: 2025-07-25
- **License**: MIT License
- **Copyright**: Copyright (C) 2025 Nice Creative Co., Ltd.

---

## License

MIT License

---

## Author

簡盛弓 (Carter Chein)


---

## Links

- [奈思創藝網頁設計](http://www.nim.com.tw)

