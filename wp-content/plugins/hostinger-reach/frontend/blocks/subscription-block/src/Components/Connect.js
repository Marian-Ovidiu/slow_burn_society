import {__} from '@wordpress/i18n';
import './Connect.scss'

const Connect = () => {
	return <div className="hostinger-reach-block-connect">
		<div className="hostinger-reach-block-connect__title">
			{__("You are not connected to Hostinger Reach", "hostinger-reach")}
		</div>
		<div className="hostinger-reach-block-connect__subtitle">
			{__("You are not connected to Hostinger Reach. To gain full access to this block, you need to connect your Hostinger Reach account.", "hostinger-reach")}
		</div>
		<div className="hostinger-reach-block-connect__button-wrap"><a
			target="_blank"
			href="/wp-admin/admin.php?page=hostinger-reach"
			className="hostinger-block-button hostinger-block-button--is-normal hostinger-block-button--is-primary">
			{__("Connect Now", "hostinger-reach")}
		</a></div>
	</div>
}

export default Connect;
