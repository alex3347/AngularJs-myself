var app = angular.module("36krDemoApp", []);
var n = 2;//翻页的计数器
app.controller("ajaxXml", function($scope, $http, $timeout) {
	$scope.LoadXml = function() {
		$http.get("feed.xml")//涉及到跨域问题，google的rss解析停止服务了，试了下php自己弄解析，发现时间可能不够用，所以这里用了get方法请求本地文件
			.success(function(data) {
				$scope.feeds = [];
				var rawItems = angular.element(data.trim()).find("item");//找到xml里的item
				for(var i = 0; i < rawItems.length; i++) {//轮询将每个item的属性提取出来，组成单个对象
					var feedItem = rawItems.eq(i);
					linkItem = feedItem.find("link");
					$scope.feeds.push({
						title: feedItem.find("title").text().replace(/\<\!\[CDATA\[(.*)\]\]\>/g, '$1'),
						author: feedItem.find("author").text(),
						category: feedItem.find("category").text(),
						publishedDate: feedItem.find("pubDate").html().replace(/\<\!--\[CDATA\[(.+,\s)(\d{2})\s(\w{3})\s(\d{4}\s)(\d{2}:\d{2})(.*)\]\]--\>/g, function($1, $2, $3, $4, $5, $6) {
							var engToNum = {
								Jan: 1,Feb: 2,Mar: 3,Apr: 4,May: 5,Jun: 6,
								Jul: 7,Aug: 8,Sep: 9,Oct: 10,Nov: 11,Dec: 12
							};
							var mon = engToNum[$4];
							return mon + ' 月 ' + $3 + ' 日 ' + $6;
						}),
						contentSnippet: feedItem.find("description").text().substring(0, 54) + '......',
						imgSrc: feedItem.find("img")[0] == undefined ? 'temp.jpg' : feedItem.find("img")[0].src.split('!')[0],//有些item里没有图片地址，这里我自己加了个本地地址
						link: linkItem[0].nextSibling.data
					});
				}
			})
			.error(function() {
				alert("读取错误");
			})
	};
	$scope.showNum = 10;//每次加载item数量
	$scope.$on("BUSY", function() {
		$scope.busy = true;//true表示加载中...可见
	});
	$scope.$on("NOTBUSY", function() {
		$scope.busy = false;
	});
	$scope.loadMore = function() {//滑动到尾部时加载的函数
		if(n < 11) {
			$scope.$emit("BUSY");
			$scope.showNum = 10 * n;
			$timeout(function() {
				return $scope.$emit("NOTBUSY");
			}, 3000);
			n++;
		} else {
			$scope.$emit("NOTBUSY");
			return;
		}
	};
});
app.directive('infiniteScroll', ['$rootScope', '$window', '$timeout', function($rootScope, $window, $timeout) {//这个是网上找到的比较成熟的方法，缺点是用了jquery，时间有限，后面我会试着用原生方法自己实现
	return {
		link: function(scope, elem, attrs) {
			var checkWhenEnabled, handler, scrollDistance, scrollEnabled;
			$window = angular.element($window);
			scrollDistance = 0;
			if(attrs.infiniteScrollDistance != null) { //接收并返回触发加载更多的距离   
				scope.$watch(attrs.infiniteScrollDistance, function(value) {
					return scrollDistance = parseInt(value, 10);
				});
			}
			scrollEnabled = true;
			checkWhenEnabled = false;
			if(attrs.infiniteScrollDisabled != null) {
				scope.$watch(attrs.infiniteScrollDisabled, function(value) {
					scrollEnabled = !value;
					if(scrollEnabled && checkWhenEnabled) {
						checkWhenEnabled = false;
						return handler();
					}
				});
			}
			handler = function() {
				var elementBottom, remaining, shouldScroll, windowBottom;
				windowBottom = $window.height() + $window.scrollTop(); //所选中元素展示框的高度 + 滑动条向下滑动的距离  
				elementBottom = elem.offset().top + elem.height(); //页面的总长度  
				remaining = elementBottom - windowBottom;
				shouldScroll = remaining <= $window.height() * scrollDistance;
				if(shouldScroll && scrollEnabled) { //达到可加载距离  
					if($rootScope.$$phase) {
						return scope.$eval(attrs.infiniteScroll);
					} else {
						if(remaining <= 50) {
							scope.loadMore(); //在此调用加载更多的函数  
						}
						return scope.$apply(attrs.infiniteScroll);
					}
				} else if(shouldScroll) {
					return checkWhenEnabled = true;
				}
			};
			$window.on('scroll', handler); //监控scroll滑动则运行handler函数   
			scope.$on('$destroy', function() { //离开页面则关闭scroll与handler的绑定  
				return $window.off('scroll', handler);
			});
			return $timeout((function() {
				if(attrs.infiniteScrollImmediateCheck) {
					if(scope.$eval(attrs.infiniteScrollImmediateCheck)) {
						return handler();
					}
				} else {
					return handler();
				}
			}), 0);
		}
	};
}]);