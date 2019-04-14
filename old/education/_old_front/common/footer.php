		<div class="clear"></div>
	</div>
</div>
<div id="footer">
	<div class="wrapper">
		<div class="box latest listings">
			<h2>Latest News</h2>
		
			<div class="items">
			<?php 
				$q = mysql_query("SELECT * FROM site_news ORDER BY rank DESC LIMIT 2");
				if( $q && mysql_num_rows( $q )) {
					while( $row = mysql_fetch_object( $q )) {
						$link = "Site-News/". cleanTitleURL( $row->title ) ."/{$row->id}";
						?>
						<div class="item">
							<div class="image">
								<a href="<?php echo $link; ?>"><img src="uploads/site_news/thumb/<?php echo $row->image; ?>" border="0" /></a>
							</div>
							<div class="description">
								<?php echo summarize_my_text($row->description, 28); ?>
								 <a href="<?php echo $link; ?>">read more</a>
							</div>
						</div>
						<?php 
					}
				}
			?>
				<div class="clear"></div>
				<div class="more">
					<a href="Site-News/">More News</a>
				</div>
			</div>
		</div>
		<div class="box contact">
			<h2>Contact Us</h2>
		
			<div class="description">
				Feel free to contact us any time by dialing:
				<br />+961 9 9444441 - +961 71 417872
				<br />or visit us: Kassouba Street, Byblos, Lebanon
			</div>
			<div class="follow">
				<h2>Follow us</h2>
				
				<div class="follow-icons">
					<div class="follow-icon"><a target="_blabk" href="https://www.facebook.com/"><img src="images/footer-icon-facebook.png" /></a></div>
					<div class="follow-icon"><a target="_blabk" href="https://www.twitter.com/"><img src="images/footer-icon-twitter.png" /></a></div>
					<div class="follow-icon"><a href="Contact-Us/"><img src="images/footer-icon-contact.png" /></a></div>
					<div class="follow-icon"><a target="_blabk" href="https://www.flickr.com/"><img src="images/footer-icon-flickr.png" /></a></div>
					
					<div class="clear"></div>
				</div>
			</div>
		</div>
	
	
	
	
	
		<div class="social_techram">
			<div>
				<a href="http://www.techram.co/" target="_blank">
				<h2 class="cs-text">
					<span>Designed</span>
					<span>&amp; </span>																								
					<span>Developed</span>																																											
					<span>by</span>
					<span class="special">TECHRAM</span>				
					<span></span>
				</h2>
				</a>
			</div>
		</div>
		<div class="clear"></div>
	</div>
	<div class="Copyright">
		© <?php echo date('Y'); ?> Advaces and more. All Rightst Reserved.
	</div>
</div>
</div>
</body>
</html>