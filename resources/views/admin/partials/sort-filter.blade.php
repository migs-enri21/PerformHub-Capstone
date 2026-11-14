@php
    $sortByLabel = $sortByLabel ?? 'Name';
    $defaultSort = $defaultSort ?? 'latest';
    $sortColumnClass = $sortColumnClass ?? 'col-md-2';
    $requestedSort = request('sort');
    $selectedSort = in_array($requestedSort, ['latest', 'name_asc', 'name_desc'], true)
        ? $requestedSort
        : $defaultSort;
@endphp
<div class="{{ $sortColumnClass }}">
    <label class="form-label" for="adminListSort">Sort</label>
    <select name="sort" id="adminListSort" class="form-select ph-input">
        <option value="latest" @selected($selectedSort === 'latest')>Newest</option>
        <option value="name_asc" @selected($selectedSort === 'name_asc')>{{ $sortByLabel }} A-Z</option>
        <option value="name_desc" @selected($selectedSort === 'name_desc')>{{ $sortByLabel }} Z-A</option>
    </select>
</div>
