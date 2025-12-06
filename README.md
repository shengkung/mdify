# mdify

**HTML ↔ Markdown 雙向轉換工具** | **HTML ↔ Markdown Bidirectional Converter**

---

## 簡介 | Introduction

`mdify` 是一個 PHP 類別，提供 HTML 與 Markdown 格式之間的雙向轉換功能。支援標題、粗體、斜體、圖片、連結、表格和巢狀列表等常見格式。

`mdify` is a PHP class that provides bidirectional conversion between HTML and Markdown formats. It supports common formats such as headings, bold, italic, images, links, tables, and nested lists.

---

## 功能特色 | Features

### HTML → Markdown
- ✅ 標題轉換（H1-H6）| Heading conversion (H1-H6)
- ✅ 文字格式（粗體、斜體）| Text formatting (bold, italic)
- ✅ 圖片轉換 | Image conversion
- ✅ 連結轉換 | Link conversion
- ✅ 表格轉換 | Table conversion
- ✅ 巢狀列表轉換 | Nested list conversion
- ✅ 自動清理空白標籤 | Automatic empty tag cleanup

### Markdown → HTML
- ✅ 標題轉換（# 至 ######）| Heading conversion (# to ######)
- ✅ 文字格式（**粗體**、*斜體*）| Text formatting (**bold**, *italic*)
- ✅ 圖片轉換 | Image conversion
- ✅ 連結轉換 | Link conversion
- ✅ 表格轉換（支援對齊）| Table conversion (with alignment support)
- ✅ 巢狀列表轉換 | Nested list conversion

---

## 安裝 | Installation

將 `mdify.php` 檔案包含到您的專案中：

Include the `mdify.php` file in your project:

```php
require_once 'mdify/mdify.php';
```

---

## 使用方法 | Usage

### HTML → Markdown

```php
$sHtmlContent = '<h1>標題</h1><p>這是一段<strong>粗體</strong>文字。</p>';
$sMarkdown = mdify::htmlToMarkdown($sHtmlContent);
echo $sMarkdown;
// 輸出: # 標題
//
// 這是一段**粗體**文字。
```

### Markdown → HTML

```php
$sMarkdown = '# 標題\n\n這是一段**粗體**文字。';
$sHtml = mdify::markdownToHtml($sMarkdown);
echo $sHtml;
// 輸出: <h1>標題</h1>
//       <p>這是一段<strong>粗體</strong>文字。</p>
```

---

## API 參考 | API Reference

### `htmlToMarkdown($sHtmlContent)`

將 HTML 內容轉換為 Markdown 格式。

Converts HTML content to Markdown format.

**參數 | Parameters:**
- `$sHtmlContent` (string) - HTML 內容 | HTML content

**回傳值 | Returns:**
- (string) - Markdown 格式文字 | Markdown formatted text

**範例 | Example:**

```php
$sHtml = '<h2>子標題</h2><p>內容</p>';
$sMarkdown = mdify::htmlToMarkdown($sHtml);
```

---

### `markdownToHtml($sMarkdownContent)`

將 Markdown 內容轉換為 HTML 格式。

Converts Markdown content to HTML format.

**參數 | Parameters:**
- `$sMarkdownContent` (string) - Markdown 內容 | Markdown content

**回傳值 | Returns:**
- (string) - HTML 格式文字 | HTML formatted text

**範例 | Example:**

```php
$sMarkdown = '## 子標題\n\n內容';
$sHtml = mdify::markdownToHtml($sMarkdown);
```

---

## 支援的格式 | Supported Formats

### HTML → Markdown 支援 | HTML → Markdown Support

| HTML 標籤 | Markdown 語法 |
|-----------|--------------|
| `<h1>` ~ `<h6>` | `#` ~ `######` |
| `<strong>`, `<b>` | `**文字**` |
| `<em>`, `<i>` | `*文字*` |
| `<img src="..." alt="...">` | `![alt](src)` |
| `<a href="...">文字</a>` | `[文字](href)` |
| `<table>...</table>` | Markdown 表格 |
| `<ul>`, `<ol>` | `- ` 或 `1. ` |

### Markdown → HTML 支援 | Markdown → HTML Support

| Markdown 語法 | HTML 標籤 |
|--------------|-----------|
| `#` ~ `######` | `<h1>` ~ `<h6>` |
| `**文字**` | `<strong>文字</strong>` |
| `*文字*` | `<em>文字</em>` |
| `![alt](url)` | `<img src="url" alt="alt" />` |
| `[文字](url)` | `<a href="url">文字</a>` |
| Markdown 表格 | `<table>...</table>` |
| `- ` 或 `1. ` | `<ul>`, `<ol>` |

---

## 注意事項 | Notes

1. **巢狀列表** | **Nested Lists**: 支援多層巢狀結構，使用 2 個空格縮排表示一個層級。
   Supports multi-level nested structures, using 2 spaces for each indentation level.

2. **表格對齊** | **Table Alignment**: Markdown → HTML 轉換時，支援左對齊、置中、右對齊。
   When converting Markdown → HTML, supports left, center, and right alignment.

3. **HTML 實體** | **HTML Entities**: 自動處理 HTML 實體的解碼。
   Automatically handles HTML entity decoding.

4. **空白清理** | **Whitespace Cleanup**: 自動移除多餘的空白行和空標籤。
   Automatically removes excessive blank lines and empty tags.

---

## 版本資訊 | Version

- **版本 | Version**: 2025-07-25
- **授權 | License**: Nice Co., Ltd License
- **版權 | Copyright**: Copyright (C) 2025 Nice Creative Co., Ltd. Team

---

## 授權 | License

Nice Co., Ltd License

---

## 作者 | Author

Sheng Kung

---

## 相關連結 | Links

- [Nice Panel](http://www.nim.com.tw)

