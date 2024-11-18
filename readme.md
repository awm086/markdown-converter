# Markdown Converter

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

## Running Tests

### Using Composer
Run the following command:
```
composer test:unit
```

### Using Docker
Run the following command:
```
docker run markdown-converter ./vendor/bin/phpunit tests
```

## Usage

To convert a Markdown file to HTML, use the following command:
```
composer convert-markdown readme.md 
```

Replace `readme.md ` with the actual path to your Markdown file.
