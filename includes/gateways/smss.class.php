<?php

class smss extends WP_SMS {
	private $wsdl_link = "http://api.smss.co.il/";
	public $tariff = "";
	public $unitrial = false;
	public $unit;
	public $flash = "enable";
	public $isflash = false;

	public function __construct() {
		parent::__construct();
		$this->validateNumber = "The phone number of the message recipient. It should be a valid phone number in E164 format";
	}

	public function SendSMS() {
		// Check gateway credit
		if ( is_wp_error( $this->GetCredit() ) ) {
			return new WP_Error( 'account-credit', __( 'Your account does not credit for sending sms.', 'wp-sms' ) );
		}

		/**
		 * Modify sender number
		 *
		 * @since 3.4
		 *
		 * @param string $this ->from sender number.
		 */
		$this->from = apply_filters( 'wp_sms_from', $this->from );

		/**
		 * Modify Receiver number
		 *
		 * @since 3.4
		 *
		 * @param array $this ->to receiver number
		 */
		$this->to = apply_filters( 'wp_sms_to', $this->to );

		/**
		 * Modify text message
		 *
		 * @since 3.4
		 *
		 * @param string $this ->msg text message.
		 */
		$this->msg = apply_filters( 'wp_sms_msg', $this->msg );

		$postdata = http_build_query(
			array(
				'user'      => $this->username,
				'password'  => $this->password,
				'from'      => $this->from,
				'recipient' => implode( ',', $this->to ),
				'message'   => $this->msg,
			)
		);

		$opts     = array(
			'http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
		);
		$context  = stream_context_create( $opts );
		$response = file_get_contents( 'MultiSendAPI/sendsms', false, $context );

		$result = json_decode( $response );

		if ( $result->success ) {
			$this->InsertToDB( $this->from, $this->msg, $this->to );

			/**
			 * Run hook after send sms.
			 *
			 * @since 2.4
			 *
			 * @param string $result result output.
			 */
			do_action( 'wp_sms_send', $result );

			return $result;
		}

		return new WP_Error( 'send-sms', print_r( $result ) );
	}

	public function GetCredit() {
		// Check username and password
		if ( ! $this->username && ! $this->password ) {
			return new WP_Error( 'account-credit', __( 'Username/Password does not set for this gateway', 'wp-sms' ) );
		}

		$response = file_get_contents( $this->wsdl_link . 'MultiSendAPI/balance&user=' . $this->username . '&password=' . $this->password . '&country_phone_code=' . $this->options['mobile_county_code'] );
		$result   = json_decode( $response );

		if ( $result->success ) {
			return $result->sms;
		}

		return new WP_Error( 'account-credit', print_r( $result ) );
	}
}