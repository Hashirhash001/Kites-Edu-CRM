<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #1e293b; background: #f8fafc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
        .header { background: linear-gradient(135deg, #667eea, #764ba2); padding: 30px; color: #fff; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p  { margin: 6px 0 0; opacity: .85; font-size: 14px; }
        .body { padding: 28px 32px; }
        .stats { display: flex; gap: 12px; flex-wrap: wrap; margin: 20px 0; }
        .stat-box { flex: 1; min-width: 100px; background: #f1f5f9; border-radius: 8px; padding: 14px; text-align: center; }
        .stat-box .num { font-size: 26px; font-weight: 800; color: #667eea; }
        .stat-box .lbl { font-size: 11px; color: #64748b; margin-top: 3px; text-transform: uppercase; letter-spacing: .4px; }
        .no-leads { text-align: center; padding: 30px 20px; background: #f8fafc; border-radius: 8px; margin: 20px 0; border: 1px dashed #cbd5e1; }
        .no-leads .icon { font-size: 40px; margin-bottom: 10px; }
        .no-leads p { margin: 0; color: #64748b; font-size: 14px; }
        .footer { background: #f8fafc; padding: 16px 32px; text-align: center; font-size: 12px; color: #94a3b8; border-top: 1px solid #e2e8f0; }
    </style>
</head>
<body>
<div class="container">

    <div class="header">
        <h1>{{ $hasLeads ? '📊 Today\'s New Leads' : '📭 No New Leads Today' }}</h1>
        <p>{{ $date }}</p>
    </div>

    <div class="body">
        <p>Hi Team,</p>

        @if($hasLeads)
            <p>
                <strong>{{ $stats['total'] }} new lead(s)</strong> were added today.
                Please find the full report attached as <strong>{{ $fileName }}</strong>.
            </p>

            <div class="stats">
                <div class="stat-box">
                    <div class="num">{{ $stats['total'] }}</div>
                    <div class="lbl">New Today</div>
                </div>
                <div class="stat-box">
                    <div class="num">{{ $stats['hot'] }}</div>
                    <div class="lbl">🔥 Hot</div>
                </div>
                <div class="stat-box">
                    <div class="num">{{ $stats['admitted'] }}</div>
                    <div class="lbl">Admitted</div>
                </div>
                <div class="stat-box">
                    <div class="num">{{ $stats['pending'] }}</div>
                    <div class="lbl">Pending</div>
                </div>
            </div>
        @else
            <div class="no-leads">
                <div class="icon">😴</div>
                <p><strong>No new leads were created today.</strong></p>
                <p style="margin-top:8px;">The daily report ran successfully — there's just nothing to report for {{ $date }}.</p>
            </div>
        @endif

        <p style="color:#64748b; font-size:13px;">
            This is an automated report generated daily at 8:00 PM IST.
        </p>
    </div>

    <div class="footer">
        Kites Education CRM &bull; Automated Report &bull; Do not reply to this email
    </div>

</div>
</body>
</html>
