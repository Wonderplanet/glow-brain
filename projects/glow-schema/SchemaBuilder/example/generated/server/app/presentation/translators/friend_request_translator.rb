class FriendRequestTranslator
  def self.translate(friend_request_model)
    view_model = FriendRequestViewModel.new
    view_model.user_name = friend_request_model.user_name
    view_model.user_id = friend_request_model.user_id
    view_model.rank_level = friend_request_model.rank_level
    view_model.description = friend_request_model.description
    view_model.last_accessed_at = friend_request_model.last_accessed_at
    view_model.request_type = friend_request_model.request_type
    view_model.expire_at = friend_request_model.expire_at
    view_model.character_variant = friend_request_model.character_variant
    view_model
  end
end
