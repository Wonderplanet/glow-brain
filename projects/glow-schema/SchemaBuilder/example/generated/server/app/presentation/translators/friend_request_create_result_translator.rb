class FriendRequestCreateResultTranslator
  def self.translate(friend_request_create_result_model)
    view_model = FriendRequestCreateResultViewModel.new
    view_model.friend_request = friend_request_create_result_model.friend_request
    view_model.updated_mission = friend_request_create_result_model.updated_mission
    view_model
  end
end
