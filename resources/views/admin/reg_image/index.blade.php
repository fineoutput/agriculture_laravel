@extends('admin.base_template')

@section('main')

 <h2 style="margin-top: 200px">Image List</h2>
    <a href="{{ route('admin.reg_image.create') }}" class="btn btn-primary mb-3">Upload New Image</a>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Image</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($images as $img)
                <tr>
                    <td><img src="{{ asset($img->image_path) }}" width="100"></td>
                    <td>{{ $img->is_enabled ? 'Enabled' : 'Disabled' }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.reg_image.destroy', $img->id) }}" style="display:inline-block;">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                      <form method="POST" action="{{ route('admin.reg_image.toggle', $img->id) }}" style="display:inline-block;">
    @csrf
    @if($img->is_enabled)
        <button class="btn btn-success btn-sm">Active</button>
    @else
        <button class="btn btn-secondary btn-sm">Inactive</button>
    @endif
</form>

                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection
