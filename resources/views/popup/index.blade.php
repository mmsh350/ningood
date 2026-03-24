@extends('layouts.dashboard')

@section('title', 'Popup Notification')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">

            {{-- Card --}}
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="mdi mdi-window-restore mr-2"></i>
                        Edit Popup Notification
                    </h5>
                </div>

                <div class="card-body">
                    {{-- Success alert --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-circle-outline mr-1"></i>
                            {{ session('success') }}

                        </div>
                    @endif

                    {{-- Form --}}
                    <form method="POST" action="{{ route('admin.popup.store') }}">
                        @csrf


                         {{-- Message --}}
                        <div class="form-group mb-4">
                            <label for="title" class="font-weight-bold">Title</label>
                            <input class="form-control" name="title" id="title"  value="{{ old('title', $popup->title ?? '') }}" required/>
                            <small class="form-text text-muted">This text will be displayed in the popup title.</small>
                        </div>


                        {{-- Message --}}
                        <div class="form-group mb-4">
                            <label for="message" class="font-weight-bold">Popup Message</label>
                            <textarea class="form-control" name="message" id="message" rows="4" required>{{ old('message', $popup->message ?? '') }}</textarea>
                            <small class="form-text text-muted">This text will be displayed in the popup.</small>
                        </div>

                        {{-- Active switch --}}
                        <div class="custom-control custom-switch mb-4">
                            <input type="checkbox" class="custom-control-input" name="is_active" id="is_active"
                                {{ isset($popup) && $popup->is_active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="is_active">Enable Popup</label>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ url()->previous() }}" class="btn btn-light border">
                                <i class="mdi mdi-arrow-left mr-1"></i> Back
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save mr-1"></i> Save Popup
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
