class FriendRequestIndexTranslator
  def self.translate(friend_request_index_model)
    view_model = FriendRequestIndexViewModel.new
    view_model.total_count = friend_request_index_model.total_count
    view_model.limit = friend_request_index_model.limit
    view_model.offset = friend_request_index_model.offset
    view_model.friend_requests = friend_request_index_model.friend_requests
    view_model
  end
end
