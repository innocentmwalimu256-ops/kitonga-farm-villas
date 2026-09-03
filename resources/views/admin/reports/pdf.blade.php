<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #333; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #064e3b; padding-bottom: 10px; margin-bottom: 30px; }
        .title { font-size: 24px; font-weight: bold; color: #064e3b; margin: 0; text-transform: uppercase; letter-spacing: 1px; }
        .subtitle { font-size: 12px; color: #666; margin: 5px 0 0 0; }
        .section { margin-bottom: 25px; }
        .section-title { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #064e3b; border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; }
        .metric-table { w-full; border-collapse: collapse; margin-bottom: 20px; }
        .metric-table td { padding: 8px 0; font-size: 13px; }
        .metric-label { font-weight: bold; color: #555; }
        .metric-val { text-align: right; font-family: monospace; font-weight: bold; }
        .footer { text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 15px; margin-top: 50px; }
    </style>
</head>
<body>

    <div class="header">
        <h1 class="title">Kitonga Farm Villas</h1>
        <p class="subtitle">Operational Business Summary Report</p>
        <p class="subtitle">Generated on: {{ date('Y-m-d H:i:s') }}</p>
    </div>

    <div class="section">
        <div class="section-title">Key Performance Indicators</div>
        <table style="width: 100%;" class="metric-table">
            <tr>
                <td class="metric-label">Total Reservations Logged</td>
                <td class="metric-val">{{ $bookingsCount }} Bookings</td>
            </tr>
            <tr>
                <td class="metric-label">Total Expense Slips File</td>
                <td class="metric-val">{{ $expensesCount }} Expenses</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Financial Summary (TZS)</div>
        <table style="width: 100%;" class="metric-table">
            <tr>
                <td class="metric-label">Villa Stays Accommodation Revenue</td>
                <td class="metric-val">TZS {{ number_format($accommodationRevenue) }}</td>
            </tr>
            <tr>
                <td class="metric-label">POS Terminal & Cafe Revenue</td>
                <td class="metric-val">TZS {{ number_format($posRevenue) }}</td>
            </tr>
            <tr style="border-top: 1px solid #eee;">
                <td class="metric-label" style="padding-top: 10px;">Total Gross Revenue</td>
                <td class="metric-val" style="padding-top: 10px; color: #064e3b;">TZS {{ number_format($totalRevenue) }}</td>
            </tr>
            <tr>
                <td class="metric-label">Total Approved Expenses</td>
                <td class="metric-val" style="color: #b91c1c;">- TZS {{ number_format($totalExpenses) }}</td>
            </tr>
            <tr style="border-top: 2px solid #064e3b;">
                <td class="metric-label" style="font-size: 14px; padding-top: 10px;">Estimated Net Operating Profit</td>
                <td class="metric-val" style="font-size: 14px; padding-top: 10px; color: #064e3b;">TZS {{ number_format($netProfit) }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Confidential Document — For Management Eyes Only — Kitonga Farm Villas Ltd.
    </div>

</body>
</html>
