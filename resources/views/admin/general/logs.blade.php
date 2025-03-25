@extends('admin.general.layout')
@section('general')
    <div class="card bg-transparent border rounded-3">
        <!-- Card header START -->
        <div class="card-header bg-transparent border-bottom">
            <div class="row">
                <!-- Content -->
                <div class="col-md-5">
                    <h3 class="mb-0">Error Log Files</h3>
                </div>

                <!-- Select option -->
                <div class="col-md-7 text-end">
                    <!-- Short by filter -->
                    <a href="{{ route('admingenerallogs.destroy_all') }}" class="btn btn-sm btn-danger me-1 ">
                        <i class="bi bi-trash me-1"></i>Delete all </a>

                    <span class="js-choice ">

                    </span>
                </div>
            </div>

        </div>
        <!-- Card header END -->

        <!-- Card body START -->
        <div class="card-body">

            <!-- Course list table START -->
            <div class="table-responsive border-0">
                <table class="table table-dark-gray align-middle p-4 mb-0 table-hover">
                    <thead>
                        <tr class="bg-gray-2 text-left dark:bg-meta-4">
                            <th class="px-4 py-4 font-medium text-light dark:text-white">
                                Error
                            </th>
                            <th class="px-4 py-4 font-medium text-light dark:text-white">
                                Time
                            </th>
                            <th class="px-4 py-4 font-medium text-light dark:text-white">

                            </th>
                        </tr>
                    </thead>
                    <tbody>

                        @forelse ($filelists as $item)
                            <tr>
                                <td class="border-b border-[#eee] px-4 py-5 pl-5 dark:border-strokedark">
                                    <div class="flex items-center gap-3">
                                        <p class="hidden font-medium text-black dark:text-white sm:block capitalize">

                                            {{ str_replace('.html', '', str_replace('_', '-', $item)) }}
                                        </p>
                                    </div>
                                </td>
                                <td class="border-b border-[#eee] px-4 py-5 pl-5 dark:border-strokedark">
                                    <div class="flex items-center gap-3">
                                        <p class="hidden font-medium text-black dark:text-white sm:block capitalize">

                                            {{ str_replace('.html', '', str_replace('_', '-', $item)) }}
                                        </p>
                                    </div>
                                </td>

                                <td class="border-b border-[#eee] px-4 py-5 dark:border-strokedark text-end">
                                    <div class="flex items-center">
                                        <a title='View details' class="ms-auto me-2"
                                            role="button"  href="{{ route('admingenerallogs.show', $item) }}">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a title="Delete log" role="button"
                                        href="{{ route('admingenerallogs.delete',str_replace('.html', '', $item)) }}">
                                           <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100"
                                    class="border-b text-center border-[#eee] px-4 py-4 dark:border-strokedark">
                                    No records found.
                                </td>
                            </tr>
                        @endforelse

                    </tbody>
                </table>
            </div>
            <!-- Course list table END -->

        </div>
        <!-- Card body START -->
    </div>
    <!-- Main content END -->
@endsection
