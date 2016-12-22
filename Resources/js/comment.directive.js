app.directive('commentGroup', ['$http', '$rootScope', function($http, $rootScope) {
        return {
            templateUrl: '/templates/comment/comment_group.tpl.html',
            scope: {
                threadObjType: '@',
                threadObjId: '=',
                showNewCommentBox: '=?',
                txtNoComment: '@',
                commentCount: '=?',
                onLoaded: '&?'
            },
            link: function(scope, element, attr) {
                scope.loaded = false;
                if (!angular.isDefined(scope.showNewCommentBox)) scope.showNewCommentBox = true;
                if (!angular.isDefined(scope.txtNoComment)) scope.txtNoComment = 'Không có bình luận.';
                var threadCode = null;

                scope.load = function() {
                    if (!threadCode) return;
                    scope.loading = true;
                    $http({
                        method: 'GET',
                        url: Routing.generate('api_comment_list', {code: threadCode})
                    }).then(function(response) {
                        scope.comments = response.data.data;
                        scope.commentCount = scope.comments.length;
                        scope.loading = false;
                        scope.loaded = true;
                        if (angular.isDefined(scope.onLoaded)) scope.onLoaded();
                    });
                }

                scope.$watch('threadObjId', function(newValue, oldValue) {
                    if (isNaN(newValue)) return;
                    threadCode = scope.threadObjType + '-' + scope.threadObjId;
                    scope.load();
                });

                scope.send = function() {
                    if (!threadCode) return;
                    scope.sending = true;
                    $http({
                        method: 'POST',
                        url: Routing.generate('api_comment_post', {objType: scope.threadObjType, objId: scope.threadObjId}),
                        data: { content: scope.commentContent },
                    }).then(function(response) {
                        if (response.data.status == 'success') {
                            scope.load(threadCode);
                            scope.commentContent = '';
                        } else {
                            scope.errorMessage = response.data.message;
                        }
                        scope.showReply = false;
                        scope.sending = false;
                    });
                }
            },
        };
    }])
    .directive('comment', ['$http', '$rootScope', function($http, $rootScope) {
        return {
            templateUrl: '/templates/comment/comment.tpl.html',
            scope: {
                comment: '=ngData',
                noReply: '@',
                noRootComment: '@?',
                alwaysShowReplyBox: '@?',
                commentCount: '=?',
                onLoaded: '&?',
            },
            link: function(scope, element, attr) {
                scope.noRootComment = angular.isDefined(scope.noRootComment);
                scope.alwaysShowReplyBox = angular.isDefined(scope.alwaysShowReplyBox);

                $http.get(Routing.generate('api_comment_permission', {id: scope.comment.id})).then(function(response) {
                    scope.permission = response.data.data;
                });

                scope.load = function() {
                    scope.loading = true;
                    $http({
                        method: 'GET',
                        url: Routing.generate('api_comment_list', {code: scope.comment.threadCode}) + '?parent=' + scope.comment.id
                    }).then(function(response) {
                        scope.comments = response.data.data;
                        scope.commentCount = scope.comments.length;
                        scope.loading = false;
                        if (angular.isDefined(scope.onLoaded)) scope.onLoaded();
                    });
                }

                scope.delete = function() {
                    if (!confirm("Bạn muốn xoá?")) return;
                    $http({
                        method: 'DELETE',
                        url: Routing.generate('api_comment', {id: scope.comment.id})
                    }).then(function(response) {
                        scope.comment = null;
                    })
                }

                scope.send = function() {
                    scope.sending = true;
                    $http({
                        method: 'POST',
                        url: Routing.generate('api_comment_post', {objType: scope.comment.threadObjType, objId: scope.comment.threadObjId}),
                        data: { content: scope.commentContent, parent: scope.comment.id },
                    }).then(function(response) {
                        if (response.data.status == 'success') {
                            scope.load();
                            scope.commentContent = '';
                        } else {
                            scope.errorMessage = response.data.message;
                        }
                        scope.showReply = false;
                        scope.sending = false;
                    });
                }

                scope.load();
            },
        };
    }])
