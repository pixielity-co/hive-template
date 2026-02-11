<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MonoPhp\Calculator\Calculator;

$calculator = new Calculator();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo App - PHP Monorepo</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1em;
        }
        .demo-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .demo-section h2 {
            color: #667eea;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        .calculation {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 4px solid #667eea;
        }
        .calculation:last-child {
            margin-bottom: 0;
        }
        .calculation .expression {
            color: #333;
            font-size: 1.1em;
        }
        .calculation .result {
            color: #667eea;
            font-weight: bold;
            font-size: 1.2em;
        }
        .info {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 8px;
            color: #1565c0;
        }
        .info strong {
            display: block;
            margin-bottom: 5px;
        }
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Demo App</h1>
        <p class="subtitle">PHP Turborepo Monorepo Testing</p>

        <div class="demo-section">
            <h2>📦 Calculator Package Demo</h2>
            <div class="calculation">
                <span class="expression">5 + 3</span>
                <span class="result"><?= $calculator->add(5, 3) ?></span>
            </div>
            <div class="calculation">
                <span class="expression">10 - 4</span>
                <span class="result"><?= $calculator->subtract(10, 4) ?></span>
            </div>
            <div class="calculation">
                <span class="expression">7 × 6</span>
                <span class="result"><?= $calculator->multiply(7, 6) ?></span>
            </div>
            <div class="calculation">
                <span class="expression">20 ÷ 4</span>
                <span class="result"><?= $calculator->divide(20, 4) ?></span>
            </div>
        </div>

        <div class="info">
            <strong>✅ Monorepo Working!</strong>
            This app successfully uses the <code>@mono-php/calculator</code> package from the monorepo.
        </div>
    </div>
</body>
</html>
