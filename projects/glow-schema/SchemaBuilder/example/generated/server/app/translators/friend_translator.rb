class FriendTranslator
  def self.translate(friend_model)
    view_model = FriendViewModel.new
    view_model.user_name = friend_model.user_name
    view_model.user_id = friend_model.user_id
    view_model.rank_level = friend_model.rank_level
    view_model.description = friend_model.description
    view_model.last_accessed_at = friend_model.last_accessed_at
    view_model.character_variant = friend_model.character_variant
    view_model
  end
end
