<div id="sumup-card" style="min-height: 521px;"></div>

<script src="https://gateway.sumup.com/gateway/ecom/card/v2/sdk.js" ></script>
<script>
SumUpCard.mount({
	id: 'sumup-card',
	checkoutId: '<?php echo $_GET['id'] ?>',
	locale: 'en-GB',
	country: 'GB',
	currency: 'EUR',
	onResponse: function (type, body) {
		if(type == 'success'){
			window.location.href = './gateway.php?id=' + body.id + '&verify';
		}
		if(type == 'error' || type == 'invalid'){
			alert('An error occurred while processing the payment\n' + body.detail);
		}
	}
});
</script>
