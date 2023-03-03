

<?php $__env->startSection('css'); ?>
	<!-- Sweet Alert CSS -->
	<link href="<?php echo e(URL::asset('plugins/sweetalert/sweetalert2.min.css')); ?>" rel="stylesheet" />
<?php $__env->stopSection(); ?>

<?php $__env->startSection('page-header'); ?>
	<!-- PAGE HEADER -->
	<div class="page-header mt-5-7">
		<div class="page-leftheader">
			<h4 class="page-title mb-0"><?php echo e(__('Dashboard')); ?></h4>
			<ol class="breadcrumb mb-2">
				<li class="breadcrumb-item"><a href="<?php echo e(route('user.dashboard')); ?>"><i class="fa-solid fa-chart-tree-map mr-2 fs-12"></i><?php echo e(__('AI Panel')); ?></a></li>
				<li class="breadcrumb-item active" aria-current="page"><a href="<?php echo e(url('#')); ?>"> <?php echo e(__('Dashboard')); ?></a></li>
			</ol>
		</div>
	</div>
	<!-- END PAGE HEADER -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
	<!-- USER PROFILE PAGE -->
	<div class="row">

		<div class="col-xl-4 col-lg-4 col-md-12">
			<div class="card border-0" id="dashboard-background">
				<div class="card-body text-center">
					<div class="row no-gutters">
					<div class="col-lg-6 col-md-6 col-sm-12">
						<div class="widget-user-image overflow-hidden mx-auto mt-3 mb-4"><img alt="User Avatar" class="rounded-circle" src="<?php if(auth()->user()->profile_photo_path): ?><?php echo e(asset(auth()->user()->profile_photo_path)); ?> <?php else: ?> <?php echo e(URL::asset('img/users/avatar.jpg')); ?> <?php endif; ?>"></div>
						<h4 class="mb-2 mt-2 font-weight-800 fs-16 text-primary text-shadow"><?php echo e(auth()->user()->name); ?></h4>
						<h6 class="text-white fs-12 text-shadow mb-3"><?php echo e(auth()->user()->job_role); ?></h6>
					</div>
					<div class="col-lg-6 col-md-6 col-sm-12">
						<?php if(is_null(auth()->user()->plan_id)): ?>
							<div class="d-flex w-100">
								<div class="flex w-100">
									<h4 class="mb-3 mt-4 font-weight-800 text-primary text-shadow fs-16"><?php echo e(number_format(auth()->user()->available_words + auth()->user()->available_words_prepaid)); ?> / <?php echo e(number_format(auth()->user()->total_words)); ?></h4>
									<h6 class="text-white fs-12 mb-4 text-shadow"><?php echo e(__('Words Left')); ?></h6>
								</div>
								<div class="flex w-100">
									<h4 class="mb-3 mt-4 font-weight-800 text-primary text-shadow fs-16"><?php echo e(number_format(auth()->user()->available_images + auth()->user()->available_images_prepaid)); ?> / <?php echo e(number_format(auth()->user()->total_images)); ?></h4>
									<h6 class="text-white fs-12 mb-4 text-shadow"><?php echo e(__('Images Left')); ?></h6>
								</div>
							</div>
							<span class=" fs-10 btn btn-cancel-black"><i class="fa-sharp fa-solid fa-gifts text-yellow mr-2"></i><?php echo e(__('Free Trial')); ?></span><br>
							<a href="<?php echo e(route('admin.finance.plans')); ?>" class="btn btn-primary mt-3"><i class="fa-solid fa-hand-holding-box mr-2"></i><?php echo e(__('Upgrade Now')); ?></a>
						<?php else: ?>
							<div class="flex w-100">
								<h4 class="mb-3 mt-7 font-weight-800 text-primary text-shadow fs-16"><?php echo e(number_format(auth()->user()->available_words + auth()->user()->available_words_prepaid)); ?> / <?php echo e(number_format(auth()->user()->total_words)); ?></h4>
								<h6 class="text-white fs-12 mb-4 text-shadow"><?php echo e(__('Words Left')); ?></h6>
							</div>
							<div class="flex w-100">
								<h4 class="mb-3 mt-7 font-weight-800 text-primary text-shadow fs-16"><?php echo e(number_format(auth()->user()->available_images + auth()->user()->available_images_prepaid)); ?> / <?php echo e(number_format(auth()->user()->total_images)); ?></h4>
								<h6 class="text-white fs-12 mb-4 text-shadow"><?php echo e(__('Images Left')); ?></h6>
							</div>
							<span class=" fs-10 btn btn-primary"><i class="fa-sharp fa-solid fa-crown text-yellow mr-2"></i><?php echo e($subscription); ?> <?php echo e(__('Plan')); ?></span><br>
						<?php endif; ?>
					</div>
				</div>
				</div>
			</div>
		</div>

		<div class="col-xl-8 col-lg-8 col-md-12">
			<div class="row">

				<div class="col-lg-12 col-md-12 col-sm-12">
					<div class="row">
						<div class="col-lg-6 col-md-12 col-sm-12">
							<div class="card overflow-hidden border-0">
								<div class="card-body d-flex">
									<div class="usage-info w-100">
										<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Documents Created')); ?> <span class="text-muted">(<?php echo e(__('Current Month')); ?>)</span></p>
										<h2 class="mb-2 number-font fs-20"><?php echo e(number_format($data['contents'])); ?> <span class="text-muted fs-18"><?php echo e(__('contents')); ?></span></h2>
									</div>
									<div class="usage-icon w-100 text-right">
										<i class="fa-solid fa-folder-grid"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-12 col-sm-12">
							<div class="card overflow-hidden border-0">
								<div class="card-body d-flex">
									<div class="usage-info w-100">
										<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Words Generated')); ?> <span class="text-muted">(<?php echo e(__('Current Month')); ?>)</span></p>
										<h2 class="mb-2 number-font fs-20"><?php echo e(number_format($data['words'])); ?> <span class="text-muted fs-18"><?php echo e(__('words')); ?></span></h2>
									</div>
									<div class="usage-icon w-100 text-right">
										<i class="fa-solid fa-scroll-old"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-12 col-sm-12">
							<div class="card overflow-hidden border-0">
								<div class="card-body d-flex">
									<div class="usage-info w-100">
										<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Images Created')); ?> <span class="text-muted">(<?php echo e(__('Current Month')); ?>)</span></p>
										<h2 class="mb-2 number-font fs-20"><?php echo e(number_format($data['images'])); ?> <span class="text-muted fs-18"><?php echo e(__('images')); ?></span></h2>
									</div>
									<div class="usage-icon w-100 text-right">
										<i class="fa-solid fa-image-landscape"></i>
									</div>
								</div>
							</div>
						</div>
						<div class="col-lg-6 col-md-12 col-sm-12">
							<div class="card overflow-hidden border-0">
								<div class="card-body d-flex">
									<div class="usage-info w-100">
										<p class=" mb-3 fs-12 font-weight-bold"><?php echo e(__('Templates Used')); ?> <span class="text-muted">(<?php echo e(__('Current Month')); ?>)</span></p>
										<h2 class="mb-2 number-font fs-20"><?php echo e($data['templates_used']); ?> / <?php echo e($templates_total); ?></h2>
									</div>
									<div class="usage-icon w-100 text-right">
										<i class="fa-solid fa-cloud-word"></i>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="col-lg-12 col-md-12 col-xm-12 mt-5">
			<div class="card border-0">
				<div class="card-header border-0">
					<div class="mt-3">
						<h3 class="card-title mb-2"><i class="fa-solid fa-stars mr-2 text-yellow"></i><?php echo e(__('Favorite Templates')); ?></h3>
						<h6 class="text-muted"><?php echo e(__('Always have your top favorite templates handy whenever you need them')); ?></h6>
					</div>
				</div>
				<div class="card-body pt-2 favorite-templates-panel">

					<?php if($template_quantity): ?>
						<div class="row" id="templates-panel">

							<?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $template): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
								<div class="col-lg-3 col-md-6 col-sm-12" id="<?php echo e($template->template_code); ?>">
									<div class="template">
										<a id="<?php echo e($template->template_code); ?>" <?php if($template->favorite): ?> data-tippy-content="<?php echo e(__('Remove from favorite')); ?>" <?php else: ?> data-tippy-content="<?php echo e(__('Select as favorite')); ?>" <?php endif; ?> onclick="favoriteStatus(this.id)"><i class="<?php if($template->favorite): ?> fa-solid fa-stars <?php else: ?> fa-regular fa-star <?php endif; ?> star"></i></a>
										<div class="card <?php if($template->professional): ?> professional <?php elseif($template->favorite): ?> favorite <?php else: ?> border-0 <?php endif; ?>" onclick="window.location.href='<?php echo e(url('user/templates')); ?>/<?php echo e($template->slug); ?>'">
											<div class="card-body pt-5">
												<div class="template-icon mb-4">
													<?php echo $template->icon; ?>													
												</div>
												<div class="template-title">
													<h6 class="mb-2 fs-16 number-font"><?php echo e($template->name); ?></h6>
												</div>
												<div class="template-info">
													<p class="fs-12 text-muted mb-2"><?php echo e($template->description); ?></p>
												</div>
												<?php if($template->professional): ?> <p class="fs-8 btn btn-yellow"><i class="fa-sharp fa-solid fa-crown mr-2"></i><?php echo e(__('Pro')); ?></p> <?php endif; ?>
											</div>
										</div>
									</div>							
								</div>
							<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

						</div>
					<?php else: ?>
						<div class="row text-center mt-8">
							<div class="col-sm-12">
								<h6 class="text-muted"><?php echo e(__('To add templates as your favorite ones, simply click on the start icon on desired ')); ?> <a href="<?php echo e(route('user.templates')); ?>" class="text-primary internal-special-links font-weight-bold"><?php echo e(__('templates')); ?></a></h6>
							</div>
						</div>
					<?php endif; ?>
					
				</div>
			</div>
		</div>

		<div class="col-lg-12 col-md-12 col-xm-12 mt-5">
			<div class="card border-0">
				<div class="card-header border-0">
					<div class="mt-3">
						<h3 class="card-title mb-2"><i class="fa-solid fa-scroll-old mr-2 text-info"></i><?php echo e(__('Word Generation')); ?> <span class="text-muted">(<?php echo e(__('Current Month')); ?>)</span></h3>
						<h6 class="text-muted"><?php echo e(__('Monitor your daily word generation closely')); ?></h6>
					</div>
				</div>
				<div class="card-body pt-2">
					<div class="row">
						<div class="col-lg-12 col-md-12 col-sm-12">
							<div class="">
								<canvas id="chart-monthly-usage" class="h-330"></canvas>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</div>
	<!-- END USER PROFILE PAGE -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js'); ?>
	<!-- Chart JS -->
	<script src="<?php echo e(URL::asset('plugins/chart/chart.min.js')); ?>"></script>
	<script src="<?php echo e(URL::asset('plugins/sweetalert/sweetalert2.all.min.js')); ?>"></script>
	<script>
		$(function() {
	
			'use strict';

			// Total New User Analysis Chart
			var userMonthlyData = JSON.parse(`<?php echo $chart_data['user_monthly_usage']; ?>`);
			var userMonthlyDataset = Object.values(userMonthlyData);
			var ctx = document.getElementById('chart-monthly-usage');
			let delayed1;

			new Chart(ctx, {
				type: 'bar',
				data: {
					labels: ['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'],
					datasets: [{
						label: '<?php echo e(__('Words Generated')); ?> ',
						data: userMonthlyDataset,
						backgroundColor: '#007bff',
						borderWidth: 1,
						borderRadius: 20,
						barPercentage: 0.7,
						fill: true
					}]
				},
				options: {
					maintainAspectRatio: false,
					legend: {
						display: false,
						labels: {
							display: false
						}
					},
					responsive: true,
					animation: {
						onComplete: () => {
							delayed1 = true;
						},
						delay: (context) => {
							let delay = 0;
							if (context.type === 'data' && context.mode === 'default' && !delayed1) {
								delay = context.dataIndex * 50 + context.datasetIndex * 5;
							}
							return delay;
						},
					},
					scales: {
						y: {
							stacked: true,
							ticks: {
								beginAtZero: true,
								font: {
									size: 10
								},
								stepSize: 50000,
							},
							grid: {
								color: '#ebecf1',
								borderDash: [3, 2]                            
							}
						},
						x: {
							stacked: true,
							ticks: {
								font: {
									size: 10
								}
							},
							grid: {
								color: '#ebecf1',
								borderDash: [3, 2]                            
							}
						}
					},
					plugins: {
						tooltip: {
							cornerRadius: 10,
							xPadding: 10,
							yPadding: 10,
							backgroundColor: '#000000',
							titleColor: '#FF9D00',
							yAlign: 'bottom',
							xAlign: 'center',
						},
						legend: {
							position: 'bottom',
							labels: {
								boxWidth: 10,
								font: {
									size: 10
								}
							}
						}
					}
				}
			});

		});

		function favoriteStatus(id) {

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
							Swal.fire('<?php echo e(__('Template Removed from Favorites')); ?>', '<?php echo e(__('Selected template has been successfully removed from favorites')); ?>', 'success');
							document.getElementById(id).style.display = 'none';	
						} else {
							Swal.fire('<?php echo e(__('Template Added to Favorites')); ?>', '<?php echo e(__('Selected template has been successfully added to favorites')); ?>', 'success');
						}
														
					} else {
						Swal.fire('<?php echo e(__('Favorite Setting Issue')); ?>', '<?php echo e(__('There as an issue with setting favorite status for this template')); ?>', 'warning');
					}      
				},
				error: function(data) {
					Swal.fire('Oops...','Something went wrong!', 'error')
				}
			})

			return false;
		}

		function viewTemplate() {
			console.log('hey')

			
		}
	</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/user/dashboard/index.blade.php ENDPATH**/ ?>