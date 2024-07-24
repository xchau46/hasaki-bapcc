<?php echo form_open(base_url() . "lien-he"); ?>
<section>
	<div class="container">
		<div class="col-md-7 col-12">
			<div class="section-article contactpage" style="  padding-left: 20px;">
				<?php
				echo validation_errors();

				?>
				<form accept-charset="UTF-8" action="<?php echo base_url() ?>lien-he" id="contact" method="post">
					<input name="FormType" type="hidden" value="contact">
					<input name="utf8" type="hidden" value="true">
					<h1 style="color: black">Liên hệ với chúng tôi</h1>

					<div class="form-comment">
						<div class="row" style="padding-left: 14px;">
							<div class="col-md-4 col-12">
								<div class="form-group" style="width: 200px;">
									<label for="name"><em> Họ tên</em><span class="required">*</span></label>
									<input id="name" name="fullname" type="text" value="" class="form-control">
								</div>
							</div>
							<div class="col-md-4 col-12">
								<div class="form-group" style="width: 200px;">
									<label for="email"><em> Email</em><span class="required">*</span></label>
									<input id="email" name="email" class="form-control" type="email" value="">
								</div>
							</div>
							<div class="col-md-4 col-12">
								<div class="form-group" style="width: 200px;">
									<label for="phone"><em> Số điện thoại</em><span class="required">*</span></label>
									<input type="number" id="phone" class="form-control" name="phone">

								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="message"><em> Tiêu đề</em><span class="required">*</span></label>
							<textarea id="message" name="title" class="form-control custom-control" rows="2"></textarea>
						</div>
						<div class="form-group">
							<label for="message"><em> Lời nhắn</em><span class="required">*</span></label>
							<textarea id="message" name="content" class="form-control custom-control" rows="5"></textarea>
						</div>
						<button type="submit" class="btn-update-order">Gửi nhận xét</button>

					</div>
				</form>
			</div>
		</div>
		<div class="col-md-4 col-12">
			<div class="f-contact" style="
			padding-left: 32px;
			">
				<h1 style="color: black">Thông tin liên hệ</h1>
				<ul class="list-unstyled">
					<li class="clearfix">
						<i class="fa fa-map-marker fa-1x" style="color:#0f9ed8; padding: 20px; "></i>
						<span style="color: black"> 43/19 đường số 27 phường 6, Gò Vấp, Tp. HCM</span><br>
					</li>
					<li class="clearfix">
						<i class="fa fa-phone fa-1x" style="color:#0f9ed8;padding: 20px;  "></i>
						<span style="color: black">0949604728 - 0888753246</span>
					</li>
					<li class="clearfix">
						<i class="fa fa-envelope fa-1x " style="color:#0f9ed8; padding: 20px; "></i>
						<span style="color: black"><a href="mailto:sale.24hstore@gmail.com">@gmail.com</a></span>
					</li>
				</ul>
			</div>

		</div>
		<div class="col-md-12 col-lg-12 col-xs-12 col-12">

			<div style="margin-top: 15px;">
				<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3918.5901289050894!2d106.67607797461794!3d10.84264545796966!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752852fd1675b3%3A0x1433639bcf19e7da!2zNDMgxJAuIFPhu5EgMjcsIFBoxrDhu51uZyA2LCBHw7IgVuG6pXAsIFRow6BuaCBwaOG7kSBI4buTIENow60gTWluaCwgVmnhu4d0IE5hbQ!5e0!3m2!1svi!2s!4v1683095617788!5m2!1svi!2s"></iframe>
			</div>
		</div>
	</div>

</section>