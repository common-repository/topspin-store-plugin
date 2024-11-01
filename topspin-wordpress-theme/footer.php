<?php $artistID = get_option('topspin_artist_id'); ?>
		</div>

		<div id="footer">
			<div id="ts_footer">
				<?php if(!empty($artistID)) : ?><a href="https://app.topspin.net/fan/login?artist_id=<?php echo $artistID; ?>">My Account</a> |<?php endif; ?>
				<a href="https://app.topspin.net/account/help_public">Customer Support</a> |
				<a href="http://app.topspin.net/account/privacypolicy_public">Privacy Policy</a> |
				<a href="https://app.topspin.net/account/terms_public">Terms of Service</a>
			</div>
		</div>
	</div>
</body>
</html>