class FriendIndexTranslator
  def self.translate(friend_index_model)
    view_model = FriendIndexViewModel.new
    view_model.total_count = friend_index_model.total_count
    view_model.limit = friend_index_model.limit
    view_model.offset = friend_index_model.offset
    view_model.friends = friend_index_model.friends
    view_model
  end
end
