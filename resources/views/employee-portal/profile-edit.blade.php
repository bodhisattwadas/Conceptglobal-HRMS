@extends('layouts.app', ['heading' => 'Edit Profile', 'subheading' => $employee->full_name])

@section('content')
@php
    $documentTypes = $documentTypes ?? [];
    $relatedDocs = collect($employee->related_document_paths ?? [])->map(function ($doc) {
        return is_array($doc) ? $doc : ['type' => 'Document', 'name' => basename((string) $doc), 'path' => (string) $doc];
    });
@endphp

<form method="post" action="{{ route('employee.profile.update') }}" enctype="multipart/form-data">
    @csrf
    @method('put')

    <div class="card table-card">
        <div class="card-header bg-white fw-semibold">My Profile</div>
        <div class="card-body">
            <div class="row g-3 align-items-start">
                <div class="col-lg-9">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name</label>
                            <input name="first_name" value="{{ old('first_name', $employee->first_name) }}" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name</label>
                            <input name="last_name" value="{{ old('last_name', $employee->last_name) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input value="{{ $employee->email }}" class="form-control" disabled>
                            <div class="form-text">Contact admin to change login email.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input name="phone" value="{{ old('phone', $employee->phone) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">Select</option>
                                @foreach (['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('gender', $employee->gender) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date of Birth</label>
                            <input name="date_of_birth" type="date" value="{{ old('date_of_birth', $employee->date_of_birth?->format('Y-m-d')) }}" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Address</label>
                            <textarea name="address" class="form-control" rows="2">{{ old('address', $employee->address) }}</textarea>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Country</label>
                            <input name="country" value="{{ old('country', $employee->country) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">State</label>
                            <input name="state" value="{{ old('state', $employee->state) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">City</label>
                            <input name="city" value="{{ old('city', $employee->city) }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">ZIP</label>
                            <input name="zip" value="{{ old('zip', $employee->zip) }}" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Image Upload</label>
                            <input name="profile_photo_file" type="file" class="form-control" accept="image/jpeg,image/png,image/webp">
                            <div class="form-text">JPG, PNG, or WebP. 100x100 to 3000x3000 px. Max 2 MB.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CV Upload</label>
                            <input name="cv_file" type="file" class="form-control" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                            <div class="form-text">PDF, DOC, or DOCX. Max 10 MB.</div>
                            @if($employee->cv_file_path)
                                <div class="mt-2">
                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($employee->cv_file_path) }}" target="_blank">View current CV</a>
                                </div>
                            @endif
                        </div>
                        <div class="col-12">
                            <label class="form-label">Documents</label>
                            <div class="employee-doc-upload-list" data-doc-upload-list>
                                <div class="employee-doc-upload-row">
                                    <select name="related_document_types[]" class="form-select">
                                        <option value="">Select document type</option>
                                        @foreach($documentTypes as $type)
                                            <option value="{{ $type }}">{{ $type }}</option>
                                        @endforeach
                                    </select>
                                    <input name="related_documents[]" type="file" class="form-control" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                                    <button type="button" class="btn btn-outline-secondary" data-remove-doc-row aria-label="Remove document row">&times;</button>
                                </div>
                            </div>
                            <button type="button" class="btn btn-sm btn-light border mt-2" data-add-doc-row>
                                <i class="bi bi-plus-lg"></i>
                                Add Document
                            </button>
                            <div class="form-text">Upload documents one by one. Allowed: PDF, DOC, DOCX, PNG, JPG, JPEG. Max 10 MB each.</div>
                        </div>
                        @if($relatedDocs->isNotEmpty())
                            <div class="col-12">
                                <div class="fw-semibold mb-2">Current Documents</div>
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>File</th>
                                                <th>Uploaded</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($relatedDocs as $doc)
                                                <tr>
                                                    <td>{{ $doc['type'] ?? 'Document' }}</td>
                                                    <td>
                                                        @if(!empty($doc['path']))
                                                            <a href="{{ \Illuminate\Support\Facades\Storage::url($doc['path']) }}" target="_blank">{{ $doc['name'] ?? basename($doc['path']) }}</a>
                                                        @else
                                                            {{ $doc['name'] ?? '-' }}
                                                        @endif
                                                    </td>
                                                    <td>{{ $doc['uploaded_at'] ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="employee-profile-avatar">
                        @if($employee->profile_photo_url)
                            <img src="{{ $employee->profile_photo_url }}" alt="{{ $employee->full_name }}">
                        @else
                            <span>{{ $employee->initials }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button class="btn btn-primary">Save Profile</button>
                <a href="{{ route('employee.dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </div>
    </div>
</form>
@endsection

@push('styles')
    <style>
        .employee-profile-avatar {
            align-items: center;
            background: #6f42c1;
            color: #fff;
            display: flex;
            font-size: 3rem;
            font-weight: 700;
            height: 150px;
            justify-content: center;
            margin-left: auto;
            overflow: hidden;
            width: 150px;
        }
        .employee-profile-avatar img {
            height: 100%;
            object-fit: cover;
            width: 100%;
        }
        .employee-doc-upload-list {
            display: grid;
            gap: 10px;
        }
        .employee-doc-upload-row {
            display: grid;
            gap: 10px;
            grid-template-columns: minmax(180px, 260px) minmax(220px, 1fr) auto;
        }
        @media (max-width: 900px) {
            .employee-doc-upload-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        document.addEventListener('click', function (event) {
            const addButton = event.target.closest('[data-add-doc-row]');
            const removeButton = event.target.closest('[data-remove-doc-row]');

            if (addButton) {
                const list = document.querySelector('[data-doc-upload-list]');
                const row = list.querySelector('.employee-doc-upload-row').cloneNode(true);

                row.querySelectorAll('select, input').forEach(function (field) {
                    field.value = '';
                });

                list.appendChild(row);
            }

            if (removeButton) {
                const list = removeButton.closest('[data-doc-upload-list]');
                const rows = list.querySelectorAll('.employee-doc-upload-row');

                if (rows.length > 1) {
                    removeButton.closest('.employee-doc-upload-row').remove();
                }
            }
        });
    </script>
@endpush
