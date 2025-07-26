@extends('admin.base_template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>Competition Rankings</h1>
        <ol class="breadcrumb">
            <li><a href="{{ url('admin/dashboard') }}"><i class="fa fa-dashboard"></i> Home</a></li>
            <li><a href="{{ route('admin.competition.index') }}"><i class="fa fa-dashboard"></i> Competition Entries</a></li>
            <li class="active">Competition Rankings</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-lg-12">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h3 class="panel-title">
                            Rankings for Competition #{{ $competition->id }}
                            <span class="pull-right">
                                <a href="{{ route('admin.competition.index') }}" class="btn btn-default btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back to Competitions
                                </a>
                            </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h4>Competition Details:</h4>
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Competition Date:</th>
                                        <td>{{ $competition->competition_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Start Date:</th>
                                        <td>{{ $competition->start_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>End Date:</th>
                                        <td>{{ $competition->end_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Entry Fees:</th>
                                        <td>₹{{ $competition->entry_fees ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h4>Rankings ({{ $rankings->count() }} entries):</h4>
                        
                        @php
                            $winners = $rankings->where('position', '!=', null)->sortBy('position');
                        @endphp
                        
                        @if($winners->count() > 0)
                            <div class="alert alert-info">
                                <h5><i class="fa fa-trophy"></i> Current Winners:</h5>
                                @foreach($winners as $winner)
                                    <strong>{{ $winner->position }}{{ $winner->position == 1 ? 'st' : ($winner->position == 2 ? 'nd' : 'rd') }} Place:</strong> 
                                    {{ $winner->farmer->name ?? 'N/A' }} ({{ $winner->weight }} kg)
                                    @if(!$loop->last), @endif
                                @endforeach
                            </div>
                        @endif
                        
                        @if($rankings->count() > 0)
                            <div class="box-body table-responsive no-padding">
                                <table class="table table-bordered table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>Rank</th>
                                            <th>Farmer Name</th>
                                            <th>Weight (kg)</th>
                                            <th>Image</th>
                                            <th>Submission Date</th>
                                            <th>Assigned Position</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    
                                    <tbody>
                                        @foreach($rankings as $index => $ranking)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-{{ $index == 0 ? 'success' : ($index == 1 ? 'warning' : ($index == 2 ? 'info' : 'default')) }}">
                                                        {{ $index + 1 }}
                                                    </span>
                                                    @if($ranking->position)
                                                        <br>
                                                        <small class="text-muted">Assigned: {{ $ranking->position }}{{ $ranking->position == 1 ? 'st' : ($ranking->position == 2 ? 'nd' : 'rd') }}</small>
                                                    @endif
                                                </td>
                                                <td>{{ $ranking->farmer->name ?? 'N/A' }}</td>
                                                <td><strong>{{ $ranking->weight }}</strong></td>
                                               
                                                <td>
                            @if($ranking->image)
                                <img src="{{ $ranking->image }}" alt="milk" width="80">
                            @endif
                        </td>
                                                <td>{{ $ranking->created_at->format('d M Y, h:i A') }}</td>
                                                <td>
                                                    <select class="form-control position-select" data-ranking-id="{{ $ranking->id }}" style="width: 100px;">
                                                        <option value="">No Rank</option>
                                                        <option value="1" {{ $ranking->position == 1 ? 'selected' : '' }}>1st</option>
                                                        <option value="2" {{ $ranking->position == 2 ? 'selected' : '' }}>2nd</option>
                                                        <option value="3" {{ $ranking->position == 3 ? 'selected' : '' }}>3rd</option>
                                                    </select>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="{{ route('admin.competition.ranking.edit', $ranking->id) }}" class="btn btn-primary btn-sm" title="Edit Ranking">
                                                            <i class="fa fa-edit"></i> Edit
                                                        </a>
                                                        <form action="{{ route('admin.competition.ranking.delete', $ranking->id) }}" method="POST" style="display:inline-block" onsubmit="return confirm('Are you sure you want to delete this ranking? This action cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger btn-sm" title="Delete Ranking">
                                                                <i class="fa fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <h4><i class="icon fa fa-info"></i> No Rankings Found</h4>
                                No milk rankings have been submitted for this competition yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('.position-select').on('change', function() {
        var rankingId = $(this).data('ranking-id');
        var position = $(this).val();
        var selectElement = $(this);
        
        // Disable the select while processing
        selectElement.prop('disabled', true);
        
        $.ajax({
            url: '{{ route("admin.competition.ranking.update-position") }}',
            method: 'POST',
            data: {
                ranking_id: rankingId,
                position: position,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Show success message
                    showAlert('Position updated successfully!', 'success');
                } else {
                    // Show error message
                    showAlert(response.message, 'error');
                    // Reset the select to previous value
                    selectElement.val(selectElement.find('option[selected]').val());
                }
            },
            error: function(xhr) {
                var message = 'An error occurred while updating the position.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }
                showAlert(message, 'error');
                // Reset the select to previous value
                selectElement.val(selectElement.find('option[selected]').val());
            },
            complete: function() {
                // Re-enable the select
                selectElement.prop('disabled', false);
            }
        });
    });
    
    function showAlert(message, type) {
        var alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
        var alertHtml = '<div class="alert ' + alertClass + ' alert-dismissible" style="position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px;">' +
            '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>' +
            '<h4><i class="icon fa fa-' + (type === 'success' ? 'check' : 'ban') + '"></i> Alert!</h4>' +
            message +
            '</div>';
        
        $('body').append(alertHtml);
        
        // Auto remove after 3 seconds
        setTimeout(function() {
            $('.alert').fadeOut(function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>

@endsection




