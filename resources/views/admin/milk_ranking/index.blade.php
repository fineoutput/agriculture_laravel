@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Milk Ranking Entries</h1>
    </section>

    <section class="content">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Farmer Name</th>
                    <th>Weight</th>
                    <th>Image</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rankings as $index => $entry)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $entry->farmer->name ?? 'N/A' }}</td>
                        <td>{{ $entry->weight }}</td>
                        <td>
                            @if($entry->image)
                                <img src="{{ $entry->image }}" alt="milk" width="80">
                            @endif
                        </td>
                        <td>{{ $entry->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">No entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
@endsection
