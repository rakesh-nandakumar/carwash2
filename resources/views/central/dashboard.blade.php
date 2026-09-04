@extends('central.layouts.central')

@section('title', 'Dashboard — Master Control')

@section('content')
    <h2>Dashboard</h2>
    <p class="sub">Platform overview — tenants, environments and users.</p>

    <div class="card">
        <table>
            <thead>
            <tr>
                <th>Stat</th>
                <th>Count</th>
            </tr>
            </thead>
            <tbody>
            <tr><td>Tenants (incl. trashed)</td><td>{{ $totals['tenants'] }}</td></tr>
            <tr><td>Live tenants</td><td>{{ $totals['live'] }}</td></tr>
            <tr><td>Test instances</td><td>{{ $totals['test'] }}</td></tr>
            <tr><td>Tenant users</td><td>{{ $totals['users'] }}</td></tr>
            </tbody>
        </table>
    </div>
@endsection
