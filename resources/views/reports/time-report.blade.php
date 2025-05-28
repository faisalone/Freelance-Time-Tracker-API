<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time Report - {{ $reportTitle }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 24px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .report-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .report-info h2 {
            margin: 0 0 10px 0;
            color: #007bff;
            font-size: 16px;
        }
        
        .info-grid {
            display: table;
            width: 100%;
        }
        
        .info-row {
            display: table-row;
        }
        
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 3px 10px 3px 0;
            width: 120px;
        }
        
        .info-value {
            display: table-cell;
            padding: 3px 0;
        }
        
        .summary {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .summary h2 {
            margin: 0 0 15px 0;
            color: #007bff;
            font-size: 18px;
        }
        
        .summary-stats {
            display: table;
            width: 100%;
        }
        
        .summary-stat {
            display: table-cell;
            text-align: center;
            padding: 10px;
            border-right: 1px solid #ccc;
        }
        
        .summary-stat:last-child {
            border-right: none;
        }
        
        .stat-value {
            font-size: 20px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 11px;
        }
        
        table th,
        table td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
        }
        
        table th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        
        table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .group-header {
            background-color: #e9ecef !important;
            font-weight: bold;
            color: #495057;
        }
        
        .group-total {
            background-color: #d1ecf1 !important;
            font-weight: bold;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Freelancer Time Tracker</h1>
        <div class="subtitle">{{ $reportTitle }}</div>
    </div>

    <div class="report-info">
        <h2>Report Information</h2>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Report Type:</div>
                <div class="info-value">{{ ucfirst(str_replace('_', ' ', $groupBy)) }} Report</div>
            </div>
            <div class="info-row">
                <div class="info-label">Date Range:</div>
                <div class="info-value">
                    {{ \Carbon\Carbon::parse($startDate)->format('M j, Y') }} - 
                    {{ \Carbon\Carbon::parse($endDate)->format('M j, Y') }}
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Generated:</div>
                <div class="info-value">{{ now()->format('M j, Y \a\t g:i A') }}</div>
            </div>
            @if(isset($client))
            <div class="info-row">
                <div class="info-label">Client:</div>
                <div class="info-value">{{ $client->name }}</div>
            </div>
            @endif
            @if(isset($project))
            <div class="info-row">
                <div class="info-label">Project:</div>
                <div class="info-value">{{ $project->name }}</div>
            </div>
            @endif
        </div>
    </div>

    <div class="summary">
        <h2>Summary</h2>
        <div class="summary-stats">
            <div class="summary-stat">
                <div class="stat-value">{{ number_format($totalHours, 1) }}</div>
                <div class="stat-label">Total Hours</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value">${{ number_format($totalEarnings, 2) }}</div>
                <div class="stat-label">Total Earnings</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value">{{ count($timeLogs) }}</div>
                <div class="stat-label">Total Entries</div>
            </div>
            <div class="summary-stat">
                <div class="stat-value">${{ number_format($averageHourlyRate, 2) }}</div>
                <div class="stat-label">Avg Hourly Rate</div>
            </div>
        </div>
    </div>

    @if(count($timeLogs) > 0)
        @if($groupBy === 'project')
            @foreach($timeLogs->groupBy('project.name') as $projectName => $projectLogs)
                <h3 style="color: #007bff; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">
                    Project: {{ $projectName }}
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 15%">Date</th>
                            <th style="width: 15%">Start Time</th>
                            <th style="width: 15%">End Time</th>
                            <th style="width: 10%">Hours</th>
                            <th style="width: 35%">Description</th>
                            <th style="width: 10%" class="text-right">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($projectLogs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->start_time)->format('M j, Y') }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->start_time)->format('g:i A') }}</td>
                            <td>{{ $log->end_time ? \Carbon\Carbon::parse($log->end_time)->format('g:i A') : 'Running' }}</td>
                            <td class="text-center">{{ number_format($log->hours ?? 0, 1) }}</td>
                            <td>{{ $log->description }}</td>
                            <td class="text-right">
                                ${{ number_format(($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0), 2) }}
                            </td>
                        </tr>
                        @endforeach
                        <tr class="group-total">
                            <td colspan="3"><strong>Project Total</strong></td>
                            <td class="text-center"><strong>{{ number_format($projectLogs->sum('hours'), 1) }} hrs</strong></td>
                            <td></td>
                            <td class="text-right">
                                <strong>${{ number_format($projectLogs->sum(function($log) {
                                    return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
                                }), 2) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
                @if(!$loop->last)<div class="page-break"></div>@endif
            @endforeach

        @elseif($groupBy === 'client')
            @foreach($timeLogs->groupBy('project.client.name') as $clientName => $clientLogs)
                <h3 style="color: #007bff; border-bottom: 1px solid #dee2e6; padding-bottom: 5px;">
                    Client: {{ $clientName }}
                </h3>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 20%">Project</th>
                            <th style="width: 15%">Date</th>
                            <th style="width: 10%">Hours</th>
                            <th style="width: 40%">Description</th>
                            <th style="width: 15%" class="text-right">Earnings</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($clientLogs as $log)
                        <tr>
                            <td>{{ $log->project->name }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->start_time)->format('M j, Y') }}</td>
                            <td class="text-center">{{ number_format($log->hours ?? 0, 1) }}</td>
                            <td>{{ $log->description }}</td>
                            <td class="text-right">
                                ${{ number_format(($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0), 2) }}
                            </td>
                        </tr>
                        @endforeach
                        <tr class="group-total">
                            <td colspan="2"><strong>Client Total</strong></td>
                            <td class="text-center"><strong>{{ number_format($clientLogs->sum('hours'), 1) }} hrs</strong></td>
                            <td></td>
                            <td class="text-right">
                                <strong>${{ number_format($clientLogs->sum(function($log) {
                                    return ($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0);
                                }), 2) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
                @if(!$loop->last)<div class="page-break"></div>@endif
            @endforeach

        @else
            {{-- Daily or Weekly reports --}}
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%">Date</th>
                        <th style="width: 20%">Project</th>
                        <th style="width: 15%">Client</th>
                        <th style="width: 10%">Hours</th>
                        <th style="width: 30%">Description</th>
                        <th style="width: 10%" class="text-right">Earnings</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($timeLogs as $log)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($log->start_time)->format('M j, Y') }}</td>
                        <td>{{ $log->project->name }}</td>
                        <td>{{ $log->project->client->name }}</td>
                        <td class="text-center">{{ number_format($log->hours ?? 0, 1) }}</td>
                        <td>{{ $log->description }}</td>
                        <td class="text-right">
                            ${{ number_format(($log->hours ?? 0) * ($log->project->hourly_rate ?? $log->project->client->hourly_rate ?? 0), 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @else
        <div class="no-data">
            <h3>No time logs found for the selected period</h3>
            <p>Try adjusting your date range or filters to view time tracking data.</p>
        </div>
    @endif

    <div class="footer">
        <p>Generated by Freelancer Time Tracker API • {{ now()->format('Y') }} • Page 1 of 1</p>
    </div>
</body>
</html>
