@extends('layouts.app')

@section('css')
	<!-- Sweet Alert CSS -->
	<link href="{{URL::asset('plugins/sweetalert/sweetalert2.min.css')}}" rel="stylesheet" />
@endsection

@section('page-header')
	<!-- PAGE HEADER -->
	<div class="page-header mt-5-7">
		<div class="page-leftheader">
			<h4 class="page-title mb-0">{{ __('Templates') }}</h4>
			<ol class="breadcrumb mb-2">
				<li class="breadcrumb-item"><a href="{{route('user.dashboard')}}"><i class="fa-solid fa-microchip-ai mr-2 fs-12"></i>{{ __('AI Panel') }}</a></li>
				<li class="breadcrumb-item active" aria-current="page"><a href="{{url('#')}}"> {{ __('Templates') }}</a></li>
			</ol>
		</div>
	</div>
	<!-- END PAGE HEADER -->
@endsection

@section('content')
	<!-- USER PROFILE PAGE -->
	<div class="row">

		<div class="col-lg-12 col-md-12 col-sm-12">
			<div class="card border-0">
				<div class="card-header border-0">
					<div class="mt-3">
						<h3 class="card-title mb-2"><i class="fa-solid fa-scroll-old mr-2 text-primary"></i>{{ __('All Templates') }}</h3>
						<h6 class="text-muted">{{ __('Got some ideas to generate? We got you covered! Checkout list of templates that you can use.') }}</h6>
					</div>
				</div>
				<div class="card-body pt-2 favorite-templates-panel">

					<div class="row" id="templates-panel">

						@foreach ($favorite_templates as $template)
							<div class="col-lg-3 col-md-6 col-sm-12">
								<div class="template">
									<a id="{{ $template->template_code }}" @if($template->favorite) data-tippy-content="{{ __('Remove from favorite') }}" @else data-tippy-content="{{ __('Select as favorite') }}" @endif onclick="favoriteStatus(this.id)"><i id="{{ $template->template_code }}-icon" class="@if($template->favorite) fa-solid fa-stars @else fa-regular fa-star @endif star"></i></a>
									<div class="card @if($template->professional) professional @elseif($template->favorite) favorite @else border-0 @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('user/templates') }}/{{ $template->slug }}'">
										<div class="card-body pt-5">
											<div class="template-icon mb-4">
												{!! $template->icon !!}												
											</div>
											<div class="template-title">
												<h6 class="mb-2 fs-16 number-font">{{ $template->name }}</h6>
											</div>
											<div class="template-info">
												<p class="fs-12 text-muted mb-2">{{ $template->description }}</p>
											</div>
											@if($template->professional) <p class="fs-8 btn btn-yellow"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }}</p> @endif
										</div>
									</div>
								</div>							
							</div>
						@endforeach

						@foreach ($other_templates as $template)
							<div class="col-lg-3 col-md-6 col-sm-12">
								<div class="template">
									<a id="{{ $template->template_code }}" @if($template->favorite) data-tippy-content="{{ __('Remove from favorite') }}" @else data-tippy-content="{{ __('Select as favorite') }}" @endif onclick="favoriteStatus(this.id)"><i id="{{ $template->template_code }}-icon" class="@if($template->favorite) fa-solid fa-stars @else fa-regular fa-star @endif star"></i></a>
									<div class="card @if($template->professional) professional @elseif($template->favorite) favorite @else border-0 @endif" id="{{ $template->template_code }}-card" onclick="window.location.href='{{ url('user/templates') }}/{{ $template->slug }}'">
										<div class="card-body pt-5">
											<div class="template-icon mb-4">
												{!! $template->icon !!}												
											</div>
											<div class="template-title">
												<h6 class="mb-2 fs-16 number-font">{{ $template->name }}</h6>
											</div>
											<div class="template-info">
												<p class="fs-12 text-muted mb-2">{{ $template->description }}</p>
											</div>
											@if($template->professional) <p class="fs-8 btn btn-yellow"><i class="fa-sharp fa-solid fa-crown mr-2"></i>{{ __('Pro') }}</p> @endif
										</div>
									</div>
								</div>							
							</div>
						@endforeach

					</div>					
				</div>
			</div>
		</div>

	</div>
	<!-- END USER PROFILE PAGE -->
@endsection

@section('js')
	<script src="{{URL::asset('plugins/sweetalert/sweetalert2.all.min.js')}}"></script>
	<script>
		function favoriteStatus(id) {

			let icon, card;
			let formData = new FormData();
			formData.append("id", id);

			$.ajax({
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
				method: 'post',
				url: 'dashboard/favorite',
				data: formData,
				processData: false,
				contentType: false,
				success: function (data) {

					if (data['status'] == 'success') {
						if (data['set']) {
							Swal.fire('{{ __('Template Removed from Favorites') }}', '{{ __('Selected template has been successfully removed from favorites') }}', 'success');
							icon = document.getElementById(id + '-icon');
							icon.classList.remove("fa-solid");
							icon.classList.remove("fa-stars");
							icon.classList.add("fa-regular");
							icon.classList.add("fa-star");

							card = document.getElementById(id + '-card');
							if(card.classList.contains("professional")) {
								// do nothing
							} else {
								card.classList.remove("favorite");
								card.classList.add('border-0');
							}							
						} else {
							Swal.fire('{{ __('Template Added to Favorites') }}', '{{ __('Selected template has been successfully added to favorites') }}', 'success');
							icon = document.getElementById(id + '-icon');
							icon.classList.remove("fa-regular");
							icon.classList.remove("fa-star");
							icon.classList.add("fa-solid");
							icon.classList.add("fa-stars");

							card = document.getElementById(id + '-card');
							if(card.classList.contains("professional")) {
								// do nothing
							} else {
								card.classList.add('favorite');
								card.classList.remove('border-0');
							}
						}
														
					} else {
						Swal.fire('{{ __('Favorite Setting Issue') }}', '{{ __('There as an issue with setting favorite status for this template') }}', 'warning');
					}      
				},
				error: function(data) {
					Swal.fire('Oops...','Something went wrong!', 'error')
				}
			})
		}
	</script>
@endsection
