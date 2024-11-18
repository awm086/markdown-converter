# Mini Markdown Parser and Converter

A PHP library that parses Markdown into an Abstract Syntax Tree (AST) structure and renders it to HTML.

## Features

- Parses Markdown into a structured AST representation
- Renders AST to HTML output
- Supports headers (h1-h6), paragraphs, and links
- Clean separation between parsing and rendering
- Provides a command line tool.

## Basic Usage

```php
use Acme\TextTools\Markdown\MarkdownDocumentParser;
use Acme\TextTools\Markdown\HtmlDocumentRenderer;

$parser = new MarkdownDocumentParser();
$renderer = new HtmlDocumentRenderer();

// Parse markdown into AST
$doc = $parser->parse($markdown);

// Render AST to HTML
$html = $renderer->render($doc);
```

## Setup and Installation

### Option 1: Local PHP Setup

1. Install PHP on your system if not already installed.
2. Clone this repository.
3. Run a PHP server in the root directory:
   ```
   php -S localhost:8000
   ```
 4. Open localhost:8000
  

### Option 2: Docker Setup

1. Build the Docker image:
   ```
   docker build -t markdown-converter .
   ```
2. Run the container and convert a markdown file:
   ```
   docker run -v $(pwd):/app markdown-converter composer convert-markdown /app/path-to-your-markdown-file.md
   ```

### Option 3: Lando Setup (Optional)

1. Install Lando on your system.
2. In the project root, run:
   ```
   lando start
   ```

## Testing

The project includes comprehensive unit tests for both parsing and rendering functionality.

### Running Tests

#### Using Composer
```bash
# Run all tests with coverage
composer test:unit

# Run specific test suites
./vendor/bin/phpunit tests/MarkdownDocumentParserTest.php
./vendor/bin/phpunit tests/HtmlDocumentRendererTest.php
```

#### Using Docker
```bash
# Run all tests in container
docker run markdown-converter ./vendor/bin/phpunit tests

# Run with coverage report
docker run markdown-converter ./vendor/bin/phpunit tests --coverage-text
```

### Test Structure

- `MarkdownDocumentParserTest.php`: Integration tests for parser
- `MarkdownDocumentParserUnitTest.php`: Unit tests for parser internals
- `HtmlDocumentRendererTest.php`: Tests for HTML rendering

## Command Line Usage

The package includes a fast and memory-efficient command line tool for converting Markdown files to HTML.

### Using the CLI Tool

Convert a Markdown file to HTML:
```bash
bin/markdown-converter input.md > output.html
```

You can also use it with pipes:
```bash
cat input.md | bin/markdown-converter > output.html
```
Or process multiple files:
```bash
cat *.md | bin/markdown-converter > combined.html
```

### Composer Method

Alternatively, you can use the Composer command:
```bash
composer convert-markdown readme.md 
```

Replace `readme.md` with the actual path to your Markdown file.
