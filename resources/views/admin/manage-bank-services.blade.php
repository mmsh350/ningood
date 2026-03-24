@extends('layouts.dashboard')

@section('title', 'Manage Bank Services')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Bank Services & Pricing</h5>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Bank</th>
                                    <th>Service</th>
                                    <th>Base Price (₦)</th>
                                    <th>Commission (₦)</th>
                                    <th>Total Price (₦)</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($bankServices as $index => $bs)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $bs->bank->name }}</td>
                                        <td>{{ $bs->service->name }}</td>
                                        <td>{{ number_format($bs->price, 2) }}</td>
                                        <td>{{ number_format($bs->commission, 2) }}</td>
                                        <td>{{ number_format($bs->total_price, 2) }}</td>
                                        <td>
                                            <span class="badge {{ $bs->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $bs->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                                data-bs-target="#editPrice{{ $bs->id }}">
                                                Edit Price
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editPrice{{ $bs->id }}" tabindex="-1"
                                        aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h6 class="modal-title">Edit Price - {{ $bs->bank->name }}
                                                        ({{ $bs->service->name }})
                                                    </h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('admin.bank-services.update-price') }}"
                                                    method="POST">
                                                    @csrf
                                                    <input type="hidden" name="bank_id" value="{{ $bs->bank_id }}">
                                                    <input type="hidden" name="service_id" value="{{ $bs->service_id }}">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label class="form-label">Base Price (₦)</label>
                                                            <input type="number" name="price" step="0.01"
                                                                class="form-control" value="{{ $bs->price }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Commission (₦)</label>
                                                            <input type="number" name="commission" step="0.01"
                                                                class="form-control" value="{{ $bs->commission }}"
                                                                required>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-primary">Save Changes</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
