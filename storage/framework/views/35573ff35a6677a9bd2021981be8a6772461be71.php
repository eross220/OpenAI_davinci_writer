<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
	<head>
		<!-- METADATA -->
		<meta charset="UTF-8">
		<meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
		<meta content="" name="description">
		<meta content="" name="author">
		<meta name="keywords" content=""/>
		
        <!-- CSRF TOKEN -->
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <!-- TITLE -->
        <title><?php echo e(config('app.name', 'Polly')); ?></title>
        
        <?php echo $__env->make('layouts.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

	</head>

	<body class="app sidebar-mini">

		<!-- LOADER -->
		<div id="preloader" >
			<img src="<?php echo e(URL::asset('img/svgs/preloader.gif')); ?>" alt="loader">           
		</div>
		<!-- END LOADER -->

		<!-- PAGE -->
		<div class="page">
			<div class="page-main">

				<?php echo $__env->make('layouts.nav-aside', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

				<!-- APP CONTENT -->			
				<div class="app-content main-content">

					<div class="side-app">

						<?php echo $__env->make('layouts.nav-top', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

                        

						<?php echo $__env->yieldContent('page-header'); ?>

						<?php echo $__env->yieldContent('content'); ?>						

                    </div>                   
                </div>
                <!-- END APP CONTENT -->

                <?php echo $__env->make('layouts.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>                

            </div>		
        </div><!-- END PAGE -->
        
		<?php echo $__env->make('layouts.footer-backend', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>        

	</body>
</html>


<?php /**PATH E:\workspace\openaidavinci-1.0\resources\views/layouts/app.blade.php ENDPATH**/ ?>