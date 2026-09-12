<div class="ph-card p-4 {{ $extraClass ?? '' }}">
    <h5 class="fw-bold mb-3">{{ $heading }}</h5>
    <div class="table-responsive">
        <table class="table table-dark table-hover mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                    <tr>
                        <td class="align-middle"><strong>{{ $item->name }}</strong></td>
                        <td class="align-middle"><p class="mb-0 text-truncate" style="max-width: 350px;">{{ $item->description ?? '—' }}</p></td>
                        <td class="align-middle"><span class="badge {{ $item->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $item->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td class="align-middle text-end">
                            <a href="{{ route('admin.'.$prefix.'.show', $item) }}" class="btn btn-sm btn-outline-info" title="View"><i class="fas fa-eye"></i></a>
                            <a href="{{ route('admin.'.$prefix.'.edit', $item) }}" class="btn btn-sm btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                            <form method="POST" action="{{ route('admin.'.$prefix.'.toggle', $item) }}" class="d-inline" style="display:inline-block;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm {{ $item->is_active ? 'btn-outline-secondary' : 'btn-outline-success' }}" title="{{ $item->is_active ? 'Deactivate' : 'Activate' }}"><i class="fas {{ $item->is_active ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i></button>
                            </form>
                            <form method="POST" action="{{ route('admin.'.$prefix.'.destroy', $item) }}" class="d-inline" onsubmit="return confirm('Delete this {{ $itemLabel }}?');" style="display:inline-block;">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            <i class="fas fa-inbox fa-2x mb-3 d-block"></i>
                            {{ $empty }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
</div>
