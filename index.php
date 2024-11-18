<?php
require_once 'vendor/autoload.php';
use App\MarkdownConverter;

$converter = new MarkdownConverter();
$convertedHtml = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $markdown = file_get_contents('php://input');
    parse_str($markdown, $postData);
    if (isset($postData['markdown'])) {
        $convertedHtml = $converter->convertToHtml($postData['markdown']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Markdown Converter</title>
</head>
<body>
    <h1>Markdown Converter</h1>
    <form method="post" enctype="application/x-www-form-urlencoded">
        <textarea name="markdown" rows="10" cols="50" placeholder="Enter your markdown here"><?php echo isset($postData['markdown']) ? htmlspecialchars($postData['markdown']) : ''; ?></textarea>
        <br>
        <input type="submit" value="Convert">
    </form>

    <?php if (!empty($convertedHtml)): ?>
        <h2>Converted HTML:</h2>
        <div><?php echo $convertedHtml; ?></div>
    <?php endif; ?>
</body>
</html>
