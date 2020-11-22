<template>
	<view class="content bg-white" :style="'height:'+windowHeight+'px'">
		<view class="title">邀请好友得现金</view>
		<view class="describe text-center text-gray">成功邀请一位好友注册我们，您将获得<span class="text-red text-bold">10元现金</span></view>
		<view class="describe text-center text-gray">上不封顶，快去邀请吧</view>
		<view class="img">
			<image src="../../static/share.jpg"></image>
		</view>
		<view class="sub flex">
			<view class="flex-sub text-center">
				<button class="cu-btn round bg-red" @click="shareFc">生成邀请码</button>
			</view>
		</view>
		<view class="flow flex justify-center">
			<view class="ll">
				<view class="icon-box bg-red"><span class="cuIcon-forward"></span></view>
				<view class="name">分享好友</view>
			</view>
			<view class="ll text-gray">
				<span class="icon cuIcon-more"></span>
				<span class="icon cuIcon-right"></span>
			</view>
			<view class="ll">
				<view class="icon-box bg-red"><span class="cuIcon-myfill"></span></view>
				<view class="name">注册</view>
			</view>
			<view class="ll text-gray">
				<span class="icon cuIcon-more"></span>
				<span class="icon cuIcon-right"></span>
			</view>
			<view class="ll">
				<view class="icon-box bg-red"><span class="cuIcon-redpacket_fill"></span></view>
				<view class="name">领取资金</view>
			</view>
		</view>
		<view class="flex_row_c_c modalView" :class="qrShow?'show':''" @tap="hideQr()">
			<view class="flex_column">
				<view class="backgroundColor-white padding1vh border_radius_10px">
					<image :src="poster.finalPath || ''" mode="widthFix" class="posterImage" @tap="previewImage()"></image>
					<view class="text-center">点击图片后可长按下载或转发给好友</view>
				</view>
			</view>
		</view>
		<!-- 画布 -->
		<view class="hideCanvasView">
			<canvas class="hideCanvas" canvas-id="default_PosterCanvasId" :style="{width: (poster.width||10) + 'px', height: (poster.height||10) + 'px'}"></canvas>
		</view>
	</view>
</template>

<script>
	import _app from '@/components/QS-SharePoster/app.js';
	import User from '../../api/user';
	import {
		getSharePoster
	} from '@/components/QS-SharePoster/QS-SharePoster.js';
	export default {
		data(){
			return {
				poster: {},
				windowHeight: '600',
				screenHeight: '500',
				qrcode: '',
				canvasImage: {},
				qrShow: false,
				canvasId: 'default_PosterCanvasId',
				uuid: ''
			}
		},
		onLoad() {
			const that = this
			uni.getSystemInfo({
			    success: function (res) {
			        that.windowHeight = res.windowHeight
					that.screenHeight = res.screenHeight
			    }
			})
			this.getUser()
		},
		methods:{
			async shareFc() {
				try {
					const d = await getSharePoster({
						_this: this, //若在组件中使用 必传
						type: 'testShareType',
						backgroundImage: '/static/temp/ad-splash.jpg', //背景图片路径
						posterCanvasId: this.canvasId,	//canvasId
						delayTimeScale: 20, //延时系数
						drawArray: ({
							bgObj,
							type,
							bgScale
						}) => {
							const dx = bgObj.width * 0.3;
							const fontSize = bgObj.width * 0.045;
							const lineHeight = bgObj.height * 0.04;
							const url = this.configURL.DomainName +'/h5/#/pages/public/register?uuid=' + this.uuid
							//可直接return数组，也可以return一个promise对象, 但最终resolve一个数组, 这样就可以方便实现后台可控绘制海报
							return new Promise((rs, rj) => {
								rs([{
									type: 'roundFillRect',
									backgroundColor: '#ffffff',
									dx: bgObj.width*0.05-10,
									dy: bgObj.height - bgObj.width*0.25-10,
									width: bgObj.width * 0.2 +20,
									height:bgObj.width * 0.2 +20
								},{
										type: 'qrcode',
										text: url,
										size: bgObj.width * 0.2,
										dx: bgObj.width*0.05,
										dy: bgObj.height - bgObj.width*0.25,
										background: '#ffffff'
									}
								]);
							})
						},
						setCanvasWH: ({
							bgObj,
							type,
							bgScale
						}) => { // 为动态设置画布宽高的方法，
							this.poster = bgObj;
						}
					});
					this.poster.finalPath = d.poster.tempFilePath;
					this.qrShow = true;
				} catch (e) {
					_app.hideLoading();
					_app.showToast(JSON.stringify(e));
				}
			},
			getUser(){
				const that = this
				User.user(function(res){
					that.uuid = res.uuid
				})
			},
			hideQr() {
				this.qrShow = false;
			},
			// 预览图片
			previewImage(){
				uni.previewImage({
					urls: [this.poster.finalPath],
					longPressActions: {
						itemList: ['发送给朋友', '保存图片'],
						success: function(data) {
							console.log('1111')
						},
						fail: function(err) {
							console.log('err',err)
						}
					}
				})
			},
			toJSON(){
				
			}
		}
	}
</script>

<style lang='scss'>
	.content{
		.poster{
			position: fixed;
			top: 0;
			left: 0;
		}
		.title{
			font-size: 50upx;
			text-align: center;
			line-height: 120upx;
		}
		.describe{
			line-height: 60upx;
		}
		.img{
			margin-top:40upx;
			text-align: center;
			image{
				width: 80%;
			}
		}
		.sub{
			.cu-btn{
				width: 80%;
				line-height: 80upx;
				height: 80upx;
				margin-bottom: 80upx;
				margin-top:60upx;
			}
		}
		.flow{
			position: relative;
			width: 100%;
			.text-gray{
				padding-left:20upx;
				padding-right:20upx;
			}
			.ll{
				text-align: center;
				float:left;
				font-size: 24upx;
				.icon{
					line-height: 80upx;
				}
				.name{
					line-height: 60upx;
				}
				.icon-box{
					text-align: center;
					line-height: 80upx;
					width: 80upx;
					height:80upx;
					border-radius: 50%;
					span{
						font-size: 36upx;
					}
				}
			}
		}
		.hideCanvasView {
			position: relative;
		}
		
		.hideCanvas {
			position: fixed;
			top: -99999upx;
			left: -99999upx;
			z-index: -99999;
		}
		.flex_row_c_c {
			display: flex;
			flex-direction: row;
			justify-content: center;
			align-items: center;
		}
		
		.modalView {
			width: 100%;
			height: 100%;
			position: fixed;
			top: 0;
			left: 0;
			opacity: 0;
			background: rgba(0, 0, 0, 0.6);
			transition: all .3s ease-in-out;
			pointer-events: none;
			backface-visibility: hidden;
			z-index: 999;
		}
		.modalView.show {
			opacity: 1;
			transform: scale(1);
			pointer-events: auto;
		}
		.flex_column {
			display: flex;
			flex-direction: column;
			width: 600upx;
		}
		
		.backgroundColor-white {
			background-color: white;
		}
		
		.border_radius_10px {
			border-radius: 10px;
		}
		
		.padding1vh {
			padding: 1vh;
		}
		
		.posterImage {
			width: 100%;
		}
		
		.flex_row {
			display: flex;
			flex-direction: row;
		}
		
		.marginTop2vh {
			margin-top: 2vh;
		}
	}
</style>
