<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Trade History Report</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            line-height: 1.4;
        }
        h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }
        .summary {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-item {
            display: table-cell;
            width: 25%;
            padding: 5px;
        }
        .summary-label {
            font-weight: bold;
            color: #666;
            font-size: 8px;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f8f9fa;
            padding: 8px 4px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
            font-size: 9px;
        }
        td {
            padding: 6px 4px;
            border-bottom: 1px solid #dee2e6;
        }
        .text-success {
            color: #198754;
        }
        .text-danger {
            color: #dc3545;
        }
        .text-muted {
            color: #6c757d;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        .badge-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 8px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 10px;
        }
        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Trade History Report</h1>
        <p class="text-muted">Generated on {{ now()->format('F d, Y \a\t H:i:s') }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Trades</div>
                <div class="summary-value">{{ number_format($summary['total_trades']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Winning Trades</div>
                <div class="summary-value text-success">{{ number_format($summary['winning_trades']) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Volume</div>
                <div class="summary-value">${{ number_format($summary['total_volume'], 2) }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total P&L</div>
                <div class="summary-value {{ $summary['total_pnl'] >= 0 ? 'text-success' : 'text-danger' }}">
                    {{ $summary['total_pnl'] >= 0 ? '+' : '' }}${{ number_format($summary['total_pnl'], 2) }}
                </div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Time</th>
                <th>User</th>
                <th>Pair</th>
                <th>Type</th>
                <th>Entry</th>
                <th>Exit</th>
                <th>Size</th>
                <th>P&L</th>
            </tr>
        </thead>
        <tbody>
            @foreach($trades as $trade)
            <tr>
                <td>
                    <div>{{ $trade->created_at->format('M d, Y') }}</div>
                    <div class="text-muted">{{ $trade->created_at->format('H:i:s') }}</div>
                </td>
                <td>
                    <div>{{ $trade->user->name ?? 'N/A' }}</div>
                    <div class="text-muted">ID: {{ $trade->user_id }}</div>
                </td>
                <td>{{ $trade->symbol }}</td>
                <td>
                    <span class="badge badge-{{ $trade->type == 'long' ? 'success' : 'danger' }}">
                        {{ strtoupper($trade->type) }}
                    </span>
                </td>
                <td>${{ number_format($trade->entry_price, 2) }}</td>
                <td>
                    @if($trade->status == 'closed' && $trade->exit_price)
                        ${{ number_format($trade->exit_price, 2) }}
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ number_format($trade->quantity, 4) }}</td>
                <td>
                    @if($trade->status == 'closed' && $trade->realized_pnl !== null)
                        <span class="{{ $trade->realized_pnl >= 0 ? 'text-success' : 'text-danger' }}">
                            {{ $trade->realized_pnl >= 0 ? '+' : '' }}${{ number_format($trade->realized_pnl, 2) }}
                        </span>
                    @else
                        <span class="text-muted">{{ ucfirst($trade->status) }}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>CryptoBot Pro - Trade History Report</p>
        <p>This report contains {{ count($trades) }} trade{{ count($trades) != 1 ? 's' : '' }}</p>
    </div>
</body>
</html>