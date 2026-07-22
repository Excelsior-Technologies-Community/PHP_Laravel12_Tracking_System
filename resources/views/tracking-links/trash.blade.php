@extends('layouts.app')

@section('title', 'Trash - Tracking Links')


@section('content')


<div class="d-flex justify-content-between align-items-center mb-4">

    <h1>
        <i class="bi bi-trash"></i>
        Deleted Tracking Links
    </h1>


    <a href="{{ route('tracking-links.index') }}"
        class="btn btn-secondary">

        <i class="bi bi-arrow-left"></i>
        Back to Links

    </a>


</div>





@if(session('success'))

<div class="alert alert-success">

    {{ session('success') }}

</div>

@endif





@if($links->count() == 0)


<div class="alert alert-info">

    Trash is empty.

</div>


@else



<div class="card">


    <div class="card-body">


        <div class="table-responsive">


            <table class="table table-bordered table-hover">


                <thead class="table-dark">


                    <tr>

                        <th>
                            Name
                        </th>


                        <th>
                            Original URL
                        </th>


                        <th>
                            Slug
                        </th>


                        <th>
                            Clicks
                        </th>


                        <th>
                            Deleted Date
                        </th>


                        <th>
                            Action
                        </th>


                    </tr>


                </thead>




                <tbody>


                    @foreach($links as $link)


                    <tr>


                        <td>

                            {{ $link->name }}

                        </td>





                        <td>


                            <a href="{{ $link->original_url }}"
                                target="_blank">

                                {{ Str::limit($link->original_url,40) }}

                            </a>


                        </td>





                        <td>

                            {{ $link->slug }}

                        </td>





                        <td>

                            <span class="badge bg-primary">

                                {{ $link->click_count }}

                            </span>


                        </td>





                        <td>


                            {{ $link->deleted_at->format('d M Y h:i A') }}


                        </td>





                        <td>



                            {{-- Restore --}}

                            <form action="{{ route('tracking-links.restore',$link->id) }}"
                                method="POST"
                                class="d-inline">


                                @csrf

                                @method('PUT')


                                <button type="submit"
                                    class="btn btn-success btn-sm"
                                    onclick="return confirm('Restore this link?')">


                                    <i class="bi bi-arrow-counterclockwise"></i>

                                    Restore


                                </button>


                            </form>






                            {{-- Permanent Delete --}}

                            <form action="{{ route('tracking-links.forceDelete',$link->id) }}"
                                method="POST"
                                class="d-inline">


                                @csrf

                                @method('DELETE')


                                <button type="submit"
                                    class="btn btn-danger btn-sm"
                                    onclick="return confirm('Delete permanently?')">


                                    <i class="bi bi-trash-fill"></i>

                                    Delete Forever


                                </button>


                            </form>



                        </td>



                    </tr>


                    @endforeach



                </tbody>


            </table>


        </div>



    </div>


</div>







{{-- Pagination --}}

<div class="d-flex justify-content-center mt-3">


    {{ $links->links() }}


</div>



@endif




@endsection